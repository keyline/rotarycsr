<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportSelectedApplicantsRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ApplicantExportData;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SelectedApplicantExportController extends Controller
{
    public function __invoke(ExportSelectedApplicantsRequest $request, ApplicantExportData $exportData): Response|BinaryFileResponse
    {
        $validated = $request->validated();
        $applicants = User::query()
            ->where('role', 'applicant')
            ->whereKey($validated['applicant_ids'])
            ->with(['application.reviewer', 'application.supportingDocuments'])
            ->orderBy('name')
            ->get();
        $records = $applicants->map(fn (User $applicant): array => $exportData->for($applicant));

        ActivityLogger::log(
            'admin.applicants_exported',
            'Exported '.$applicants->count().' selected applicant record(s) to '.strtoupper($validated['format']).'.',
            properties: ['applicant_ids' => $applicants->pluck('id')->all(), 'format' => $validated['format']],
        );

        return $validated['format'] === 'pdf' ? $this->pdf($records) : $this->spreadsheet($records);
    }

    /** @param Collection<int, array<string, string>> $records */
    private function pdf(Collection $records): Response
    {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(View::make('admin.applicants.export-pdf', ['records' => $records])->render());
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename('pdf').'"',
        ]);
    }

    /** @param Collection<int, array<string, string>> $records */
    private function spreadsheet(Collection $records): BinaryFileResponse
    {
        $headers = $records->flatMap(fn (array $record): array => array_keys($record))->unique()->values();
        $rows = $records->map(fn (array $record): array => $headers
            ->map(fn (string $header): string => $this->spreadsheetValue($record[$header] ?? ''))
            ->all());

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Applicants');
        $sheet->fromArray($headers->all(), null, 'A1');
        $sheet->fromArray($rows->all(), null, 'A2');

        $lastColumn = Coordinate::stringFromColumnIndex($headers->count());
        $lastRow = $records->count() + 1;
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '17458F']],
        ]);
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        foreach (range(1, $headers->count()) as $column) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(24);
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'rotary-csr-export-');
        abort_if($temporaryPath === false, 500, 'Unable to prepare the spreadsheet export.');
        (new Xlsx($spreadsheet))->save($temporaryPath);
        $spreadsheet->disconnectWorksheets();

        return response()->download($temporaryPath, $this->filename('xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function filename(string $extension): string
    {
        return 'rotary-csr-selected-applicants-'.now()->format('Y-m-d-His').'.'.$extension;
    }

    private function spreadsheetValue(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) === 1 ? "'{$value}" : $value;
    }
}

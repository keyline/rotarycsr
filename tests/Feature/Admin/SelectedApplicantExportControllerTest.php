<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\User;
use App\Services\ApplicantExportData;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SelectedApplicantExportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_export_selected_complete_records_as_a_spreadsheet(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $selected = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
            'name' => 'Selected Applicant',
            'email' => 'selected@example.com',
        ]);
        $notSelected = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'individual',
            'name' => 'Excluded Applicant',
        ]);
        $application = Application::create([
            'user_id' => $selected->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'reference_number' => 'RICSR/CP/0001',
            'company_size' => 'mega',
            'company_turnover' => '5000.25',
            'project_name' => 'Clean Water Programme',
            'intervention_design' => '<p>Safe water access</p>',
            'unique_feature' => '=HYPERLINK("https://invalid.example")',
        ]);
        Application::create([
            'user_id' => $notSelected->id,
            'applicant_type' => 'individual',
            'status' => 'submitted',
        ]);
        Storage::disk('local')->put('supporting/evidence.jpg', 'image');
        Storage::disk('local')->put('supporting/evidence.mp4', 'video');
        $image = $application->supportingDocuments()->create([
            'path' => 'supporting/evidence.jpg',
            'original_name' => 'evidence.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 5,
            'media_type' => 'image',
        ]);
        $video = $application->supportingDocuments()->create([
            'path' => 'supporting/evidence.mp4',
            'original_name' => 'evidence.mp4',
            'mime_type' => 'video/mp4',
            'size' => 5,
            'media_type' => 'video',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.applicants.export-selected'), [
            'applicant_ids' => [$selected->id],
            'format' => 'xlsx',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $spreadsheet = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();
        $record = array_combine($rows[0], $rows[1]);

        $this->assertSame('Selected Applicant', $record['Name']);
        $this->assertSame('Mega', $record['Company Size']);
        $this->assertArrayNotHasKey('Turnover FY 2025–2026 (₹ Crore)', $record);
        $this->assertSame('RICSR/CP/0001', $record['Application ID']);
        $this->assertArrayNotHasKey('Applicant ID', $record);
        foreach ([
            'Email Verified',
            'Registered At',
            'Blacklisted At',
            'Application Status',
            'Review Status',
            'Submitted At',
            'Reviewed At',
            'Reviewed By',
            'Application Updated At',
        ] as $removedHeader) {
            $this->assertArrayNotHasKey($removedHeader, $record);
        }
        $this->assertSame('Clean Water Programme', $record['Project Title']);
        $this->assertSame('Safe water access', $record['Brief Project Concept / Design']);
        $this->assertSame('\'=HYPERLINK("https://invalid.example")', $record['Unique Feature of the Initiative']);
        $this->assertSame(route('application.supporting-documents.preview', $image), $record['Image 1 Link']);
        $this->assertSame(route('application.supporting-documents.preview', $video), $record['Video 1 Link']);

        $imageColumn = array_search('Image 1 Link', $rows[0], true);
        $videoColumn = array_search('Video 1 Link', $rows[0], true);
        $this->assertNotFalse($imageColumn);
        $this->assertNotFalse($videoColumn);
        $this->assertSame(
            route('application.supporting-documents.preview', $image),
            $spreadsheet->getActiveSheet()->getCell(Coordinate::stringFromColumnIndex($imageColumn + 1).'2')->getHyperlink()->getUrl(),
        );
        $this->assertSame(
            route('application.supporting-documents.preview', $video),
            $spreadsheet->getActiveSheet()->getCell(Coordinate::stringFromColumnIndex($videoColumn + 1).'2')->getHyperlink()->getUrl(),
        );
        $this->assertCount(2, $rows);
        $this->assertNotContains('Excluded Applicant', $rows[1]);
        $spreadsheet->disconnectWorksheets();
    }

    public function test_admin_can_export_selected_records_as_a_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'individual']);
        Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'individual',
            'status' => 'submitted',
            'ind_designation' => 'CSR Director',
            'ind_projects' => [['problem' => 'Education access', 'outcomes' => '1,000 students supported']],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.applicants.export-selected'), [
            'applicant_ids' => [$applicant->id],
            'format' => 'pdf',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_pdf_template_renders_image_thumbnails_and_full_video_links(): void
    {
        Storage::fake('local');
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'reference_number' => 'RICSR/CP/0017',
        ]);
        Storage::disk('local')->put('supporting/evidence.png', 'image');
        Storage::disk('local')->put('supporting/evidence.mp4', 'video');
        $application->supportingDocuments()->create([
            'path' => 'supporting/evidence.png',
            'original_name' => 'evidence.png',
            'mime_type' => 'image/png',
            'size' => 5,
            'media_type' => 'image',
        ]);
        $video = $application->supportingDocuments()->create([
            'path' => 'supporting/evidence.mp4',
            'original_name' => 'evidence.mp4',
            'mime_type' => 'video/mp4',
            'size' => 5,
            'media_type' => 'video',
        ]);
        $applicant->load('application.supportingDocuments');
        $record = app(ApplicantExportData::class)->for($applicant, true);

        $html = view('admin.applicants.export-pdf', ['records' => collect([$record])])->render();

        $this->assertStringContainsString('max-height: 200px', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString(route('application.supporting-documents.preview', $video), $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringNotContainsString('Email Verified', $html);
        $this->assertStringNotContainsString('Application Updated At', $html);
    }

    public function test_export_requires_at_least_one_valid_applicant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('admin.applicants.index'))
            ->post(route('admin.applicants.export-selected'), ['format' => 'pdf']);

        $response->assertRedirect(route('admin.applicants.index'));
        $response->assertSessionHasErrors('applicant_ids');
    }

    public function test_applicant_cannot_export_other_applicant_records(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant']);
        $otherApplicant = User::factory()->create(['role' => 'applicant']);

        $response = $this->actingAs($applicant)->post(route('admin.applicants.export-selected'), [
            'applicant_ids' => [$otherApplicant->id],
            'format' => 'pdf',
        ]);

        $response->assertForbidden();
    }
}

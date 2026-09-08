<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantController extends Controller
{
    public function index(Request $request): View
    {
        $applicants = $this->filtered($request)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.applicants.index', [
            'applicants' => $applicants,
            'filters' => $request->only(['search', 'applicant_type', 'status']),
        ]);
    }

    public function show(User $applicant): View
    {
        abort_if($applicant->role !== 'applicant', 404);

        return view('admin.applicants._show', [
            'applicant' => $applicant,
            'application' => $applicant->application,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $applicants = $this->filtered($request)->orderBy('created_at')->get();

        ActivityLogger::log(
            'admin.applicants_exported',
            'Exported '.$applicants->count().' applicant record(s) to CSV.',
            properties: $request->only(['search', 'applicant_type', 'status'])
        );

        $columns = ['ID', 'Name', 'Company Name', 'Email', 'Category', 'Verified', 'Registered At'];

        return response()->streamDownload(function () use ($applicants, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($applicants as $applicant) {
                fputcsv($handle, [
                    $applicant->id,
                    $applicant->name,
                    $applicant->company_name,
                    $applicant->email,
                    $applicant->applicant_type,
                    $applicant->email_verified_at ? 'Yes' : 'No',
                    $applicant->created_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, 'rotary-csr-applicants-'.now()->format('Y-m-d-His').'.csv');
    }

    private function filtered(Request $request)
    {
        return User::where('role', 'applicant')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('applicant_type'), function ($query) use ($request) {
                $query->where('applicant_type', $request->string('applicant_type'));
            })
            ->when($request->input('status') === 'verified', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when($request->input('status') === 'pending', fn ($query) => $query->whereNull('email_verified_at'));
    }
}

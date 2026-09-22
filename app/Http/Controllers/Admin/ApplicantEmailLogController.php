<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApplicantMessageMailer;
use Illuminate\View\View;

class ApplicantEmailLogController extends Controller
{
    public function __invoke(User $applicant): View
    {
        abort_if($applicant->role !== 'applicant', 404);

        return view('admin.applicants.email-logs', [
            'applicant' => $applicant,
            'emailLogs' => $applicant->emailLogs()->paginate(20),
            'defaultMessage' => ApplicantMessageMailer::GENERAL_MESSAGE,
        ]);
    }
}

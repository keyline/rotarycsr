<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendApplicantEmailRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ApplicantMessageMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ApplicantEmailController extends Controller
{
    public function __invoke(
        SendApplicantEmailRequest $request,
        User $applicant,
        ApplicantMessageMailer $mailer,
    ): RedirectResponse {
        abort_if($applicant->role !== 'applicant', 404);

        $validated = $request->validated();

        if ($validated['message_type'] === 'award' && $applicant->application?->review_status !== 'approved') {
            throw ValidationException::withMessages([
                'message_type' => 'Award emails can only be sent to approved applicants.',
            ]);
        }

        if ($validated['message_type'] === 'award' && $applicant->application?->award_winner_at === null) {
            throw ValidationException::withMessages([
                'message_type' => 'Mark the applicant as an award winner before sending the award email.',
            ]);
        }

        $sent = $mailer->send(
            $applicant,
            $validated['subject'],
            $validated['message'],
            $validated['message_type'],
            $request->user(),
        );

        ActivityLogger::log(
            $validated['message_type'] === 'award' ? 'admin.award_notification_sent' : 'admin.applicant_email_sent',
            ($validated['message_type'] === 'award' ? 'Award email' : 'Direct email').' sent to '.$applicant->email.'.',
            $applicant,
            [
                'message_type' => $validated['message_type'],
                'subject' => $validated['subject'],
                'sent' => $sent,
            ],
        );

        if (! $sent) {
            return back()->with('error', 'The email could not be sent. Check the SMTP configuration and logs.');
        }

        return back()->with('status', $validated['message_type'] === 'award'
            ? 'Award email sent to '.$applicant->name.'.'
            : 'Email sent to '.$applicant->name.'.');
    }
}

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
                'message_type' => 'Award notifications can only be sent to approved applicants.',
            ]);
        }

        if ($validated['message_type'] === 'award') {
            $applicant->application->update([
                'award_winner_at' => now(),
                'award_winner_by' => $request->user()->id,
            ]);
        }

        $sent = $mailer->send($applicant, $validated['subject'], $validated['message']);

        ActivityLogger::log(
            $validated['message_type'] === 'award' ? 'admin.award_notification_sent' : 'admin.applicant_email_sent',
            ($validated['message_type'] === 'award' ? 'Award notification' : 'Direct email').' sent to '.$applicant->email.'.',
            $applicant,
            [
                'message_type' => $validated['message_type'],
                'subject' => $validated['subject'],
                'sent' => $sent,
                'marked_as_winner' => $validated['message_type'] === 'award',
            ],
        );

        if (! $sent) {
            return back()->with('error', 'The email could not be sent. Check the Brevo configuration and logs.');
        }

        return back()->with('status', $validated['message_type'] === 'award'
            ? 'Award notification sent to '.$applicant->name.'.'
            : 'Email sent to '.$applicant->name.'.');
    }
}

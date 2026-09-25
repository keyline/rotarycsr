<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateApplicationDecisionRequest;
use App\Models\Application;
use App\Services\ActivityLogger;
use App\Services\ApplicationDecisionMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationDecisionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        UpdateApplicationDecisionRequest $request,
        Application $application,
        ApplicationDecisionMailer $mailer,
    ): RedirectResponse {
        if (! $application->isSubmitted()) {
            throw ValidationException::withMessages([
                'decision' => 'Only submitted applications can be reviewed.',
            ]);
        }

        $decision = $request->validated('decision');

        DB::transaction(function () use ($application, $decision, $request): void {
            $application->update([
                'review_status' => $decision,
                'reviewed_at' => now(),
                'reviewed_by' => $request->user()->id,
            ]);

            $application->user->update([
                'blacklisted_at' => null,
                'blacklisted_by' => null,
            ]);
        });

        $mailSent = $mailer->send($application->fresh('user'), $request->user());

        ActivityLogger::log(
            'admin.application_reviewed',
            "Application marked as {$decision}.",
            $application,
            ['decision' => $decision, 'notification_sent' => $mailSent],
        );

        $message = 'Application marked as '.ucfirst($decision).'.';

        if (! $mailSent) {
            $message .= ' The email could not be sent; check the SMTP configuration and logs.';
        }

        return back()->with('status', $message);
    }
}

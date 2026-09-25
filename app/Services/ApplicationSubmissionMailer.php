<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;

class ApplicationSubmissionMailer
{
    public function __construct(private readonly BrevoMailer $mailer) {}

    /**
     * @return array{applicant_sent: bool, admin_recipients: int, admin_sent: int}
     */
    public function send(Application $application): array
    {
        $application->loadMissing('user');
        $applicant = $application->user;
        $awardName = $application->applicant_type === 'corporate'
            ? 'CSR Corporate Excellence Award'
            : 'Corporate CSR Leader Award';

        $applicantSent = $this->mailer->send(
            $applicant->email,
            $applicant->name,
            'Your Rotary CSR Awards application has been submitted',
            $this->applicantBody($application, $awardName),
        );

        $admins = User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->get(['id', 'name', 'email']);
        $adminSent = 0;

        foreach ($admins as $admin) {
            $sent = $this->mailer->send(
                $admin->email,
                $admin->name,
                "New Rotary CSR Awards application: {$application->reference_number}",
                $this->adminBody($application, $awardName),
            );

            if ($sent) {
                $adminSent++;
            }
        }

        return [
            'applicant_sent' => $applicantSent,
            'admin_recipients' => $admins->count(),
            'admin_sent' => $adminSent,
        ];
    }

    private function applicantBody(Application $application, string $awardName): string
    {
        $applicantName = e($application->user->name);
        $escapedAwardName = e($awardName);
        $referenceNumber = e((string) $application->reference_number);

        return <<<HTML
            <div style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937">
                <p>Dear {$applicantName},</p>
                <p>Your application for the <strong>{$escapedAwardName}</strong> has been submitted successfully.</p>
                <p><strong>Application ID:</strong> {$referenceNumber}</p>
                <p>The Rotary District 3291 CSR Awards team will review your application and share further updates with you by email.</p>
                <p>Regards,<br>Rotary District 3291 CSR Awards Team</p>
            </div>
            HTML;
    }

    private function adminBody(Application $application, string $awardName): string
    {
        $applicantName = e($application->user->name);
        $applicantEmail = e($application->user->email);
        $escapedAwardName = e($awardName);
        $referenceNumber = e((string) $application->reference_number);
        $submittedAt = e($application->submitted_at?->format('d M Y, h:i A') ?? '');

        return <<<HTML
            <div style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937">
                <p>A new Rotary CSR Awards application has been submitted.</p>
                <ul>
                    <li><strong>Application ID:</strong> {$referenceNumber}</li>
                    <li><strong>Applicant:</strong> {$applicantName}</li>
                    <li><strong>Email:</strong> {$applicantEmail}</li>
                    <li><strong>Award:</strong> {$escapedAwardName}</li>
                    <li><strong>Submitted:</strong> {$submittedAt}</li>
                </ul>
                <p>Please sign in to the admin dashboard to review the application.</p>
            </div>
            HTML;
    }
}

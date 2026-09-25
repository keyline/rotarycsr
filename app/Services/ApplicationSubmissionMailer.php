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
            <p style="margin: 0 0 16px;">Dear {$applicantName},</p>
            <p style="margin: 0 0 20px;">Thank you for submitting your application for the <strong>{$escapedAwardName}</strong>.</p>
            <p style="margin: 0 0 20px; padding: 14px 18px; border-left: 3px solid #c8942f; background-color: #f3f6fa; color: #0b3763;"><strong>Application ID</strong><br>{$referenceNumber}</p>
            <p style="margin: 0 0 20px;">Our team will review your application and send any updates to this email address.</p>
            <p style="margin: 0;">Kind regards,<br>Rotary District 3291 CSR Awards Team</p>
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
            <p style="margin: 0 0 18px;">A new Rotary CSR Awards application is ready for review.</p>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px; border: 1px solid #e7edf3; border-radius: 8px; background-color: #f9fbfd; font-size: 14px;">
                <tr><td style="padding: 10px 16px; color: #64748b;">Application ID</td><td style="padding: 10px 16px; color: #0b3763; font-weight: 700;">{$referenceNumber}</td></tr>
                <tr><td style="padding: 10px 16px; color: #64748b;">Applicant</td><td style="padding: 10px 16px;">{$applicantName}</td></tr>
                <tr><td style="padding: 10px 16px; color: #64748b;">Email</td><td style="padding: 10px 16px;">{$applicantEmail}</td></tr>
                <tr><td style="padding: 10px 16px; color: #64748b;">Award</td><td style="padding: 10px 16px;">{$escapedAwardName}</td></tr>
                <tr><td style="padding: 10px 16px; color: #64748b;">Submitted</td><td style="padding: 10px 16px;">{$submittedAt}</td></tr>
            </table>
            <p style="margin: 0;">Sign in to the admin dashboard to review the application.</p>
            HTML;
    }
}

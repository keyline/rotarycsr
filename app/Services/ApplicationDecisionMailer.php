<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicantEmailLog;
use App\Models\Setting;
use App\Models\User;

class ApplicationDecisionMailer
{
    public const DEFAULT_TEMPLATES = [
        'approved' => [
            'subject' => 'Your Rotary CSR Awards application has been approved',
            'body' => '<p>Dear {name},</p><p>We are pleased to confirm that your {application_type} application for the Rotary CSR Awards 2026 has been approved.</p><p>We will share further details with you by email.</p><p>Kind regards,<br>Rotary District 3291 CSR Awards Team</p>',
        ],
        'rejected' => [
            'subject' => 'Update on your Rotary CSR Awards application',
            'body' => '<p>Dear {name},</p><p>Thank you for submitting your {application_type} application for the Rotary CSR Awards 2026.</p><p>After careful review, your application was not selected. We appreciate the time and care you put into your submission.</p><p>Kind regards,<br>Rotary District 3291 CSR Awards Team</p>',
        ],
    ];

    public function __construct(private readonly BrevoMailer $mailer) {}

    public function send(Application $application, ?User $sentBy = null): bool
    {
        $application->loadMissing('user');
        $decision = $application->review_status;
        $template = self::DEFAULT_TEMPLATES[$decision];
        $subject = Setting::get("decision_mail_{$decision}_subject", $template['subject']);
        $body = Setting::get("decision_mail_{$decision}_body", $template['body']);
        $applicant = $application->user;

        $subjectReplacements = [
            '{name}' => $applicant->name,
            '{decision}' => ucfirst($decision),
            '{application_type}' => ucfirst($application->applicant_type),
        ];
        $bodyReplacements = [
            '{name}' => e($applicant->name),
            '{decision}' => e(ucfirst($decision)),
            '{application_type}' => e(ucfirst($application->applicant_type)),
        ];
        $resolvedSubject = strip_tags(strtr($subject, $subjectReplacements));
        $resolvedBody = strtr($body, $bodyReplacements);

        $sent = $this->mailer->send($applicant->email, $applicant->name, $resolvedSubject, $resolvedBody);

        if ($sent) {
            ApplicantEmailLog::create([
                'applicant_id' => $applicant->id,
                'application_id' => $application->id,
                'sent_by' => $sentBy?->id,
                'type' => $decision,
                'recipient_email' => $applicant->email,
                'recipient_name' => $applicant->name,
                'subject' => $resolvedSubject,
                'body' => $resolvedBody,
                'sent_at' => now(),
            ]);
        }

        return $sent;
    }
}

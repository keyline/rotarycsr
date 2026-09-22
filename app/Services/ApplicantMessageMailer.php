<?php

namespace App\Services;

use App\Models\ApplicantEmailLog;
use App\Models\User;

class ApplicantMessageMailer
{
    public const AWARD_SUBJECT = 'Congratulations — Rotary CSR Awards 2026 Winner Notification';

    public const AWARD_MESSAGE = "Dear {name},\n\nCongratulations! We are delighted to inform you that you have been selected for recognition at the Rotary CSR Awards 2026.\n\nFurther ceremony and award details will be shared with you shortly.\n\nRegards,\nRotary District 3291 CSR Awards Team";

    public const GENERAL_MESSAGE = "Dear {name},\n\n\n\nRegards,\nRotary District 3291 CSR Awards Team";

    public function __construct(private readonly BrevoMailer $mailer) {}

    public function send(
        User $applicant,
        string $subject,
        string $message,
        string $type = 'general',
        ?User $sentBy = null,
    ): bool
    {
        $replacements = [
            '{name}' => $applicant->name,
            '{email}' => $applicant->email,
            '{application_type}' => ucfirst((string) $applicant->applicant_type),
            '{company_name}' => (string) $applicant->company_name,
        ];

        $resolvedSubject = strip_tags(strtr($subject, $replacements));
        $resolvedMessage = strtr($message, $replacements);
        $html = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937">'
            .nl2br(e($resolvedMessage), false)
            .'</div>';

        $sent = $this->mailer->send($applicant->email, $applicant->name, $resolvedSubject, $html);

        if ($sent) {
            ApplicantEmailLog::create([
                'applicant_id' => $applicant->id,
                'application_id' => $applicant->application?->id,
                'sent_by' => $sentBy?->id,
                'type' => $type,
                'recipient_email' => $applicant->email,
                'recipient_name' => $applicant->name,
                'subject' => $resolvedSubject,
                'body' => $html,
                'sent_at' => now(),
            ]);
        }

        return $sent;
    }
}

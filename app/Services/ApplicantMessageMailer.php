<?php

namespace App\Services;

use App\Models\ApplicantEmailLog;
use App\Models\User;

class ApplicantMessageMailer
{
    public const AWARD_SUBJECT = 'Congratulations! You Have Been Selected for an Award';

    public const AWARD_MESSAGE = "Dear {name},\n\nCongratulations!\n\nWe are pleased to inform you that you have been selected for an award. Your application and achievements have been recognized by our evaluation committee.\n\nFurther details regarding the award and related proceedings will be communicated to you shortly.\n\nOnce again, congratulations on this achievement, and we wish you continued success.\n\nWarm regards,\n{organization_name}\n{award_name}";

    public const ORGANIZATION_NAME = 'Rotary International District 3291';

    public const AWARD_NAME = 'Rotary CSR Awards 2026';

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
            '{organization_name}' => self::ORGANIZATION_NAME,
            '{award_name}' => self::AWARD_NAME,
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

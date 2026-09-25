<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    private const TTL_MINUTES = 10;

    public function __construct(private readonly BrevoMailer $mailer)
    {
    }

    /**
     * Generate a fresh OTP for the user, store its hash, and email it.
     * Returns the plain OTP in debug mode or when email delivery fails.
     */
    public function issue(User $user): ?string
    {
        $otp = (string) random_int(100000, 999999);

        $user->forceFill([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ])->save();

        $sent = $this->mailer->send(
            $user->email,
            $user->name,
            'Your Rotary CSR Awards verification code',
            $this->emailBody($user->name, $otp)
        );

        return (config('app.debug') || ! $sent) ? $otp : null;
    }

    public function verify(User $user, string $otp): bool
    {
        if (! $user->otp_code || ! $user->otp_expires_at) {
            return false;
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return false;
        }

        if (! Hash::check($otp, $user->otp_code)) {
            return false;
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        return true;
    }

    private function emailBody(string $name, string $otp): string
    {
        $recipientName = e($name);

        return <<<HTML
            <p style="margin: 0 0 16px;">Dear {$recipientName},</p>
            <p style="margin: 0 0 20px;">Use the verification code below to complete your Rotary CSR Awards registration.</p>
            <p style="margin: 0 0 20px; padding: 16px 20px; border: 1px solid #dbe4ee; border-radius: 8px; background-color: #f3f6fa; color: #17458f; font-size: 30px; font-weight: 700; letter-spacing: 5px; text-align: center;">{$otp}</p>
            <p style="margin: 0; color: #64748b; font-size: 13px;">This code expires in 10 minutes. If you did not request it, you can ignore this email.</p>
            HTML;
    }
}

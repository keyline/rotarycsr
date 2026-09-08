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
     * Returns the plain OTP only when the email could not be sent (local debug fallback).
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

        return $sent ? null : $otp;
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
        return <<<HTML
            <p>Hi {$name},</p>
            <p>Your verification code for the Rotary District 3291 CSR Awards portal is:</p>
            <p style="font-size:28px;font-weight:700;letter-spacing:4px;">{$otp}</p>
            <p>This code expires in 10 minutes. If you didn't request this, you can ignore this email.</p>
            HTML;
    }
}

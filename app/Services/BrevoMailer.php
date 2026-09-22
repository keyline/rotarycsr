<?php

namespace App\Services;

use App\Mail\TransactionalMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BrevoMailer
{
    public function send(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        try {
            Mail::to($toEmail, $toName)->send(new TransactionalMessage($subject, $htmlContent));

            return true;
        } catch (Throwable $exception) {
            Log::error('SMTP email delivery failed.', [
                'to' => $toEmail,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}

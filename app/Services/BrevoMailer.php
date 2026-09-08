<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoMailer
{
    /**
     * Send a transactional email via the Brevo (Sendinblue) HTTP API.
     *
     * Returns true on success, false on failure (failure is logged, never thrown,
     * so a missing/invalid API key degrades gracefully instead of crashing registration).
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $apiKey = config('services.brevo.api_key');

        if (blank($apiKey)) {
            Log::warning('Brevo API key is not configured; skipping email send.', ['to' => $toEmail]);

            return false;
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'email' => config('services.brevo.sender_email'),
                    'name' => config('services.brevo.sender_name'),
                ],
                'to' => [
                    ['email' => $toEmail, 'name' => $toName],
                ],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]);

            if ($response->failed()) {
                Log::error('Brevo email send failed.', [
                    'to' => $toEmail,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Brevo email send threw an exception.', [
                'to' => $toEmail,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

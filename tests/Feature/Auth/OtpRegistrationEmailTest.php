<?php

namespace Tests\Feature\Auth;

use App\Mail\TransactionalMessage;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OtpRegistrationEmailTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('applicantTypes')]
    public function test_registration_sends_an_otp_to_the_entered_email_address(string $applicantType): void
    {
        Mail::fake();

        $email = "{$applicantType}@example.com";
        $payload = [
            'name' => ucfirst($applicantType).' Applicant',
            'email' => $email,
            'applicant_type' => $applicantType,
        ];

        if ($applicantType === 'corporate') {
            $payload['company_name'] = 'Example Foundation';
        }

        $response = $this->post(route('register'), $payload);

        $response->assertRedirect(route('verification.otp'));
        Mail::assertSent(TransactionalMessage::class, function (TransactionalMessage $message) use ($email): bool {
            return $message->hasTo($email)
                && $message->messageSubject === 'Your Rotary CSR Awards verification code'
                && str_contains($message->htmlContent, 'expires in 10 minutes');
        });
    }

    public static function applicantTypes(): array
    {
        return [
            'corporate' => ['corporate'],
            'individual' => ['individual'],
        ];
    }

    public function test_verification_email_escapes_the_applicant_name(): void
    {
        Mail::fake();

        $response = $this->post(route('register'), [
            'name' => '<script>alert(1)</script>',
            'email' => 'unsafe-name@example.com',
            'applicant_type' => 'individual',
        ]);

        $response->assertRedirect(route('verification.otp'));
        Mail::assertSent(TransactionalMessage::class, function (TransactionalMessage $message): bool {
            return str_contains($message->htmlContent, '&lt;script&gt;')
                && ! str_contains($message->htmlContent, '<script>');
        });
    }
}

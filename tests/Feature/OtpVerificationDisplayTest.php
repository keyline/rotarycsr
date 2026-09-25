<?php

namespace Tests\Feature;

use App\Mail\TransactionalMessage;
use App\Services\BrevoMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OtpVerificationDisplayTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('applicantTypes')]
    public function test_registration_never_displays_the_emailed_code(string $applicantType): void
    {
        config()->set('app.debug', true);
        Mail::fake();

        $payload = [
            'name' => 'Applicant',
            'email' => "{$applicantType}@example.com",
            'applicant_type' => $applicantType,
        ];

        if ($applicantType === 'corporate') {
            $payload['company_name'] = 'Example Foundation';
        }

        $response = $this->post(route('register'), $payload);

        $response->assertRedirect(route('verification.otp'))
            ->assertSessionMissing('debug_otp');

        $emailedOtp = '';
        Mail::assertSent(TransactionalMessage::class, function (TransactionalMessage $message) use (&$emailedOtp): bool {
            if (preg_match('/>(\d{6})<\/p>/', $message->htmlContent, $matches) !== 1) {
                return false;
            }

            $emailedOtp = $matches[1];

            return true;
        });

        $this->get(route('verification.otp'))
            ->assertOk()
            ->assertDontSee('Your verification code')
            ->assertDontSee($emailedOtp)
            ->assertSee('id="otp"', false);
    }

    public function test_resending_a_code_does_not_display_it(): void
    {
        config()->set('app.debug', true);
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'Individual Applicant',
            'email' => 'individual@example.com',
            'applicant_type' => 'individual',
        ])->assertRedirect(route('verification.otp'));

        $this->post(route('verification.otp.resend'))
            ->assertRedirect(route('verification.otp'))
            ->assertSessionMissing('debug_otp');

        $this->get(route('verification.otp'))
            ->assertDontSee('Your verification code');
    }

    public function test_registration_reports_email_delivery_failure_without_displaying_a_code(): void
    {
        $this->mock(BrevoMailer::class)
            ->shouldReceive('send')
            ->once()
            ->andReturn(false);

        $this->post(route('register'), [
            'name' => 'Corporate Applicant',
            'email' => 'corporate@example.com',
            'company_name' => 'Example Foundation',
            'applicant_type' => 'corporate',
        ])->assertRedirect(route('register', ['type' => 'corporate']))
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('debug_otp');
    }

    public function test_resend_reports_email_delivery_failure_without_displaying_a_code(): void
    {
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'Individual Applicant',
            'email' => 'individual@example.com',
            'applicant_type' => 'individual',
        ])->assertRedirect(route('verification.otp'));

        $this->mock(BrevoMailer::class)
            ->shouldReceive('send')
            ->once()
            ->andReturn(false);

        $this->post(route('verification.otp.resend'))
            ->assertRedirect(route('verification.otp'))
            ->assertSessionHas('error', 'We could not send a new verification code. Please try again.')
            ->assertSessionMissing('debug_otp');

        $this->get(route('verification.otp'))
            ->assertSee('We could not send a new verification code. Please try again.')
            ->assertDontSee('Your verification code');
    }

    public static function applicantTypes(): array
    {
        return [
            'corporate' => ['corporate'],
            'individual' => ['individual'],
        ];
    }
}

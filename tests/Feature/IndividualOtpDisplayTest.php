<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class IndividualOtpDisplayTest extends TestCase
{
    public function test_individual_applicant_sees_the_otp_above_the_input_in_debug_mode(): void
    {
        config()->set('app.debug', true);
        Mail::fake();

        $response = $this->post(route('register'), [
            'name' => 'Individual Applicant',
            'email' => 'individual@example.com',
            'applicant_type' => 'individual',
        ]);

        $response->assertRedirect(route('verification.otp'))
            ->assertSessionHas('debug_otp', fn (string $otp): bool => preg_match('/^\d{6}$/', $otp) === 1);

        $otp = session('debug_otp');

        $this->get(route('verification.otp'))
            ->assertOk()
            ->assertSee('Your verification code')
            ->assertSee($otp)
            ->assertSeeInOrder([$otp, 'id="otp"'], false);
    }

    public function test_successfully_emailed_otp_is_not_exposed_when_debug_mode_is_off(): void
    {
        config()->set('app.debug', false);
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'Production Applicant',
            'email' => 'production@example.com',
            'applicant_type' => 'individual',
        ])->assertRedirect(route('verification.otp'))
            ->assertSessionMissing('debug_otp');
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BlacklistedRegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_blacklisted_email_cannot_register_for_a_future_application(): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
            'blacklisted_at' => now(),
        ]);

        $response = $this->from(route('register', ['type' => 'corporate']))->post(route('register'), [
            'name' => 'Blacklisted Applicant',
            'company_name' => 'Example Company',
            'email' => $applicant->email,
            'applicant_type' => 'corporate',
        ]);

        $response->assertRedirect(route('register', ['type' => 'corporate']));
        $response->assertSessionHasErrors([
            'email' => 'This email address has been blacklisted and cannot submit applications in future award cycles.',
        ]);
    }

    public function test_blacklisted_applicant_cannot_edit_an_application(): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'individual',
            'blacklisted_at' => now(),
        ]);

        $response = $this->actingAs($applicant)->get(route('application.step', 1));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status', 'Your account is blacklisted and cannot create or edit applications.');
    }
}

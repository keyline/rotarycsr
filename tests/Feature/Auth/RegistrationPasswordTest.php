<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationPasswordTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_verified_applicant_can_set_a_ten_character_password(): void
    {
        $applicant = User::factory()->create([
            'password' => null,
            'applicant_type' => 'individual',
        ]);

        $response = $this
            ->withSession(['pending_registration_user_id' => $applicant->id])
            ->post(route('register.password'), [
                'password' => '1234567890',
                'password_confirmation' => '1234567890',
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionMissing('pending_registration_user_id');

        $this->assertAuthenticatedAs($applicant);
        $this->assertTrue(Hash::check('1234567890', $applicant->refresh()->password));
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $applicant->id,
            'event' => 'auth.register.completed',
        ]);
    }

    public function test_verified_applicant_cannot_set_a_password_longer_than_ten_characters(): void
    {
        $applicant = User::factory()->create([
            'password' => null,
            'applicant_type' => 'individual',
        ]);

        $response = $this
            ->from(route('register.password'))
            ->withSession(['pending_registration_user_id' => $applicant->id])
            ->post(route('register.password'), [
                'password' => '12345678901',
                'password_confirmation' => '12345678901',
            ]);

        $response->assertRedirect(route('register.password'));
        $response->assertSessionHasErrors([
            'password' => 'The password field must not be greater than 10 characters.',
        ]);

        $this->assertGuest();
        $this->assertNull($applicant->refresh()->password);
        $this->assertDatabaseMissing('activity_logs', [
            'user_id' => $applicant->id,
            'event' => 'auth.register.completed',
        ]);
    }
}

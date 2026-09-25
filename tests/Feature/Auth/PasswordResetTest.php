<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, PasswordResetNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, PasswordResetNotification::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertOk();

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, PasswordResetNotification::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_reset_password_email_uses_html_and_text_views(): void
    {
        $user = User::factory()->make([
            'name' => 'Reset Applicant',
            'email' => 'reset-applicant@example.com',
        ]);

        $message = (new PasswordResetNotification('test-reset-token'))->toMail($user);
        $html = view($message->view['html'], $message->viewData)->render();
        $text = view($message->view['text'], $message->viewData)->render();

        $this->assertSame([
            'html' => 'auth.password-reset-email',
            'text' => 'auth.password-reset-email-text',
        ], $message->view);
        $this->assertNull($message->markdown);
        $this->assertStringContainsString('https://rotarycsr3291.com/images/logo.png', $html);
        $this->assertStringContainsString('test-reset-token', $html);
        $this->assertStringContainsString('Reset Applicant', $html);
        $this->assertStringContainsString('test-reset-token', $text);
    }
}

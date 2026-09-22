<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\User;
use App\Services\BrevoMailer;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ApplicantEmailControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_send_a_direct_email_to_an_applicant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'name' => 'Applicant <script>alert(1)</script>',
            'email' => 'applicant@example.com',
            'applicant_type' => 'corporate',
        ]);
        $this->mock(BrevoMailer::class)
            ->shouldReceive('send')
            ->once()
            ->withArgs(function (string $email, string $name, string $subject, string $html): bool {
                return $email === 'applicant@example.com'
                    && $name === 'Applicant <script>alert(1)</script>'
                    && $subject === 'Application update'
                    && str_contains($html, 'Applicant &lt;script&gt;alert(1)&lt;/script&gt;')
                    && ! str_contains($html, '<script>');
            })
            ->andReturnTrue();

        $response = $this->actingAs($admin)->post(route('admin.applicants.email', $applicant), [
            'message_type' => 'general',
            'subject' => 'Application update',
            'message' => 'Dear {name},',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Email sent to '.$applicant->name.'.');
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'admin.applicant_email_sent',
            'subject_id' => $applicant->id,
        ]);
        $this->assertDatabaseHas('applicant_email_logs', [
            'applicant_id' => $applicant->id,
            'sent_by' => $admin->id,
            'type' => 'general',
            'recipient_email' => 'applicant@example.com',
            'subject' => 'Application update',
        ]);
        $emailBody = (string) $applicant->emailLogs()->value('body');
        $this->assertStringContainsString('Applicant &lt;script&gt;alert(1)&lt;/script&gt;', $emailBody);
        $this->assertStringNotContainsString('<script>', $emailBody);
    }

    public function test_admin_can_manually_send_an_award_email_to_a_marked_winner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => $applicant->applicant_type,
            'status' => 'submitted',
            'review_status' => 'approved',
            'award_winner_at' => now(),
            'award_winner_by' => $admin->id,
        ]);
        $this->mock(BrevoMailer::class)
            ->shouldReceive('send')
            ->once()
            ->withArgs(fn (string $email, string $name, string $subject, string $html): bool => $email === $applicant->email
                && $name === $applicant->name
                && $subject === 'Winner notification'
                && str_contains($html, 'Rotary International District 3291')
                && str_contains($html, 'Rotary CSR Awards 2026'))
            ->andReturnTrue();

        $response = $this->actingAs($admin)->post(route('admin.applicants.email', $applicant), [
            'message_type' => 'award',
            'subject' => 'Winner notification',
            'message' => "Congratulations {name}!\n{organization_name}\n{award_name}",
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Award email sent to '.$applicant->name.'.');
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'admin.award_notification_sent',
            'subject_id' => $applicant->id,
        ]);
        $this->assertDatabaseHas('applications', [
            'user_id' => $applicant->id,
            'award_winner_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('applicant_email_logs', [
            'applicant_id' => $applicant->id,
            'sent_by' => $admin->id,
            'type' => 'award',
            'subject' => 'Winner notification',
        ]);
        $this->assertNotNull($applicant->application->fresh()->award_winner_at);
    }

    public function test_award_notification_cannot_be_sent_to_an_unapproved_applicant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => $applicant->applicant_type,
            'status' => 'submitted',
            'review_status' => 'pending',
        ]);
        $this->mock(BrevoMailer::class)->shouldNotReceive('send');

        $response = $this->actingAs($admin)->post(route('admin.applicants.email', $applicant), [
            'message_type' => 'award',
            'subject' => 'Winner notification',
            'message' => 'Congratulations {name}!',
        ]);

        $response->assertSessionHasErrors([
            'message_type' => 'Award emails can only be sent to approved applicants.',
        ]);
    }

    public function test_award_email_requires_the_applicant_to_be_marked_as_a_winner_first(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'review_status' => 'approved',
        ]);
        $this->mock(BrevoMailer::class)->shouldNotReceive('send');

        $response = $this->actingAs($admin)->post(route('admin.applicants.email', $applicant), [
            'message_type' => 'award',
            'subject' => 'Winner notification',
            'message' => 'Congratulations {name}!',
        ]);

        $response->assertSessionHasErrors([
            'message_type' => 'Mark the applicant as an award winner before sending the award email.',
        ]);
        $this->assertNull($applicant->application->fresh()->award_winner_at);
    }

    public function test_admin_sees_an_error_when_delivery_fails(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant']);
        $this->mock(BrevoMailer::class)->shouldReceive('send')->once()->andReturnFalse();

        $response = $this->actingAs($admin)->post(route('admin.applicants.email', $applicant), [
            'message_type' => 'general',
            'subject' => 'Application update',
            'message' => 'An update for you.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'The email could not be sent. Check the SMTP configuration and logs.');
        $this->assertDatabaseCount('applicant_email_logs', 0);
    }

    public function test_email_requires_a_subject_and_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant']);

        $response = $this->actingAs($admin)->post(route('admin.applicants.email', $applicant), [
            'message_type' => 'general',
        ]);

        $response->assertSessionHasErrors(['subject', 'message']);
    }

    public function test_applicant_cannot_send_email_to_another_applicant(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant']);
        $recipient = User::factory()->create(['role' => 'applicant']);

        $response = $this->actingAs($applicant)->post(route('admin.applicants.email', $recipient), [
            'message_type' => 'general',
            'subject' => 'Not authorized',
            'message' => 'This should not send.',
        ]);

        $response->assertForbidden();
    }
}

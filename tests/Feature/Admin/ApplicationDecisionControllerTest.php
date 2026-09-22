<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\User;
use App\Services\BrevoMailer;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ApplicationDecisionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_blacklist_a_submitted_application_and_email_the_applicant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $this->mock(BrevoMailer::class)
            ->shouldReceive('send')
            ->once()
            ->withArgs(fn (string $email, string $name, string $subject, string $body): bool => $email === $applicant->email
                && $name === $applicant->name
                && str_contains($subject, 'Important notice')
                && str_contains($body, 'blacklisted'))
            ->andReturnTrue();

        $response = $this->actingAs($admin)->patch(route('admin.applications.decision', $application), [
            'decision' => 'blacklisted',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Application marked as Blacklisted.');
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'review_status' => 'blacklisted',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertNotNull($applicant->fresh()->blacklisted_at);
        $this->assertSame($admin->id, $applicant->fresh()->blacklisted_by);
    }

    public function test_non_admin_cannot_review_an_application(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'individual']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'individual',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($applicant)->patch(route('admin.applications.decision', $application), [
            'decision' => 'approved',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'review_status' => 'pending']);
    }

    public function test_draft_application_cannot_be_reviewed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.applicants.index'))
            ->patch(route('admin.applications.decision', $application), ['decision' => 'approved']);

        $response->assertRedirect(route('admin.applicants.index'));
        $response->assertSessionHasErrors(['decision' => 'Only submitted applications can be reviewed.']);
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'review_status' => 'pending']);
    }

    public function test_approving_a_blacklisted_application_removes_the_applicant_blacklist(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create([
            'applicant_type' => 'corporate',
            'blacklisted_at' => now(),
            'blacklisted_by' => $admin->id,
        ]);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
            'review_status' => 'blacklisted',
        ]);
        $this->mock(BrevoMailer::class)->shouldReceive('send')->once()->andReturnTrue();

        $response = $this->actingAs($admin)->patch(route('admin.applications.decision', $application), [
            'decision' => 'approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'review_status' => 'approved']);
        $this->assertNull($applicant->fresh()->blacklisted_at);
        $this->assertNull($applicant->fresh()->blacklisted_by);
    }
}

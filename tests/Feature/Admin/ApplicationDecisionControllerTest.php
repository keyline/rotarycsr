<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\Setting;
use App\Models\User;
use App\Services\BrevoMailer;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApplicationDecisionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('decisionTemplates')]
    public function test_each_decision_uses_its_saved_email_template_and_records_the_successful_delivery(
        string $decision,
        string $decisionLabel,
    ): void {
        $this->travelTo('2026-09-22 15:30:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'name' => 'Applicant <script>alert(1)</script>',
            'email' => 'applicant@example.com',
            'applicant_type' => 'corporate',
        ]);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        Setting::set("decision_mail_{$decision}_subject", 'Status {decision} for {name}');
        Setting::set("decision_mail_{$decision}_body", '<p>Hello {name}; {decision}; {application_type}</p>');
        $expectedSubject = "Status {$decisionLabel} for Applicant alert(1)";
        $expectedBody = "<p>Hello Applicant &lt;script&gt;alert(1)&lt;/script&gt;; {$decisionLabel}; Corporate</p>";
        $this->mock(BrevoMailer::class)
            ->shouldReceive('send')
            ->once()
            ->with('applicant@example.com', 'Applicant <script>alert(1)</script>', $expectedSubject, $expectedBody)
            ->andReturnTrue();

        $response = $this->actingAs($admin)->patch(route('admin.applications.decision', $application), [
            'decision' => $decision,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', "Application marked as {$decisionLabel}.");
        $this->assertDatabaseHas('applicant_email_logs', [
            'applicant_id' => $applicant->id,
            'application_id' => $application->id,
            'sent_by' => $admin->id,
            'type' => $decision,
            'recipient_email' => 'applicant@example.com',
            'subject' => $expectedSubject,
            'body' => $expectedBody,
            'sent_at' => '2026-09-22 15:30:00',
        ]);
    }

    public static function decisionTemplates(): array
    {
        return [
            'approved' => ['approved', 'Approved'],
            'rejected' => ['rejected', 'Rejected'],
        ];
    }

    public function test_blacklist_is_not_an_available_application_decision(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.applicants.index'))
            ->patch(route('admin.applications.decision', $application), ['decision' => 'blacklisted']);

        $response->assertRedirect(route('admin.applicants.index'));
        $response->assertSessionHasErrors('decision');
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'review_status' => 'pending',
        ]);
        $this->assertNull($applicant->fresh()->blacklisted_at);
        $this->assertDatabaseCount('applicant_email_logs', 0);
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

    public function test_failed_decision_email_delivery_is_not_recorded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $this->mock(BrevoMailer::class)->shouldReceive('send')->once()->andReturnFalse();

        $response = $this->actingAs($admin)->patch(route('admin.applications.decision', $application), [
            'decision' => 'rejected',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Application marked as Rejected. The email could not be sent; check the SMTP configuration and logs.');
        $this->assertDatabaseCount('applicant_email_logs', 0);
    }
}

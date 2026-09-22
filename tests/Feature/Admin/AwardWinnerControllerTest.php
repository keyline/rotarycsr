<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AwardWinnerControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_mark_multiple_approved_applicants_as_winners_without_sending_email(): void
    {
        $this->travelTo('2026-09-22 15:30:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $applications = collect(['corporate', 'individual'])->map(function (string $applicantType): Application {
            $applicant = User::factory()->create([
                'role' => 'applicant',
                'applicant_type' => $applicantType,
            ]);

            return Application::create([
                'user_id' => $applicant->id,
                'applicant_type' => $applicantType,
                'status' => 'submitted',
                'submitted_at' => now(),
                'review_status' => 'approved',
            ]);
        });
        Mail::fake();

        foreach ($applications as $application) {
            $response = $this->actingAs($admin)->post(route('admin.applications.award-winner', $application));

            $response->assertRedirect();
            $response->assertSessionHas('status', $application->user->name.' marked as an award winner. You can now send the award email.');
            $this->assertDatabaseHas('applications', [
                'id' => $application->id,
                'award_winner_at' => '2026-09-22 15:30:00',
                'award_winner_by' => $admin->id,
            ]);
            $this->assertDatabaseHas('activity_logs', [
                'event' => 'admin.award_winner_selected',
                'subject_id' => $application->id,
            ]);
        }

        Mail::assertNothingSent();
        $this->assertDatabaseCount('applicant_email_logs', 0);
    }

    public function test_unapproved_application_cannot_be_marked_as_an_award_winner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
            'review_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.applicants.index'))
            ->post(route('admin.applications.award-winner', $application));

        $response->assertRedirect(route('admin.applicants.index'));
        $response->assertSessionHasErrors([
            'award' => 'Only approved, submitted applications can be marked as award winners.',
        ]);
        $this->assertNull($application->fresh()->award_winner_at);
    }

    public function test_existing_award_winner_is_not_overwritten(): void
    {
        $originalAdmin = User::factory()->create(['role' => 'admin']);
        $anotherAdmin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
            'review_status' => 'approved',
            'award_winner_at' => '2026-09-20 10:00:00',
            'award_winner_by' => $originalAdmin->id,
        ]);

        $response = $this->actingAs($anotherAdmin)->post(route('admin.applications.award-winner', $application));

        $response->assertRedirect();
        $response->assertSessionHas('status', $applicant->name.' is already marked as an award winner.');
        $this->assertSame($originalAdmin->id, $application->fresh()->award_winner_by);
    }

    public function test_applicant_cannot_mark_an_award_winner(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
            'review_status' => 'approved',
        ]);

        $response = $this->actingAs($applicant)->post(route('admin.applications.award-winner', $application));

        $response->assertForbidden();
        $this->assertNull($application->fresh()->award_winner_at);
    }
}

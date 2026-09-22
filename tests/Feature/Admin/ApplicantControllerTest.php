<?php

namespace Tests\Feature\Admin;

use App\Models\ApplicantEmailLog;
use App\Models\Application;
use App\Models\User;
use App\Services\ApplicantMessageMailer;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ApplicantControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_email_action_opens_the_applicants_email_history_in_a_new_tab(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant']);

        $response = $this->actingAs($admin)->get(route('admin.applicants.index'));

        $response->assertSee(route('admin.applicants.email-logs', $applicant), false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_completed_award_email_highlights_the_row_and_shows_status_badges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'submitted_at' => now(),
            'review_status' => 'approved',
            'award_winner_at' => now(),
            'award_winner_by' => $admin->id,
        ]);
        ApplicantEmailLog::factory()->for($applicant, 'applicant')->create([
            'application_id' => $application->id,
            'sent_by' => $admin->id,
            'type' => 'award',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applicants.index'));

        $response->assertSee('data-applicant-row="'.$applicant->id.'"', false);
        $response->assertSee('data-award-complete="true"', false);
        $response->assertSee('bg-green-50 hover:bg-green-100', false);
        $response->assertSee('Award Winner');
        $response->assertSee('Award Email Sent');
    }

    public function test_marked_winner_sees_manual_award_email_action_with_requested_defaults(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'individual']);
        Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'individual',
            'status' => 'submitted',
            'submitted_at' => now(),
            'review_status' => 'approved',
            'award_winner_at' => now(),
            'award_winner_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applicants.index'));

        $response->assertSee('Award Email');
        $response->assertSee(route('admin.applicants.email', $applicant), false);
        $response->assertSee(ApplicantMessageMailer::AWARD_SUBJECT);
        $response->assertSee('We are pleased to inform you that you have been selected for an award.');
        $response->assertSee('No email will be sent automatically.');
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_sees_application_status_counts_and_percentages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createApplication('corporate', 'submitted', 'approved');
        $this->createApplication('individual', 'submitted', 'rejected');
        $this->createApplication('corporate', 'submitted', 'blacklisted');
        $this->createApplication('individual', 'submitted', 'pending');
        $this->createApplication('corporate', 'draft', 'pending');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewIs('admin.dashboard');
        $response->assertViewHasAll([
            'applicationStats' => [
                'total' => 5,
                'submitted' => 4,
                'in_progress' => 1,
                'approved' => 1,
                'not_approved' => 2,
                'pending_review' => 1,
            ],
            'applicationPercentages' => [
                'submitted' => 80.0,
                'in_progress' => 20.0,
                'approved' => 20.0,
                'not_approved' => 40.0,
                'pending_review' => 20.0,
            ],
        ]);
        $response->assertSeeText('Application Status Overview');
        $response->assertSeeText('Approved');
        $response->assertSeeText('Not Approved');
        $response->assertSeeText('Pending Review');
    }

    public function test_admin_sees_zero_percentages_when_there_are_no_applications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertViewHasAll([
            'applicationStats' => [
                'total' => 0,
                'submitted' => 0,
                'in_progress' => 0,
                'approved' => 0,
                'not_approved' => 0,
                'pending_review' => 0,
            ],
            'applicationPercentages' => [
                'submitted' => 0.0,
                'in_progress' => 0.0,
                'approved' => 0.0,
                'not_approved' => 0.0,
                'pending_review' => 0.0,
            ],
        ]);
        $response->assertSeeText('Percentages are calculated from 0 total applications.');
    }

    public function test_applicant_cannot_view_the_admin_dashboard(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant']);

        $this->actingAs($applicant)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    private function createApplication(string $type, string $status, string $reviewStatus): Application
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => $type,
        ]);

        return Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => $type,
            'status' => $status,
            'review_status' => $reviewStatus,
            'submitted_at' => $status === 'submitted' ? now() : null,
        ]);
    }
}

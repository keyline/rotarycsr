<?php

namespace Tests\Feature\Admin;

use App\Models\ApplicantEmailLog;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ApplicantEmailLogControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_an_applicants_email_history_and_compose_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'name' => 'Email History Applicant',
            'email' => 'history@example.com',
        ]);
        ApplicantEmailLog::factory()->for($applicant, 'applicant')->create([
            'type' => 'approved',
            'recipient_email' => $applicant->email,
            'recipient_name' => $applicant->name,
            'subject' => 'Approved email subject',
            'body' => '<p>Approved email body</p><script>alert(1)</script>',
            'sent_at' => '2026-09-22 15:30:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applicants.email-logs', $applicant));

        $response->assertSee('Email History Applicant');
        $response->assertSee('history@example.com');
        $response->assertSee('Approved email subject');
        $response->assertSee('Approved email body');
        $response->assertSee('22 Sep 2026, 03:30 PM');
        $response->assertSee('Compose email');
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_non_admin_cannot_view_an_applicants_email_history(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant']);

        $response = $this->actingAs($applicant)->get(route('admin.applicants.email-logs', $applicant));

        $response->assertForbidden();
    }

    public function test_returns_not_found_when_the_email_history_subject_is_not_an_applicant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.applicants.email-logs', $anotherAdmin));

        $response->assertNotFound();
    }
}

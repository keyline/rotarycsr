<?php

namespace Tests\Feature\Admin;

use App\Models\ApplicantEmailLog;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ApplicantControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_an_applicants_email_history_in_the_list(): void
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

        $response = $this->actingAs($admin)->get(route('admin.applicants.index'));

        $response->assertOk();
        $response->assertSee('Email history');
        $response->assertSee('Approved email subject');
        $response->assertSee('Approved email body');
        $response->assertSee('22 Sep 2026, 03:30 PM');
        $response->assertDontSee('<script>alert(1)</script>', false);
    }
}

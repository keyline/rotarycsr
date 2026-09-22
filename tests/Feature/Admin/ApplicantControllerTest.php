<?php

namespace Tests\Feature\Admin;

use App\Models\User;
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
}

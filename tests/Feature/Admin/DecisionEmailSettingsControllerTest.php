<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DecisionEmailSettingsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_update_all_decision_email_templates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $templates = [
            'approved' => ['subject' => 'Approved {name}', 'body' => '<p>Approved {application_type}</p>'],
            'rejected' => ['subject' => 'Rejected {name}', 'body' => '<p>Rejected {decision}</p>'],
        ];

        $response = $this->actingAs($admin)->put(route('admin.settings.decision-emails'), [
            'templates' => $templates,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Decision email templates updated.');

        foreach ($templates as $decision => $template) {
            $this->assertDatabaseHas('settings', [
                'key' => "decision_mail_{$decision}_subject",
                'value' => $template['subject'],
            ]);
            $this->assertDatabaseHas('settings', [
                'key' => "decision_mail_{$decision}_body",
                'value' => $template['body'],
            ]);
        }
    }

    public function test_applicant_cannot_update_decision_email_templates(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant']);

        $response = $this->actingAs($applicant)->put(route('admin.settings.decision-emails'), [
            'templates' => [],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_blacklisted_email_template_is_not_shown_in_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertDontSee('Blacklisted email');
        $response->assertDontSee('templates[blacklisted]', false);
    }
}

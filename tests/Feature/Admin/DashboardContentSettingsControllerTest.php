<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\DashboardContent;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardContentSettingsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function tearDown(): void
    {
        Cache::forget('setting.'.DashboardContent::SETTING_KEY);

        parent::tearDown();
    }

    public function test_admin_can_update_the_dashboard_how_it_works_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = "Prepare the required information.\nComplete every application step.\nReview and submit.";

        $response = $this->actingAs($admin)
            ->from(route('admin.settings.index'))
            ->put(route('admin.settings.dashboard-content'), [
                'how_it_works' => $content,
            ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('status', 'Dashboard How It Works content updated.');
        $this->assertDatabaseHas('settings', [
            'key' => DashboardContent::SETTING_KEY,
            'value' => $content,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'event' => 'admin.dashboard_content_updated',
        ]);
    }

    public function test_how_it_works_content_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('admin.settings.index'))
            ->put(route('admin.settings.dashboard-content'), [
                'how_it_works' => '',
            ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHasErrors([
            'how_it_works' => 'The how it works field is required.',
        ]);
        $this->assertDatabaseMissing('settings', ['key' => DashboardContent::SETTING_KEY]);
    }

    public function test_applicant_cannot_update_the_dashboard_how_it_works_content(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant']);

        $this->actingAs($applicant)
            ->put(route('admin.settings.dashboard-content'), [
                'how_it_works' => 'Unauthorized change',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('settings', ['key' => DashboardContent::SETTING_KEY]);
    }
}

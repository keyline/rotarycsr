<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\DashboardContent;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function tearDown(): void
    {
        Cache::forget('setting.'.DashboardContent::SETTING_KEY);

        parent::tearDown();
    }

    public function test_managed_how_it_works_content_appears_before_the_application_section(): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
        ]);
        Setting::set(
            DashboardContent::SETTING_KEY,
            "Prepare your documents.\n<script>alert('xss')</script>\nSubmit the application.",
        );

        $response = $this->actingAs($applicant)->get(route('dashboard'));

        $response->assertViewIs('dashboard');
        $response->assertSeeTextInOrder([
            'How it works',
            'Prepare your documents.',
            'Submit the application.',
            'Application Progress',
        ]);
        $response->assertSee("<script>alert('xss')</script>");
        $response->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_default_how_it_works_content_is_shown_when_admin_has_not_saved_content(): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'individual',
        ]);

        $response = $this->actingAs($applicant)->get(route('dashboard'));

        $response->assertSeeText('Complete the application in short, guided steps - your answers are saved automatically as you type.');
    }
}

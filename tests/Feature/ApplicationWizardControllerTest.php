<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationSupportingDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApplicationWizardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[DataProvider('companySizes')]
    public function test_each_supported_company_size_is_saved(string $companySize): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
        ]);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'draft',
            'current_step' => 1,
        ]);

        $response = $this->actingAs($applicant)->post(route('application.step', 1), [
            'company_size' => $companySize,
        ]);

        $response->assertRedirect(route('application.step', 2));
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'company_size' => $companySize,
            'current_step' => 2,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $applicant->id,
            'event' => 'application.step_completed',
            'subject_id' => $application->id,
        ]);
    }

    public static function companySizes(): array
    {
        return [
            'micro' => ['micro'],
            'macro' => ['macro'],
            'mega' => ['mega'],
        ];
    }

    public function test_company_information_is_required(): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
        ]);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'draft',
            'current_step' => 1,
        ]);

        $response = $this->actingAs($applicant)
            ->from(route('application.step', 1))
            ->post(route('application.step', 1));

        $response->assertRedirect(route('application.step', 1));
        $response->assertSessionHasErrors([
            'company_size' => 'The company size field is required.',
        ]);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'company_size' => null,
            'current_step' => 1,
        ]);
        $this->assertDatabaseMissing('activity_logs', [
            'event' => 'application.step_completed',
            'subject_id' => $application->id,
        ]);
    }

    public function test_unsupported_company_size_is_rejected(): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
        ]);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'draft',
            'current_step' => 1,
        ]);

        $response = $this->actingAs($applicant)
            ->from(route('application.step', 1))
            ->post(route('application.step', 1), [
                'company_size' => 'enterprise',
            ]);

        $response->assertRedirect(route('application.step', 1));
        $response->assertSessionHasErrors([
            'company_size' => 'The selected company size is invalid.',
        ]);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'company_size' => null,
            'current_step' => 1,
        ]);
        $this->assertDatabaseMissing('activity_logs', [
            'event' => 'application.step_completed',
            'subject_id' => $application->id,
        ]);
    }

    public function test_autosave_saves_company_size_and_ignores_removed_fields(): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
        ]);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'draft',
            'current_step' => 1,
        ]);

        $response = $this->actingAs($applicant)->postJson(route('application.autosave'), [
            'step_key' => 'category',
            'data' => [
                'company_size' => 'mega',
                'company_turnover' => '9876.50',
                'corporate_category' => 'large',
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('saved', true);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'company_size' => 'mega',
            'company_turnover' => null,
            'corporate_category' => null,
        ]);
    }

    public function test_company_information_appears_on_the_corporate_review_page(): void
    {
        $applicant = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
        ]);
        Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'draft',
            'current_step' => 5,
            'company_size' => 'macro',
            'company_turnover' => '1234.50',
        ]);

        $response = $this->actingAs($applicant)->get(route('application.review'));

        $response->assertSeeText('Company Information');
        $response->assertSeeText('Company Size');
        $response->assertSeeText('Macro');
        $response->assertDontSeeText('Turnover — FY 2025–2026');
        $response->assertDontSeeText('₹1,234.50 Crore');
    }

    public function test_area_of_focus_page_uses_the_new_environment_option(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'draft',
            'current_step' => 2,
        ]);

        $this->actingAs($applicant)
            ->get(route('application.step', 2))
            ->assertOk()
            ->assertSeeText('Environment & Nature Protection / Conservation');
    }

    public function test_corporate_and_contact_details_are_saved(): void
    {
        [$applicant, $application] = $this->corporateApplicationAtStep(3);

        $response = $this->actingAs($applicant)->post(route('application.step', 3), [
            'corporate_foundation_name' => 'Example Foundation',
            'csr_registration_number' => 'CSR00001234',
            'industry_sector' => 'Manufacturing',
            'head_office_location' => '10 Park Street, Kolkata',
            'corporate_presence' => 'national',
            'business_group_name' => 'Example Group',
            'primary_contact_name' => 'Primary Person',
            'primary_contact_designation' => 'CSR Head',
            'primary_contact_email' => 'primary@example.com',
            'primary_contact_mobile' => '+91 98765 43210',
            'secondary_contact_name' => 'Secondary Person',
            'secondary_contact_designation' => 'CSR Manager',
            'secondary_contact_email' => 'secondary@example.com',
            'secondary_contact_mobile' => '+91 91234 56789',
        ]);

        $response->assertRedirect(route('application.step', 4));
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'corporate_presence' => 'national',
            'business_group_name' => 'Example Group',
            'primary_contact_email' => 'primary@example.com',
            'secondary_contact_mobile' => '+91 91234 56789',
            'current_step' => 4,
        ]);
    }

    public function test_partial_secondary_contact_is_rejected(): void
    {
        [$applicant, $application] = $this->corporateApplicationAtStep(3);

        $response = $this->actingAs($applicant)
            ->from(route('application.step', 3))
            ->post(route('application.step', 3), [
                'corporate_foundation_name' => 'Example Foundation',
                'csr_registration_number' => 'CSR00001234',
                'industry_sector' => 'Manufacturing',
                'head_office_location' => 'Kolkata',
                'corporate_presence' => 'state',
                'primary_contact_name' => 'Primary Person',
                'primary_contact_designation' => 'CSR Head',
                'primary_contact_email' => 'primary@example.com',
                'primary_contact_mobile' => '9876543210',
                'secondary_contact_name' => 'Secondary Person',
            ]);

        $response->assertRedirect(route('application.step', 3));
        $response->assertSessionHasErrors([
            'secondary_contact_designation',
            'secondary_contact_email',
            'secondary_contact_mobile',
        ]);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'current_step' => 3,
            'primary_contact_name' => null,
        ]);
    }

    public function test_project_details_and_supporting_media_are_saved(): void
    {
        Storage::fake('local');
        [$applicant, $application] = $this->corporateApplicationAtStep(4);

        $response = $this->actingAs($applicant)->post(route('application.step', 4), array_merge(
            $this->validProjectDetails(),
            [
                'supporting_documents' => [
                    UploadedFile::fake()->image('project.jpg'),
                    UploadedFile::fake()->create('project.mp4', 1024, 'video/mp4'),
                ],
            ],
        ));

        $response->assertRedirect(route('application.step', 5));
        $application->refresh();
        $this->assertSame('continuing', $application->project_completion_status);
        $this->assertNull($application->project_completion_date);
        $this->assertCount(2, $application->supportingDocuments);
        $application->supportingDocuments->each(function (ApplicationSupportingDocument $document): void {
            Storage::disk('local')->assertExists($document->path);
        });
    }

    public function test_completed_project_requires_a_completion_date(): void
    {
        [$applicant, $application] = $this->corporateApplicationAtStep(4);
        $details = $this->validProjectDetails();
        $details['project_completion_status'] = 'completed';

        $response = $this->actingAs($applicant)
            ->from(route('application.step', 4))
            ->post(route('application.step', 4), $details);

        $response->assertRedirect(route('application.step', 4));
        $response->assertSessionHasErrors('project_completion_date');
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'current_step' => 4,
            'project_name' => null,
        ]);
    }

    public function test_project_narrative_word_limits_are_enforced(): void
    {
        [$applicant] = $this->corporateApplicationAtStep(4);
        $details = $this->validProjectDetails();
        $details['intervention_design'] = implode(' ', array_fill(0, 101, 'word'));

        $response = $this->actingAs($applicant)
            ->from(route('application.step', 4))
            ->post(route('application.step', 4), $details);

        $response->assertRedirect(route('application.step', 4));
        $response->assertSessionHasErrors([
            'intervention_design' => 'The intervention design field must not contain more than 100 words.',
        ]);
    }

    public function test_non_media_supporting_file_is_rejected(): void
    {
        Storage::fake('local');
        [$applicant, $application] = $this->corporateApplicationAtStep(4);

        $response = $this->actingAs($applicant)
            ->from(route('application.step', 4))
            ->post(route('application.step', 4), array_merge($this->validProjectDetails(), [
                'supporting_documents' => [UploadedFile::fake()->create('script.php', 10, 'text/x-php')],
            ]));

        $response->assertRedirect(route('application.step', 4));
        $response->assertSessionHasErrors('supporting_documents.0');
        $this->assertDatabaseMissing('application_supporting_documents', ['application_id' => $application->id]);
    }

    public function test_supporting_media_download_is_limited_to_the_owner_and_admin(): void
    {
        Storage::fake('local');
        [$owner, $application] = $this->corporateApplicationAtStep(4);
        $otherApplicant = User::factory()->create(['role' => 'applicant']);
        $admin = User::factory()->create(['role' => 'admin']);
        Storage::disk('local')->put('application-supporting-documents/evidence.jpg', 'evidence');
        $document = $application->supportingDocuments()->create([
            'path' => 'application-supporting-documents/evidence.jpg',
            'original_name' => 'evidence.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 8,
            'media_type' => 'image',
        ]);

        $this->actingAs($owner)
            ->get(route('application.supporting-documents.download', $document))
            ->assertOk();
        $this->actingAs($otherApplicant)
            ->get(route('application.supporting-documents.download', $document))
            ->assertNotFound();
        $this->actingAs($admin)
            ->get(route('application.supporting-documents.download', $document))
            ->assertOk();
    }

    public function test_submission_assigns_separate_sequential_ids_for_each_award(): void
    {
        $firstCorporate = $this->completeCorporateApplication();
        $secondCorporate = $this->completeCorporateApplication();
        $individual = $this->completeIndividualApplication();

        $this->actingAs($firstCorporate->user)
            ->post(route('application.submit'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Your application has been submitted successfully. Application ID: RICSR/CP/0001');
        $this->actingAs($secondCorporate->user)->post(route('application.submit'))->assertRedirect(route('dashboard'));
        $this->actingAs($individual->user)->post(route('application.submit'))->assertRedirect(route('dashboard'));

        $this->assertSame('RICSR/CP/0001', $firstCorporate->refresh()->reference_number);
        $this->assertSame('RICSR/CP/0002', $secondCorporate->refresh()->reference_number);
        $this->assertSame('RICSR/IN/0001', $individual->refresh()->reference_number);
        $this->actingAs($firstCorporate->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('RICSR/CP/0001');
    }

    /** @return array{User, Application} */
    private function corporateApplicationAtStep(int $step): array
    {
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'corporate']);
        $application = Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'corporate',
            'status' => 'draft',
            'current_step' => $step,
        ]);

        return [$applicant, $application];
    }

    /** @return array<string, mixed> */
    private function validProjectDetails(): array
    {
        return [
            'project_name' => 'Clean Water Project',
            'project_launch_date' => '2025-06-15',
            'project_completion_status' => 'continuing',
            'project_completion_date' => null,
            'geographic_coverage' => 'Village, District, West Bengal',
            'implementation_partners' => 'Example Foundation',
            'csr_budget' => '1500000.00',
            'beneficiaries_impacted' => '2,000 direct and 5,000 indirect beneficiaries',
            'intervention_design' => '<p>Community-managed clean water facilities.</p>',
            'unique_feature' => '<p>Solar-powered filtration.</p>',
            'outcomes_impact' => '<p>Improved access to safe drinking water.</p>',
        ];
    }

    private function completeCorporateApplication(): Application
    {
        [$applicant] = $this->corporateApplicationAtStep(5);
        $application = Application::whereBelongsTo($applicant)->firstOrFail();
        $application->fill(array_merge([
            'company_size' => 'macro',
            'focus_area' => 'environment',
            'corporate_foundation_name' => 'Example Foundation',
            'csr_registration_number' => 'CSR00001234',
            'industry_sector' => 'Manufacturing',
            'head_office_location' => 'Kolkata',
            'corporate_presence' => 'national',
            'primary_contact_name' => 'Primary Person',
            'primary_contact_designation' => 'CSR Head',
            'primary_contact_email' => 'primary@example.com',
            'primary_contact_mobile' => '9876543210',
        ], $this->validProjectDetails()))->save();

        return $application;
    }

    private function completeIndividualApplication(): Application
    {
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'individual']);

        return Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'individual',
            'status' => 'draft',
            'current_step' => 3,
            'ind_designation' => 'CSR Director',
            'ind_organisation' => 'Example Company',
            'ind_industry' => 'Manufacturing',
            'ind_location' => 'Kolkata',
            'ind_csr_experience_years' => 10,
            'ind_total_experience_years' => 15,
            'ind_current_responsibilities' => 'Leading the CSR programme.',
            'ind_annual_budget_handled' => '₹10 Crore',
            'ind_geographic_responsibility' => 'National',
            'ind_projects' => [[
                'problem' => 'Access to education',
                'intervention' => 'School support programme',
                'investment' => '₹1 Crore',
                'beneficiaries' => '1,000 students',
                'outcomes' => 'Improved attendance',
            ], [], []],
        ]);
    }
}

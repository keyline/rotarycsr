<?php

namespace Tests\Feature\Admin;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SelectedApplicantExportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_export_selected_complete_records_as_a_spreadsheet(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $selected = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'corporate',
            'name' => 'Selected Applicant',
            'email' => 'selected@example.com',
        ]);
        $notSelected = User::factory()->create([
            'role' => 'applicant',
            'applicant_type' => 'individual',
            'name' => 'Excluded Applicant',
        ]);
        Application::create([
            'user_id' => $selected->id,
            'applicant_type' => 'corporate',
            'status' => 'submitted',
            'reference_number' => 'RICSR/CP/0001',
            'company_size' => 'mega',
            'company_turnover' => '5000.25',
            'project_name' => 'Clean Water Programme',
            'intervention_design' => '<p>Safe water access</p>',
            'unique_feature' => '=HYPERLINK("https://invalid.example")',
        ]);
        Application::create([
            'user_id' => $notSelected->id,
            'applicant_type' => 'individual',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.applicants.export-selected'), [
            'applicant_ids' => [$selected->id],
            'format' => 'xlsx',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $spreadsheet = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();
        $record = array_combine($rows[0], $rows[1]);

        $this->assertSame('Selected Applicant', $record['Name']);
        $this->assertSame('Mega', $record['Company Size']);
        $this->assertArrayNotHasKey('Turnover FY 2025–2026 (₹ Crore)', $record);
        $this->assertSame('RICSR/CP/0001', $record['Application ID']);
        $this->assertSame('Clean Water Programme', $record['Project Title']);
        $this->assertSame('Safe water access', $record['Brief Project Concept / Design']);
        $this->assertSame('\'=HYPERLINK("https://invalid.example")', $record['Unique Feature of the Initiative']);
        $this->assertCount(2, $rows);
        $this->assertNotContains('Excluded Applicant', $rows[1]);
        $spreadsheet->disconnectWorksheets();
    }

    public function test_admin_can_export_selected_records_as_a_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['role' => 'applicant', 'applicant_type' => 'individual']);
        Application::create([
            'user_id' => $applicant->id,
            'applicant_type' => 'individual',
            'status' => 'submitted',
            'ind_designation' => 'CSR Director',
            'ind_projects' => [['problem' => 'Education access', 'outcomes' => '1,000 students supported']],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.applicants.export-selected'), [
            'applicant_ids' => [$applicant->id],
            'format' => 'pdf',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_export_requires_at_least_one_valid_applicant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('admin.applicants.index'))
            ->post(route('admin.applicants.export-selected'), ['format' => 'pdf']);

        $response->assertRedirect(route('admin.applicants.index'));
        $response->assertSessionHasErrors('applicant_ids');
    }

    public function test_applicant_cannot_export_other_applicant_records(): void
    {
        $applicant = User::factory()->create(['role' => 'applicant']);
        $otherApplicant = User::factory()->create(['role' => 'applicant']);

        $response = $this->actingAs($applicant)->post(route('admin.applicants.export-selected'), [
            'applicant_ids' => [$otherApplicant->id],
            'format' => 'pdf',
        ]);

        $response->assertForbidden();
    }
}

<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;

class ApplicantExportData
{
    /** @return array<string, string> */
    public function for(User $applicant): array
    {
        $application = $applicant->application;
        $data = [
            'Applicant ID' => (string) $applicant->id,
            'Name' => $this->text($applicant->name),
            'Email' => $this->text($applicant->email),
            'Applicant Type' => ucfirst($this->text($applicant->applicant_type)),
            'Company Name' => $this->text($applicant->company_name),
            'Email Verified' => $applicant->email_verified_at ? 'Yes' : 'No',
            'Registered At' => $applicant->created_at?->format('d M Y, h:i A') ?? '',
            'Blacklisted At' => $applicant->blacklisted_at?->format('d M Y, h:i A') ?? '',
            'Application Status' => ucfirst($this->text($application?->status ?: 'Not started')),
            'Review Status' => ucfirst($this->text($application?->review_status ?: 'Not started')),
            'Submitted At' => $application?->submitted_at?->format('d M Y, h:i A') ?? '',
            'Reviewed At' => $application?->reviewed_at?->format('d M Y, h:i A') ?? '',
            'Reviewed By' => $this->text($application?->reviewer?->name),
            'Application Updated At' => $application?->updated_at?->format('d M Y, h:i A') ?? '',
        ];

        if (! $application) {
            return $data;
        }

        return array_merge($data, $application->applicant_type === 'individual'
            ? $this->individualFields($application)
            : $this->corporateFields($application));
    }

    /** @return array<string, string> */
    private function corporateFields(Application $application): array
    {
        return [
            'Corporate Category' => ucfirst($this->text($application->corporate_category)),
            'Focus Area' => $this->text($application->focus_area),
            'Corporate / Foundation Name' => $this->text($application->corporate_foundation_name),
            'CSR Registration Number' => $this->text($application->csr_registration_number),
            'Industry Sector' => $this->text($application->industry_sector),
            'Head Office Location' => $this->text($application->head_office_location),
            'Project Name' => $this->text($application->project_name),
            'Project Period' => $this->text($application->project_period),
            'Geographic Coverage' => $this->text($application->geographic_coverage),
            'CSR Budget' => $this->text($application->csr_budget),
            'Problem Addressed' => $this->text($application->problem_addressed),
            'Intervention Design' => $this->text($application->intervention_design),
            'Beneficiaries Impacted' => $this->text($application->beneficiaries_impacted),
            'Outcomes and Impact' => $this->text($application->outcomes_impact),
            'Implementation Partners' => $this->text($application->implementation_partners),
            'Additional Information' => $this->text($application->additional_info),
        ];
    }

    /** @return array<string, string> */
    private function individualFields(Application $application): array
    {
        $fields = [
            'Designation' => $this->text($application->ind_designation),
            'Organisation' => $this->text($application->ind_organisation),
            'Industry' => $this->text($application->ind_industry),
            'Location' => $this->text($application->ind_location),
            'CSR Experience (Years)' => $this->text($application->ind_csr_experience_years),
            'Total Experience (Years)' => $this->text($application->ind_total_experience_years),
            'Current Responsibilities' => $this->text($application->ind_current_responsibilities),
            'Annual Budget Handled' => $this->text($application->ind_annual_budget_handled),
            'Geographic Responsibility' => $this->text($application->ind_geographic_responsibility),
        ];

        $projects = $application->ind_projects ?? [];

        for ($index = 0; $index < 3; $index++) {
            $number = $index + 1;
            $project = $projects[$index] ?? [];
            $fields["Project {$number} - Problem"] = $this->text($project['problem'] ?? null);
            $fields["Project {$number} - Intervention"] = $this->text($project['intervention'] ?? null);
            $fields["Project {$number} - Investment"] = $this->text($project['investment'] ?? null);
            $fields["Project {$number} - Beneficiaries"] = $this->text($project['beneficiaries'] ?? null);
            $fields["Project {$number} - Outcomes"] = $this->text($project['outcomes'] ?? null);
        }

        return $fields;
    }

    private function text(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = preg_replace('/<br\s*\/?\s*>/i', "\n", (string) $value) ?? (string) $value;

        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationSupportingDocument;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ApplicantExportData
{
    /**
     * @return array{
     *     fields: array<string, string>,
     *     application_status: string,
     *     review_status: string,
     *     media: list<array{name: string, type: string, url: string, data_uri: string}>
     * }
     */
    public function for(User $applicant, bool $includeImageData = false): array
    {
        $application = $applicant->application;
        $data = [
            'Application ID' => $this->text($application?->reference_number),
            'Name' => $this->text($applicant->name),
            'Email' => $this->text($applicant->email),
            'Applicant Type' => ucfirst($this->text($applicant->applicant_type)),
            'Company Name' => $this->text($applicant->company_name),
        ];

        if ($application) {
            $data = array_merge($data, $application->applicant_type === 'individual'
                ? $this->individualFields($application)
                : $this->corporateFields($application));
        }

        return [
            'fields' => $data,
            'application_status' => $this->text($application?->status ?: 'not_started'),
            'review_status' => $this->text($application?->review_status ?: 'not_started'),
            'media' => $application ? $this->supportingMedia($application, $includeImageData) : [],
        ];
    }

    /** @return array<string, string> */
    private function corporateFields(Application $application): array
    {
        return [
            'Company Size' => ucfirst($this->text($application->company_size)),
            'Focus Area' => ApplicationOptions::FOCUS_AREAS[$application->focus_area] ?? '',
            'Corporate / Foundation Name' => $this->text($application->corporate_foundation_name),
            'CSR Registration Number' => $this->text($application->csr_registration_number),
            'Industry Sector' => $this->text($application->industry_sector),
            'Registered / Head Office Address' => $this->text($application->head_office_location),
            'Presence' => ApplicationOptions::CORPORATE_PRESENCE_OPTIONS[$application->corporate_presence] ?? '',
            'Business Group Name' => $this->text($application->business_group_name),
            'Primary Contact Name' => $this->text($application->primary_contact_name),
            'Primary Contact Designation' => $this->text($application->primary_contact_designation),
            'Primary Contact Email ID' => $this->text($application->primary_contact_email),
            'Primary Contact Mobile Number' => $this->text($application->primary_contact_mobile),
            'Secondary Contact Name' => $this->text($application->secondary_contact_name),
            'Secondary Contact Designation' => $this->text($application->secondary_contact_designation),
            'Secondary Contact Email ID' => $this->text($application->secondary_contact_email),
            'Secondary Contact Mobile Number' => $this->text($application->secondary_contact_mobile),
            'Project Title' => $this->text($application->project_name),
            'Project Launch Date' => $application->project_launch_date?->format('d M Y') ?? '',
            'Project Completion Date / Continuing' => $application->project_completion_status === 'continuing'
                ? 'Continuing'
                : ($application->project_completion_date?->format('d M Y') ?? ''),
            'Geographical Coverage of Project' => $this->text($application->geographic_coverage),
            'Execution Partners' => $this->text($application->implementation_partners),
            'CSR Budget / Project Cost' => $this->text($application->csr_budget),
            'Direct and Indirect Beneficiaries' => $this->text($application->beneficiaries_impacted),
            'Brief Project Concept / Design' => $this->text($application->intervention_design),
            'Unique Feature of the Initiative' => $this->text($application->unique_feature),
            'Impact Assessment' => $this->text($application->outcomes_impact),
        ];
    }

    /** @return list<array{name: string, type: string, url: string, data_uri: string}> */
    private function supportingMedia(Application $application, bool $includeImageData): array
    {
        return $application->supportingDocuments
            ->map(function (ApplicationSupportingDocument $document) use ($includeImageData): array {
                $dataUri = '';

                if ($includeImageData
                    && $document->media_type === 'image'
                    && Storage::disk('local')->exists($document->path)) {
                    $contents = Storage::disk('local')->get($document->path);

                    if (is_string($contents)) {
                        $dataUri = 'data:'.$document->mime_type.';base64,'.base64_encode($contents);
                    }
                }

                return [
                    'name' => $this->text($document->original_name),
                    'type' => $this->text($document->media_type),
                    'url' => route('application.supporting-documents.preview', $document),
                    'data_uri' => $dataUri,
                ];
            })
            ->values()
            ->all();
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

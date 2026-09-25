<?php

namespace App\Services;

use App\Models\Setting;
use App\Rules\MaxWords;
use Illuminate\Support\Carbon;

class ApplicationOptions
{
    public const COMPANY_SIZES = [
        'micro' => [
            'label' => 'Micro',
            'help' => 'Turnover: FY 2025–2026 (in ₹ Crore) up to 100',
        ],
        'macro' => [
            'label' => 'Macro',
            'help' => 'Turnover: FY 2025–2026 (in ₹ Crore) 101–499',
        ],
        'mega' => [
            'label' => 'Mega',
            'help' => 'Turnover: FY 2025–2026 (in ₹ Crore) 500 and above',
        ],
    ];

    public const FOCUS_AREAS = [
        'peacebuilding' => 'Peacebuilding and Conflict Prevention',
        'disease_prevention' => 'Disease Prevention and Treatment',
        'wash' => 'Water, Sanitation, and Hygiene (WASH)',
        'maternal_child_health' => 'Maternal and Child Health',
        'education_literacy' => 'Basic Education and Literacy',
        'economic_development' => 'Community Economic Development',
        'environment' => 'Environment & Nature Protection / Conservation',
    ];

    public const CORPORATE_PRESENCE_OPTIONS = [
        'national' => 'National',
        'regional' => 'Regional',
        'state' => 'State',
    ];

    /** The five field names that make up one CSR project block (Category B). */
    public const PROJECT_FIELDS = ['problem', 'intervention', 'investment', 'beneficiaries', 'outcomes'];

    /** Ordered step keys per applicant type. */
    public static function steps(string $applicantType): array
    {
        return $applicantType === 'corporate'
            ? ['category', 'focus', 'nomination', 'assessment', 'review']
            : ['profile', 'projects', 'review'];
    }

    public static function stepTitle(string $applicantType, string $stepKey): string
    {
        return match ($stepKey) {
            'category' => 'Company Information',
            'focus' => 'Primary Rotary Area of Focus',
            'nomination' => 'Corporate / Applicant Details',
            'assessment' => 'Project Details',
            'profile' => 'Personal & Professional Details',
            'projects' => 'CSR Projects',
            'review' => 'Review & Submit',
            default => ucfirst($stepKey),
        };
    }

    public static function stepKeyForNumber(string $applicantType, int $stepNumber): ?string
    {
        return self::steps($applicantType)[$stepNumber - 1] ?? null;
    }

    public static function stepNumberForKey(string $applicantType, string $stepKey): ?int
    {
        $index = array_search($stepKey, self::steps($applicantType), true);

        return $index === false ? null : $index + 1;
    }

    /** Validation rules for a given step, keyed by field name. */
    public static function rulesForStep(string $applicantType, string $stepKey): array
    {
        return match ($stepKey) {
            'category' => [
                'company_size' => ['required', 'in:'.implode(',', array_keys(self::COMPANY_SIZES))],
            ],
            'focus' => [
                'focus_area' => ['required', 'in:'.implode(',', array_keys(self::FOCUS_AREAS))],
            ],
            'nomination' => [
                'corporate_foundation_name' => ['required', 'string', 'max:255'],
                'csr_registration_number' => ['required', 'string', 'max:255'],
                'industry_sector' => ['required', 'string', 'max:255'],
                'head_office_location' => ['required', 'string', 'max:255'],
                'corporate_presence' => ['required', 'in:'.implode(',', array_keys(self::CORPORATE_PRESENCE_OPTIONS))],
                'business_group_name' => ['nullable', 'string', 'max:255'],
                'primary_contact_name' => ['required', 'string', 'max:255'],
                'primary_contact_designation' => ['required', 'string', 'max:255'],
                'primary_contact_email' => ['required', 'email', 'max:255'],
                'primary_contact_mobile' => ['required', 'string', 'max:30'],
                'secondary_contact_name' => ['nullable', 'required_with:secondary_contact_designation,secondary_contact_email,secondary_contact_mobile', 'string', 'max:255'],
                'secondary_contact_designation' => ['nullable', 'required_with:secondary_contact_name,secondary_contact_email,secondary_contact_mobile', 'string', 'max:255'],
                'secondary_contact_email' => ['nullable', 'required_with:secondary_contact_name,secondary_contact_designation,secondary_contact_mobile', 'email', 'max:255'],
                'secondary_contact_mobile' => ['nullable', 'required_with:secondary_contact_name,secondary_contact_designation,secondary_contact_email', 'string', 'max:30'],
            ],
            'assessment' => [
                'project_name' => ['required', 'string', 'max:255'],
                'project_launch_date' => ['required', 'date', 'after_or_equal:2025-04-01', 'before_or_equal:2026-03-31'],
                'project_completion_status' => ['required', 'in:completed,continuing'],
                'project_completion_date' => ['nullable', 'required_if:project_completion_status,completed', 'date', 'after_or_equal:project_launch_date'],
                'geographic_coverage' => ['required', 'string', 'max:255'],
                'csr_budget' => ['required', 'numeric', 'min:0'],
                'implementation_partners' => ['nullable', 'string'],
                'beneficiaries_impacted' => ['required', 'string'],
                'intervention_design' => ['required', 'string', new MaxWords(100)],
                'unique_feature' => ['nullable', 'string', new MaxWords(100)],
                'outcomes_impact' => ['required', 'string', new MaxWords(150)],
            ],
            'profile' => [
                'ind_designation' => ['required', 'string', 'max:255'],
                'ind_organisation' => ['required', 'string', 'max:255'],
                'ind_industry' => ['required', 'string', 'max:255'],
                'ind_location' => ['required', 'string', 'max:255'],
                'ind_csr_experience_years' => ['required', 'integer', 'min:0', 'max:80'],
                'ind_total_experience_years' => ['required', 'integer', 'min:0', 'max:80'],
                'ind_current_responsibilities' => ['required', 'string'],
                'ind_annual_budget_handled' => ['required', 'string', 'max:255'],
                'ind_geographic_responsibility' => ['required', 'string', 'max:255'],
            ],
            'projects' => self::projectRules(),
            default => [],
        };
    }

    /**
     * Flat, index-suffixed validation rules for the three CSR project slots
     * (e.g. "problem_1", "investment_2") — project 1 is required, 2 and 3 are optional.
     */
    public static function projectRules(): array
    {
        $rules = [];

        foreach ([1, 2, 3] as $slot) {
            foreach (self::PROJECT_FIELDS as $field) {
                $rules["{$field}_{$slot}"] = $slot === 1
                    ? ['required', 'string']
                    : ['nullable', 'string'];
            }
        }

        return $rules;
    }

    /** Field names that belong to a step (used to whitelist autosave payloads). */
    public static function fieldsForStep(string $applicantType, string $stepKey): array
    {
        return array_keys(self::rulesForStep($applicantType, $stepKey));
    }

    public static function deadline(): ?string
    {
        return Setting::get('submission_deadline');
    }

    public static function deadlineHasPassed(): bool
    {
        $deadline = self::deadline();

        return $deadline && Carbon::parse($deadline)->isPast();
    }
}

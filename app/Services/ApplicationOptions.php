<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Carbon;

class ApplicationOptions
{
    public const CORPORATE_CATEGORIES = [
        'small' => [
            'label' => 'Small Corporate',
            'help' => 'Average annual CSR expenditure: up to ₹2 crore',
        ],
        'medium' => [
            'label' => 'Medium Corporate',
            'help' => 'Average annual CSR expenditure: above ₹2 crore and up to ₹10 crore',
        ],
        'large' => [
            'label' => 'Large Corporate',
            'help' => 'Average annual CSR expenditure: above ₹10 crore',
        ],
    ];

    public const FOCUS_AREAS = [
        'peacebuilding' => 'Peacebuilding and Conflict Prevention',
        'disease_prevention' => 'Disease Prevention and Treatment',
        'wash' => 'Water, Sanitation, and Hygiene (WASH)',
        'maternal_child_health' => 'Maternal and Child Health',
        'education_literacy' => 'Basic Education and Literacy',
        'economic_development' => 'Community Economic Development',
        'environment' => 'Supporting the Environment: Protecting natural resources and promoting environmental sustainability',
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
            'category' => 'Corporate Category',
            'focus' => 'Primary Rotary Area of Focus',
            'nomination' => 'CSR Project Nomination',
            'assessment' => 'Project Assessment',
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
                'corporate_category' => ['required', 'in:'.implode(',', array_keys(self::CORPORATE_CATEGORIES))],
            ],
            'focus' => [
                'focus_area' => ['required', 'in:'.implode(',', array_keys(self::FOCUS_AREAS))],
            ],
            'nomination' => [
                'corporate_foundation_name' => ['required', 'string', 'max:255'],
                'csr_registration_number' => ['required', 'string', 'max:255'],
                'industry_sector' => ['required', 'string', 'max:255'],
                'head_office_location' => ['required', 'string', 'max:255'],
                'project_name' => ['required', 'string', 'max:255'],
                'project_period' => ['required', 'string', 'max:255'],
                'geographic_coverage' => ['required', 'string', 'max:255'],
                'csr_budget' => ['required', 'numeric', 'min:0'],
            ],
            'assessment' => [
                'problem_addressed' => ['required', 'string'],
                'intervention_design' => ['required', 'string'],
                'beneficiaries_impacted' => ['required', 'string'],
                'outcomes_impact' => ['required', 'string'],
                'implementation_partners' => ['required', 'string'],
                'additional_info' => ['nullable', 'string'],
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

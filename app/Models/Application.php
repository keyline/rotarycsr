<?php

namespace App\Models;

use App\Services\ApplicationOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'applicant_type', 'status', 'current_step', 'submitted_at',
        'review_status', 'reviewed_at', 'reviewed_by', 'award_winner_at', 'award_winner_by',
        'corporate_category', 'focus_area',
        'corporate_foundation_name', 'csr_registration_number', 'industry_sector',
        'head_office_location', 'project_name', 'project_period', 'geographic_coverage', 'csr_budget',
        'problem_addressed', 'intervention_design', 'beneficiaries_impacted', 'outcomes_impact',
        'implementation_partners', 'additional_info',
        'ind_designation', 'ind_organisation', 'ind_industry', 'ind_location',
        'ind_csr_experience_years', 'ind_total_experience_years', 'ind_current_responsibilities',
        'ind_annual_budget_handled', 'ind_geographic_responsibility', 'ind_projects',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'award_winner_at' => 'datetime',
            'csr_budget' => 'decimal:2',
            'ind_projects' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function awardSelector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'award_winner_by');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /** Locked once submitted, or once the global deadline has passed. */
    public function isLocked(): bool
    {
        return $this->isSubmitted() || ApplicationOptions::deadlineHasPassed();
    }

    public function totalSteps(): int
    {
        return count(ApplicationOptions::steps($this->applicant_type));
    }

    public function progressPercent(): int
    {
        if ($this->isSubmitted()) {
            return 100;
        }

        return (int) round((($this->current_step - 1) / $this->totalSteps()) * 100);
    }
}

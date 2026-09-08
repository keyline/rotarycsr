<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Snapshot of the stream this application is for (individual's applicant_type
            // could theoretically change later, this keeps the application self-consistent).
            $table->enum('applicant_type', ['corporate', 'individual']);

            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->timestamp('submitted_at')->nullable();

            // --- Category A: Corporate Excellence Award ---
            $table->enum('corporate_category', ['small', 'medium', 'large'])->nullable();
            $table->string('focus_area')->nullable();

            $table->string('corporate_foundation_name')->nullable();
            $table->string('csr_registration_number')->nullable();
            $table->string('industry_sector')->nullable();
            $table->string('head_office_location')->nullable();
            $table->string('project_name')->nullable();
            $table->string('project_period')->nullable();
            $table->string('geographic_coverage')->nullable();
            $table->decimal('csr_budget', 15, 2)->nullable();

            $table->text('problem_addressed')->nullable();
            $table->text('intervention_design')->nullable();
            $table->text('beneficiaries_impacted')->nullable();
            $table->text('outcomes_impact')->nullable();
            $table->text('implementation_partners')->nullable();
            $table->text('additional_info')->nullable();

            // --- Category B: CSR Leader of the Year (Individual) ---
            $table->string('ind_designation')->nullable();
            $table->string('ind_organisation')->nullable();
            $table->string('ind_industry')->nullable();
            $table->string('ind_location')->nullable();
            $table->unsignedSmallInteger('ind_csr_experience_years')->nullable();
            $table->unsignedSmallInteger('ind_total_experience_years')->nullable();
            $table->text('ind_current_responsibilities')->nullable();
            $table->string('ind_annual_budget_handled')->nullable();
            $table->string('ind_geographic_responsibility')->nullable();

            // Up to 3 repeatable project blocks, each:
            // {problem, intervention, investment, beneficiaries, outcomes}
            $table->json('ind_projects')->nullable();

            $table->timestamps();

            $table->index(['applicant_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};

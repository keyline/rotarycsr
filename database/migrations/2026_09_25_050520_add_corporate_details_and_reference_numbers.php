<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->unique()->after('id');
            $table->string('corporate_presence')->nullable()->after('head_office_location');
            $table->string('business_group_name')->nullable()->after('corporate_presence');
            $table->string('primary_contact_name')->nullable()->after('business_group_name');
            $table->string('primary_contact_designation')->nullable()->after('primary_contact_name');
            $table->string('primary_contact_email')->nullable()->after('primary_contact_designation');
            $table->string('primary_contact_mobile', 30)->nullable()->after('primary_contact_email');
            $table->string('secondary_contact_name')->nullable()->after('primary_contact_mobile');
            $table->string('secondary_contact_designation')->nullable()->after('secondary_contact_name');
            $table->string('secondary_contact_email')->nullable()->after('secondary_contact_designation');
            $table->string('secondary_contact_mobile', 30)->nullable()->after('secondary_contact_email');
            $table->date('project_launch_date')->nullable()->after('project_name');
            $table->string('project_completion_status', 20)->nullable()->after('project_launch_date');
            $table->date('project_completion_date')->nullable()->after('project_completion_status');
            $table->text('unique_feature')->nullable()->after('intervention_design');
        });

        Schema::create('application_number_sequences', function (Blueprint $table) {
            $table->string('applicant_type')->primary();
            $table->unsignedBigInteger('last_number')->default(0);
        });

        foreach (['corporate' => 'CP', 'individual' => 'IN'] as $applicantType => $prefix) {
            $applications = DB::table('applications')
                ->where('applicant_type', $applicantType)
                ->where('status', 'submitted')
                ->orderBy('submitted_at')
                ->orderBy('id')
                ->get(['id']);

            foreach ($applications as $index => $application) {
                DB::table('applications')->where('id', $application->id)->update([
                    'reference_number' => sprintf('RICSR/%s/%04d', $prefix, $index + 1),
                ]);
            }

            DB::table('application_number_sequences')->insert([
                'applicant_type' => $applicantType,
                'last_number' => $applications->count(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_number_sequences');

        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique(['reference_number']);
            $table->dropColumn([
                'reference_number',
                'corporate_presence',
                'business_group_name',
                'primary_contact_name',
                'primary_contact_designation',
                'primary_contact_email',
                'primary_contact_mobile',
                'secondary_contact_name',
                'secondary_contact_designation',
                'secondary_contact_email',
                'secondary_contact_mobile',
                'project_launch_date',
                'project_completion_status',
                'project_completion_date',
                'unique_feature',
            ]);
        });
    }
};

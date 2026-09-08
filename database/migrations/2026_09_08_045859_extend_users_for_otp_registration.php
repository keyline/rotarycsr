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
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('applicant_type');
            $table->string('otp_code')->nullable()->after('remember_token');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });

        DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'otp_code', 'otp_expires_at']);
        });

        DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL');
    }
};

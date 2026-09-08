<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Who performed the action (nullable — guests/failed logins have no user yet).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('causer_name')->nullable();
            $table->string('causer_email')->nullable();

            // What happened, e.g. "auth.login", "auth.register.otp_sent", "application.submitted".
            $table->string('event');
            $table->text('description')->nullable();

            // Optional link to the record the action affected (polymorphic).
            $table->nullableMorphs('subject');

            // Arbitrary structured context (old/new values, extra metadata).
            $table->json('properties')->nullable();

            // Request context.
            $table->string('method', 10)->nullable();
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['event']);
            $table->index(['user_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

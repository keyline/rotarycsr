<?php

namespace Database\Factories;

use App\Models\ApplicantEmailLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicantEmailLog>
 */
class ApplicantEmailLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_id' => User::factory(),
            'application_id' => null,
            'sent_by' => null,
            'type' => 'general',
            'recipient_email' => fake()->safeEmail(),
            'recipient_name' => fake()->name(),
            'subject' => fake()->sentence(),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'sent_at' => now(),
        ];
    }
}

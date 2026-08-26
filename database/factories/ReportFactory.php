<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Enums\ReportTargetType;
use App\Models\Organization;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reporter_user_id' => User::factory(),
            'organization_id' => Organization::factory(),
            'target_type' => ReportTargetType::User,
            'target_id' => User::factory(),
            'reason' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'status' => ReportStatus::Open,
            'resolved_by' => null,
            'resolved_at' => null,
            'resolution_notes' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\GroupStatus;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'status' => GroupStatus::Active,
            'created_by' => User::factory(),
            'archived_at' => null,
        ];
    }
}

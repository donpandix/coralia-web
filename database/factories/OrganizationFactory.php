<?php

namespace Database\Factories;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => fake()->optional()->paragraph(),
            'logo_path' => null,
            'owner_user_id' => User::factory(),
            'status' => OrganizationStatus::Active,
            'city' => fake()->city(),
            'archived_at' => null,
        ];
    }
}

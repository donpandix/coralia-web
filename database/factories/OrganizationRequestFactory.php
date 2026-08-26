<?php

namespace Database\Factories;

use App\Enums\OrganizationRequestStatus;
use App\Models\OrganizationRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationRequest>
 */
class OrganizationRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requested_by' => User::factory(),
            'organization_name' => fake()->company(),
            'description' => fake()->optional()->paragraph(),
            'city' => fake()->city(),
            'additional_info' => fake()->optional()->sentence(),
            'status' => OrganizationRequestStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
            'organization_id' => null,
        ];
    }
}

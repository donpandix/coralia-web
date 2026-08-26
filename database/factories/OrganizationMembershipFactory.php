<?php

namespace Database\Factories;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Enums\VoiceType;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationMembership>
 */
class OrganizationMembershipFactory extends Factory
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
            'user_id' => User::factory(),
            'role' => OrganizationRole::Member,
            'voice_type' => fake()->randomElement([
                VoiceType::Soprano,
                VoiceType::Alto,
                VoiceType::Tenor,
                VoiceType::Bass,
            ]),
            'status' => OrganizationMembershipStatus::Active,
            'requested_at' => now(),
            'approved_at' => now(),
            'approved_by' => null,
            'joined_at' => now(),
            'left_at' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => OrganizationRole::Admin,
            'voice_type' => null,
        ]);
    }

    public function forVoice(VoiceType $voiceType): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => OrganizationRole::Member,
            'voice_type' => $voiceType,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => null,
            'joined_at' => null,
            'status' => OrganizationMembershipStatus::Pending,
        ]);
    }
}

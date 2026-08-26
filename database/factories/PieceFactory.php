<?php

namespace Database\Factories;

use App\Enums\PieceStatus;
use App\Models\Organization;
use App\Models\Piece;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Piece>
 */
class PieceFactory extends Factory
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
            'title' => fake()->words(3, true),
            'subtitle' => fake()->optional()->sentence(4),
            'body' => fake()->optional()->paragraph(),
            'status' => PieceStatus::Active,
            'created_by' => User::factory(),
            'updated_by' => null,
            'published_at' => now(),
            'archived_at' => null,
        ];
    }
}

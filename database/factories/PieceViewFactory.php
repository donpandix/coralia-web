<?php

namespace Database\Factories;

use App\Models\Piece;
use App\Models\PieceView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PieceView>
 */
class PieceViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'piece_id' => Piece::factory(),
            'first_viewed_at' => now()->subDay(),
            'last_viewed_at' => now(),
            'view_count' => fake()->numberBetween(1, 20),
        ];
    }
}

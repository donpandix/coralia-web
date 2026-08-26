<?php

namespace Database\Factories;

use App\Enums\TagStatus;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::of(fake()->unique()->sentence(2))->trim('.')->toString();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'status' => TagStatus::Active,
            'created_by' => User::factory(),
        ];
    }
}

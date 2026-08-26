<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
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
            'organization_id' => Organization::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'archived']),
            'entity_type' => 'PIECE',
            'entity_id' => fake()->numberBetween(1, 1000),
            'old_values' => null,
            'new_values' => ['status' => 'ACTIVE'],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}

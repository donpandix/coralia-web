<?php

namespace Database\Factories;

use App\Enums\PieceShareType;
use App\Enums\VoiceType;
use App\Models\Group;
use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\PieceShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PieceShare>
 */
class PieceShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'piece_id' => Piece::factory(),
            'share_type' => PieceShareType::Organization,
            'voice_type' => null,
            'group_id' => null,
            'membership_id' => null,
            'created_by' => User::factory(),
        ];
    }

    public function forVoice(VoiceType $voiceType): static
    {
        return $this->state(fn (array $attributes) => [
            'share_type' => PieceShareType::Voice,
            'voice_type' => $voiceType,
            'group_id' => null,
            'membership_id' => null,
        ]);
    }

    public function forGroup(Group $group): static
    {
        return $this->state(fn (array $attributes) => [
            'piece_id' => Piece::factory()->state(['organization_id' => $group->organization_id]),
            'share_type' => PieceShareType::Group,
            'voice_type' => null,
            'group_id' => $group->getKey(),
            'membership_id' => null,
        ]);
    }

    public function forMembership(OrganizationMembership $membership): static
    {
        return $this->state(fn (array $attributes) => [
            'piece_id' => Piece::factory()->state(['organization_id' => $membership->organization_id]),
            'share_type' => PieceShareType::Member,
            'voice_type' => null,
            'group_id' => null,
            'membership_id' => $membership->getKey(),
        ]);
    }
}

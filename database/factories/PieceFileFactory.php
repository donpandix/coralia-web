<?php

namespace Database\Factories;

use App\Enums\PieceFileType;
use App\Enums\VoiceType;
use App\Models\Piece;
use App\Models\PieceFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PieceFile>
 */
class PieceFileFactory extends Factory
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
            'file_type' => PieceFileType::Score,
            'voice_type' => VoiceType::General,
            'storage_disk' => 'local',
            'storage_path' => 'pieces/'.fake()->uuid().'.pdf',
            'original_filename' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 5 * 1024 * 1024),
            'duration_seconds' => null,
            'checksum' => fake()->sha256(),
            'created_by' => User::factory(),
        ];
    }

    public function audio(VoiceType $voiceType): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_seconds' => fake()->numberBetween(30, 600),
            'file_type' => PieceFileType::Audio,
            'mime_type' => 'audio/mpeg',
            'original_filename' => fake()->word().'.mp3',
            'storage_path' => 'pieces/'.fake()->uuid().'.mp3',
            'voice_type' => $voiceType,
        ]);
    }
}

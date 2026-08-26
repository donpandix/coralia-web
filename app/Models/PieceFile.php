<?php

namespace App\Models;

use App\Enums\PieceFileType;
use App\Enums\VoiceType;
use App\Models\Concerns\HasPublicId;
use Database\Factories\PieceFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * @property PieceFileType $file_type
 * @property VoiceType $voice_type
 */
#[Fillable(['piece_id', 'file_type', 'voice_type', 'storage_disk', 'storage_path', 'original_filename', 'mime_type', 'file_size', 'duration_seconds', 'checksum', 'created_by'])]
class PieceFile extends Model
{
    /** @use HasFactory<PieceFileFactory> */
    use HasFactory, HasPublicId;

    /** @return BelongsTo<Piece, $this> */
    public function piece(): BelongsTo
    {
        return $this->belongsTo(Piece::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::saving(function (self $pieceFile): void {
            $isValidScore = $pieceFile->file_type === PieceFileType::Score
                && $pieceFile->voice_type === VoiceType::General;
            $isValidAudio = $pieceFile->file_type === PieceFileType::Audio
                && $pieceFile->voice_type->isChoralVoice();

            if (! $isValidScore && ! $isValidAudio) {
                throw ValidationException::withMessages([
                    'voice_type' => 'Scores must be general and audio files must target a choral voice.',
                ]);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'file_size' => 'integer',
            'file_type' => PieceFileType::class,
            'voice_type' => VoiceType::class,
        ];
    }
}

<?php

namespace App\Models;

use Database\Factories\NotificationPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'new_piece', 'voice_audio_added', 'membership_changes', 'administrative_events'])]
class NotificationPreference extends Model
{
    /** @use HasFactory<NotificationPreferenceFactory> */
    use HasFactory;

    protected $attributes = [
        'administrative_events' => true,
        'membership_changes' => true,
        'new_piece' => true,
        'voice_audio_added' => true,
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'administrative_events' => 'boolean',
            'membership_changes' => 'boolean',
            'new_piece' => 'boolean',
            'voice_audio_added' => 'boolean',
        ];
    }
}

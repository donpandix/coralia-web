<?php

namespace App\Models;

use App\Enums\TagStatus;
use App\Models\Concerns\HasPublicId;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'status', 'created_by'])]
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory, HasPublicId;

    protected $attributes = [
        'status' => TagStatus::Active->value,
    ];

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsToMany<Piece, $this, PieceTag, 'pivot'> */
    public function pieces(): BelongsToMany
    {
        return $this->belongsToMany(Piece::class, 'piece_tags')
            ->using(PieceTag::class)
            ->withPivot('created_at');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TagStatus::class,
        ];
    }
}

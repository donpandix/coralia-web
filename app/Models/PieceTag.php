<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $piece_id
 * @property int $tag_id
 */
#[Fillable(['piece_id', 'tag_id'])]
class PieceTag extends Pivot
{
    public $incrementing = false;

    public $timestamps = false;

    public function getUpdatedAtColumn(): ?string
    {
        return null;
    }

    /** @return BelongsTo<Piece, $this> */
    public function piece(): BelongsTo
    {
        return $this->belongsTo(Piece::class);
    }

    /** @return BelongsTo<Tag, $this> */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}

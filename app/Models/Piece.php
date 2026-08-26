<?php

namespace App\Models;

use App\Enums\PieceShareType;
use App\Enums\PieceStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasPublicId;
use Database\Factories\PieceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'title', 'subtitle', 'body', 'status', 'created_by', 'updated_by', 'published_at', 'archived_at'])]
class Piece extends Model
{
    /** @use HasFactory<PieceFactory> */
    use BelongsToOrganization, HasFactory, HasPublicId;

    protected $attributes = [
        'status' => PieceStatus::Active->value,
    ];

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @return HasMany<PieceFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(PieceFile::class);
    }

    /** @return BelongsToMany<Tag, $this, PieceTag, 'pivot'> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'piece_tags')
            ->using(PieceTag::class)
            ->withPivot('created_at');
    }

    /** @return HasMany<PieceShare, $this> */
    public function shares(): HasMany
    {
        return $this->hasMany(PieceShare::class);
    }

    /** @return HasMany<Favorite, $this> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @return HasMany<PieceView, $this> */
    public function views(): HasMany
    {
        return $this->hasMany(PieceView::class);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleToMembership(Builder $query, OrganizationMembership $membership): Builder
    {
        $groupIds = $membership->groups()->pluck('groups.id');

        return $query
            ->where('organization_id', $membership->organization_id)
            ->where('status', PieceStatus::Active)
            ->whereNotNull('published_at')
            ->where(function (Builder $pieceQuery) use ($membership, $groupIds): void {
                $pieceQuery->whereHas('shares', fn (Builder $shareQuery): Builder => $shareQuery
                    ->where('share_type', PieceShareType::Organization))
                    ->orWhereHas('shares', fn (Builder $shareQuery): Builder => $shareQuery
                        ->where('share_type', PieceShareType::Voice)
                        ->where('voice_type', $membership->voice_type))
                    ->orWhereHas('shares', fn (Builder $shareQuery): Builder => $shareQuery
                        ->where('share_type', PieceShareType::Group)
                        ->whereIn('group_id', $groupIds))
                    ->orWhereHas('shares', fn (Builder $shareQuery): Builder => $shareQuery
                        ->where('share_type', PieceShareType::Member)
                        ->where('membership_id', $membership->id));
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
            'archived_at' => 'datetime',
            'published_at' => 'datetime',
            'status' => PieceStatus::class,
        ];
    }
}

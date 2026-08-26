<?php

namespace App\Models;

use App\Enums\PieceShareType;
use App\Enums\VoiceType;
use Database\Factories\PieceShareFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * @property PieceShareType $share_type
 * @property VoiceType|null $voice_type
 */
#[Fillable(['piece_id', 'share_type', 'voice_type', 'group_id', 'membership_id', 'created_by'])]
class PieceShare extends Model
{
    /** @use HasFactory<PieceShareFactory> */
    use HasFactory;

    public $timestamps = false;

    /** @return BelongsTo<Piece, $this> */
    public function piece(): BelongsTo
    {
        return $this->belongsTo(Piece::class);
    }

    /** @return BelongsTo<Group, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /** @return BelongsTo<OrganizationMembership, $this> */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(OrganizationMembership::class, 'membership_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope the query to shares for pieces in an organization.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForOrganization(Builder $query, Organization|int $organization): Builder
    {
        $organizationId = $organization instanceof Organization ? $organization->getKey() : $organization;

        return $query->whereHas(
            'piece',
            fn (Builder $pieceQuery): Builder => $pieceQuery->where('organization_id', $organizationId),
        );
    }

    protected static function booted(): void
    {
        static::saving(function (self $pieceShare): void {
            $pieceShare->validateShape();
            $pieceShare->validateOrganizationBoundaries();
        });
    }

    protected function validateShape(): void
    {
        $hasVoice = $this->voice_type !== null;
        $hasGroup = $this->group_id !== null;
        $hasMembership = $this->membership_id !== null;

        $isValid = match ($this->share_type) {
            PieceShareType::Organization => ! $hasVoice && ! $hasGroup && ! $hasMembership,
            PieceShareType::Voice => $hasVoice && ! $hasGroup && ! $hasMembership
                && $this->voice_type->isChoralVoice(),
            PieceShareType::Group => ! $hasVoice && $hasGroup && ! $hasMembership,
            PieceShareType::Member => ! $hasVoice && ! $hasGroup && $hasMembership,
        };

        if (! $isValid) {
            throw ValidationException::withMessages([
                'share_type' => 'The share target does not match the selected share type.',
            ]);
        }
    }

    protected function validateOrganizationBoundaries(): void
    {
        $pieceOrganizationId = Piece::query()->whereKey($this->piece_id)->value('organization_id');

        if ($pieceOrganizationId === null) {
            return;
        }

        if ($this->group_id !== null) {
            $groupOrganizationId = Group::query()->whereKey($this->group_id)->value('organization_id');

            if ($groupOrganizationId !== null && (int) $pieceOrganizationId !== (int) $groupOrganizationId) {
                throw ValidationException::withMessages([
                    'group_id' => 'The group must belong to the same organization as the piece.',
                ]);
            }
        }

        if ($this->membership_id !== null) {
            $membershipOrganizationId = OrganizationMembership::query()
                ->whereKey($this->membership_id)
                ->value('organization_id');

            if ($membershipOrganizationId !== null && (int) $pieceOrganizationId !== (int) $membershipOrganizationId) {
                throw ValidationException::withMessages([
                    'membership_id' => 'The membership must belong to the same organization as the piece.',
                ]);
            }
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'share_type' => PieceShareType::class,
            'voice_type' => VoiceType::class,
        ];
    }
}

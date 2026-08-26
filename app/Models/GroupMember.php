<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Validation\ValidationException;

/**
 * @property int $group_id
 * @property int $membership_id
 */
#[Fillable(['group_id', 'membership_id'])]
class GroupMember extends Pivot
{
    public $incrementing = true;

    public $timestamps = false;

    public function getUpdatedAtColumn(): ?string
    {
        return null;
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

    protected static function booted(): void
    {
        static::saving(function (self $groupMember): void {
            $groupOrganizationId = Group::query()
                ->whereKey($groupMember->group_id)
                ->value('organization_id');
            $membershipOrganizationId = OrganizationMembership::query()
                ->whereKey($groupMember->membership_id)
                ->value('organization_id');

            if ($groupOrganizationId === null || $membershipOrganizationId === null) {
                return;
            }

            if ((int) $groupOrganizationId !== (int) $membershipOrganizationId) {
                throw ValidationException::withMessages([
                    'membership_id' => 'The membership must belong to the same organization as the group.',
                ]);
            }
        });
    }
}

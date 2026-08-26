<?php

namespace App\Models;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Enums\VoiceType;
use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\OrganizationMembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'user_id', 'role', 'voice_type', 'status', 'requested_at', 'approved_at', 'approved_by', 'joined_at', 'left_at'])]
class OrganizationMembership extends Model
{
    /** @use HasFactory<OrganizationMembershipFactory> */
    use BelongsToOrganization, HasFactory;

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsToMany<Group, $this, GroupMember, 'pivot'> */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members', 'membership_id', 'group_id')
            ->using(GroupMember::class)
            ->withPivot(['id', 'created_at']);
    }

    /** @return HasMany<GroupMember, $this> */
    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'membership_id');
    }

    /** @return HasMany<PieceShare, $this> */
    public function shares(): HasMany
    {
        return $this->hasMany(PieceShare::class, 'membership_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'requested_at' => 'datetime',
            'role' => OrganizationRole::class,
            'status' => OrganizationMembershipStatus::class,
            'voice_type' => VoiceType::class,
        ];
    }
}

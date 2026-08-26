<?php

namespace App\Models;

use App\Enums\GroupStatus;
use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasPublicId;
use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'name', 'description', 'status', 'created_by', 'archived_at'])]
class Group extends Model
{
    /** @use HasFactory<GroupFactory> */
    use BelongsToOrganization, HasFactory, HasPublicId;

    protected $attributes = [
        'status' => GroupStatus::Active->value,
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

    /** @return BelongsToMany<OrganizationMembership, $this, GroupMember, 'pivot'> */
    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(OrganizationMembership::class, 'group_members', 'group_id', 'membership_id')
            ->using(GroupMember::class)
            ->withPivot(['id', 'created_at']);
    }

    /** @return HasMany<GroupMember, $this> */
    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    /** @return HasMany<PieceShare, $this> */
    public function shares(): HasMany
    {
        return $this->hasMany(PieceShare::class);
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
            'status' => GroupStatus::class,
        ];
    }
}

<?php

namespace App\Support;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CurrentOrganization
{
    private const SessionKey = 'coralia.current_organization_id';

    /** @return Collection<int, OrganizationMembership> */
    public function memberships(User $user): Collection
    {
        return $user->memberships()
            ->where('status', OrganizationMembershipStatus::Active)
            ->whereHas('organization', fn ($query) => $query->where('status', OrganizationStatus::Active))
            ->with('organization')
            ->orderBy('id')
            ->get();
    }

    public function membership(User $user): ?OrganizationMembership
    {
        $memberships = $this->memberships($user);

        if ($memberships->isEmpty()) {
            session()->forget(self::SessionKey);

            return null;
        }

        $selectedId = session(self::SessionKey);
        $membership = $memberships->firstWhere('organization_id', $selectedId) ?? $memberships->first();

        session([self::SessionKey => $membership->organization_id]);

        return $membership;
    }

    public function organization(User $user): ?Organization
    {
        return $this->membership($user)?->organization;
    }

    public function select(User $user, int $organizationId): void
    {
        $membership = $this->memberships($user)->firstWhere('organization_id', $organizationId);

        if ($membership === null) {
            throw ValidationException::withMessages([
                'organization' => 'No tienes una membresía activa en esa organización.',
            ]);
        }

        session([self::SessionKey => $organizationId]);
    }

    public function isAdmin(User $user): bool
    {
        return $this->membership($user)?->role === OrganizationRole::Admin;
    }
}

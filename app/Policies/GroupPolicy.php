<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use App\Support\CurrentOrganization;

class GroupPolicy
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->currentOrganization->membership($user) !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Group $group): bool
    {
        return $group->organization_id === $this->currentOrganization->organization($user)?->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->currentOrganization->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Group $group): bool
    {
        return $this->currentOrganization->isAdmin($user) && $this->view($user, $group);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Group $group): bool
    {
        return $this->update($user, $group);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Group $group): bool
    {
        return $this->update($user, $group);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Group $group): bool
    {
        return $this->update($user, $group);
    }
}

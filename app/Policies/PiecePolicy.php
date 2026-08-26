<?php

namespace App\Policies;

use App\Models\Piece;
use App\Models\User;
use App\Support\CurrentOrganization;

class PiecePolicy
{
    public function __construct(private CurrentOrganization $currentOrganization) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_super_admin || $this->currentOrganization->membership($user) !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Piece $piece): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        $membership = $this->currentOrganization->membership($user);

        if ($membership === null || $piece->organization_id !== $membership->organization_id) {
            return false;
        }

        return $this->currentOrganization->isAdmin($user)
            || Piece::query()->visibleToMembership($membership)->whereKey($piece)->exists();
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
    public function update(User $user, Piece $piece): bool
    {
        return $this->currentOrganization->isAdmin($user)
            && $piece->organization_id === $this->currentOrganization->organization($user)?->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Piece $piece): bool
    {
        return $this->update($user, $piece);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Piece $piece): bool
    {
        return $this->update($user, $piece);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Piece $piece): bool
    {
        return $this->update($user, $piece);
    }
}

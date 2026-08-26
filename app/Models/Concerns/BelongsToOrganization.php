<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToOrganization
{
    /**
     * Scope the query to an organization.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForOrganization(Builder $query, Organization|int $organization): Builder
    {
        return $query->where(
            $this->qualifyColumn('organization_id'),
            $organization instanceof Organization ? $organization->getKey() : $organization,
        );
    }
}

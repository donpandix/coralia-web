<?php

use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Support\CurrentOrganization;
use Illuminate\Validation\ValidationException;

it('selects the first active membership and persists an explicit selection', function () {
    $user = User::factory()->create();
    $first = OrganizationMembership::factory()->for($user)->create();
    $second = OrganizationMembership::factory()->for($user)->create();
    $currentOrganization = app(CurrentOrganization::class);

    expect($currentOrganization->membership($user)->is($first))->toBeTrue();

    $currentOrganization->select($user, $second->organization_id);

    expect($currentOrganization->membership($user)->is($second))->toBeTrue();
});

it('rejects an organization without an active membership', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    expect(fn () => app(CurrentOrganization::class)->select($user, $organization->id))
        ->toThrow(ValidationException::class);
});

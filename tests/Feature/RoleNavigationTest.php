<?php

use App\Models\OrganizationMembership;
use App\Models\User;

it('allows an organization administrator to open organization management', function () {
    $admin = User::factory()->create();
    OrganizationMembership::factory()->admin()->for($admin)->create();

    $this->actingAs($admin)->get(route('organization.members'))->assertOk()->assertSee('Miembros');
});

it('forbids a member from organization management', function () {
    $member = User::factory()->create();
    OrganizationMembership::factory()->for($member)->create();

    $this->actingAs($member)->get(route('organization.members'))->assertForbidden();
});

it('allows only super administrators to open global administration', function () {
    $regularUser = User::factory()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($regularUser)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard');
});

it('renders organization administration page :routeName', function (string $routeName) {
    $admin = User::factory()->create();
    OrganizationMembership::factory()->admin()->for($admin)->create();

    $this->actingAs($admin)->get(route($routeName))->assertOk();
})->with([
    'organization settings' => 'organization.settings',
    'members' => 'organization.members',
    'membership requests' => 'organization.requests',
    'groups' => 'organization.groups',
    'pieces' => 'organization.pieces.index',
    'piece creation' => 'organization.pieces.create',
]);

it('renders super administration page :routeName', function (string $routeName) {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)->get(route($routeName))->assertOk();
})->with([
    'organizations' => 'admin.organizations',
    'organization requests' => 'admin.requests',
    'users' => 'admin.users',
    'tags' => 'admin.tags',
    'reports' => 'admin.reports',
]);

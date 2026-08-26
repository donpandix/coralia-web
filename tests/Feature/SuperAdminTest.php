<?php

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRequestStatus;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\OrganizationRequest;
use App\Models\User;
use App\Notifications\MembershipApproved;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('approves an organization request and grants administration to its requester', function () {
    Notification::fake();
    $superAdmin = User::factory()->superAdmin()->create();
    $requester = User::factory()->create();
    $request = OrganizationRequest::factory()->for($requester, 'requester')->create(['organization_name' => 'Coro Nuevo', 'status' => OrganizationRequestStatus::Pending]);
    $this->actingAs($superAdmin);

    Livewire::test('pages::admin.requests')->call('approve', $request->id)->assertHasNoErrors();

    $organization = Organization::query()->where('name', 'Coro Nuevo')->firstOrFail();
    $membership = OrganizationMembership::query()->whereBelongsTo($organization)->whereBelongsTo($requester)->firstOrFail();
    expect($request->refresh()->status)->toBe(OrganizationRequestStatus::Approved)
        ->and($membership->status)->toBe(OrganizationMembershipStatus::Active)
        ->and($membership->role)->toBe(OrganizationRole::Admin);
    Notification::assertSentTo($requester, MembershipApproved::class);
});

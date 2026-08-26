<?php

use App\Enums\OrganizationMembershipStatus;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Notifications\MembershipApproved;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('approves a pending membership in the active organization', function () {
    Notification::fake();
    $admin = User::factory()->create();
    $adminMembership = OrganizationMembership::factory()->admin()->for($admin)->create();
    $pendingMembership = OrganizationMembership::factory()->pending()->for($adminMembership->organization)->create();
    $this->actingAs($admin);

    Livewire::test('pages::organization.requests')
        ->call('approve', $pendingMembership->id)
        ->assertHasNoErrors();

    expect($pendingMembership->refresh()->status)->toBe(OrganizationMembershipStatus::Active)
        ->and($pendingMembership->approved_by)->toBe($admin->id);
    Notification::assertSentTo($pendingMembership->user, MembershipApproved::class);
});

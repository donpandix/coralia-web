<?php

use App\Enums\OrganizationMembershipStatus;
use App\Enums\VoiceType;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Livewire\Livewire;

it('creates a pending membership when a user applies to an organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['name' => 'Coro de Cámara']);
    $this->actingAs($user);

    Livewire::test('pages::onboarding')
        ->set('voiceType', VoiceType::Tenor->value)
        ->call('apply', $organization->id)
        ->assertHasNoErrors();

    $membership = OrganizationMembership::query()->whereBelongsTo($user)->whereBelongsTo($organization)->firstOrFail();
    expect($membership->status)->toBe(OrganizationMembershipStatus::Pending)
        ->and($membership->voice_type)->toBe(VoiceType::Tenor);
});

it('requires a voice before applying to an organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::onboarding')
        ->call('apply', $organization->id)
        ->assertHasErrors(['voiceType' => 'required']);

    expect(OrganizationMembership::query()->whereBelongsTo($user)->exists())->toBeFalse();
});

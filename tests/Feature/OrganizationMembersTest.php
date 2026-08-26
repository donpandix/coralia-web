<?php

use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('renders member avatars with profile photos and initials as fallback', function () {
    $admin = User::factory()->create();
    $adminMembership = OrganizationMembership::factory()->admin()->for($admin)->create();
    $memberWithPhoto = User::factory()->create([
        'name' => 'Ana Silva',
        'photo_path' => 'profile-photos/ana.webp',
    ]);
    $memberWithPhotoMembership = OrganizationMembership::factory()
        ->for($adminMembership->organization)
        ->for($memberWithPhoto)
        ->create();
    $memberWithoutPhoto = User::factory()->create(['name' => 'Carlos Díaz']);
    $memberWithoutPhotoMembership = OrganizationMembership::factory()
        ->for($adminMembership->organization)
        ->for($memberWithoutPhoto)
        ->create();

    $response = $this->actingAs($admin)->get(route('organization.members'));

    $response
        ->assertSeeHtml('data-test="member-avatar-'.$memberWithPhotoMembership->id.'"')
        ->assertSeeHtml('src="'.Storage::disk('public')->url('profile-photos/ana.webp').'"')
        ->assertSeeHtml('alt="Ana Silva"')
        ->assertSeeHtml('data-test="member-avatar-'.$memberWithoutPhotoMembership->id.'"')
        ->assertSee('CD');
});

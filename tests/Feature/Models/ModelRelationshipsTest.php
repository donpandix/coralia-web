<?php

use App\Enums\PieceShareType;
use App\Enums\ReportTargetType;
use App\Models\Favorite;
use App\Models\Group;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\OrganizationRequest;
use App\Models\Piece;
use App\Models\PieceFile;
use App\Models\PieceShare;
use App\Models\PieceView;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;

test('connects the required domain relationships', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $organization = Organization::factory()->for($owner, 'owner')->create();
    $membership = OrganizationMembership::factory()->for($organization)->for($member)->create();
    $group = Group::factory()->for($organization)->for($owner, 'creator')->create();
    $group->memberships()->attach($membership);
    $tag = Tag::factory()->for($owner, 'creator')->create();
    $piece = Piece::factory()->for($organization)->for($owner, 'creator')->create();
    $piece->tags()->attach($tag);
    $file = PieceFile::factory()->for($piece)->for($owner, 'creator')->create();
    $share = PieceShare::factory()->for($piece)->for($owner, 'creator')->create();
    $favorite = Favorite::factory()->for($member)->for($piece)->create();
    $view = PieceView::factory()->for($member)->for($piece)->create();

    $organization->load(['owner', 'memberships', 'groups', 'pieces']);
    $membership->load(['user', 'organization', 'groups']);
    $piece->load(['organization', 'files', 'tags', 'shares', 'favorites', 'views']);

    expect($organization->owner->is($owner))->toBeTrue()
        ->and($organization->memberships->contains($membership))->toBeTrue()
        ->and($organization->groups->contains($group))->toBeTrue()
        ->and($organization->pieces->contains($piece))->toBeTrue()
        ->and($membership->user->is($member))->toBeTrue()
        ->and($membership->organization->is($organization))->toBeTrue()
        ->and($membership->groups->contains($group))->toBeTrue()
        ->and($piece->files->contains($file))->toBeTrue()
        ->and($piece->tags->contains($tag))->toBeTrue()
        ->and($piece->shares->contains($share))->toBeTrue()
        ->and($piece->favorites->contains($favorite))->toBeTrue()
        ->and($piece->views->contains($view))->toBeTrue();
});

test('generates UUIDs for API exposed entities', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->for($user, 'owner')->create();
    $piece = Piece::factory()->for($organization)->for($user, 'creator')->create();
    $models = [
        $user,
        $organization,
        OrganizationRequest::factory()->for($user, 'requester')->create(),
        Group::factory()->for($organization)->for($user, 'creator')->create(),
        Tag::factory()->for($user, 'creator')->create(),
        $piece,
        PieceFile::factory()->for($piece)->for($user, 'creator')->create(),
        Report::factory()->for($organization)->for($user, 'reporter')->create([
            'target_type' => ReportTargetType::Piece,
            'target_id' => $piece->id,
        ]),
    ];

    foreach ($models as $model) {
        expect($model->public_id)->toBeUuid();
    }
});

test('issues Sanctum tokens without storing the plain text token', function () {
    $user = User::factory()->create();

    $accessToken = $user->createToken('ios');

    expect($accessToken->plainTextToken)->toContain('|')
        ->and($user->tokens()->firstOrFail()->token)
        ->not->toBe($accessToken->plainTextToken)
        ->toHaveLength(64);
});

test('casts share types to enums', function () {
    $share = PieceShare::factory()->create();

    expect($share->share_type)->toBe(PieceShareType::Organization);
});

<?php

use App\Enums\PieceFileType;
use App\Enums\PieceShareType;
use App\Enums\VoiceType;
use App\Models\Group;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\PieceFile;
use App\Models\PieceShare;
use Illuminate\Validation\ValidationException;

test('rejects adding a membership from another organization to a group', function () {
    $group = Group::factory()->create();
    $membership = OrganizationMembership::factory()->create();

    expect(fn () => $group->memberships()->attach($membership))
        ->toThrow(ValidationException::class, 'same organization as the group');

    expect($group->memberships()->exists())->toBeFalse();
});

test('rejects sharing a piece with a group from another organization', function () {
    $piece = Piece::factory()->create();
    $group = Group::factory()->create();

    expect(fn () => PieceShare::factory()->for($piece)->create([
        'share_type' => PieceShareType::Group,
        'group_id' => $group->id,
    ]))->toThrow(ValidationException::class, 'same organization as the piece');

    $this->assertDatabaseCount('piece_shares', 0);
});

test('rejects sharing a piece with a membership from another organization', function () {
    $piece = Piece::factory()->create();
    $membership = OrganizationMembership::factory()->create();

    expect(fn () => PieceShare::factory()->for($piece)->create([
        'share_type' => PieceShareType::Member,
        'membership_id' => $membership->id,
    ]))->toThrow(ValidationException::class, 'same organization as the piece');

    $this->assertDatabaseCount('piece_shares', 0);
});

test('rejects share targets that do not match their type', function (array $attributes) {
    expect(fn () => PieceShare::factory()->create($attributes))
        ->toThrow(ValidationException::class, 'share target');
})->with([
    'organization with a voice' => [[
        'share_type' => PieceShareType::Organization,
        'voice_type' => VoiceType::Soprano,
    ]],
    'voice without a voice type' => [[
        'share_type' => PieceShareType::Voice,
        'voice_type' => null,
    ]],
    'group without a group' => [[
        'share_type' => PieceShareType::Group,
        'group_id' => null,
    ]],
    'member without a membership' => [[
        'share_type' => PieceShareType::Member,
        'membership_id' => null,
    ]],
]);

test('rejects file and voice combinations that violate the material rules', function (PieceFileType $fileType, VoiceType $voiceType) {
    expect(fn () => PieceFile::factory()->create([
        'file_type' => $fileType,
        'voice_type' => $voiceType,
    ]))->toThrow(ValidationException::class, 'Scores must be general');
})->with([
    'score for a choral voice' => [PieceFileType::Score, VoiceType::Alto],
    'audio marked as general' => [PieceFileType::Audio, VoiceType::General],
]);

test('scopes organization records without leaking another tenant', function () {
    $firstOrganization = Organization::factory()->create();
    $secondOrganization = Organization::factory()->create();
    $firstPiece = Piece::factory()->for($firstOrganization)->create();
    Piece::factory()->for($secondOrganization)->create();

    $pieces = Piece::query()->forOrganization($firstOrganization)->get();

    expect($pieces)->toHaveCount(1)
        ->and($pieces->first()->is($firstPiece))->toBeTrue();
});

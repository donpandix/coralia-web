<?php

use App\Enums\OrganizationRole;
use App\Enums\VoiceType;
use App\Models\Group;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\PieceShare;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\CoraliaDemoSeeder;

test('creates the required multi-organization demonstration data', function () {
    $this->seed(CoraliaDemoSeeder::class);

    expect(User::query()->count())->toBe(11)
        ->and(User::query()->where('is_super_admin', true)->count())->toBe(1)
        ->and(Organization::query()->count())->toBe(2)
        ->and(OrganizationMembership::query()->where('role', OrganizationRole::Admin)->count())->toBe(2)
        ->and(OrganizationMembership::query()->where('role', OrganizationRole::Member)->count())->toBe(8)
        ->and(Group::query()->count())->toBe(3)
        ->and(Tag::query()->count())->toBe(6)
        ->and(Piece::query()->count())->toBe(10)
        ->and(PieceShare::query()->count())->toBe(16);

    foreach ([VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass] as $voiceType) {
        expect(OrganizationMembership::query()->where('voice_type', $voiceType)->count())->toBe(2);
    }
});

test('keeps seeded group members and piece shares inside their organizations', function () {
    $this->seed(CoraliaDemoSeeder::class);

    $groups = Group::query()->with('memberships')->get();
    $shares = PieceShare::query()->with(['piece', 'group', 'membership'])->get();

    foreach ($groups as $group) {
        foreach ($group->memberships as $membership) {
            expect($membership->organization_id)->toBe($group->organization_id);
        }
    }

    foreach ($shares as $share) {
        if ($share->group !== null) {
            expect($share->group->organization_id)->toBe($share->piece->organization_id);
        }

        if ($share->membership !== null) {
            expect($share->membership->organization_id)->toBe($share->piece->organization_id);
        }
    }
});

test('creates unique public identifiers for all exposed seeded entities', function () {
    $this->seed(CoraliaDemoSeeder::class);

    foreach ([User::class, Organization::class, Group::class, Tag::class, Piece::class] as $model) {
        $publicIds = $model::query()->pluck('public_id');

        expect($publicIds->unique())->toHaveCount($publicIds->count());
        $publicIds->each(fn (string $publicId) => expect($publicId)->toBeUuid());
    }
});

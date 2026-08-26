<?php

namespace Database\Seeders;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\PieceFileType;
use App\Enums\PieceShareType;
use App\Enums\PieceStatus;
use App\Enums\TagStatus;
use App\Enums\VoiceType;
use App\Models\Favorite;
use App\Models\Group;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\PieceFile;
use App\Models\PieceShare;
use App\Models\PieceView;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CoraliaDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'name' => 'Super Admin Coralia',
            'email' => 'superadmin@coralia.test',
        ]);

        $organizationAdmins = collect([
            User::factory()->create([
                'name' => 'Administradora Coral Andina',
                'email' => 'admin.andina@coralia.test',
            ]),
            User::factory()->create([
                'name' => 'Administrador Voces del Sur',
                'email' => 'admin.sur@coralia.test',
            ]),
        ]);

        $organizations = collect([
            Organization::factory()->create([
                'name' => 'Coral Andina',
                'description' => 'Coro de repertorio latinoamericano.',
                'owner_user_id' => $organizationAdmins[0]->id,
                'status' => OrganizationStatus::Active,
                'city' => 'Santiago',
            ]),
            Organization::factory()->create([
                'name' => 'Voces del Sur',
                'description' => 'Agrupación coral del sur de Chile.',
                'owner_user_id' => $organizationAdmins[1]->id,
                'status' => OrganizationStatus::Active,
                'city' => 'Puerto Montt',
            ]),
        ]);

        $organizations->each(
            fn (Organization $organization, int $index): OrganizationMembership => OrganizationMembership::factory()
                ->admin()
                ->for($organization)
                ->for($organizationAdmins[$index])
                ->create([
                    'approved_by' => $superAdmin->id,
                    'role' => OrganizationRole::Admin,
                    'status' => OrganizationMembershipStatus::Active,
                ]),
        );

        $membershipsByOrganization = $organizations->map(
            fn (Organization $organization, int $index): Collection => $this->createMembers(
                $organization,
                $index,
                $organizationAdmins[$index],
            ),
        );

        $groups = collect([
            Group::factory()->for($organizations[0])->for($organizationAdmins[0], 'creator')->create(['name' => 'Cámara Andina']),
            Group::factory()->for($organizations[0])->for($organizationAdmins[0], 'creator')->create(['name' => 'Ensamble Juvenil']),
            Group::factory()->for($organizations[1])->for($organizationAdmins[1], 'creator')->create(['name' => 'Coro de Cámara Sur']),
        ]);

        $groups[0]->memberships()->attach($membershipsByOrganization[0]->take(2)->pluck('id')->all());
        $groups[1]->memberships()->attach($membershipsByOrganization[0]->skip(2)->pluck('id')->all());
        $groups[2]->memberships()->attach($membershipsByOrganization[1]->pluck('id')->all());

        $tags = $this->createTags($superAdmin);
        $piecesByOrganization = $organizations->map(
            fn (Organization $organization, int $index): Collection => $this->createPieces(
                $organization,
                $organizationAdmins[$index],
                $tags,
                $index,
            ),
        );

        $this->createSharesAndActivity(
            $piecesByOrganization[0],
            $groups->take(2),
            $membershipsByOrganization[0],
            $organizationAdmins[0],
        );
        $this->createSharesAndActivity(
            $piecesByOrganization[1],
            $groups->slice(2, 1),
            $membershipsByOrganization[1],
            $organizationAdmins[1],
        );
    }

    /**
     * @return Collection<int, OrganizationMembership>
     */
    private function createMembers(Organization $organization, int $organizationIndex, User $approver): Collection
    {
        $voiceTypes = [VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass];

        return collect($voiceTypes)->map(
            function (VoiceType $voiceType, int $voiceIndex) use ($organization, $organizationIndex, $approver): OrganizationMembership {
                $memberNumber = ($organizationIndex * 4) + $voiceIndex + 1;
                $user = User::factory()->create([
                    'name' => "Corista {$memberNumber} {$voiceType->value}",
                    'email' => "corista{$memberNumber}@coralia.test",
                ]);

                return OrganizationMembership::factory()
                    ->forVoice($voiceType)
                    ->for($organization)
                    ->for($user)
                    ->create(['approved_by' => $approver->id]);
            },
        );
    }

    /**
     * @return Collection<int, Tag>
     */
    private function createTags(User $creator): Collection
    {
        return collect(['Sacra', 'Folclore', 'Clásica', 'Contemporánea', 'Navidad', 'Popular'])
            ->map(fn (string $name): Tag => Tag::factory()->for($creator, 'creator')->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => TagStatus::Active,
            ]));
    }

    /**
     * @param  Collection<int, Tag>  $tags
     * @return Collection<int, Piece>
     */
    private function createPieces(
        Organization $organization,
        User $creator,
        Collection $tags,
        int $organizationIndex,
    ): Collection {
        return collect(range(1, 5))->map(
            function (int $pieceIndex) use ($organization, $creator, $tags, $organizationIndex): Piece {
                $pieceNumber = ($organizationIndex * 5) + $pieceIndex;
                $piece = Piece::factory()
                    ->for($organization)
                    ->for($creator, 'creator')
                    ->create([
                        'title' => "Obra Coral {$pieceNumber}",
                        'status' => PieceStatus::Active,
                    ]);

                $piece->tags()->attach([
                    $tags[($pieceNumber - 1) % $tags->count()]->id,
                    $tags[$pieceNumber % $tags->count()]->id,
                ]);

                PieceFile::factory()
                    ->for($piece)
                    ->for($creator, 'creator')
                    ->create([
                        'file_type' => PieceFileType::Score,
                        'voice_type' => VoiceType::General,
                    ]);

                return $piece;
            },
        );
    }

    /**
     * @param  Collection<int, Piece>  $pieces
     * @param  Collection<int, Group>  $groups
     * @param  Collection<int, OrganizationMembership>  $memberships
     */
    private function createSharesAndActivity(
        Collection $pieces,
        Collection $groups,
        Collection $memberships,
        User $creator,
    ): void {
        foreach ($pieces as $index => $piece) {
            PieceShare::factory()->for($piece)->for($creator, 'creator')->create([
                'share_type' => PieceShareType::Organization,
            ]);

            if ($index === 0) {
                PieceShare::factory()->for($piece)->for($creator, 'creator')->create([
                    'share_type' => PieceShareType::Voice,
                    'voice_type' => VoiceType::Soprano,
                ]);
            }

            if ($index === 1) {
                PieceShare::factory()->for($piece)->for($creator, 'creator')->create([
                    'share_type' => PieceShareType::Group,
                    'group_id' => $groups->firstOrFail()->id,
                ]);
            }

            if ($index === 2) {
                PieceShare::factory()->for($piece)->for($creator, 'creator')->create([
                    'share_type' => PieceShareType::Member,
                    'membership_id' => $memberships->firstOrFail()->id,
                ]);
            }
        }

        $memberships->each(function (OrganizationMembership $membership, int $index) use ($pieces): void {
            $piece = $pieces[$index % $pieces->count()];

            Favorite::factory()->for($membership->user)->for($piece)->create();
            PieceView::factory()->for($membership->user)->for($piece)->create();
        });
    }
}

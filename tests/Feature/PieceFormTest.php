<?php

use App\Enums\PieceFileType;
use App\Enums\PieceShareType;
use App\Enums\TagStatus;
use App\Enums\VoiceType;
use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\PieceFile;
use App\Models\PieceShare;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\NewPieceAvailable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('creates a piece with tags, protected files and sharing rules', function () {
    Storage::fake('local');
    Notification::fake();
    $admin = User::factory()->create();
    $membership = OrganizationMembership::factory()->admin()->for($admin)->create();
    $tag = Tag::factory()->create();
    $this->actingAs($admin);

    Livewire::test('pages::organization.pieces.form')
        ->set('title', 'Ave Maria')
        ->set('tagIds', [$tag->id])
        ->set('shareOrganization', true)
        ->set('score', UploadedFile::fake()->create('partitura.pdf', 100, 'application/pdf'))
        ->set('audioTenor', UploadedFile::fake()->create('tenor.mp3', 100, 'audio/mpeg'))
        ->call('save')
        ->assertHasNoErrors();

    $piece = Piece::query()->whereBelongsTo($membership->organization)->where('title', 'Ave Maria')->firstOrFail();
    expect($piece->tags()->whereKey($tag)->exists())->toBeTrue()
        ->and(PieceShare::query()->whereBelongsTo($piece)->where('share_type', PieceShareType::Organization)->exists())->toBeTrue()
        ->and(PieceFile::query()->whereBelongsTo($piece)->where('file_type', PieceFileType::Score)->where('voice_type', VoiceType::General)->exists())->toBeTrue()
        ->and(PieceFile::query()->whereBelongsTo($piece)->where('file_type', PieceFileType::Audio)->where('voice_type', VoiceType::Tenor)->exists())->toBeTrue();
    Notification::assertSentTo($admin, NewPieceAvailable::class);
});

it('rejects piece files larger than five megabytes', function () {
    Storage::fake('local');
    $admin = User::factory()->create();
    OrganizationMembership::factory()->admin()->for($admin)->create();
    $this->actingAs($admin);

    Livewire::test('pages::organization.pieces.form')
        ->set('title', 'Pieza inválida')
        ->set('score', UploadedFile::fake()->create('partitura.pdf', 6000, 'application/pdf'))
        ->call('save')
        ->assertHasErrors(['score' => 'max']);

    expect(Piece::query()->where('title', 'Pieza inválida')->exists())->toBeFalse();
});

it('allows an organization administrator to create and select a tag from the piece form', function () {
    $admin = User::factory()->create();
    OrganizationMembership::factory()->admin()->for($admin)->create();
    $this->actingAs($admin);

    $component = Livewire::test('pages::organization.pieces.form')
        ->assertSee('Crear etiqueta')
        ->set('newTagName', '  Renacimiento   tardío  ')
        ->call('createTag')
        ->assertHasNoErrors();

    $tag = Tag::query()->where('slug', 'renacimiento-tardio')->firstOrFail();

    expect($tag->name)->toBe('Renacimiento tardío')
        ->and($tag->status)->toBe(TagStatus::Active)
        ->and($tag->created_by)->toBe($admin->id);
    $component->assertSet('tagIds', [(string) $tag->id]);
});

it('selects an existing active tag instead of creating a duplicate', function () {
    $admin = User::factory()->create();
    OrganizationMembership::factory()->admin()->for($admin)->create();
    $tag = Tag::factory()->create(['name' => 'Renacimiento tardío', 'slug' => 'renacimiento-tardio']);
    $this->actingAs($admin);

    Livewire::test('pages::organization.pieces.form')
        ->set('newTagName', 'Renacimiento tardío')
        ->call('createTag')
        ->assertHasNoErrors()
        ->assertSet('tagIds', [(string) $tag->id]);

    expect(Tag::query()->where('slug', 'renacimiento-tardio')->count())->toBe(1);
});

it('does not reactivate an inactive tag from the piece form', function () {
    $admin = User::factory()->create();
    OrganizationMembership::factory()->admin()->for($admin)->create();
    $tag = Tag::factory()->create([
        'name' => 'Etiqueta histórica',
        'slug' => 'etiqueta-historica',
        'status' => TagStatus::Inactive,
    ]);
    $this->actingAs($admin);

    Livewire::test('pages::organization.pieces.form')
        ->set('newTagName', 'Etiqueta histórica')
        ->call('createTag')
        ->assertHasErrors(['newTagName'])
        ->assertSet('tagIds', []);

    expect($tag->refresh()->status)->toBe(TagStatus::Inactive);
});

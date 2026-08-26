<?php

use App\Models\Favorite;
use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\PieceShare;
use App\Models\PieceView;
use App\Models\User;
use Livewire\Livewire;

it('shows only pieces shared with the active membership', function () {
    $user = User::factory()->create();
    $membership = OrganizationMembership::factory()->for($user)->create();
    $visiblePiece = Piece::factory()->for($membership->organization)->create(['title' => 'Ave Maria']);
    PieceShare::factory()->for($visiblePiece)->create();
    $hiddenPiece = Piece::factory()->create(['title' => 'Repertorio secreto']);
    PieceShare::factory()->for($hiddenPiece)->create();

    $this->actingAs($user)
        ->get(route('library.index'))
        ->assertOk()
        ->assertSee('Ave Maria')
        ->assertDontSee('Repertorio secreto');
});

it('toggles favorites only for a visible piece', function () {
    $user = User::factory()->create();
    $membership = OrganizationMembership::factory()->for($user)->create();
    $piece = Piece::factory()->for($membership->organization)->create();
    PieceShare::factory()->for($piece)->create();
    $this->actingAs($user);

    $component = Livewire::test('pages::library.index')
        ->assertSeeHtml('data-favorite-state="unselected"')
        ->assertSeeHtml('aria-pressed="false"')
        ->assertSee('Agregar a favoritos')
        ->call('toggleFavorite', $piece->id)
        ->assertHasNoErrors()
        ->assertSeeHtml('data-favorite-state="selected"')
        ->assertSeeHtml('aria-pressed="true"')
        ->assertSee('Quitar de favoritos');

    expect(Favorite::query()->whereBelongsTo($user)->whereBelongsTo($piece)->exists())->toBeTrue();

    $component
        ->call('toggleFavorite', $piece->id)
        ->assertHasNoErrors()
        ->assertSeeHtml('data-favorite-state="unselected"')
        ->assertSeeHtml('aria-pressed="false"')
        ->assertSee('Agregar a favoritos');

    expect(Favorite::query()->whereBelongsTo($user)->whereBelongsTo($piece)->exists())->toBeFalse();
});

it('marks a visible piece as opened and hides cross-tenant pieces', function () {
    $user = User::factory()->create();
    $membership = OrganizationMembership::factory()->for($user)->create();
    $visiblePiece = Piece::factory()->for($membership->organization)->create();
    PieceShare::factory()->for($visiblePiece)->create();
    $hiddenPiece = Piece::factory()->create();
    PieceShare::factory()->for($hiddenPiece)->create();

    $this->actingAs($user)->get(route('library.show', $visiblePiece))->assertOk();
    expect(PieceView::query()->whereBelongsTo($user)->whereBelongsTo($visiblePiece)->exists())->toBeTrue();

    $this->actingAs($user)->get(route('library.show', $hiddenPiece))->assertNotFound();
});

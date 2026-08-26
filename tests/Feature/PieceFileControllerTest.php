<?php

use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\PieceFile;
use App\Models\PieceShare;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('serves a private piece file to an authorized member', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $membership = OrganizationMembership::factory()->for($user)->create();
    $piece = Piece::factory()->for($membership->organization)->create();
    PieceShare::factory()->for($piece)->create();
    $pieceFile = PieceFile::factory()->for($piece)->create(['storage_path' => 'pieces/score.pdf']);
    Storage::disk('local')->put($pieceFile->storage_path, '%PDF-test');

    $this->actingAs($user)
        ->get(route('piece-files.show', [$piece, $pieceFile]))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('returns 404 for a piece file from another organization', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    OrganizationMembership::factory()->for($user)->create();
    $piece = Piece::factory()->create();
    PieceShare::factory()->for($piece)->create();
    $pieceFile = PieceFile::factory()->for($piece)->create(['storage_path' => 'pieces/hidden.pdf']);
    Storage::disk('local')->put($pieceFile->storage_path, '%PDF-test');

    $this->actingAs($user)
        ->get(route('piece-files.show', [$piece, $pieceFile]))
        ->assertNotFound();
});

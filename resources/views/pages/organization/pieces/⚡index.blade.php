<?php

use App\Enums\PieceStatus;
use App\Models\Piece;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Piezas')] class extends Component {
    use WithPagination;

    #[Url(as: 'buscar')] public string $search = '';
    #[Url] public string $status = '';

    #[Computed]
    public function pieces()
    {
        $organization = app(CurrentOrganization::class)->organization(Auth::user());

        return Piece::query()->forOrganization($organization)
            ->with('tags:id,name')
            ->when($this->search !== '', fn (Builder $query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(15);
    }

    public function archive(int $pieceId): void
    {
        $piece = Piece::query()->forOrganization(app(CurrentOrganization::class)->organization(Auth::user()))->findOrFail($pieceId);
        Gate::authorize('delete', $piece);
        $piece->update(['status' => PieceStatus::Archived, 'archived_at' => now()]);
        unset($this->pieces);
        Flux::toast(text: 'Pieza archivada.');
    }
}; ?>

<div>
    <x-page-header title="Piezas" description="Administra el repertorio de la organización."><flux:button variant="primary" icon="plus" :href="route('organization.pieces.create')" wire:navigate>Nueva pieza</flux:button></x-page-header>
    <div class="mt-7 grid gap-3 md:grid-cols-[1fr_200px]"><flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar piezas" aria-label="Buscar piezas" /><flux:select wire:model.live="status" aria-label="Filtrar por estado"><flux:select.option value="">Todos los estados</flux:select.option>@foreach(PieceStatus::cases() as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->label() }}</flux:select.option>@endforeach</flux:select></div>
    <div class="mt-6 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
        @forelse($this->pieces as $piece)<article class="grid gap-3 py-5 md:grid-cols-[minmax(0,1fr)_minmax(160px,.6fr)_100px_auto] md:items-center" wire:key="admin-piece-{{ $piece->id }}"><div><h2 class="font-medium">{{ $piece->title }}</h2>@if($piece->subtitle)<p class="mt-1 text-sm text-zinc-500">{{ $piece->subtitle }}</p>@endif</div><p class="text-sm text-zinc-500">{{ $piece->tags->pluck('name')->join(' · ') ?: 'Sin etiquetas' }}</p><flux:badge size="sm" color="zinc">{{ $piece->status->label() }}</flux:badge><flux:dropdown><flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" aria-label="Acciones de {{ $piece->title }}" /><flux:menu><flux:menu.item :href="route('organization.pieces.edit', $piece)" wire:navigate icon="pencil-square">Editar</flux:menu.item>@if($piece->status !== PieceStatus::Archived)<flux:menu.item wire:click="archive({{ $piece->id }})" wire:confirm="¿Archivar esta pieza?" icon="archive-box">Archivar</flux:menu.item>@endif</flux:menu></flux:dropdown></article>@empty<x-empty-state title="No hay piezas" description="Crea la primera pieza del repertorio." icon="musical-note"><flux:button variant="primary" :href="route('organization.pieces.create')" wire:navigate>Nueva pieza</flux:button></x-empty-state>@endforelse
    </div><div class="mt-6">{{ $this->pieces->links() }}</div>
</div>

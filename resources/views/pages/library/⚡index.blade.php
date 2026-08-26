<?php

use App\Enums\TagStatus;
use App\Models\Favorite;
use App\Models\Piece;
use App\Models\Tag;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Mi repertorio')] class extends Component {
    use WithPagination;

    #[Url(as: 'buscar')]
    public string $search = '';

    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $tag = '';

    #[Computed]
    public function pieces()
    {
        $membership = app(CurrentOrganization::class)->membership(Auth::user());

        return Piece::query()
            ->visibleToMembership($membership)
            ->select(['id', 'public_id', 'organization_id', 'title', 'subtitle', 'published_at', 'created_at'])
            ->with(['tags:id,name', 'favorites' => fn ($query) => $query->where('user_id', Auth::id()), 'views' => fn ($query) => $query->where('user_id', Auth::id())])
            ->when($this->search !== '', fn (Builder $query) => $query->where(function (Builder $searchQuery): void {
                $searchQuery->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('subtitle', 'like', '%'.$this->search.'%')
                    ->orWhereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->when($this->filter === 'favorites', fn (Builder $query) => $query->whereHas('favorites', fn (Builder $favoriteQuery) => $favoriteQuery->where('user_id', Auth::id())))
            ->when($this->filter === 'new', fn (Builder $query) => $query->whereDoesntHave('views', fn (Builder $viewQuery) => $viewQuery->where('user_id', Auth::id())))
            ->when($this->tag !== '', fn (Builder $query) => $query->whereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('public_id', $this->tag)))
            ->latest('published_at')
            ->paginate(12);
    }

    #[Computed]
    public function tags()
    {
        return Tag::query()->where('status', TagStatus::Active)->orderBy('name')->get(['id', 'public_id', 'name']);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'filter', 'tag'], true)) {
            $this->resetPage();
            unset($this->pieces);
        }
    }

    public function toggleFavorite(int $pieceId): void
    {
        $membership = app(CurrentOrganization::class)->membership(Auth::user());
        $piece = Piece::query()->visibleToMembership($membership)->findOrFail($pieceId);
        $favorite = Favorite::query()->whereBelongsTo(Auth::user())->whereBelongsTo($piece)->first();

        $favorite ? $favorite->delete() : Favorite::query()->create(['user_id' => Auth::id(), 'piece_id' => $piece->id]);
        unset($this->pieces);
        Flux::toast(text: $favorite ? 'Quitada de favoritos.' : 'Agregada a favoritos.');
    }
}; ?>

<div>
    <x-page-header title="Mi repertorio" description="Partituras y audios compartidos contigo." />

    <div class="mt-7 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto]">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" aria-label="Buscar repertorio" placeholder="Buscar por título, subtítulo o etiqueta" clearable />
        <div class="flex gap-2 overflow-x-auto pb-1" aria-label="Filtros de biblioteca">
            @foreach (['all' => 'Todos', 'new' => 'Nuevos', 'favorites' => 'Favoritos'] as $value => $label)<flux:button wire:click="$set('filter', '{{ $value }}')" :variant="$filter === $value ? 'primary' : 'ghost'" size="sm">{{ $label }}</flux:button>@endforeach
            <flux:select wire:model.live="tag" size="sm" aria-label="Filtrar por etiqueta" class="min-w-40"><flux:select.option value="">Etiquetas</flux:select.option>@foreach($this->tags as $tagOption)<flux:select.option :value="$tagOption->public_id" wire:key="tag-{{ $tagOption->id }}">{{ $tagOption->name }}</flux:select.option>@endforeach</flux:select>
        </div>
    </div>

    <div class="mt-7 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800" wire:loading.class="opacity-60">
        @forelse ($this->pieces as $piece)
            @php($isNew = $piece->views->isEmpty())
            <article class="group flex items-start gap-3 py-5" wire:key="piece-{{ $piece->id }}">
                <a href="{{ route('library.show', $piece) }}" wire:navigate class="min-w-0 flex-1 rounded-md outline-none focus-visible:ring-2 focus-visible:ring-coral-600 focus-visible:ring-offset-2">
                    <div class="flex flex-wrap items-center gap-2"><h2 class="font-medium text-zinc-950 group-hover:text-coral-700 dark:text-white dark:group-hover:text-coral-300">{{ $piece->title }}</h2>@if($isNew)<flux:badge size="sm" color="zinc">Nuevo</flux:badge>@endif</div>
                    @if($piece->subtitle)<p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $piece->subtitle }}</p>@endif
                    @if($piece->tags->isNotEmpty())<p class="mt-3 text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $piece->tags->pluck('name')->join(' · ') }}</p>@endif
                </a>
                <flux:button wire:click="toggleFavorite({{ $piece->id }})" variant="ghost" size="sm" icon="star" :class="$piece->favorites->isNotEmpty() ? 'text-coral-700 dark:text-coral-300' : ''" :aria-label="$piece->favorites->isNotEmpty() ? 'Quitar de favoritos' : 'Agregar a favoritos'" />
            </article>
        @empty
            <x-empty-state :title="$filter === 'favorites' ? 'Aún no has marcado favoritos' : 'No encontramos piezas'" :description="$search !== '' ? 'No encontramos piezas para «'.$search.'».' : 'Todavía no tienes piezas disponibles con estos filtros.'" />
        @endforelse
    </div>
    <div class="mt-6">{{ $this->pieces->links() }}</div>
</div>

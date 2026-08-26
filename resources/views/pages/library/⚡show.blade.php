<?php

use App\Enums\PieceFileType;
use App\Models\Favorite;
use App\Models\Piece;
use App\Models\PieceView;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Detalle de pieza')] class extends Component {
    public Piece $piece;

    public function mount(Piece $piece): void
    {
        abort_unless(Gate::allows('view', $piece), 404);
        $this->piece = $piece->load(['tags:id,name', 'files']);

        $view = PieceView::query()->firstOrNew(['user_id' => Auth::id(), 'piece_id' => $piece->id]);
        $view->first_viewed_at ??= now();
        $view->last_viewed_at = now();
        $view->view_count = $view->exists ? $view->view_count + 1 : 1;
        $view->save();
    }

    #[Computed]
    public function favorite(): bool
    {
        return Favorite::query()->where('user_id', Auth::id())->where('piece_id', $this->piece->id)->exists();
    }

    #[Computed]
    public function score()
    {
        return $this->piece->files->firstWhere('file_type', PieceFileType::Score);
    }

    #[Computed]
    public function audios()
    {
        $voiceType = app(CurrentOrganization::class)->membership(Auth::user())?->voice_type;

        return $this->piece->files
            ->where('file_type', PieceFileType::Audio)
            ->sortByDesc(fn ($file) => $file->voice_type === $voiceType);
    }

    public function toggleFavorite(): void
    {
        Gate::authorize('view', $this->piece);
        $favorite = Favorite::query()->where('user_id', Auth::id())->where('piece_id', $this->piece->id)->first();
        $favorite ? $favorite->delete() : Favorite::query()->create(['user_id' => Auth::id(), 'piece_id' => $this->piece->id]);
        unset($this->favorite);
        Flux::toast(text: $favorite ? 'Quitada de favoritos.' : 'Agregada a favoritos.');
    }
}; ?>

<div class="mx-auto max-w-5xl">
    <flux:button variant="ghost" icon="arrow-left" :href="route('library.index')" wire:navigate>Biblioteca</flux:button>

    @php($isFavorite = $this->favorite)
    <header class="mt-6 flex items-start justify-between gap-4">
        <div><flux:heading size="xl" level="1">{{ $piece->title }}</flux:heading>@if($piece->subtitle)<flux:text class="mt-2">{{ $piece->subtitle }}</flux:text>@endif@if($piece->tags->isNotEmpty())<div class="mt-4 flex flex-wrap gap-2">@foreach($piece->tags as $tag)<flux:badge size="sm" color="zinc" wire:key="piece-tag-{{ $tag->id }}">{{ $tag->name }}</flux:badge>@endforeach</div>@endif</div>
        <flux:button
            wire:click="toggleFavorite"
            variant="ghost"
            icon="star"
            icon:variant="{{ $isFavorite ? 'solid' : 'outline' }}"
            icon:class="{{ $isFavorite ? 'text-coral-600 dark:text-coral-300' : 'text-zinc-400 dark:text-zinc-500' }}"
            :class="$isFavorite ? 'ring-1 ring-coral-200 dark:ring-coral-800' : ''"
            :aria-label="$isFavorite ? 'Quitar de favoritos' : 'Agregar a favoritos'"
            :aria-pressed="$isFavorite ? 'true' : 'false'"
            data-favorite-state="{{ $isFavorite ? 'selected' : 'unselected' }}"
        />
    </header>

    @if($piece->body)<div class="mt-8 max-w-3xl whitespace-pre-line leading-7 text-zinc-600 dark:text-zinc-300">{{ $piece->body }}</div>@endif

    <section class="mt-10" aria-labelledby="score-heading">
        <div class="flex items-center justify-between gap-3 border-b border-zinc-200 pb-3 dark:border-zinc-800"><h2 id="score-heading" class="text-lg font-semibold">Partitura</h2>@if($this->score)<div class="flex gap-2"><flux:button size="sm" icon="arrow-top-right-on-square" :href="route('piece-files.show', [$piece, $this->score])" target="_blank">Abrir</flux:button><flux:button size="sm" icon="printer" :href="route('piece-files.show', [$piece, $this->score])" target="_blank">Imprimir</flux:button></div>@endif</div>
        @if($this->score)<iframe src="{{ route('piece-files.show', [$piece, $this->score]) }}" title="Partitura de {{ $piece->title }}" class="mt-5 h-[62vh] min-h-96 w-full rounded-xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800"></iframe>@else<x-empty-state class="mt-5" title="Partitura no disponible" description="La administración aún no ha cargado un PDF." icon="document" />@endif
    </section>

    <section class="mt-10" aria-labelledby="audio-heading">
        <div class="border-b border-zinc-200 pb-3 dark:border-zinc-800"><h2 id="audio-heading" class="text-lg font-semibold">Audios</h2></div>
        <div class="mt-5 space-y-4">
            @forelse($this->audios as $audio)
                @php($isMyVoice = $audio->voice_type === app(CurrentOrganization::class)->membership(Auth::user())?->voice_type)
                <article class="rounded-xl border p-4 {{ $isMyVoice ? 'border-coral-300 bg-coral-50 dark:border-coral-800 dark:bg-coral-950/30' : 'border-zinc-200 dark:border-zinc-800' }}" wire:key="audio-{{ $audio->id }}">
                    <div class="mb-3 flex items-center gap-2"><h3 class="font-medium">{{ $audio->voice_type->label() }}</h3>@if($isMyVoice)<flux:badge size="sm" color="zinc">Mi voz</flux:badge>@endif</div>
                    <audio controls preload="metadata" class="w-full" src="{{ route('piece-files.show', [$piece, $audio]) }}">Tu navegador no permite reproducir este audio.</audio>
                </article>
            @empty
                <x-empty-state title="Audios no disponibles" description="Todavía no hay pistas de ensayo para esta pieza." icon="speaker-wave" />
            @endforelse
        </div>
    </section>
</div>

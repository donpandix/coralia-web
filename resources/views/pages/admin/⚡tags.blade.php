<?php

use App\Enums\TagStatus;
use App\Models\Tag;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Etiquetas')] class extends Component {
    public ?int $tagId = null;
    public string $name = '';
    public string $status = 'ACTIVE';

    #[Computed]
    public function tags()
    {
        return Tag::query()->withCount('pieces')->orderBy('name')->get();
    }

    public function create(): void
    {
        $this->reset('tagId', 'name');
        $this->status = TagStatus::Active->value;
        Flux::modal('tag-form')->show();
    }

    public function edit(int $tagId): void
    {
        $tag = Tag::query()->findOrFail($tagId);
        $this->tagId = $tag->id;
        $this->name = $tag->name;
        $this->status = $tag->status->value;
        Flux::modal('tag-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate(['name' => ['required', 'string', 'max:100', Rule::unique(Tag::class)->ignore($this->tagId)], 'status' => ['required', Rule::enum(TagStatus::class)]]);
        $slug = Str::slug($validated['name']);
        if (Tag::query()->where('slug', $slug)->when($this->tagId, fn ($query) => $query->whereKeyNot($this->tagId))->exists()) {
            $this->addError('name', 'Ya existe una etiqueta con un nombre equivalente.');

            return;
        }
        Tag::query()->updateOrCreate(['id' => $this->tagId], ['name' => $validated['name'], 'slug' => $slug, 'status' => $validated['status'], 'created_by' => Auth::id()]);
        unset($this->tags);
        Flux::modal('tag-form')->close();
        Flux::toast(variant: 'success', text: 'Etiqueta guardada.');
    }
}; ?>

<div class="mx-auto max-w-5xl"><x-page-header title="Etiquetas" description="Catálogo global para clasificar el repertorio."><flux:button wire:click="create" variant="primary" icon="plus">Nueva etiqueta</flux:button></x-page-header><div class="mt-7 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">@forelse($this->tags as $tag)<article class="grid gap-3 py-5 sm:grid-cols-[1fr_120px_100px_auto] sm:items-center" wire:key="admin-tag-{{ $tag->id }}"><div><h2 class="font-medium">{{ $tag->name }}</h2><p class="text-sm text-zinc-500">{{ $tag->slug }}</p></div><p class="text-sm">{{ $tag->pieces_count }} piezas</p><flux:badge size="sm" color="zinc">{{ $tag->status->value }}</flux:badge><flux:button wire:click="edit({{ $tag->id }})" size="sm" variant="ghost">Editar</flux:button></article>@empty<x-empty-state title="No hay etiquetas" icon="tag" />@endforelse</div><flux:modal name="tag-form" class="md:w-96"><form wire:submit="save" class="space-y-6"><flux:heading size="lg">{{ $tagId ? 'Editar etiqueta' : 'Nueva etiqueta' }}</flux:heading><flux:input wire:model="name" label="Nombre" required /><flux:select wire:model="status" label="Estado">@foreach(TagStatus::cases() as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->value }}</flux:select.option>@endforeach</flux:select><div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Guardar</flux:button></div></form></flux:modal></div>

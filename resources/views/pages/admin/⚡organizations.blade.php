<?php

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Organizaciones')] class extends Component {
    use WithPagination;
    #[Url(as: 'buscar')] public string $search = '';
    public ?int $organizationId = null;
    public string $status = '';

    #[Computed]
    public function organizations()
    {
        return Organization::query()->with('owner:id,name,email')->withCount(['memberships', 'pieces'])->when($this->search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$this->search.'%')->orWhere('city', 'like', '%'.$this->search.'%'))->latest()->paginate(15);
    }

    public function editStatus(int $organizationId): void
    {
        $organization = Organization::query()->findOrFail($organizationId);
        $this->organizationId = $organization->id;
        $this->status = $organization->status->value;
        Flux::modal('organization-status')->show();
    }

    public function saveStatus(): void
    {
        $validated = $this->validate(['status' => ['required', Rule::enum(OrganizationStatus::class)]]);
        Organization::query()->findOrFail($this->organizationId)->update(['status' => $validated['status'], 'archived_at' => $validated['status'] === OrganizationStatus::Archived->value ? now() : null]);
        unset($this->organizations);
        Flux::modal('organization-status')->close();
        Flux::toast(variant: 'success', text: 'Estado actualizado.');
    }
}; ?>

<div><x-page-header title="Organizaciones" description="Supervisa las organizaciones registradas." /><div class="mt-7"><flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre o ciudad" aria-label="Buscar organizaciones" /></div><div class="mt-6 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">@forelse($this->organizations as $organization)<article class="grid gap-3 py-5 lg:grid-cols-[minmax(0,1fr)_minmax(180px,.7fr)_100px_100px_auto] lg:items-center" wire:key="admin-organization-{{ $organization->id }}"><div><h2 class="font-medium">{{ $organization->name }}</h2><p class="mt-1 text-sm text-zinc-500">{{ $organization->city ?: 'Sin ciudad' }}</p></div><p class="text-sm text-zinc-500">{{ $organization->owner->name }}</p><p class="text-sm">{{ $organization->memberships_count }} miembros</p><flux:badge size="sm" color="zinc">{{ $organization->status->value }}</flux:badge><flux:button wire:click="editStatus({{ $organization->id }})" size="sm" variant="ghost">Cambiar estado</flux:button></article>@empty<x-empty-state title="No hay organizaciones" icon="building-office" />@endforelse</div><div class="mt-6">{{ $this->organizations->links() }}</div><flux:modal name="organization-status" class="md:w-96"><form wire:submit="saveStatus" class="space-y-6"><flux:heading size="lg">Estado de organización</flux:heading><flux:select wire:model="status" label="Estado">@foreach(OrganizationStatus::cases() as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->value }}</flux:select.option>@endforeach</flux:select><div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Guardar</flux:button></div></form></flux:modal></div>

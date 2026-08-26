<?php

use App\Enums\GroupStatus;
use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Models\Group;
use App\Models\OrganizationMembership;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Grupos')] class extends Component {
    public ?int $groupId = null;
    public string $name = '';
    public string $description = '';
    public array $membershipIds = [];

    #[Computed]
    public function groups()
    {
        $organization = app(CurrentOrganization::class)->organization(Auth::user());

        return Group::query()->forOrganization($organization)->withCount('memberships')->orderBy('name')->get();
    }

    #[Computed]
    public function members()
    {
        $organization = app(CurrentOrganization::class)->organization(Auth::user());

        return OrganizationMembership::query()->forOrganization($organization)->where('status', OrganizationMembershipStatus::Active)->where('role', OrganizationRole::Member)->with('user:id,name')->get()->sortBy('user.name');
    }

    public function create(): void
    {
        $this->reset('groupId', 'name', 'description', 'membershipIds');
        Flux::modal('group-form')->show();
    }

    public function edit(int $groupId): void
    {
        $group = $this->findGroup($groupId)->load('memberships:id');
        Gate::authorize('update', $group);
        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->description = $group->description ?? '';
        $this->membershipIds = $group->memberships->pluck('id')->map(fn ($id) => (string) $id)->all();
        Flux::modal('group-form')->show();
    }

    public function save(): void
    {
        $organization = app(CurrentOrganization::class)->organization(Auth::user());
        $validated = $this->validate(['name' => ['required', 'string', 'max:150', Rule::unique(Group::class)->where('organization_id', $organization->id)->ignore($this->groupId)], 'description' => ['nullable', 'string', 'max:500'], 'membershipIds' => ['array'], 'membershipIds.*' => ['integer', Rule::exists(OrganizationMembership::class, 'id')->where('organization_id', $organization->id)->where('status', OrganizationMembershipStatus::Active->value)]]);

        $group = $this->groupId ? $this->findGroup($this->groupId) : new Group(['organization_id' => $organization->id, 'created_by' => Auth::id()]);
        Gate::authorize($group->exists ? 'update' : 'create', $group);
        $group->fill(['name' => $validated['name'], 'description' => $validated['description']])->save();
        $group->memberships()->sync($validated['membershipIds']);
        $this->reset('groupId', 'name', 'description', 'membershipIds');
        unset($this->groups);
        Flux::modal('group-form')->close();
        Flux::toast(variant: 'success', text: 'Grupo guardado.');
    }

    public function archive(int $groupId): void
    {
        $group = $this->findGroup($groupId);
        Gate::authorize('delete', $group);
        $group->update(['status' => GroupStatus::Archived, 'archived_at' => now()]);
        unset($this->groups);
        Flux::toast(text: 'Grupo archivado.');
    }

    private function findGroup(int $id): Group
    {
        return Group::query()->forOrganization(app(CurrentOrganization::class)->organization(Auth::user()))->findOrFail($id);
    }
}; ?>

<div>
    <x-page-header title="Grupos" description="Organiza integrantes para compartir repertorio específico."><flux:button wire:click="create" variant="primary" icon="plus">Nuevo grupo</flux:button></x-page-header>
    <div class="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($this->groups as $group)<article class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-800" wire:key="group-{{ $group->id }}"><div class="flex items-start justify-between gap-3"><div><h2 class="font-medium">{{ $group->name }}</h2><p class="mt-1 text-sm text-zinc-500">{{ $group->memberships_count }} miembros</p></div><flux:dropdown><flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" aria-label="Acciones de {{ $group->name }}" /><flux:menu><flux:menu.item wire:click="edit({{ $group->id }})" icon="pencil-square">Editar y miembros</flux:menu.item><flux:menu.item wire:click="archive({{ $group->id }})" wire:confirm="¿Archivar este grupo?" icon="archive-box">Archivar</flux:menu.item></flux:menu></flux:dropdown></div>@if($group->description)<p class="mt-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $group->description }}</p>@endif</article>@empty<x-empty-state class="sm:col-span-2 xl:col-span-3" title="Todavía no hay grupos" description="Crea un grupo para compartir piezas con un conjunto de integrantes." icon="user-group"><flux:button wire:click="create" variant="primary">Crear grupo</flux:button></x-empty-state>@endforelse
    </div>
    <flux:modal name="group-form" class="md:w-[38rem]"><form wire:submit="save" class="space-y-6"><div><flux:heading size="lg">{{ $groupId ? 'Editar grupo' : 'Nuevo grupo' }}</flux:heading><flux:text class="mt-2">Selecciona los integrantes que formarán parte del grupo.</flux:text></div><flux:input wire:model="name" label="Nombre" required /><flux:textarea wire:model="description" label="Descripción" rows="3" /><fieldset><legend class="mb-3 text-sm font-medium">Miembros</legend><div class="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">@foreach($this->members as $member)<flux:checkbox wire:model="membershipIds" :value="$member->id" :label="$member->user->name.' · '.($member->voice_type?->value ?? 'Sin cuerda')" wire:key="group-member-{{ $member->id }}" />@endforeach</div></fieldset><div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Guardar</flux:button></div></form></flux:modal>
</div>

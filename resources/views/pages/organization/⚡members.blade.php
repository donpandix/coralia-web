<?php

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Enums\VoiceType;
use App\Models\OrganizationMembership;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Miembros')] class extends Component {
    use WithPagination;

    #[Url(as: 'buscar')] public string $search = '';
    #[Url] public string $voice = '';
    #[Url] public string $status = '';
    public ?int $editingId = null;
    public string $editingRole = '';
    public string $editingVoice = '';
    public string $editingStatus = '';

    #[Computed]
    public function members()
    {
        $organization = app(CurrentOrganization::class)->organization(Auth::user());

        return OrganizationMembership::query()->forOrganization($organization)
            ->with('user:id,public_id,name,email,photo_path')
            ->when($this->search !== '', fn (Builder $query) => $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%')))
            ->when($this->voice !== '', fn (Builder $query) => $query->where('voice_type', $this->voice))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->where('status', '!=', OrganizationMembershipStatus::Pending)
            ->latest()
            ->paginate(15);
    }

    public function edit(int $membershipId): void
    {
        $membership = $this->findMembership($membershipId);
        $this->editingId = $membership->id;
        $this->editingRole = $membership->role->value;
        $this->editingVoice = $membership->voice_type?->value ?? '';
        $this->editingStatus = $membership->status->value;
        Flux::modal('edit-member')->show();
    }

    public function save(): void
    {
        $membership = $this->findMembership($this->editingId);
        $validated = $this->validate([
            'editingRole' => ['required', Rule::enum(OrganizationRole::class)],
            'editingVoice' => ['nullable', Rule::enum(VoiceType::class)->only([VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass]), Rule::requiredIf($this->editingRole === OrganizationRole::Member->value)],
            'editingStatus' => ['required', Rule::enum(OrganizationMembershipStatus::class)->only([OrganizationMembershipStatus::Active, OrganizationMembershipStatus::Suspended, OrganizationMembershipStatus::Left])],
        ]);

        abort_if($membership->user_id === Auth::id() && $validated['editingStatus'] !== OrganizationMembershipStatus::Active->value, 422, 'No puedes suspender tu propia membresía.');
        $membership->update(['role' => $validated['editingRole'], 'voice_type' => $validated['editingRole'] === OrganizationRole::Admin->value ? null : $validated['editingVoice'], 'status' => $validated['editingStatus'], 'left_at' => $validated['editingStatus'] === OrganizationMembershipStatus::Left->value ? now() : null]);
        unset($this->members);
        Flux::modal('edit-member')->close();
        Flux::toast(variant: 'success', text: 'Miembro actualizado.');
    }

    private function findMembership(?int $id): OrganizationMembership
    {
        $organization = app(CurrentOrganization::class)->organization(Auth::user());

        return OrganizationMembership::query()->forOrganization($organization)->findOrFail($id);
    }
}; ?>

<div>
    <x-page-header title="Miembros" description="Administra roles, cuerdas y estado de las membresías." />
    <div class="mt-7 grid gap-3 md:grid-cols-[1fr_180px_180px]"><flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre o email" aria-label="Buscar miembros" /><flux:select wire:model.live="voice" aria-label="Filtrar por cuerda"><flux:select.option value="">Todas las cuerdas</flux:select.option>@foreach([VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass] as $voiceOption)<flux:select.option :value="$voiceOption->value">{{ $voiceOption->label() }}</flux:select.option>@endforeach</flux:select><flux:select wire:model.live="status" aria-label="Filtrar por estado"><flux:select.option value="">Todos los estados</flux:select.option>@foreach([OrganizationMembershipStatus::Active, OrganizationMembershipStatus::Suspended, OrganizationMembershipStatus::Left] as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->label() }}</flux:select.option>@endforeach</flux:select></div>
    <div class="mt-6 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
        @forelse($this->members as $membership)<article class="grid gap-3 py-5 md:grid-cols-[minmax(0,1fr)_140px_120px_auto] md:items-center" wire:key="member-{{ $membership->id }}"><div class="flex min-w-0 items-center gap-3"><flux:avatar :src="$membership->user->photo_path ? Storage::disk('public')->url($membership->user->photo_path) : null" :name="$membership->user->name" :initials="$membership->user->initials()" size="sm" circle data-test="member-avatar-{{ $membership->id }}" /><div class="min-w-0"><h2 class="truncate font-medium">{{ $membership->user->name }}</h2><p class="truncate text-sm text-zinc-500">{{ $membership->user->email }}</p></div></div><p class="text-sm">{{ $membership->voice_type?->label() ?? 'Sin cuerda' }}</p><flux:badge size="sm" color="zinc">{{ $membership->status->label() }}</flux:badge><flux:button wire:click="edit({{ $membership->id }})" size="sm" variant="ghost" icon="pencil-square">Editar</flux:button></article>@empty<x-empty-state title="No hay miembros" description="No encontramos membresías con estos filtros." icon="users" />@endforelse
    </div><div class="mt-6">{{ $this->members->links() }}</div>
    <flux:modal name="edit-member" class="md:w-[30rem]"><form wire:submit="save" class="space-y-6"><flux:heading size="lg">Editar membresía</flux:heading><flux:select wire:model="editingRole" label="Rol">@foreach(OrganizationRole::cases() as $role)<flux:select.option :value="$role->value">{{ $role->label() }}</flux:select.option>@endforeach</flux:select><flux:select wire:model="editingVoice" label="Cuerda"><flux:select.option value="">Sin cuerda</flux:select.option>@foreach([VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass] as $voiceOption)<flux:select.option :value="$voiceOption->value">{{ $voiceOption->label() }}</flux:select.option>@endforeach</flux:select><flux:select wire:model="editingStatus" label="Estado">@foreach([OrganizationMembershipStatus::Active, OrganizationMembershipStatus::Suspended, OrganizationMembershipStatus::Left] as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->label() }}</flux:select.option>@endforeach</flux:select><div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Guardar</flux:button></div></form></flux:modal>
</div>

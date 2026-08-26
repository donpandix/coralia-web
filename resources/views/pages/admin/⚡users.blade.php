<?php

use App\Enums\UserStatus;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Usuarios')] class extends Component {
    use WithPagination;
    #[Url(as: 'buscar')] public string $search = '';
    #[Url] public string $filter = '';
    public ?int $userId = null;
    public string $status = '';

    #[Computed]
    public function users()
    {
        return User::query()->withCount('memberships')->when($this->search !== '', fn (Builder $query) => $query->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%'))->when($this->filter !== '', fn (Builder $query) => $query->where('status', $this->filter))->latest()->paginate(20);
    }

    public function edit(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        $this->userId = $user->id;
        $this->status = $user->status->value;
        Flux::modal('user-status')->show();
    }

    public function save(): void
    {
        $validated = $this->validate(['status' => ['required', Rule::enum(UserStatus::class)]]);
        abort_if($this->userId === Auth::id() && $validated['status'] === UserStatus::Suspended->value, 422, 'No puedes suspender tu propia cuenta.');
        User::query()->findOrFail($this->userId)->update($validated);
        unset($this->users);
        Flux::modal('user-status')->close();
        Flux::toast(variant: 'success', text: 'Usuario actualizado.');
    }
}; ?>

<div><x-page-header title="Usuarios" description="Consulta y administra el estado de las cuentas." /><div class="mt-7 grid gap-3 md:grid-cols-[1fr_190px]"><flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre o email" aria-label="Buscar usuarios" /><flux:select wire:model.live="filter" aria-label="Filtrar por estado"><flux:select.option value="">Todos los estados</flux:select.option>@foreach(UserStatus::cases() as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->value }}</flux:select.option>@endforeach</flux:select></div><div class="mt-6 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">@forelse($this->users as $user)<article class="grid gap-3 py-5 md:grid-cols-[minmax(0,1fr)_120px_120px_auto] md:items-center" wire:key="admin-user-{{ $user->id }}"><div><h2 class="font-medium">{{ $user->name }}</h2><p class="mt-1 text-sm text-zinc-500">{{ $user->email }}</p></div><p class="text-sm">{{ $user->memberships_count }} membresías</p><flux:badge size="sm" color="zinc">{{ $user->is_super_admin ? 'SUPER_ADMIN' : $user->status->value }}</flux:badge><flux:button wire:click="edit({{ $user->id }})" size="sm" variant="ghost">Editar</flux:button></article>@empty<x-empty-state title="No encontramos usuarios" icon="users" />@endforelse</div><div class="mt-6">{{ $this->users->links() }}</div><flux:modal name="user-status" class="md:w-96"><form wire:submit="save" class="space-y-6"><flux:heading size="lg">Estado del usuario</flux:heading><flux:select wire:model="status" label="Estado">@foreach(UserStatus::cases() as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->value }}</flux:select.option>@endforeach</flux:select><div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Guardar</flux:button></div></form></flux:modal></div>

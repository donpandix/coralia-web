<?php

use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int|string $organizationId = '';

    public function mount(CurrentOrganization $currentOrganization): void
    {
        $this->organizationId = $currentOrganization->organization(Auth::user())?->id ?? '';
    }

    #[Computed]
    public function memberships()
    {
        return app(CurrentOrganization::class)->memberships(Auth::user());
    }

    public function updatedOrganizationId(CurrentOrganization $currentOrganization): void
    {
        $currentOrganization->select(Auth::user(), (int) $this->organizationId);
        unset($this->memberships);
        Flux::toast(variant: 'success', text: 'Organización cambiada.');
        $this->redirect(route('library.index', absolute: false), navigate: true);
    }
}; ?>

<div>
    @if ($this->memberships->count() > 1)
        <flux:select wire:model.live="organizationId" aria-label="Organización actual" size="sm">
            @foreach ($this->memberships as $membership)
                <flux:select.option :value="$membership->organization_id" wire:key="organization-{{ $membership->organization_id }}">{{ $membership->organization->name }}</flux:select.option>
            @endforeach
        </flux:select>
    @elseif ($this->memberships->isNotEmpty())
        <p class="truncate px-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $this->memberships->first()->organization->name }}</p>
    @endif
</div>

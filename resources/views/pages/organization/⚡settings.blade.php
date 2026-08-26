<?php

use App\Models\Organization;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Organización')] class extends Component {
    use WithFileUploads;

    public Organization $organization;
    public string $name = '';
    public string $description = '';
    public string $city = '';
    public $logo;

    public function mount(CurrentOrganization $currentOrganization): void
    {
        $this->organization = $currentOrganization->organization(Auth::user());
        Gate::authorize('update', $this->organization);
        $this->name = $this->organization->name;
        $this->description = $this->organization->description ?? '';
        $this->city = $this->organization->city ?? '';
    }

    public function save(): void
    {
        Gate::authorize('update', $this->organization);
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'city' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($this->logo) {
            if ($this->organization->logo_path) {
                Storage::disk('public')->delete($this->organization->logo_path);
            }
            $validated['logo_path'] = $this->logo->store('organizations/'.$this->organization->public_id, 'public');
        }

        unset($validated['logo']);
        $this->organization->update($validated);
        $this->reset('logo');
        Flux::toast(variant: 'success', text: 'Organización actualizada.');
    }
}; ?>

<div class="mx-auto max-w-3xl">
    <x-page-header title="Organización" description="Información visible para los integrantes de tu coro." />
    <form wire:submit="save" class="mt-8 space-y-6">
        <div class="flex items-center gap-4">@if($organization->logo_path)<img src="{{ Storage::disk('public')->url($organization->logo_path) }}" alt="Logo de {{ $organization->name }}" class="size-16 rounded-xl object-cover">@else<div class="flex size-16 items-center justify-center rounded-xl bg-coral-100 text-xl font-semibold text-coral-800 dark:bg-coral-950 dark:text-coral-200">{{ str($organization->name)->substr(0, 1) }}</div>@endif<flux:input wire:model="logo" type="file" label="Logo" accept="image/jpeg,image/png,image/webp" /></div>
        <flux:input wire:model="name" label="Nombre" required />
        <flux:input wire:model="city" label="Ciudad" />
        <flux:textarea wire:model="description" label="Descripción" rows="5" />
        <div class="flex justify-end"><flux:button type="submit" variant="primary">Guardar cambios</flux:button></div>
    </form>
</div>

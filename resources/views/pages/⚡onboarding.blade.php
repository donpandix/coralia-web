<?php

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRequestStatus;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\VoiceType;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\OrganizationRequest;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Comenzar en Coralia')] class extends Component {
    #[Url(as: 'buscar')]
    public string $search = '';

    public string $voiceType = '';
    public string $organizationName = '';
    public string $description = '';
    public string $city = '';
    public string $additionalInfo = '';

    public function mount(CurrentOrganization $currentOrganization): void
    {
        if (Auth::user()->is_super_admin || $currentOrganization->membership(Auth::user()) !== null) {
            $this->redirect(route('dashboard', absolute: false), navigate: true);
        }
    }

    #[Computed]
    public function organizations()
    {
        $user = Auth::user();

        return Organization::query()
            ->select(['id', 'public_id', 'name', 'description', 'city'])
            ->where('status', OrganizationStatus::Active)
            ->whereDoesntHave('memberships', fn ($query) => $query->where('user_id', $user->id))
            ->when($this->search !== '', fn ($query) => $query->where(function ($searchQuery): void {
                $searchQuery->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('city', 'like', '%'.$this->search.'%');
            }))
            ->orderBy('name')
            ->limit(12)
            ->get();
    }

    #[Computed]
    public function pendingMemberships()
    {
        return Auth::user()->memberships()
            ->where('status', OrganizationMembershipStatus::Pending)
            ->with('organization:id,name,city')
            ->latest()
            ->get();
    }

    #[Computed]
    public function organizationRequests()
    {
        return Auth::user()->organizationRequests()->latest()->get();
    }

    public function apply(int $organizationId): void
    {
        $validated = $this->validate([
            'voiceType' => ['required', Rule::enum(VoiceType::class)->only([VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass])],
        ], ['voiceType.required' => 'Selecciona tu cuerda antes de postular.']);

        $organization = Organization::query()->where('status', OrganizationStatus::Active)->findOrFail($organizationId);

        OrganizationMembership::query()->create([
            'organization_id' => $organization->id,
            'user_id' => Auth::id(),
            'role' => OrganizationRole::Member,
            'voice_type' => $validated['voiceType'],
            'status' => OrganizationMembershipStatus::Pending,
            'requested_at' => now(),
        ]);

        unset($this->organizations, $this->pendingMemberships);
        Flux::toast(variant: 'success', text: 'Postulación enviada. La organización debe aprobarla.');
    }

    public function requestOrganization(): void
    {
        $validated = $this->validate([
            'organizationName' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'city' => ['nullable', 'string', 'max:150'],
            'additionalInfo' => ['nullable', 'string', 'max:5000'],
        ]);

        OrganizationRequest::query()->create([
            'requested_by' => Auth::id(),
            'organization_name' => $validated['organizationName'],
            'description' => $validated['description'],
            'city' => $validated['city'],
            'additional_info' => $validated['additionalInfo'],
            'status' => OrganizationRequestStatus::Pending,
        ]);

        $this->reset('organizationName', 'description', 'city', 'additionalInfo');
        unset($this->organizationRequests);
        Flux::modal('request-organization')->close();
        Flux::toast(variant: 'success', text: 'Solicitud de creación enviada.');
    }
}; ?>

<div class="mx-auto w-full max-w-5xl py-4 sm:py-8">
    <x-page-header title="Encuentra tu coro" description="Postula a una organización existente o solicita crear una nueva.">
        <flux:modal.trigger name="request-organization"><flux:button icon="plus">Solicitar nuevo coro</flux:button></flux:modal.trigger>
    </x-page-header>

    @if ($this->pendingMemberships->isNotEmpty() || $this->organizationRequests->isNotEmpty())
        <section class="mt-8 rounded-xl border border-coral-200 bg-coral-50 p-5 dark:border-coral-900 dark:bg-coral-950/30" aria-labelledby="pending-heading">
            <h2 id="pending-heading" class="font-medium text-coral-950 dark:text-coral-100">Solicitudes en revisión</h2>
            <div class="mt-3 grid gap-2 text-sm text-coral-800 dark:text-coral-200">
                @foreach ($this->pendingMemberships as $membership)<p wire:key="pending-membership-{{ $membership->id }}">Postulación a <strong>{{ $membership->organization->name }}</strong> · Pendiente</p>@endforeach
                @foreach ($this->organizationRequests as $request)<p wire:key="organization-request-{{ $request->id }}">Creación de <strong>{{ $request->organization_name }}</strong> · {{ $request->status->label() }}</p>@endforeach
            </div>
        </section>
    @endif

    <section class="mt-8">
        <div class="grid gap-4 sm:grid-cols-[1fr_220px]">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" label="Buscar organización" placeholder="Nombre o ciudad" />
            <flux:select wire:model="voiceType" label="Mi cuerda" placeholder="Selecciona tu cuerda">
                @foreach ([VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass] as $voice)<flux:select.option :value="$voice->value">{{ $voice->label() }}</flux:select.option>@endforeach
            </flux:select>
        </div>

        <div class="mt-6 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
            @forelse ($this->organizations as $organization)
                <article class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between" wire:key="organization-{{ $organization->id }}">
                    <div><h2 class="font-medium text-zinc-950 dark:text-white">{{ $organization->name }}</h2><p class="mt-1 text-sm text-zinc-500">{{ $organization->city ?: 'Ciudad no informada' }}</p>@if($organization->description)<p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">{{ $organization->description }}</p>@endif</div>
                    <flux:button wire:click="apply({{ $organization->id }})" wire:loading.attr="disabled" class="min-h-11">Postular</flux:button>
                </article>
            @empty
                <x-empty-state title="No encontramos organizaciones" description="Prueba otra búsqueda o solicita crear tu coro." icon="building-office" />
            @endforelse
        </div>
    </section>

    <flux:modal name="request-organization" class="md:w-[34rem]">
        <form wire:submit="requestOrganization" class="space-y-6">
            <div><flux:heading size="lg">Solicitar creación de organización</flux:heading><flux:text class="mt-2">Un superadministrador revisará los antecedentes.</flux:text></div>
            <flux:input wire:model="organizationName" label="Nombre del coro" required />
            <flux:input wire:model="city" label="Ciudad" />
            <flux:textarea wire:model="description" label="Descripción" rows="3" />
            <flux:textarea wire:model="additionalInfo" label="Información adicional" rows="3" />
            <div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Enviar solicitud</flux:button></div>
        </form>
    </flux:modal>
</div>

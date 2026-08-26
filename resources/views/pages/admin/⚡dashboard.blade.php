<?php

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRequestStatus;
use App\Enums\OrganizationStatus;
use App\Enums\ReportStatus;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\OrganizationRequest;
use App\Models\Piece;
use App\Models\Report;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Superadministración')] class extends Component {
    #[Computed]
    public function metrics(): array
    {
        return [
            ['label' => 'Organizaciones activas', 'value' => Organization::query()->where('status', OrganizationStatus::Active)->count(), 'icon' => 'building-office-2'],
            ['label' => 'Usuarios', 'value' => User::query()->count(), 'icon' => 'users'],
            ['label' => 'Membresías activas', 'value' => OrganizationMembership::query()->where('status', OrganizationMembershipStatus::Active)->count(), 'icon' => 'identification'],
            ['label' => 'Piezas', 'value' => Piece::query()->count(), 'icon' => 'musical-note'],
        ];
    }

    #[Computed]
    public function pendingRequests(): int
    {
        return OrganizationRequest::query()->where('status', OrganizationRequestStatus::Pending)->count();
    }

    #[Computed]
    public function openReports(): int
    {
        return Report::query()->whereIn('status', [ReportStatus::Open, ReportStatus::InReview])->count();
    }
}; ?>

<div><x-page-header title="Dashboard" description="Estado general de Coralia." /><div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach($this->metrics as $metric)<article class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-800" wire:key="metric-{{ $metric['label'] }}"><flux:icon :name="$metric['icon']" class="size-5 text-coral-700 dark:text-coral-300" /><p class="mt-5 text-3xl font-semibold">{{ $metric['value'] }}</p><p class="mt-1 text-sm text-zinc-500">{{ $metric['label'] }}</p></article>@endforeach</div><div class="mt-8 grid gap-4 md:grid-cols-2"><a href="{{ route('admin.requests') }}" wire:navigate class="rounded-xl border border-zinc-200 p-5 transition hover:border-coral-300 dark:border-zinc-800"><p class="text-sm text-zinc-500">Solicitudes pendientes</p><p class="mt-2 text-2xl font-semibold">{{ $this->pendingRequests }}</p></a><a href="{{ route('admin.reports') }}" wire:navigate class="rounded-xl border border-zinc-200 p-5 transition hover:border-coral-300 dark:border-zinc-800"><p class="text-sm text-zinc-500">Reportes abiertos</p><p class="mt-2 text-2xl font-semibold">{{ $this->openReports }}</p></a></div></div>

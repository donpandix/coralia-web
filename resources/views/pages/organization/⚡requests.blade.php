<?php

use App\Enums\OrganizationMembershipStatus;
use App\Models\OrganizationMembership;
use App\Notifications\MembershipApproved;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Solicitudes de ingreso')] class extends Component {
    #[Computed]
    public function requests()
    {
        $organization = app(CurrentOrganization::class)->organization(Auth::user());

        return OrganizationMembership::query()->forOrganization($organization)
            ->where('status', OrganizationMembershipStatus::Pending)
            ->with('user:id,name,email')
            ->oldest('requested_at')
            ->get();
    }

    public function approve(int $membershipId): void
    {
        $membership = $this->findRequest($membershipId);
        $membership->update(['status' => OrganizationMembershipStatus::Active, 'approved_at' => now(), 'approved_by' => Auth::id(), 'joined_at' => now()]);
        $membership->user->notify((new MembershipApproved($membership->organization))->afterCommit());
        unset($this->requests);
        Flux::toast(variant: 'success', text: 'Miembro aprobado.');
    }

    public function reject(int $membershipId): void
    {
        $membership = $this->findRequest($membershipId);
        $membership->update(['status' => OrganizationMembershipStatus::Rejected, 'approved_by' => Auth::id(), 'approved_at' => now()]);
        unset($this->requests);
        Flux::toast(text: 'Solicitud rechazada.');
    }

    private function findRequest(int $id): OrganizationMembership
    {
        $organization = app(CurrentOrganization::class)->organization(Auth::user());

        return OrganizationMembership::query()->forOrganization($organization)->where('status', OrganizationMembershipStatus::Pending)->with(['user', 'organization'])->findOrFail($id);
    }
}; ?>

<div class="mx-auto max-w-4xl">
    <x-page-header title="Solicitudes" description="Personas que esperan aprobación para ingresar al coro." />
    <div class="mt-7 space-y-3">
        @forelse($this->requests as $request)
            <article class="flex flex-col gap-4 rounded-xl border border-zinc-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800" wire:key="request-{{ $request->id }}"><div><h2 class="font-medium">{{ $request->user->name }}</h2><p class="mt-1 text-sm text-zinc-500">{{ $request->user->email }}</p><p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Solicita ingresar como {{ $request->voice_type?->label() ?? 'miembro' }}.</p></div><div class="flex gap-2"><flux:button wire:click="approve({{ $request->id }})" variant="primary">Aprobar</flux:button><flux:button wire:click="reject({{ $request->id }})" wire:confirm="¿Rechazar esta solicitud de ingreso?" variant="ghost">Rechazar</flux:button></div></article>
        @empty<x-empty-state title="No hay solicitudes pendientes" description="Las nuevas postulaciones aparecerán aquí." icon="inbox" />@endforelse
    </div>
</div>

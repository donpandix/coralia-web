<?php

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRequestStatus;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\OrganizationRequest;
use App\Notifications\MembershipApproved;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Solicitudes de organizaciones')] class extends Component {
    #[Computed]
    public function requests()
    {
        return OrganizationRequest::query()->where('status', OrganizationRequestStatus::Pending)->with('requester:id,name,email')->oldest()->get();
    }

    public function approve(int $requestId): void
    {
        $request = DB::transaction(function () use ($requestId): OrganizationRequest {
            $request = OrganizationRequest::query()->where('status', OrganizationRequestStatus::Pending)->lockForUpdate()->findOrFail($requestId);
            $organization = Organization::query()->create(['name' => $request->organization_name, 'description' => $request->description, 'city' => $request->city, 'owner_user_id' => $request->requested_by, 'status' => OrganizationStatus::Active]);
            OrganizationMembership::query()->create(['organization_id' => $organization->id, 'user_id' => $request->requested_by, 'role' => OrganizationRole::Admin, 'voice_type' => null, 'status' => OrganizationMembershipStatus::Active, 'requested_at' => $request->created_at, 'approved_at' => now(), 'approved_by' => Auth::id(), 'joined_at' => now()]);
            $request->update(['status' => OrganizationRequestStatus::Approved, 'reviewed_by' => Auth::id(), 'reviewed_at' => now(), 'organization_id' => $organization->id]);
            return $request->load(['requester', 'organization']);
        });

        $request->requester->notify((new MembershipApproved($request->organization))->afterCommit());
        unset($this->requests);
        Flux::toast(variant: 'success', text: 'Organización creada y solicitud aprobada.');
    }

    public function reject(int $requestId): void
    {
        OrganizationRequest::query()->where('status', OrganizationRequestStatus::Pending)->findOrFail($requestId)->update(['status' => OrganizationRequestStatus::Rejected, 'reviewed_by' => Auth::id(), 'reviewed_at' => now(), 'review_notes' => 'Solicitud rechazada desde administración.']);
        unset($this->requests);
        Flux::toast(text: 'Solicitud rechazada.');
    }
}; ?>

<div class="mx-auto max-w-5xl"><x-page-header title="Solicitudes" description="Revisa las solicitudes para crear nuevas organizaciones." /><div class="mt-7 space-y-4">@forelse($this->requests as $request)<article class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-800" wire:key="admin-request-{{ $request->id }}"><div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between"><div><h2 class="font-medium">{{ $request->organization_name }}</h2><p class="mt-1 text-sm text-zinc-500">Solicitada por {{ $request->requester->name }} · {{ $request->requester->email }}</p>@if($request->city)<p class="mt-3 text-sm">{{ $request->city }}</p>@endif@if($request->description)<p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $request->description }}</p>@endif@if($request->additional_info)<p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-500">{{ $request->additional_info }}</p>@endif</div><div class="flex shrink-0 gap-2"><flux:button wire:click="approve({{ $request->id }})" variant="primary">Aprobar</flux:button><flux:button wire:click="reject({{ $request->id }})" wire:confirm="¿Rechazar la creación de esta organización?" variant="ghost">Rechazar</flux:button></div></div></article>@empty<x-empty-state title="No hay solicitudes pendientes" description="Las nuevas solicitudes de creación aparecerán aquí." icon="inbox" />@endforelse</div></div>

<?php

use App\Actions\Pieces\SavePiece;
use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Enums\PieceFileType;
use App\Enums\PieceShareType;
use App\Enums\PieceStatus;
use App\Enums\TagStatus;
use App\Enums\VoiceType;
use App\Models\Group;
use App\Models\OrganizationMembership;
use App\Models\Piece;
use App\Models\Tag;
use App\Support\CurrentOrganization;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Editar pieza')] class extends Component {
    use WithFileUploads;

    public ?Piece $piece = null;
    public string $title = '';
    public string $subtitle = '';
    public string $body = '';
    public string $status = 'ACTIVE';
    public array $tagIds = [];
    public bool $shareOrganization = true;
    public array $voiceShares = [];
    public array $groupIds = [];
    public array $membershipIds = [];
    public $score;
    public $audioSoprano;
    public $audioAlto;
    public $audioTenor;
    public $audioBass;

    public function mount(?Piece $piece = null): void
    {
        if ($piece?->exists) {
            Gate::authorize('update', $piece);
            $this->piece = $piece->load(['tags:id', 'shares', 'files']);
            $this->title = $piece->title;
            $this->subtitle = $piece->subtitle ?? '';
            $this->body = $piece->body ?? '';
            $this->status = $piece->status->value;
            $this->tagIds = $piece->tags->pluck('id')->map(fn ($id) => (string) $id)->all();
            $this->shareOrganization = $piece->shares->contains('share_type', PieceShareType::Organization);
            $this->voiceShares = $piece->shares->where('share_type', PieceShareType::Voice)->pluck('voice_type')->map(fn (VoiceType $voice) => $voice->value)->all();
            $this->groupIds = $piece->shares->where('share_type', PieceShareType::Group)->pluck('group_id')->map(fn ($id) => (string) $id)->all();
            $this->membershipIds = $piece->shares->where('share_type', PieceShareType::Member)->pluck('membership_id')->map(fn ($id) => (string) $id)->all();
        } else {
            Gate::authorize('create', Piece::class);
        }
    }

    #[Computed] public function tags() { return Tag::query()->where('status', TagStatus::Active)->orderBy('name')->get(['id', 'name']); }
    #[Computed] public function groups() { return Group::query()->forOrganization(app(CurrentOrganization::class)->organization(Auth::user()))->where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name']); }
    #[Computed] public function members() { return OrganizationMembership::query()->forOrganization(app(CurrentOrganization::class)->organization(Auth::user()))->where('status', OrganizationMembershipStatus::Active)->where('role', OrganizationRole::Member)->with('user:id,name')->get()->sortBy('user.name'); }

    public function save(SavePiece $savePiece): void
    {
        Gate::authorize($this->piece ? 'update' : 'create', $this->piece ?? Piece::class);
        $organization = app(CurrentOrganization::class)->organization(Auth::user());
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:100'], 'subtitle' => ['nullable', 'string', 'max:250'], 'body' => ['nullable', 'string'], 'status' => ['required', Rule::enum(PieceStatus::class)],
            'tagIds' => ['array'], 'tagIds.*' => ['integer', Rule::exists(Tag::class, 'id')->where('status', TagStatus::Active->value)],
            'shareOrganization' => ['boolean'], 'voiceShares' => ['array'], 'voiceShares.*' => [Rule::enum(VoiceType::class)->only([VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass])],
            'groupIds' => ['array'], 'groupIds.*' => ['integer', Rule::exists(Group::class, 'id')->where('organization_id', $organization->id)],
            'membershipIds' => ['array'], 'membershipIds.*' => ['integer', Rule::exists(OrganizationMembership::class, 'id')->where('organization_id', $organization->id)->where('status', OrganizationMembershipStatus::Active->value)],
            'score' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'audioSoprano' => ['nullable', 'file', 'mimes:mp3,wav', 'max:5120'], 'audioAlto' => ['nullable', 'file', 'mimes:mp3,wav', 'max:5120'], 'audioTenor' => ['nullable', 'file', 'mimes:mp3,wav', 'max:5120'], 'audioBass' => ['nullable', 'file', 'mimes:mp3,wav', 'max:5120'],
        ]);

        if (! $validated['shareOrganization'] && $validated['voiceShares'] === [] && $validated['groupIds'] === [] && $validated['membershipIds'] === []) {
            $this->addError('shareOrganization', 'Selecciona al menos un destinatario.');
            return;
        }

        $piece = $savePiece->handle(Auth::user(), $organization, $this->piece, $validated, ['score' => $this->score, 'audioSoprano' => $this->audioSoprano, 'audioAlto' => $this->audioAlto, 'audioTenor' => $this->audioTenor, 'audioBass' => $this->audioBass]);
        Flux::toast(variant: 'success', text: 'Pieza guardada.');
        $this->redirect(route('organization.pieces.edit', $piece, absolute: false), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-4xl">
    <flux:button variant="ghost" icon="arrow-left" :href="route('organization.pieces.index')" wire:navigate>Piezas</flux:button>
    <x-page-header class="mt-5" :title="$piece ? 'Editar pieza' : 'Nueva pieza'" description="Organiza la información, archivos y destinatarios del material." />
    <form wire:submit="save" class="mt-8 space-y-10">
        <section aria-labelledby="information-heading"><h2 id="information-heading" class="border-b border-zinc-200 pb-3 text-lg font-semibold dark:border-zinc-800">Información</h2><div class="mt-5 grid gap-5"><flux:input wire:model="title" label="Título" required maxlength="100" /><flux:input wire:model="subtitle" label="Subtítulo" maxlength="250" /><flux:textarea wire:model="body" label="Texto" rows="6" /><div class="grid gap-5 sm:grid-cols-2"><flux:select wire:model="status" label="Estado">@foreach(PieceStatus::cases() as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->label() }}</flux:select.option>@endforeach</flux:select><fieldset><legend class="mb-2 text-sm font-medium">Etiquetas</legend><div class="flex max-h-40 flex-wrap gap-3 overflow-y-auto rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">@foreach($this->tags as $tag)<flux:checkbox wire:model="tagIds" :value="$tag->id" :label="$tag->name" wire:key="form-tag-{{ $tag->id }}" />@endforeach</div></fieldset></div></div></section>

        <section aria-labelledby="score-form-heading"><h2 id="score-form-heading" class="border-b border-zinc-200 pb-3 text-lg font-semibold dark:border-zinc-800">Partitura</h2><div class="mt-5"><flux:input wire:model="score" type="file" label="PDF (máximo 5 MB)" accept="application/pdf" />@if($piece?->files->firstWhere('file_type', PieceFileType::Score))<p class="mt-2 text-sm text-zinc-500">Archivo actual: {{ $piece->files->firstWhere('file_type', PieceFileType::Score)->original_filename }}</p>@endif</div></section>

        <section aria-labelledby="audio-form-heading"><h2 id="audio-form-heading" class="border-b border-zinc-200 pb-3 text-lg font-semibold dark:border-zinc-800">Audios</h2><div class="mt-5 grid gap-5 sm:grid-cols-2">@foreach(['audioSoprano' => VoiceType::Soprano, 'audioAlto' => VoiceType::Alto, 'audioTenor' => VoiceType::Tenor, 'audioBass' => VoiceType::Bass] as $property => $voice)<div><flux:input wire:model="{{ $property }}" type="file" :label="$voice->label().' · MP3 o WAV (máximo 5 MB)'" accept="audio/mpeg,audio/wav" />@php($existingAudio = $piece?->files->first(fn($file) => $file->file_type === PieceFileType::Audio && $file->voice_type === $voice))@if($existingAudio)<p class="mt-2 text-sm text-zinc-500">Actual: {{ $existingAudio->original_filename }}</p>@endif</div>@endforeach</div></section>

        <section aria-labelledby="sharing-heading"><h2 id="sharing-heading" class="border-b border-zinc-200 pb-3 text-lg font-semibold dark:border-zinc-800">Compartir con</h2><div class="mt-5 space-y-6"><flux:checkbox wire:model="shareOrganization" label="Toda la organización" /><fieldset><legend class="mb-3 text-sm font-medium">Cuerdas</legend><div class="flex flex-wrap gap-4">@foreach([VoiceType::Soprano, VoiceType::Alto, VoiceType::Tenor, VoiceType::Bass] as $voice)<flux:checkbox wire:model="voiceShares" :value="$voice->value" :label="$voice->value" />@endforeach</div></fieldset><div class="grid gap-6 md:grid-cols-2"><fieldset><legend class="mb-3 text-sm font-medium">Grupos</legend><div class="max-h-52 space-y-2 overflow-y-auto rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">@forelse($this->groups as $group)<flux:checkbox wire:model="groupIds" :value="$group->id" :label="$group->name" wire:key="share-group-{{ $group->id }}" />@empty<p class="text-sm text-zinc-500">No hay grupos activos.</p>@endforelse</div></fieldset><fieldset><legend class="mb-3 text-sm font-medium">Usuarios</legend><div class="max-h-52 space-y-2 overflow-y-auto rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">@foreach($this->members as $member)<flux:checkbox wire:model="membershipIds" :value="$member->id" :label="$member->user->name.' · '.($member->voice_type?->value ?? '')" wire:key="share-member-{{ $member->id }}" />@endforeach</div></fieldset></div><flux:error name="shareOrganization" /></div></section>
        <div class="sticky bottom-4 flex justify-end rounded-xl border border-zinc-200 bg-white/95 p-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95"><flux:button type="submit" variant="primary" wire:loading.attr="disabled">Guardar pieza</flux:button></div>
    </form>
</div>

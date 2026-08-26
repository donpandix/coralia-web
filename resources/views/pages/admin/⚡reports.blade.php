<?php

use App\Enums\ReportStatus;
use App\Models\Report;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Reportes')] class extends Component {
    use WithPagination;
    public ?int $reportId = null;
    public string $status = '';
    public string $resolutionNotes = '';

    #[Computed]
    public function reports()
    {
        return Report::query()->with(['reporter:id,name,email', 'organization:id,name'])->latest()->paginate(15);
    }

    public function review(int $reportId): void
    {
        $report = Report::query()->findOrFail($reportId);
        $this->reportId = $report->id;
        $this->status = $report->status->value;
        $this->resolutionNotes = $report->resolution_notes ?? '';
        Flux::modal('report-review')->show();
    }

    public function save(): void
    {
        $validated = $this->validate(['status' => ['required', Rule::enum(ReportStatus::class)], 'resolutionNotes' => ['nullable', 'string', 'max:5000']]);
        $isClosed = in_array($validated['status'], [ReportStatus::Resolved->value, ReportStatus::Dismissed->value], true);
        Report::query()->findOrFail($this->reportId)->update(['status' => $validated['status'], 'resolution_notes' => $validated['resolutionNotes'], 'resolved_by' => $isClosed ? Auth::id() : null, 'resolved_at' => $isClosed ? now() : null]);
        unset($this->reports);
        Flux::modal('report-review')->close();
        Flux::toast(variant: 'success', text: 'Reporte actualizado.');
    }
}; ?>

<div><x-page-header title="Reportes" description="Revisa alertas sobre usuarios y piezas." /><div class="mt-7 space-y-3">@forelse($this->reports as $report)<article class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-800" wire:key="report-{{ $report->id }}"><div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"><div><div class="flex flex-wrap items-center gap-2"><h2 class="font-medium">{{ $report->reason }}</h2><flux:badge size="sm" color="zinc">{{ $report->status->value }}</flux:badge></div><p class="mt-1 text-sm text-zinc-500">{{ $report->organization->name }} · {{ $report->target_type->value }} #{{ $report->target_id }} · {{ $report->reporter->name }}</p>@if($report->description)<p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $report->description }}</p>@endif</div><flux:button wire:click="review({{ $report->id }})" size="sm">Revisar</flux:button></div></article>@empty<x-empty-state title="No hay reportes" description="Los reportes enviados aparecerán aquí." icon="flag" />@endforelse</div><div class="mt-6">{{ $this->reports->links() }}</div><flux:modal name="report-review" class="md:w-[32rem]"><form wire:submit="save" class="space-y-6"><flux:heading size="lg">Revisar reporte</flux:heading><flux:select wire:model="status" label="Estado">@foreach(ReportStatus::cases() as $statusOption)<flux:select.option :value="$statusOption->value">{{ $statusOption->value }}</flux:select.option>@endforeach</flux:select><flux:textarea wire:model="resolutionNotes" label="Notas de resolución" rows="5" /><div class="flex justify-end gap-2"><flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close><flux:button type="submit" variant="primary">Guardar</flux:button></div></form></flux:modal></div>

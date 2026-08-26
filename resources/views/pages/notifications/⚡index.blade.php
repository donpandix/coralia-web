<?php

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Notificaciones')] class extends Component {
    use WithPagination;

    #[Computed]
    public function notifications()
    {
        return Auth::user()->notifications()->latest()->paginate(15);
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        unset($this->notifications);

        if (isset($notification->data['url'])) {
            $this->redirect($notification->data['url'], navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->each(fn (DatabaseNotification $notification) => $notification->markAsRead());
        unset($this->notifications);
    }
}; ?>

<div class="mx-auto max-w-4xl">
    <x-page-header title="Notificaciones" description="Novedades de tus organizaciones y repertorio.">
        @if(Auth::user()->unreadNotifications()->exists())<flux:button wire:click="markAllAsRead" variant="ghost" size="sm">Marcar todas como leídas</flux:button>@endif
    </x-page-header>

    <div class="mt-7 divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
        @forelse($this->notifications as $notification)
            <button type="button" wire:click="markAsRead('{{ $notification->id }}')" class="flex w-full items-start gap-4 px-1 py-5 text-left outline-none hover:bg-zinc-50 focus-visible:ring-2 focus-visible:ring-coral-600 dark:hover:bg-zinc-900" wire:key="notification-{{ $notification->id }}">
                <span class="mt-2 size-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-zinc-300 dark:bg-zinc-700' : 'bg-coral-600' }}" aria-label="{{ $notification->read_at ? 'Leída' : 'No leída' }}"></span>
                <span class="min-w-0 flex-1"><span class="block font-medium text-zinc-950 dark:text-white">{{ $notification->data['title'] ?? 'Notificación' }}</span><span class="mt-1 block text-sm text-zinc-600 dark:text-zinc-400">{{ $notification->data['message'] ?? '' }}</span><span class="mt-2 block text-xs text-zinc-500">{{ $notification->created_at->diffForHumans() }}</span></span>
            </button>
        @empty
            <x-empty-state title="No tienes notificaciones" description="Las novedades importantes aparecerán aquí." icon="bell" />
        @endforelse
    </div>
    <div class="mt-6">{{ $this->notifications->links() }}</div>
</div>

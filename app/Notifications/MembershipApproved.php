<?php

namespace App\Notifications;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MembershipApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Organization $organization) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array{title: string, message: string, url: string} */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tu postulación fue aprobada',
            'message' => 'Ya puedes acceder al repertorio de '.$this->organization->name.'.',
            'url' => route('dashboard', absolute: false),
        ];
    }
}

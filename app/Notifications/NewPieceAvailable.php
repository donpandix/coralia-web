<?php

namespace App\Notifications;

use App\Models\Piece;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewPieceAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Piece $piece) {}

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
            'title' => 'Nueva pieza disponible',
            'message' => $this->piece->title.' fue agregada a tu repertorio.',
            'url' => route('library.show', $this->piece, absolute: false),
        ];
    }
}

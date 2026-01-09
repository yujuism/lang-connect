<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PdfHighlightChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $sessionId;
    public int $user_id;
    public array $highlights;

    public function __construct(int $sessionId, int $userId, array $highlights)
    {
        $this->sessionId = $sessionId;
        $this->user_id = $userId;
        $this->highlights = $highlights;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('session.' . $this->sessionId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pdf.highlight';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user_id,
            'highlights' => $this->highlights,
        ];
    }
}

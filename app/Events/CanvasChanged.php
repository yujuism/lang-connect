<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CanvasChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $sessionId;
    public int $userId;
    public array $snapshot;

    public function __construct(int $sessionId, int $userId, array $snapshot)
    {
        $this->sessionId = $sessionId;
        $this->userId = $userId;
        $this->snapshot = $snapshot;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("session.{$this->sessionId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'canvas.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'snapshot' => $this->snapshot,
        ];
    }
}

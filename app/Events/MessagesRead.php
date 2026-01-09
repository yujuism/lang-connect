<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $readerId;
    public int $senderId;
    public array $messageIds;
    public string $readAt;

    public function __construct(int $readerId, int $senderId, array $messageIds, string $readAt)
    {
        $this->readerId = $readerId;
        $this->senderId = $senderId;
        $this->messageIds = $messageIds;
        $this->readAt = $readAt;
    }

    public function broadcastOn(): array
    {
        $userIds = [$this->readerId, $this->senderId];
        sort($userIds);

        return [
            new PrivateChannel("conversation.{$userIds[0]}.{$userIds[1]}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'messages.read';
    }

    public function broadcastWith(): array
    {
        return [
            'reader_id' => $this->readerId,
            'message_ids' => $this->messageIds,
            'read_at' => $this->readAt,
        ];
    }
}

<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Call $call;
    public int $endedBy;

    public function __construct(Call $call, int $endedBy)
    {
        $this->call = $call;
        $this->endedBy = $endedBy;
    }

    public function broadcastOn(): array
    {
        $userIds = [$this->call->caller_id, $this->call->receiver_id];
        sort($userIds);

        return [
            new PrivateChannel("conversation.{$userIds[0]}.{$userIds[1]}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.ended';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->call->id,
            'ended_by' => $this->endedBy,
            'ended_at' => $this->call->ended_at->toISOString(),
        ];
    }
}

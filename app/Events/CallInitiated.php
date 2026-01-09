<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallInitiated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Call $call;

    public function __construct(Call $call)
    {
        $this->call = $call->load(['caller', 'receiver']);
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
        return 'call.initiated';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->call->id,
            'caller_id' => $this->call->caller_id,
            'receiver_id' => $this->call->receiver_id,
            'type' => $this->call->type,
            'caller' => [
                'id' => $this->call->caller->id,
                'name' => $this->call->caller->name,
            ],
        ];
    }
}

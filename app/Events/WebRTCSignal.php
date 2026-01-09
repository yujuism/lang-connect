<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $callId;
    public int $fromUserId;
    public int $toUserId;
    public string $signalType;
    public array $signalData;

    public function __construct(int $callId, int $fromUserId, int $toUserId, string $signalType, array $signalData)
    {
        $this->callId = $callId;
        $this->fromUserId = $fromUserId;
        $this->toUserId = $toUserId;
        $this->signalType = $signalType;
        $this->signalData = $signalData;
    }

    public function broadcastOn(): array
    {
        $userIds = [$this->fromUserId, $this->toUserId];
        sort($userIds);

        return [
            new PrivateChannel("conversation.{$userIds[0]}.{$userIds[1]}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'webrtc.signal';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->callId,
            'from_user_id' => $this->fromUserId,
            'signal_type' => $this->signalType,
            'signal_data' => $this->signalData,
        ];
    }
}

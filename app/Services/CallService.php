<?php

namespace App\Services;

use App\Models\Call;
use App\Models\User;
use App\Events\CallInitiated;
use App\Events\CallAccepted;
use App\Events\CallRejected;
use App\Events\CallEnded;
use App\Events\WebRTCSignal;

class CallService
{
    public function initiateCall(User $caller, User $receiver, string $type): Call
    {
        $call = Call::create([
            'caller_id' => $caller->id,
            'receiver_id' => $receiver->id,
            'type' => $type,
            'status' => 'initiated',
        ]);

        $call->load(['caller', 'receiver']);

        broadcast(new CallInitiated($call))->toOthers();

        return $call;
    }

    public function acceptCall(Call $call): Call
    {
        $call->update([
            'status' => 'accepted',
            'started_at' => now(),
        ]);

        broadcast(new CallAccepted($call))->toOthers();

        return $call;
    }

    public function rejectCall(Call $call): Call
    {
        $call->update([
            'status' => 'rejected',
        ]);

        broadcast(new CallRejected($call))->toOthers();

        return $call;
    }

    public function endCall(Call $call, int $endedBy): Call
    {
        $call->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        broadcast(new CallEnded($call, $endedBy))->toOthers();

        return $call;
    }

    public function sendSignal(Call $call, User $fromUser, string $signalType, array $signalData): void
    {
        $toUserId = $call->caller_id === $fromUser->id
            ? $call->receiver_id
            : $call->caller_id;

        $userIds = [$fromUser->id, $toUserId];
        sort($userIds);

        $payloadSize = strlen(json_encode($signalData));
        \Log::info("WebRTC Signal: {$signalType} from {$fromUser->id} to {$toUserId} on channel conversation.{$userIds[0]}.{$userIds[1]} (payload: {$payloadSize} bytes)");

        // Don't use toOthers() - we filter by from_user_id on the client side
        broadcast(new WebRTCSignal(
            $call->id,
            $fromUser->id,
            $toUserId,
            $signalType,
            $signalData
        ));
    }

    public function getActiveCallForUser(User $user): ?Call
    {
        return Call::active()
            ->forUser($user->id)
            ->with(['caller', 'receiver'])
            ->first();
    }

    public function markAsMissed(Call $call): Call
    {
        $call->update([
            'status' => 'missed',
        ]);

        return $call;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\User;
use App\Services\CallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    public function __construct(
        private CallService $callService
    ) {}

    public function initiate(User $user, Request $request)
    {
        $request->validate([
            'type' => 'required|in:video,voice',
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        $call = $this->callService->initiateCall(
            $currentUser,
            $user,
            $request->input('type')
        );

        return response()->json([
            'success' => true,
            'call' => $call,
        ]);
    }

    public function accept(Call $call)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($call->receiver_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $call = $this->callService->acceptCall($call);

        return response()->json([
            'success' => true,
            'call' => $call,
        ]);
    }

    public function reject(Call $call)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($call->receiver_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $call = $this->callService->rejectCall($call);

        return response()->json([
            'success' => true,
        ]);
    }

    public function end(Call $call)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($call->caller_id !== $currentUser->id && $call->receiver_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $call = $this->callService->endCall($call, $currentUser->id);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Show the call window (popup)
     */
    public function window(User $user, Request $request)
    {
        $callType = $request->query('type'); // 'video' or 'voice' for caller
        $callId = $request->query('call_id'); // For receiving calls
        $receiverCallType = $request->query('call_type'); // 'video' or 'voice' for receiver
        $autoAccept = $request->query('auto_accept') === '1';
        $isCaller = $callType !== null && $callId === null;

        return view('call.window', [
            'partner' => $user,
            'callType' => $callType ?: $receiverCallType,
            'callId' => $callId,
            'isCaller' => $isCaller,
            'autoAccept' => $autoAccept,
            'recordingPreference' => auth()->user()->recording_preference ?? 'ask',
        ]);
    }

    public function signal(Call $call, Request $request)
    {
        \Log::info("Signal endpoint called: call {$call->id}, type {$request->input('signal_type')}");

        $request->validate([
            'signal_type' => 'required|in:offer,answer,ice-candidate,ready,video-enabled,video-disabled',
            'signal_data' => 'present|array',
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        \Log::info("Signal from user {$currentUser->id}, call parties: caller={$call->caller_id}, receiver={$call->receiver_id}");

        if ($call->caller_id !== $currentUser->id && $call->receiver_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $this->callService->sendSignal(
            $call,
            $currentUser,
            $request->input('signal_type'),
            $request->input('signal_data')
        );

        \Log::info("Signal sent successfully");

        return response()->json([
            'success' => true,
        ]);
    }
}

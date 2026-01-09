<?php

namespace App\Http\Controllers;

use App\Events\CanvasChanged;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanvasController extends Controller
{
    /**
     * Save canvas state to database.
     */
    public function save(Request $request, PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        // Validate canvas data (tldraw snapshot format)
        $validated = $request->validate([
            'snapshot' => 'required|array',
        ]);

        // Store as JSON
        $session->update([
            'canvas_data' => [
                'snapshot' => $validated['snapshot'],
                'updated_at' => now()->toISOString(),
                'updated_by' => Auth::id(),
            ],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Load canvas state from database.
     */
    public function load(PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'canvas_data' => $session->canvas_data,
        ]);
    }

    /**
     * Broadcast canvas changes to other user (real-time sync).
     */
    public function broadcast(Request $request, PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'snapshot' => 'required|array',
        ]);

        // Broadcast to the session channel
        broadcast(new CanvasChanged(
            $session->id,
            Auth::id(),
            $validated['snapshot']
        ))->toOthers();

        return response()->json(['success' => true]);
    }
}

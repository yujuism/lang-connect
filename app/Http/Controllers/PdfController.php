<?php

namespace App\Http\Controllers;

use App\Events\PdfHighlightChanged;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class PdfController extends Controller
{
    /**
     * Get the MinIO storage disk.
     */
    private function minio(): FilesystemAdapter
    {
        /** @var FilesystemAdapter */
        return Storage::disk('minio');
    }
    /**
     * Upload PDF for a session.
     */
    public function upload(Request $request, PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        // Delete old PDF if exists
        if ($session->pdf_path) {
            $this->minio()->delete($session->pdf_path);
        }

        // Store new PDF
        $path = $request->file('pdf')->store('session-pdfs', 'minio');

        $session->update([
            'pdf_path' => $path,
            'pdf_highlights' => [], // Reset highlights for new PDF
        ]);

        return response()->json([
            'success' => true,
            'pdf_url' => $this->minio()->url($path),
        ]);
    }

    /**
     * Get PDF info for a session.
     */
    public function show(PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'pdf_url' => $session->pdf_path ? $this->minio()->url($session->pdf_path) : null,
            'highlights' => $session->pdf_highlights ?? [],
            'drawings' => $session->pdf_drawings ?? [],
        ]);
    }

    /**
     * Save highlights.
     */
    public function saveHighlights(Request $request, PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'highlights' => 'required|array',
        ]);

        $session->update([
            'pdf_highlights' => $validated['highlights'],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Broadcast highlight changes.
     */
    public function broadcast(Request $request, PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'highlights' => 'required|array',
        ]);

        broadcast(new PdfHighlightChanged(
            $session->id,
            Auth::id(),
            $validated['highlights']
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Save drawings.
     */
    public function saveDrawings(Request $request, PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'drawings' => 'required|array',
        ]);

        $session->update([
            'pdf_drawings' => $validated['drawings'],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Broadcast drawing changes.
     */
    public function broadcastDrawings(Request $request, PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'drawings' => 'required|array',
        ]);

        broadcast(new PdfHighlightChanged(
            $session->id,
            Auth::id(),
            $validated['drawings'],
            'drawings'
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Remove PDF from session.
     */
    public function destroy(PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        if ($session->pdf_path) {
            $this->minio()->delete($session->pdf_path);
        }

        $session->update([
            'pdf_path' => null,
            'pdf_highlights' => null,
        ]);

        return response()->json(['success' => true]);
    }
}

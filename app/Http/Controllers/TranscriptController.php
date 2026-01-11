<?php

namespace App\Http\Controllers;

use App\Jobs\TranscribeAudio;
use App\Models\PracticeSession;
use App\Models\SessionTranscript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TranscriptController extends Controller
{
    /**
     * Upload an audio chunk for transcription.
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:webm,ogg,mp3,wav|max:102400', // 100MB max
            'chunk_number' => 'required|integer|min:0',
            'session_id' => 'nullable|integer|exists:practice_sessions,id',
            'call_id' => 'nullable|integer',
        ]);

        $sessionId = $request->input('session_id');
        $chunkNumber = $request->input('chunk_number');

        // If no session, try to find one from the call
        if (!$sessionId && $request->input('call_id')) {
            // For now, skip - we'll need to link calls to sessions in the future
            // Just store the audio without session link
        }

        // Store audio file
        $file = $request->file('audio');
        $path = $file->store('session-audio', 'minio');

        // Create transcript record
        $transcript = SessionTranscript::create([
            'practice_session_id' => $sessionId,
            'chunk_number' => $chunkNumber,
            'audio_path' => $path,
            'status' => 'pending',
        ]);

        // Dispatch transcription job
        TranscribeAudio::dispatch($transcript);

        return response()->json([
            'success' => true,
            'transcript_id' => $transcript->id,
            'message' => 'Audio chunk uploaded and queued for transcription',
        ]);
    }

    /**
     * Get transcript for a session.
     */
    public function show(PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $analysis = $session->analysis;
        $transcripts = $session->transcripts()->orderBy('chunk_number')->get();

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'transcript' => $analysis?->full_transcript,
            'summary' => $analysis?->summary,
            'topics' => $analysis?->topics,
            'key_phrases' => $analysis?->key_phrases,
            'pronunciation_notes' => $analysis?->pronunciation_notes,
            'status' => $analysis?->status ?? 'pending',
            'chunks' => $transcripts->map(fn($t) => [
                'chunk_number' => $t->chunk_number,
                'status' => $t->status,
                'duration_seconds' => $t->duration_seconds,
            ]),
        ]);
    }

    /**
     * Manually trigger transcription for a session (re-process).
     */
    public function transcribe(PracticeSession $session)
    {
        // Verify user is participant
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        // Find pending transcripts and requeue them
        $pendingTranscripts = $session->transcripts()
            ->whereIn('status', ['pending', 'failed'])
            ->get();

        foreach ($pendingTranscripts as $transcript) {
            $transcript->update(['status' => 'pending', 'error_message' => null]);
            TranscribeAudio::dispatch($transcript);
        }

        return response()->json([
            'success' => true,
            'message' => "Queued {$pendingTranscripts->count()} transcripts for processing",
        ]);
    }
}

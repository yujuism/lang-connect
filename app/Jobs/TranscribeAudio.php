<?php

namespace App\Jobs;

use App\Models\SessionTranscript;
use App\Services\TranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranscribeAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // Retry after 60 seconds
    public int $timeout = 600; // 10 minute timeout

    public function __construct(
        public SessionTranscript $transcript
    ) {}

    public function handle(TranscriptionService $transcriptionService): void
    {
        Log::info("Starting transcription for transcript #{$this->transcript->id}");

        $transcriptionService->transcribeRecord($this->transcript);

        // Check if all chunks for this session are complete
        $this->checkAndTriggerAnalysis();
    }

    /**
     * Check if all chunks are transcribed, then trigger analysis.
     */
    private function checkAndTriggerAnalysis(): void
    {
        $sessionId = $this->transcript->practice_session_id;

        // Count chunks for this session
        $totalChunks = SessionTranscript::where('practice_session_id', $sessionId)->count();
        $completedChunks = SessionTranscript::where('practice_session_id', $sessionId)
            ->where('status', 'completed')
            ->count();

        if ($totalChunks === $completedChunks && $totalChunks > 0) {
            Log::info("All {$totalChunks} chunks transcribed for session #{$sessionId}. Triggering analysis.");

            // Dispatch analysis job
            AnalyzeSession::dispatch($sessionId);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("TranscribeAudio job failed for transcript #{$this->transcript->id}: {$exception->getMessage()}");

        $this->transcript->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}

<?php

namespace App\Jobs;

use App\Services\SessionAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300; // 5 minute timeout

    public function __construct(
        public int $sessionId
    ) {}

    public function handle(SessionAnalysisService $analysisService): void
    {
        Log::info("Starting analysis for session #{$this->sessionId}");

        $analysisService->analyzeSession($this->sessionId);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AnalyzeSession job failed for session #{$this->sessionId}: {$exception->getMessage()}");
    }
}

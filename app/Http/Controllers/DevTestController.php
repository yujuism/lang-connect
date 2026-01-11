<?php

namespace App\Http\Controllers;

use App\Services\SessionAnalysisService;
use App\Services\TranscriptionService;
use Illuminate\Http\Request;

class DevTestController extends Controller
{
    /**
     * Test transcription and analysis pipeline.
     * Only available in local environment.
     */
    public function testTranscribe(Request $request)
    {
        // Only allow in local environment
        if (app()->environment('production')) {
            abort(404);
        }

        $request->validate([
            'audio' => 'required|file|mimes:webm,mp3,wav,m4a,ogg|max:51200', // 50MB max
            'language' => 'nullable|string|max:10',
        ]);

        $transcriptionService = app(TranscriptionService::class);
        $analysisService = app(SessionAnalysisService::class);

        // Save uploaded file temporarily
        $file = $request->file('audio');
        $tempPath = $file->getPathname();
        $originalFilename = $file->getClientOriginalName();

        try {
            // Transcribe
            $transcriptResult = $transcriptionService->transcribeFile(
                $tempPath,
                $request->input('language'),
                $originalFilename
            );

            // Analyze
            $analysisResult = null;
            if (!empty($transcriptResult['transcript'])) {
                $analysisResult = $analysisService->analyzeTranscript(
                    $transcriptResult['transcript'],
                    $transcriptResult['language']
                );
            }

            return response()->json([
                'success' => true,
                'transcription' => [
                    'transcript' => $transcriptResult['transcript'],
                    'language' => $transcriptResult['language'],
                    'duration_seconds' => $transcriptResult['duration'],
                ],
                'analysis' => $analysisResult,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simple test page for uploading audio.
     */
    public function testPage()
    {
        if (app()->environment('production')) {
            abort(404);
        }

        return view('dev.test-transcribe');
    }
}

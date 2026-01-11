<?php

namespace App\Services;

use App\Models\SessionTranscript;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TranscriptionService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->baseUrl = config('services.groq.base_url');
        $this->model = config('services.groq.whisper_model');
    }

    /**
     * Transcribe an audio file using Groq Whisper API.
     *
     * @param string $audioPath Path to audio file in MinIO storage
     * @param string|null $language Optional language hint (e.g., 'en', 'es', 'ja')
     * @return array{transcript: string, language: string, duration: int}
     * @throws \Exception
     */
    public function transcribe(string $audioPath, ?string $language = null): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Groq API key not configured');
        }

        // Get the audio file from MinIO
        $disk = Storage::disk('minio');
        if (!$disk->exists($audioPath)) {
            throw new \Exception("Audio file not found: {$audioPath}");
        }

        // Download to temp file for multipart upload
        $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
        file_put_contents($tempFile, $disk->get($audioPath));

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(300)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                ])
                ->attach(
                    'file',
                    file_get_contents($tempFile),
                    basename($audioPath)
                )
                ->post("{$this->baseUrl}/audio/transcriptions", [
                    'model' => $this->model,
                    'language' => $language,
                    'response_format' => 'verbose_json',
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message') ?? $response->body();
                throw new \Exception("Groq API error: {$error}");
            }

            $data = $response->json();

            return [
                'transcript' => $data['text'] ?? '',
                'language' => $data['language'] ?? $language ?? 'unknown',
                'duration' => (int) ($data['duration'] ?? 0),
            ];
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Transcribe a session transcript record.
     */
    public function transcribeRecord(SessionTranscript $transcript): void
    {
        $transcript->update(['status' => 'processing']);

        try {
            $result = $this->transcribe($transcript->audio_path);

            $transcript->update([
                'transcript' => $result['transcript'],
                'language' => $result['language'],
                'duration_seconds' => $result['duration'],
                'status' => 'completed',
            ]);

            Log::info("Transcription completed for transcript #{$transcript->id}");
        } catch (\Exception $e) {
            Log::error("Transcription failed for transcript #{$transcript->id}: {$e->getMessage()}");

            $transcript->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Transcribe from a local file path (for dev testing).
     *
     * @param string $localPath Path to the audio file
     * @param string|null $language Language hint
     * @param string|null $filename Original filename (for proper extension detection)
     */
    public function transcribeFile(string $localPath, ?string $language = null, ?string $filename = null): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Groq API key not configured');
        }

        if (!file_exists($localPath)) {
            throw new \Exception("File not found: {$localPath}");
        }

        $uploadFilename = $filename ?? basename($localPath);

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::timeout(300)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])
            ->attach(
                'file',
                file_get_contents($localPath),
                $uploadFilename
            )
            ->post("{$this->baseUrl}/audio/transcriptions", [
                'model' => $this->model,
                'language' => $language,
                'response_format' => 'verbose_json',
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("Groq API error: {$error}");
        }

        $data = $response->json();

        return [
            'transcript' => $data['text'] ?? '',
            'language' => $data['language'] ?? $language ?? 'unknown',
            'duration' => (int) ($data['duration'] ?? 0),
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Flashcard;
use App\Models\PracticeSession;
use App\Models\SessionAnalysis;
use App\Models\SessionTranscript;
use App\Models\VocabularyEntry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SessionAnalysisService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.chat_model');
        $this->baseUrl = config('services.groq.base_url');
    }

    /**
     * Analyze a completed session with all transcripts.
     */
    public function analyzeSession(int $sessionId): SessionAnalysis
    {
        $session = PracticeSession::with(['user1', 'user2', 'learningRequest.language'])
            ->findOrFail($sessionId);

        // Get all completed transcripts for this session
        $transcripts = SessionTranscript::where('practice_session_id', $sessionId)
            ->where('status', 'completed')
            ->orderBy('chunk_number')
            ->get();

        if ($transcripts->isEmpty()) {
            throw new \Exception("No completed transcripts found for session #{$sessionId}");
        }

        // Merge all transcripts
        $fullTranscript = $transcripts->pluck('transcript')->join("\n\n");
        $language = $transcripts->first()->language ?? 'en';

        // Create or get analysis record
        $analysis = SessionAnalysis::firstOrCreate(
            ['practice_session_id' => $sessionId],
            ['status' => 'pending']
        );

        $analysis->update([
            'status' => 'processing',
            'full_transcript' => $fullTranscript,
        ]);

        try {
            // Call GPT for analysis
            $result = $this->callGPT($fullTranscript, $language, $session);

            $analysis->update([
                'summary' => $result['summary'],
                'topics' => $result['topics'],
                'key_phrases' => $result['key_phrases'],
                'pronunciation_notes' => $result['pronunciation_notes'],
                'vocabulary_extracted' => $result['vocabulary'],
                'status' => 'completed',
            ]);

            // Create flashcards from extracted vocabulary
            $this->createFlashcards($session, $result['vocabulary'], $language);

            // Track vocabulary usage
            $this->trackVocabulary($session, $fullTranscript, $language);

            Log::info("Session analysis completed for session #{$sessionId}");

            return $analysis;
        } catch (\Exception $e) {
            Log::error("Session analysis failed for session #{$sessionId}: {$e->getMessage()}");

            $analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Call Groq Llama for session analysis.
     */
    private function callGPT(string $transcript, string $language, PracticeSession $session): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Groq API key not configured');
        }

        $languageName = $session->learningRequest?->language?->name ?? 'the target language';

        $systemPrompt = <<<PROMPT
You are a language learning assistant analyzing a practice session transcript.
The learner is practicing {$languageName}.

Analyze the conversation and provide:
1. A brief summary (2-3 sentences) of what was discussed
2. Main topics covered (list of 3-5 topics)
3. Key phrases the learner used or should learn (5-10 phrases with translations)
4. Pronunciation or grammar notes if any patterns are visible
5. Vocabulary to turn into flashcards (10-15 useful words/phrases)

You MUST respond with ONLY valid JSON, no other text:
{
    "summary": "string",
    "topics": ["topic1", "topic2"],
    "key_phrases": [{"phrase": "...", "translation": "...", "context": "..."}],
    "pronunciation_notes": "string or null",
    "vocabulary": [{"front": "target language", "back": "English translation", "context": "example usage"}]
}
PROMPT;

        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Here is the transcript to analyze:\n\n{$transcript}"],
                ],
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("Groq API error: {$error}");
        }

        $content = $response->json('choices.0.message.content');

        // Extract JSON from response (handle potential markdown code blocks)
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = $matches[1];
        }

        $data = json_decode(trim($content), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("Failed to parse Groq response as JSON: {$content}");
            throw new \Exception('Failed to parse Groq response as JSON');
        }

        return [
            'summary' => $data['summary'] ?? '',
            'topics' => $data['topics'] ?? [],
            'key_phrases' => $data['key_phrases'] ?? [],
            'pronunciation_notes' => $data['pronunciation_notes'] ?? null,
            'vocabulary' => $data['vocabulary'] ?? [],
        ];
    }

    /**
     * Create flashcards from extracted vocabulary.
     */
    private function createFlashcards(PracticeSession $session, array $vocabulary, string $language): void
    {
        foreach ($vocabulary as $item) {
            if (empty($item['front']) || empty($item['back'])) {
                continue;
            }

            // Create for both participants
            foreach ([$session->user1_id, $session->user2_id] as $userId) {
                if (!$userId) continue;

                // Check if this flashcard already exists
                $exists = Flashcard::where('user_id', $userId)
                    ->where('front', $item['front'])
                    ->where('language', $language)
                    ->exists();

                if (!$exists) {
                    Flashcard::create([
                        'user_id' => $userId,
                        'practice_session_id' => $session->id,
                        'front' => $item['front'],
                        'back' => $item['back'],
                        'language' => $language,
                        'context' => $item['context'] ?? null,
                        'next_review_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Track vocabulary usage from transcript.
     */
    private function trackVocabulary(PracticeSession $session, string $transcript, string $language): void
    {
        // Extract words (simple tokenization)
        $words = preg_split('/[\s\p{P}]+/u', mb_strtolower($transcript), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn($w) => mb_strlen($w) >= 2); // Skip single chars
        $words = array_unique($words);

        foreach ([$session->user1_id, $session->user2_id] as $userId) {
            if (!$userId) continue;

            foreach ($words as $word) {
                VocabularyEntry::recordWord($userId, $word, $language);
            }
        }
    }

    /**
     * Analyze a transcript directly (for dev testing without a session).
     */
    public function analyzeTranscript(string $transcript, string $language = 'en'): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Groq API key not configured');
        }

        $systemPrompt = <<<PROMPT
You are a language learning assistant analyzing a practice session transcript.
Analyze the conversation and provide:
1. A brief summary (2-3 sentences)
2. Main topics covered (3-5 topics)
3. Key phrases used or to learn (5-10 phrases with translations)
4. Pronunciation or grammar notes
5. Vocabulary for flashcards (10-15 useful words/phrases)

You MUST respond with ONLY valid JSON, no other text:
{
    "summary": "string",
    "topics": ["topic1", "topic2"],
    "key_phrases": [{"phrase": "...", "translation": "...", "context": "..."}],
    "pronunciation_notes": "string or null",
    "vocabulary": [{"front": "target language", "back": "English translation", "context": "example usage"}]
}
PROMPT;

        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Transcript (language: {$language}):\n\n{$transcript}"],
                ],
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("Groq API error: {$error}");
        }

        $content = $response->json('choices.0.message.content');

        // Extract JSON from response (handle potential markdown code blocks)
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = $matches[1];
        }

        return json_decode(trim($content), true) ?? [];
    }
}

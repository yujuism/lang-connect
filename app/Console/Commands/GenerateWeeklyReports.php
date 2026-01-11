<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WeeklyReport;
use App\Models\PracticeSession;
use App\Models\VocabularyEntry;
use App\Models\Flashcard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyReports extends Command
{
    protected $signature = 'reports:generate-weekly {--user= : Generate for specific user ID}';
    protected $description = 'Generate weekly learning progress reports for all active users';

    public function handle(): int
    {
        $this->info('Starting weekly report generation...');

        // Get users who had activity in the past week
        $query = User::query();

        if ($userId = $this->option('user')) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $generated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $stats = $this->getUserWeeklyStats($user);

            // Skip users with no activity
            if ($stats['sessions_count'] === 0 && $stats['new_vocabulary'] === 0 && $stats['flashcards_reviewed'] === 0) {
                $skipped++;
                continue;
            }

            try {
                $report = $this->generateReport($user, $stats);
                $generated++;
                $this->info("Generated report for {$user->name}");
            } catch (\Exception $e) {
                $this->error("Failed to generate report for {$user->name}: {$e->getMessage()}");
                Log::error("Weekly report generation failed for user {$user->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done! Generated {$generated} reports, skipped {$skipped} inactive users.");

        return Command::SUCCESS;
    }

    private function getUserWeeklyStats(User $user): array
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        // Sessions this week
        $sessions = PracticeSession::where(function ($q) use ($user) {
            $q->where('user1_id', $user->id)->orWhere('user2_id', $user->id);
        })
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$weekStart, $weekEnd])
            ->get();

        $totalMinutes = $sessions->sum('duration_minutes');

        // New vocabulary this week
        $newVocabulary = VocabularyEntry::where('user_id', $user->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->get();

        // Flashcard reviews this week
        $flashcardsReviewed = Flashcard::where('user_id', $user->id)
            ->whereBetween('last_reviewed_at', [$weekStart, $weekEnd])
            ->count();

        $flashcardsMastered = Flashcard::where('user_id', $user->id)
            ->where('mastery_level', '>=', 3)
            ->whereBetween('updated_at', [$weekStart, $weekEnd])
            ->count();

        // Languages practiced
        $languagesPracticed = $sessions->pluck('language_id')->unique()->count();

        // Get some vocabulary samples for the report
        $vocabularySamples = $newVocabulary->take(10)->pluck('word')->toArray();

        return [
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'sessions_count' => $sessions->count(),
            'total_minutes' => $totalMinutes,
            'languages_practiced' => $languagesPracticed,
            'new_vocabulary' => $newVocabulary->count(),
            'vocabulary_samples' => $vocabularySamples,
            'flashcards_reviewed' => $flashcardsReviewed,
            'flashcards_mastered' => $flashcardsMastered,
            'total_vocabulary' => VocabularyEntry::where('user_id', $user->id)->count(),
            'total_flashcards' => Flashcard::where('user_id', $user->id)->count(),
        ];
    }

    private function generateReport(User $user, array $stats): WeeklyReport
    {
        $prompt = $this->buildPrompt($user, $stats);
        $insights = $this->callGroqForInsights($prompt);

        // Map to database column names
        return WeeklyReport::create([
            'user_id' => $user->id,
            'week_start' => $stats['week_start'],
            'sessions_count' => $stats['sessions_count'],
            'practice_minutes' => $stats['total_minutes'],
            'words_learned' => $stats['new_vocabulary'],
            'flashcards_reviewed' => $stats['flashcards_reviewed'],
            'report_content' => $insights,
            'highlights' => [
                'sessions' => $stats['sessions_count'],
                'vocabulary' => $stats['vocabulary_samples'],
                'mastered' => $stats['flashcards_mastered'],
            ],
            'suggestions' => [
                'total_vocabulary' => $stats['total_vocabulary'],
                'total_flashcards' => $stats['total_flashcards'],
                'languages_practiced' => $stats['languages_practiced'],
            ],
        ]);
    }

    private function buildPrompt(User $user, array $stats): string
    {
        $vocabularyList = implode(', ', $stats['vocabulary_samples']);

        return <<<PROMPT
You are a helpful language learning coach. Generate a brief, encouraging weekly progress report for a language learner.

User: {$user->name}
Week: {$stats['week_start']} to {$stats['week_end']}

This Week's Activity:
- Practice sessions completed: {$stats['sessions_count']}
- Total practice time: {$stats['total_minutes']} minutes
- Languages practiced: {$stats['languages_practiced']}
- New vocabulary learned: {$stats['new_vocabulary']} words
- Sample new words: {$vocabularyList}
- Flashcards reviewed: {$stats['flashcards_reviewed']}
- Flashcards mastered: {$stats['flashcards_mastered']}

Overall Progress:
- Total vocabulary: {$stats['total_vocabulary']} words
- Total flashcards: {$stats['total_flashcards']}

Please provide:
1. A brief (2-3 sentences) personalized summary of their progress
2. One specific thing they did well this week
3. One actionable tip for next week based on their activity
4. An encouraging closing message

Keep the tone friendly and motivating. Be specific about their achievements where possible.
Response should be in plain text, no markdown.
PROMPT;
    }

    private function callGroqForInsights(string $prompt): string
    {
        $apiKey = config('services.groq.api_key');
        $baseUrl = config('services.groq.base_url');
        $model = config('services.groq.chat_model');

        if (!$apiKey) {
            Log::warning('Groq API key not configured, using placeholder insights');
            return 'Keep up the great work with your language learning! Regular practice is the key to success.';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a supportive language learning coach.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? 'Great progress this week!';
            }

            Log::error('Groq API error for weekly report: ' . $response->body());
            return 'Keep up the great work with your language learning!';

        } catch (\Exception $e) {
            Log::error('Groq API exception for weekly report: ' . $e->getMessage());
            return 'Keep up the great work with your language learning!';
        }
    }
}

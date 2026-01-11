<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
{
    protected $fillable = [
        'user_id',
        'practice_session_id',
        'front',
        'back',
        'language',
        'context',
        'mastery_level',
        'easiness_factor',
        'repetitions',
        'interval_days',
        'next_review_at',
        'last_reviewed_at',
    ];

    protected $casts = [
        'mastery_level' => 'integer',
        'easiness_factor' => 'float',
        'repetitions' => 'integer',
        'interval_days' => 'integer',
        'next_review_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class);
    }

    /**
     * Check if this card is due for review.
     */
    public function isDue(): bool
    {
        return $this->next_review_at === null || $this->next_review_at->isPast();
    }

    /**
     * Apply SM-2 algorithm to update card after review.
     * @param int $quality Response quality (0-5): 0-2 = fail, 3 = hard, 4 = good, 5 = easy
     */
    public function applyReview(int $quality): void
    {
        $quality = max(0, min(5, $quality));

        // If quality < 3, reset repetitions (failed recall)
        if ($quality < 3) {
            $this->repetitions = 0;
            $this->interval_days = 1;
        } else {
            // Successful recall
            if ($this->repetitions === 0) {
                $this->interval_days = 1;
            } elseif ($this->repetitions === 1) {
                $this->interval_days = 6;
            } else {
                $this->interval_days = (int) round($this->interval_days * $this->easiness_factor);
            }
            $this->repetitions++;
        }

        // Update easiness factor (minimum 1.3)
        $this->easiness_factor = max(1.3, $this->easiness_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02)));

        // Update mastery level based on repetitions
        $this->mastery_level = min(5, (int) floor($this->repetitions / 2));

        // Set next review date
        $this->next_review_at = now()->addDays($this->interval_days);
        $this->last_reviewed_at = now();
    }
}

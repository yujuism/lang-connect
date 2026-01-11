<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    protected $fillable = [
        'user_id',
        'week_start',
        'sessions_count',
        'practice_minutes',
        'words_learned',
        'flashcards_reviewed',
        'report_content',
        'highlights',
        'suggestions',
    ];

    protected $casts = [
        'week_start' => 'date',
        'sessions_count' => 'integer',
        'practice_minutes' => 'integer',
        'words_learned' => 'integer',
        'flashcards_reviewed' => 'integer',
        'highlights' => 'array',
        'suggestions' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionAnalysis extends Model
{
    protected $table = 'session_analyses';

    protected $fillable = [
        'practice_session_id',
        'full_transcript',
        'summary',
        'topics',
        'key_phrases',
        'pronunciation_notes',
        'vocabulary_extracted',
        'status',
        'error_message',
    ];

    protected $casts = [
        'topics' => 'array',
        'key_phrases' => 'array',
        'vocabulary_extracted' => 'array',
    ];

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}

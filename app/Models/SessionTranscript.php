<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionTranscript extends Model
{
    protected $fillable = [
        'practice_session_id',
        'chunk_number',
        'audio_path',
        'transcript',
        'language',
        'duration_seconds',
        'status',
        'error_message',
    ];

    protected $casts = [
        'chunk_number' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}

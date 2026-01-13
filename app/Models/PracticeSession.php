<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PracticeSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user1_id',
        'user2_id',
        'language_id',
        'topic',
        'session_type',
        'scheduled_at',
        'duration_minutes',
        'status',
        'started_at',
        'completed_at',
        'notes',
        'canvas_data',
        'pdf_path',
        'pdf_highlights',
        'pdf_drawings',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'canvas_data' => 'array',
        'pdf_highlights' => 'array',
        'pdf_drawings' => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(LearningRequest::class, 'request_id');
    }

    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SessionReview::class, 'session_id');
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(SessionAnalysis::class);
    }

    public function transcripts(): HasMany
    {
        return $this->hasMany(SessionTranscript::class)->orderBy('chunk_number');
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }
}

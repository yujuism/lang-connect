<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'reviewer_id',
        'reviewed_user_id',
        'overall_rating',
        'helpfulness_rating',
        'patience_rating',
        'clarity_rating',
        'engagement_rating',
        'comment',
        'is_public',
        'topics_rated_well',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'topics_rated_well' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class, 'session_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }
}

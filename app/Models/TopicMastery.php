<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicMastery extends Model
{
    protected $fillable = [
        'user_id',
        'language_id',
        'topic_name',
        'sessions_practiced',
        'mastery_percentage',
        'streak_days',
        'last_practiced',
    ];

    protected $casts = [
        'last_practiced' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}

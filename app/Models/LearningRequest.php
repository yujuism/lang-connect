<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'language_id',
        'topic_category',
        'topic_name',
        'specific_question',
        'keywords',
        'proficiency_level',
        'status',
        'matched_with_user_id',
        'matched_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'matched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function matchedWith(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_with_user_id');
    }

    public function matchedWithUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_with_user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class, 'request_id');
    }
}

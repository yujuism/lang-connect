<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularyEntry extends Model
{
    protected $fillable = [
        'user_id',
        'word',
        'language',
        'times_used',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'times_used' => 'integer',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a word usage (create or update).
     */
    public static function recordWord(int $userId, string $word, string $language): self
    {
        $word = mb_strtolower(trim($word));

        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'word' => $word,
                'language' => $language,
            ],
            [
                'times_used' => \DB::raw('times_used + 1'),
                'last_seen_at' => now(),
                'first_seen_at' => \DB::raw('COALESCE(first_seen_at, NOW())'),
            ]
        );
    }
}

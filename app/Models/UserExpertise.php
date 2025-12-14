<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserExpertise extends Model
{
    protected $fillable = [
        'user_id',
        'language_id',
        'topic_name',
        'times_helped',
        'average_rating',
        'specialization_level',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
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

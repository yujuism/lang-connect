<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLanguage extends Model
{
    protected $fillable = [
        'user_id',
        'language_id',
        'proficiency_level',
        'is_native',
        'is_learning',
        'can_help',
    ];

    protected $casts = [
        'is_native' => 'boolean',
        'is_learning' => 'boolean',
        'can_help' => 'boolean',
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

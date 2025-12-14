<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'flag_emoji',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function userLanguages(): HasMany
    {
        return $this->hasMany(UserLanguage::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_languages')
            ->withPivot('proficiency_level', 'is_native', 'is_learning')
            ->withTimestamps();
    }

    public function learningRequests(): HasMany
    {
        return $this->hasMany(LearningRequest::class);
    }

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class);
    }
}

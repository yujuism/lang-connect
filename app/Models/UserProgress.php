<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'contribution_hours',
        'level',
        'karma_points',
        'total_sessions',
        'members_helped',
        'sessions_received',
    ];

    protected $casts = [
        'contribution_hours' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getContributionBalanceAttribute()
    {
        return $this->total_sessions - $this->sessions_received;
    }
}

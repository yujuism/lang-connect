<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'caller_id',
        'receiver_id',
        'type',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['initiated', 'accepted']);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('caller_id', $userId)
              ->orWhere('receiver_id', $userId);
        });
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isVoice(): bool
    {
        return $this->type === 'voice';
    }

    public function getOtherParticipant(int $userId): ?User
    {
        if ($this->caller_id === $userId) {
            return $this->receiver;
        }
        return $this->caller;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Mark message as read
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    // Get conversation between two users
    public static function getConversation($user1Id, $user2Id, $limit = 50)
    {
        return self::where(function($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user1Id)->where('receiver_id', $user2Id);
        })->orWhere(function($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user2Id)->where('receiver_id', $user1Id);
        })->with(['sender', 'receiver'])
          ->orderBy('created_at', 'desc')
          ->limit($limit)
          ->get()
          ->reverse()
          ->values();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'location',
        'profile_photo',
        'timezone',
        'email_notifications_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_notifications_enabled' => 'boolean',
        ];
    }

    public function progress()
    {
        return $this->hasOne(UserProgress::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'user_languages')
            ->withPivot('proficiency_level', 'is_native', 'is_learning', 'can_help')
            ->withTimestamps();
    }

    public function userLanguages()
    {
        return $this->hasMany(UserLanguage::class);
    }

    public function learningRequests()
    {
        return $this->hasMany(LearningRequest::class);
    }

    public function sessionsAsUser1()
    {
        return $this->hasMany(PracticeSession::class, 'user1_id');
    }

    public function sessionsAsUser2()
    {
        return $this->hasMany(PracticeSession::class, 'user2_id');
    }

    public function allSessions()
    {
        return $this->sessionsAsUser1->merge($this->sessionsAsUser2);
    }

    public function reviewsGiven()
    {
        return $this->hasMany(SessionReview::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(SessionReview::class, 'reviewed_user_id');
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('unlocked_at');
    }

    public function topicMasteries()
    {
        return $this->hasMany(TopicMastery::class);
    }

    public function expertise()
    {
        return $this->hasMany(UserExpertise::class);
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Get unread message count
    public function getUnreadMessageCount()
    {
        return $this->messagesReceived()->where('is_read', false)->count();
    }

    // Get unread notification count
    public function getUnreadNotificationCount()
    {
        return $this->notifications()->where('is_read', false)->count();
    }
}

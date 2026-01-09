<?php

use Illuminate\Support\Facades\Broadcast;

// Private user channel for notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private user channel for incoming calls (global handler)
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private conversation channel between two users
Broadcast::channel('conversation.{userId1}.{userId2}', function ($user, $userId1, $userId2) {
    // User can only join if they are one of the participants
    return (int) $user->id === (int) $userId1 || (int) $user->id === (int) $userId2;
});

// Presence channel for online status
Broadcast::channel('online', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});

// Private session channel for collaborative canvas
Broadcast::channel('session.{sessionId}', function ($user, $sessionId) {
    $session = \App\Models\PracticeSession::find($sessionId);
    if (!$session) return false;
    return (int) $user->id === (int) $session->user1_id || (int) $user->id === (int) $session->user2_id;
});

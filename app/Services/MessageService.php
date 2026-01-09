<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use App\Models\Notification;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Message Service - Handles all messaging business logic
 *
 * Responsibilities:
 * - Message retrieval and organization
 * - Sending messages with notifications
 * - Conversation management
 * - Read status tracking
 */
class MessageService
{
    /**
     * Get all conversations for a user
     *
     * Returns list of users with conversation metadata
     *
     * @param User $user
     * @return Collection
     */
    public function getUserConversations(User $user): Collection
    {
        $userId = $user->id;

        // Get conversation metadata using raw SQL for performance
        $conversations = DB::table('messages')
            ->select(DB::raw("
                CASE
                    WHEN sender_id = {$userId} THEN receiver_id
                    ELSE sender_id
                END as user_id,
                MAX(created_at) as last_message_at,
                SUM(CASE WHEN receiver_id = {$userId} AND is_read = 0 THEN 1 ELSE 0 END) as unread_count
            "))
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->groupBy('user_id')
            ->orderBy('last_message_at', 'desc')
            ->get();

        // Load user details
        $conversationUsers = User::whereIn('id', $conversations->pluck('user_id'))
            ->get()
            ->keyBy('id');

        // Merge data
        return $conversations->map(function ($conv) use ($conversationUsers) {
            return (object) [
                'user' => $conversationUsers[$conv->user_id] ?? null,
                'last_message_at' => $conv->last_message_at,
                'unread_count' => $conv->unread_count,
            ];
        })->filter(fn($conv) => $conv->user !== null);
    }

    /**
     * Get conversation between two users
     *
     * @param User $user1
     * @param User $user2
     * @return Collection
     */
    public function getConversation(User $user1, User $user2): Collection
    {
        return Message::getConversation($user1->id, $user2->id);
    }

    /**
     * Mark messages as read
     *
     * Also marks the notification from this sender as read
     * and broadcasts read receipt via WebSocket
     *
     * @param User $sender
     * @param User $receiver
     * @return int Number of messages marked as read
     */
    public function markAsRead(User $sender, User $receiver): int
    {
        $now = now();

        // Get message IDs that will be marked as read
        $messageIds = Message::where('sender_id', $sender->id)
            ->where('receiver_id', $receiver->id)
            ->where('is_read', false)
            ->pluck('id')
            ->toArray();

        if (empty($messageIds)) {
            return 0;
        }

        // Mark messages as read
        Message::whereIn('id', $messageIds)
            ->update(['is_read' => true, 'read_at' => $now]);

        // Also mark notification from this sender as read
        Notification::where('user_id', $receiver->id)
            ->where('type', 'new_message')
            ->where('is_read', false)
            ->whereJsonContains('data->user_id', $sender->id)
            ->update(['is_read' => true, 'read_at' => $now]);

        // Broadcast read receipt
        broadcast(new MessagesRead(
            $receiver->id,
            $sender->id,
            $messageIds,
            $now->toISOString()
        ))->toOthers();

        return count($messageIds);
    }

    /**
     * Send a message from one user to another
     *
     * Also creates notification and broadcasts via WebSocket
     *
     * @param User $sender
     * @param User $receiver
     * @param string $messageText
     * @return Message
     */
    public function sendMessage(User $sender, User $receiver, string $messageText): Message
    {
        return DB::transaction(function () use ($sender, $receiver, $messageText) {
            // Create message
            $message = Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'message' => $messageText,
            ]);

            // Load relationships for broadcasting
            $message->load(['sender', 'receiver']);

            // Broadcast via WebSocket (to others, not sender)
            broadcast(new MessageSent($message))->toOthers();

            // Create notification for receiver
            $this->createMessageNotification($sender, $receiver, $message);

            return $message;
        });
    }

    /**
     * Create notification for new message
     *
     * Only creates ONE notification per sender until it's read.
     * This prevents notification spam when sender sends multiple messages.
     *
     * @param User $sender
     * @param User $receiver
     * @param Message $message
     * @return void
     */
    private function createMessageNotification(User $sender, User $receiver, Message $message): void
    {
        // Check if there's already an unread notification from this sender
        $existingNotification = Notification::where('user_id', $receiver->id)
            ->where('type', 'new_message')
            ->where('is_read', false)
            ->whereJsonContains('data->user_id', $sender->id)
            ->exists();

        // Only create notification if none exists
        if (!$existingNotification) {
            Notification::createNotification(
                $receiver->id,
                'new_message',
                'New message from ' . $sender->name,
                $sender->name . ' sent you a message',
                [
                    'user_id' => $sender->id,
                    'message_id' => $message->id
                ]
            );
        }
    }

    /**
     * Fetch new messages after a specific message ID
     *
     * Used for AJAX polling (though WebSocket is preferred)
     *
     * @param User $user1
     * @param User $user2
     * @param int $lastMessageId
     * @return Collection
     */
    public function fetchNewMessages(User $user1, User $user2, int $lastMessageId): Collection
    {
        $messages = Message::where(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->id)
                ->where('receiver_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user2->id)
                ->where('receiver_id', $user1->id);
        })
            ->where('id', '>', $lastMessageId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark new messages as read
        $this->markAsRead($user2, $user1);

        return $messages;
    }
}

# Smart Message Notifications

## Problem Solved

Previously, every message created a new notification, leading to notification spam:

```
Alice sends 5 messages to Bob
❌ BEFORE: 5 notifications created
✅ AFTER:  1 notification created
```

---

## How It Works Now

### Scenario 1: First Message from Alice to Bob

```
1. Alice sends: "Hey Bob!"
   ↓
2. MessageService checks:
   - Is there an unread notification from Alice to Bob?
   - Answer: No
   ↓
3. Creates notification:
   "New message from Alice"
   ↓
4. Bob sees: 🔔 (1)
```

### Scenario 2: Alice Sends More Messages

```
1. Alice sends: "Are you there?"
   ↓
2. MessageService checks:
   - Is there an unread notification from Alice to Bob?
   - Answer: YES (from message 1)
   ↓
3. Skips creating notification
   ↓
4. Bob still sees: 🔔 (1)  ← Same notification
```

### Scenario 3: Bob Reads Messages

```
1. Bob opens conversation with Alice
   ↓
2. MessageService.markAsRead(Alice, Bob)
   ↓
3. Marks all messages from Alice as read
   ↓
4. Marks notification from Alice as read
   ↓
5. Bob sees: 🔔 (0)  ← Notification cleared
```

### Scenario 4: Alice Sends Again (After Read)

```
1. Alice sends: "Did you see my message?"
   ↓
2. MessageService checks:
   - Is there an unread notification from Alice to Bob?
   - Answer: NO (Bob read the previous one)
   ↓
3. Creates NEW notification:
   "New message from Alice"
   ↓
4. Bob sees: 🔔 (1)  ← New notification
```

---

## Code Implementation

### Creating Notification (Smart Logic)

```php
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
```

### Marking as Read (Clear Notification)

```php
public function markAsRead(User $sender, User $receiver): int
{
    // Mark messages as read
    $count = Message::where('sender_id', $sender->id)
        ->where('receiver_id', $receiver->id)
        ->where('is_read', false)
        ->update(['is_read' => true, 'read_at' => now()]);

    // Also mark notification from this sender as read
    Notification::where('user_id', $receiver->id)
        ->where('type', 'new_message')
        ->where('is_read', false)
        ->whereJsonContains('data->user_id', $sender->id)
        ->update(['is_read' => true, 'read_at' => now()]);

    return $count;
}
```

---

## Real-World Example

### Timeline

```
10:00 AM - Alice: "Hey Bob, how are you?"
           📱 Notification created for Bob
           🔔 Bob sees: (1 unread)

10:01 AM - Alice: "I have a question about Laravel"
           📱 No notification (already exists)
           🔔 Bob sees: (1 unread) ← Same notification

10:02 AM - Alice: "Can you help me?"
           📱 No notification (already exists)
           🔔 Bob sees: (1 unread) ← Still same notification

10:05 AM - Bob opens chat with Alice
           ✅ All messages marked as read
           ✅ Notification marked as read
           🔔 Bob sees: (0 unread)

10:10 AM - Alice: "Thanks for the help!"
           📱 New notification created (previous was read)
           🔔 Bob sees: (1 unread)
```

---

## Benefits

### ✅ User Experience
- No notification spam
- Clean notification list
- One notification per conversation

### ✅ Performance
- Fewer database writes
- Faster notification queries
- Less noise in notification center

### ✅ Similar to Popular Apps
- **WhatsApp**: One notification per chat
- **Slack**: Groups messages from same sender
- **Telegram**: Smart notification bundling

---

## Database Queries

### Check for Existing Notification
```sql
SELECT EXISTS (
    SELECT 1 FROM notifications
    WHERE user_id = ?
      AND type = 'new_message'
      AND is_read = false
      AND JSON_EXTRACT(data, '$.user_id') = ?
)
```

### Clear Notification on Read
```sql
UPDATE notifications
SET is_read = true, read_at = NOW()
WHERE user_id = ?
  AND type = 'new_message'
  AND is_read = false
  AND JSON_EXTRACT(data, '$.user_id') = ?
```

---

## Testing

### Test Case 1: Multiple Messages, One Notification
```php
// Alice sends 3 messages
$this->actingAs($alice);
$this->post("/messages/{$bob->id}/send", ['message' => 'Message 1']);
$this->post("/messages/{$bob->id}/send", ['message' => 'Message 2']);
$this->post("/messages/{$bob->id}/send", ['message' => 'Message 3']);

// Bob should have only 1 notification
$this->assertEquals(1, $bob->notifications()->where('is_read', false)->count());
```

### Test Case 2: Notification Cleared on Read
```php
// Bob reads messages
$this->actingAs($bob);
$this->get("/messages/{$alice->id}");

// Notification should be marked as read
$this->assertEquals(0, $bob->notifications()->where('is_read', false)->count());
```

### Test Case 3: New Notification After Read
```php
// Bob reads messages
$this->actingAs($bob);
$this->get("/messages/{$alice->id}");

// Alice sends new message
$this->actingAs($alice);
$this->post("/messages/{$bob->id}/send", ['message' => 'New message']);

// Bob should have 1 new notification
$this->assertEquals(1, $bob->notifications()->where('is_read', false)->count());
```

---

## Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│  Alice sends message to Bob                             │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│  Check: Does Bob have unread notification from Alice?   │
└────────┬─────────────────────────┬──────────────────────┘
         │                         │
         │ NO                      │ YES
         │                         │
         ▼                         ▼
┌──────────────────────┐   ┌─────────────────────┐
│ Create notification  │   │ Skip notification   │
│ "New message from    │   │ (already exists)    │
│  Alice"              │   │                     │
└──────────────────────┘   └─────────────────────┘
         │                         │
         └──────────┬──────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────┐
│  Broadcast via WebSocket (always)                       │
│  Message appears in chat instantly                      │
└─────────────────────────────────────────────────────────┘
```

---

## Comparison with Other Messaging Apps

| App | Notification Strategy |
|-----|----------------------|
| **WhatsApp** | One notification per chat, updated with message count |
| **Slack** | Groups messages from same sender within 5 minutes |
| **Telegram** | One notification per chat until read |
| **Discord** | One notification per channel until opened |
| **LangConnect** | ✅ One notification per sender until read |

---

**Updated:** December 13, 2025
**Status:** ✅ Implemented

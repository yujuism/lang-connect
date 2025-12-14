# WebSocket Debugging Guide

## Step-by-Step Testing

### 1. Check Reverb Server is Running

In the terminal where Reverb is running, you should see output like:
```
Starting Reverb server on 0.0.0.0:8080
```

If you don't see this, start it:
```bash
php artisan reverb:start
```

### 2. Open Browser Console (Both Users)

**User A (Alice):**
1. Open chat with User B
2. Press F12 → Console tab
3. Look for these messages:

```
Echo available: true
Subscribing to channel: conversation.1.2
Current user ID: 1
Partner user ID: 2
WebSocket listener setup complete
✅ Successfully subscribed to channel: conversation.1.2
```

**User B (Marie):**
1. Open same chat with User A
2. Press F12 → Console tab
3. Should see same messages (with user IDs swapped)

### 3. Send a Test Message

**From User A:**
1. Type: "Test message"
2. Click Send
3. Watch console for BOTH users

**Expected Console Output:**

**User A (sender) console:**
```
Sending message...
Message sent successfully
```

**User B (receiver) console:**
```
📨 New message received via WebSocket: {
  id: 123,
  sender_id: 1,
  receiver_id: 2,
  message: "Test message",
  ...
}
```

**Reverb Terminal:**
```
Connection opened
Subscribed to conversation.1.2
Message broadcast on conversation.1.2
```

### 4. Common Issues & Solutions

#### Issue 1: "Echo available: false"
**Problem:** JavaScript not loaded
**Solution:**
```bash
# Hard refresh browser (clears cache)
# Mac: Cmd + Shift + R
# Windows: Ctrl + Shift + R
```

#### Issue 2: No subscription confirmation
**Problem:** WebSocket connection failed
**Check:**
- Is Reverb running? `ps aux | grep reverb`
- Check browser Network tab → WS (WebSocket) connections
- Look for connection to `ws://localhost:8080`

#### Issue 3: "Unauthorized" error
**Problem:** Channel authorization failed
**Check:**
- Are both users logged in?
- Check `routes/channels.php` authorization logic

#### Issue 4: Message sent but not received
**Problem:** Broadcasting failed
**Check Reverb terminal for errors**

#### Issue 5: No errors but no messages
**Problem:** Event name mismatch
**Check:**
- Event broadcasts as `.message.sent`
- Listener listens for `.message.sent` (with leading dot)

### 5. Manual Test via Tinker

Test broadcasting manually:

```bash
php artisan tinker
```

```php
$message = App\Models\Message::first();
broadcast(new App\Events\MessageSent($message));
```

Check Reverb terminal - should see broadcast activity.

### 6. Check Browser Network Tab

1. Open DevTools → Network tab
2. Filter by "WS" (WebSocket)
3. Should see connection to `ws://localhost:8080`
4. Click on it to see messages being sent/received

### 7. What Success Looks Like

**Reverb Terminal:**
```
[2025-12-13 22:00:00] Connection opened
[2025-12-13 22:00:01] Subscribed to conversation.1.2
[2025-12-13 22:00:05] Broadcasting: App\Events\MessageSent
[2025-12-13 22:00:05] Pushed to conversation.1.2
```

**Browser Console (Receiver):**
```
Echo available: true
Subscribing to channel: conversation.1.2
✅ Successfully subscribed to channel: conversation.1.2
📨 New message received via WebSocket: {...}
```

**Result:**
Message appears **instantly** without page refresh! ⚡

---

## Quick Checklist

- [ ] Reverb server running (`php artisan reverb:start`)
- [ ] Laravel server running (`./start-server.sh`)
- [ ] Assets built (`npm run build`)
- [ ] Hard refresh browser (Cmd+Shift+R)
- [ ] Console shows "Echo available: true"
- [ ] Console shows subscription confirmation
- [ ] Both users logged in
- [ ] Both users on same conversation page

If all checked but still not working, share the **exact console output** from both browsers!

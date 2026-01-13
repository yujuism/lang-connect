@extends('layout')

@section('title', 'Messages - LangConnect')

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="messages-container">
    <div class="messages-wrapper">
        <!-- Left Sidebar: Conversation List -->
        <div class="conversations-sidebar" id="conversations-sidebar">
            <div class="sidebar-header">
                <h5 class="fw-bold mb-0"><i class="bi bi-chat-fill me-2"></i>Messages</h5>
            </div>
            <div class="conversations-list" id="conversations-list">
                @forelse($conversations as $conversation)
                    <div class="conversation-item {{ $activeUser && $activeUser->id === $conversation->user->id ? 'active' : '' }} {{ $conversation->unread_count > 0 ? 'has-unread' : '' }}"
                         data-user-id="{{ $conversation->user->id }}"
                         data-user-name="{{ $conversation->user->name }}"
                         data-user-avatar="{{ $conversation->user->avatar_path }}"
                         data-last-message="{{ $conversation->last_message_at }}"
                         onclick="selectConversation({{ $conversation->user->id }})">
                        <div class="conversation-avatar">
                            @if($conversation->user->avatar_path)
                                <img src="{{ Storage::url($conversation->user->avatar_path) }}" alt="" class="rounded-circle">
                            @else
                                <div class="avatar-placeholder">{{ substr($conversation->user->name, 0, 1) }}</div>
                            @endif
                            <span class="online-dot" data-user-id="{{ $conversation->user->id }}"></span>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-name">{{ $conversation->user->name }}</div>
                            <div class="conversation-time">{{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans(null, true) }}</div>
                        </div>
                        @if($conversation->unread_count > 0)
                            <span class="unread-badge">{{ $conversation->unread_count > 9 ? '9+' : $conversation->unread_count }}</span>
                        @endif
                    </div>
                @empty
                    <div class="no-conversations" id="no-conversations">
                        <i class="bi bi-chat-dots"></i>
                        <p>No conversations yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Panel: Messages -->
        <div class="messages-panel" id="messages-panel">
            @if($activeUser)
                <!-- Chat Header -->
                <div class="chat-header" id="chat-header">
                    <button class="back-btn d-md-none" onclick="showSidebar()">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <div class="chat-user-info">
                        <div class="chat-avatar">
                            @if($activeUser->avatar_path)
                                <img src="{{ Storage::url($activeUser->avatar_path) }}" alt="" class="rounded-circle" id="active-user-avatar">
                            @else
                                <div class="avatar-placeholder" id="active-user-avatar-placeholder">{{ substr($activeUser->name, 0, 1) }}</div>
                            @endif
                            <span class="online-dot" id="active-online-dot" data-user-id="{{ $activeUser->id }}"></span>
                        </div>
                        <div>
                            <div class="chat-user-name" id="active-user-name">{{ $activeUser->name }}</div>
                            <div class="chat-user-status" id="active-user-status">Offline</div>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="action-btn" onclick="window.openCallWindow(activeUserId, 'voice')" title="Voice Call">
                            <i class="bi bi-telephone-fill"></i>
                        </button>
                        <button class="action-btn" onclick="window.openCallWindow(activeUserId, 'video')" title="Video Call">
                            <i class="bi bi-camera-video-fill"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="messages-area" id="messages-area">
                    <div id="messages-list">
                        @foreach($messages as $message)
                            @include('messages.partials.message', ['message' => $message])
                        @endforeach
                    </div>
                    <div id="typing-indicator" class="typing-indicator d-none">
                        <span class="typing-dots">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </span>
                        <span id="typing-user-name">{{ $activeUser->name }}</span> is typing...
                    </div>
                </div>

                <!-- Message Input -->
                <div class="message-input-area">
                    <form id="message-form" onsubmit="sendMessage(event)">
                        <input type="text" id="message-input" placeholder="Type a message..." autocomplete="off">
                        <button type="submit" class="send-btn">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            @else
                <!-- No Conversation Selected -->
                <div class="no-chat-selected" id="no-chat-selected">
                    <i class="bi bi-chat-dots"></i>
                    <h5>Select a conversation</h5>
                    <p>Choose from your existing conversations or start a new one</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.messages-container {
    height: calc(100vh - 76px);
    margin: 0;
    padding: 0;
    overflow: hidden;
}

.messages-wrapper {
    display: flex;
    height: 100%;
    max-width: 1400px;
    margin: 0 auto;
    background: var(--bg-primary);
    box-shadow: var(--shadow-lg);
}

/* Sidebar */
.conversations-sidebar {
    width: 340px;
    min-width: 340px;
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    background: var(--bg-primary);
}

.sidebar-header {
    padding: 1.25rem;
    border-bottom: 1px solid var(--border-color);
}

.conversations-list {
    flex: 1;
    overflow-y: auto;
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 0.875rem 1rem;
    cursor: pointer;
    transition: background 0.15s;
    border-bottom: 1px solid var(--border-color);
}

.conversation-item:hover {
    background: var(--bg-secondary);
}

.conversation-item.active {
    background: var(--bg-tertiary);
    border-left: 3px solid var(--primary-color);
}

.conversation-item.has-unread .conversation-name {
    font-weight: 700;
}

.conversation-avatar {
    position: relative;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.conversation-avatar img,
.conversation-avatar .avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    font-weight: 600;
    font-size: 1.25rem;
}

.online-dot {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #6c757d;
    border: 2px solid white;
}

.online-dot.online {
    background: #22c55e;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-name {
    font-weight: 500;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-time {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.unread-badge {
    background: var(--primary-color);
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 1rem;
    min-width: 20px;
    text-align: center;
    flex-shrink: 0;
}

.no-conversations {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.no-conversations i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

/* Messages Panel */
.messages-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    background: var(--bg-secondary);
}

.chat-header {
    display: flex;
    align-items: center;
    padding: 1rem 1.25rem;
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border-color);
}

.back-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--text-primary);
    margin-right: 0.75rem;
    padding: 0.25rem;
}

.chat-user-info {
    display: flex;
    align-items: center;
    flex: 1;
}

.chat-avatar {
    position: relative;
    margin-right: 0.75rem;
}

.chat-avatar img,
.chat-avatar .avatar-placeholder {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-user-name {
    font-weight: 600;
    color: var(--text-primary);
}

.chat-user-status {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.chat-user-status.online {
    color: #22c55e;
}

.chat-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s;
}

.action-btn:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

/* Messages Area */
.messages-area {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.25rem;
}

.typing-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
    padding: 0.5rem 0;
}

.typing-dots {
    display: flex;
    gap: 3px;
}

.typing-dots .dot {
    width: 6px;
    height: 6px;
    background: var(--primary-color);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-dots .dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dots .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
    30% { opacity: 1; transform: translateY(-4px); }
}

/* Message Input */
.message-input-area {
    padding: 1rem 1.25rem;
    background: var(--bg-primary);
    border-top: 1px solid var(--border-color);
}

.message-input-area form {
    display: flex;
    gap: 0.75rem;
}

#message-input {
    flex: 1;
    padding: 0.75rem 1.25rem;
    border: 1px solid var(--border-color);
    border-radius: 2rem;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.15s;
}

#message-input:focus {
    border-color: var(--primary-color);
}

.send-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: none;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.15s, background 0.15s;
}

.send-btn:hover {
    background: var(--primary-dark);
    transform: scale(1.05);
}

/* No Chat Selected */
.no-chat-selected {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
}

.no-chat-selected i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-chat-selected h5 {
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .conversations-sidebar {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 100%;
        z-index: 10;
        transition: transform 0.3s;
    }

    .conversations-sidebar.hidden {
        transform: translateX(-100%);
    }

    .messages-panel {
        width: 100%;
    }
}
</style>

@push('scripts')
<script>
// State
let activeUserId = {{ $activeUser?->id ?? 'null' }};
let lastMessageId = {{ $messages->last()?->id ?? 0 }};
const currentUserId = {{ Auth::id() }};
let activeChannel = null;
let typingTimeout = null;
let lastTypingTime = 0;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    setupGlobalMessageListener();
    if (activeUserId) {
        setupConversationChannel(activeUserId);
    }
    updateAllOnlineStatus();
});

// Scroll to bottom of messages
function scrollToBottom() {
    const area = document.getElementById('messages-area');
    if (area) area.scrollTop = area.scrollHeight;
}

// Select a conversation
async function selectConversation(userId) {
    if (userId === activeUserId) return;

    // Update URL without reload
    history.pushState({}, '', `/messages/${userId}`);

    // Update UI - remove active from all, add to clicked
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
        if (parseInt(item.dataset.userId) === userId) {
            item.classList.add('active');
            item.classList.remove('has-unread');
            const badge = item.querySelector('.unread-badge');
            if (badge) badge.remove();
        }
    });

    // Get user data from the conversation item
    const convItem = document.querySelector(`[data-user-id="${userId}"]`);
    const userData = {
        id: userId,
        name: convItem?.dataset.userName || 'User',
        avatar_path: convItem?.dataset.userAvatar || null
    };

    // Show loading state or just update header immediately
    activeUserId = userId;

    // Update header
    updateChatHeader(userData);

    // Load messages via fetch
    try {
        const response = await fetch(`/messages/${userId}/json`);
        const data = await response.json();

        lastMessageId = 0;

        // Clear and populate messages
        const messagesList = document.getElementById('messages-list');
        messagesList.innerHTML = '';

        data.messages.forEach(msg => {
            appendMessage(msg);
            lastMessageId = Math.max(lastMessageId, msg.id);
        });

        scrollToBottom();

        // Switch WebSocket channel
        setupConversationChannel(userId);

        // Hide sidebar on mobile
        if (window.innerWidth <= 768) {
            document.getElementById('conversations-sidebar').classList.add('hidden');
        }
    } catch (error) {
        console.error('Error loading conversation:', error);
    }
}

// Update chat header
function updateChatHeader(user) {
    // Update or create header elements
    let nameEl = document.getElementById('active-user-name');
    let typingNameEl = document.getElementById('typing-user-name');

    if (nameEl) nameEl.textContent = user.name;
    if (typingNameEl) typingNameEl.textContent = user.name;

    // Update avatar
    const avatarPlaceholder = document.getElementById('active-user-avatar-placeholder');
    const avatarImg = document.getElementById('active-user-avatar');

    if (user.avatar_path) {
        if (avatarPlaceholder) avatarPlaceholder.style.display = 'none';
        if (avatarImg) {
            avatarImg.src = `/storage/${user.avatar_path}`;
            avatarImg.style.display = 'block';
        }
    } else {
        if (avatarImg) avatarImg.style.display = 'none';
        if (avatarPlaceholder) {
            avatarPlaceholder.textContent = user.name.charAt(0);
            avatarPlaceholder.style.display = 'flex';
        }
    }

    // Update online dot data-user-id
    const dot = document.getElementById('active-online-dot');
    if (dot) dot.dataset.userId = user.id;

    updateOnlineStatus(user.id);
}

// Send message
async function sendMessage(e) {
    e.preventDefault();

    const input = document.getElementById('message-input');
    const message = input.value.trim();

    if (!message || !activeUserId) return;

    input.value = '';

    try {
        const formData = new FormData();
        formData.append('message', message);

        const response = await fetch(`/messages/${activeUserId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            appendMessage(data.message);
            lastMessageId = data.message.id;
            scrollToBottom();
            moveConversationToTop(activeUserId);
        }
    } catch (error) {
        console.error('Error sending message:', error);
        input.value = message;
    }
}

// Append message to list
function appendMessage(message) {
    const messagesList = document.getElementById('messages-list');
    if (!messagesList) return;

    // Prevent duplicates
    if (document.querySelector(`[data-message-id="${message.id}"]`)) return;

    const isOwn = message.sender_id === currentUserId;
    const senderName = message.sender?.name || (isOwn ? 'You' : 'User');

    const html = `
        <div class="mb-3 d-flex ${isOwn ? 'justify-content-end' : 'justify-content-start'}" data-message-id="${message.id}">
            <div style="max-width: 70%;">
                <div class="mb-1">
                    <span class="small fw-semibold" style="color: var(--text-secondary);">
                        ${isOwn ? 'You' : escapeHtml(senderName)}
                    </span>
                </div>
                <div class="p-3" style="background: ${isOwn ? 'var(--primary-color)' : 'var(--bg-primary)'};
                     color: ${isOwn ? 'white' : 'var(--text-primary)'};
                     border-radius: 1rem;
                     box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    ${escapeHtml(message.message)}
                </div>
                <div class="small text-secondary mt-1">
                    ${formatTime(message.created_at)}
                </div>
            </div>
        </div>
    `;

    messagesList.insertAdjacentHTML('beforeend', html);
}

// Move conversation to top of list (with animation)
function moveConversationToTop(userId) {
    const list = document.getElementById('conversations-list');
    const item = list.querySelector(`[data-user-id="${userId}"]`);

    if (item && list.firstChild !== item) {
        // Add animation class
        item.style.transition = 'transform 0.2s, opacity 0.2s';
        list.insertBefore(item, list.firstChild);
    }

    // Update time
    const timeEl = item?.querySelector('.conversation-time');
    if (timeEl) timeEl.textContent = 'now';
}

// ==================== WEBSOCKET REAL-TIME ====================

// Global listener for ALL incoming messages (to update sidebar)
function setupGlobalMessageListener() {
    if (typeof window.Echo === 'undefined') {
        setTimeout(setupGlobalMessageListener, 100);
        return;
    }

    // Listen on user's private channel for incoming messages
    window.Echo.private(`user.${currentUserId}`)
        .listen('.message.sent', (event) => {
            console.log('[WebSocket] Message received on user channel:', event);
            handleIncomingMessage(event);
        });
}

// Setup channel for active conversation (for typing indicators)
function setupConversationChannel(userId) {
    if (typeof window.Echo === 'undefined') {
        setTimeout(() => setupConversationChannel(userId), 100);
        return;
    }

    const userIds = [currentUserId, userId].sort((a, b) => a - b);
    const channelName = `conversation.${userIds[0]}.${userIds[1]}`;

    // Leave old channel
    if (activeChannel) {
        window.Echo.leave(activeChannel);
    }

    // Join new channel
    activeChannel = channelName;
    window.Echo.private(channelName)
        .listen('.message.sent', (event) => {
            // Only handle if from other user (not ourselves)
            if (event.sender_id !== currentUserId) {
                handleIncomingMessage(event);
            }
        })
        .listenForWhisper('typing', (e) => {
            if (e.userId !== currentUserId) {
                showTypingIndicator();
            }
        });
}

// Handle incoming message from WebSocket
function handleIncomingMessage(message) {
    const senderId = message.sender_id;

    // If this is the active conversation, append message
    if (senderId === activeUserId) {
        appendMessage(message);
        lastMessageId = message.id;
        scrollToBottom();
        hideTypingIndicator();

        // Mark as read
        fetch(`/messages/${senderId}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
    } else {
        // Update conversation list - move to top, show unread badge
        updateConversationInList(senderId, message);
    }
}

// Update conversation list when new message arrives
function updateConversationInList(userId, message) {
    const list = document.getElementById('conversations-list');
    if (!list) return;

    // Hide "no conversations" if visible
    const noConv = document.getElementById('no-conversations');
    if (noConv) noConv.style.display = 'none';

    let item = list.querySelector(`[data-user-id="${userId}"]`);

    if (item) {
        // Move to top
        list.insertBefore(item, list.firstChild);

        // Update time
        const timeEl = item.querySelector('.conversation-time');
        if (timeEl) timeEl.textContent = 'now';

        // Add unread indicator (bold + badge)
        if (userId !== activeUserId) {
            item.classList.add('has-unread');
            let badge = item.querySelector('.unread-badge');
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'unread-badge';
                badge.textContent = '1';
                item.appendChild(badge);
            } else {
                const count = parseInt(badge.textContent) || 0;
                badge.textContent = count >= 9 ? '9+' : count + 1;
            }
        }
    } else {
        // Create new conversation item for new sender
        const senderName = message.sender?.name || 'User';
        const newItem = document.createElement('div');
        newItem.className = 'conversation-item has-unread';
        newItem.dataset.userId = userId;
        newItem.dataset.userName = senderName;
        newItem.dataset.userAvatar = '';
        newItem.dataset.lastMessage = new Date().toISOString();
        newItem.onclick = () => selectConversation(userId);

        newItem.innerHTML = `
            <div class="conversation-avatar">
                <div class="avatar-placeholder">${senderName.charAt(0)}</div>
                <span class="online-dot" data-user-id="${userId}"></span>
            </div>
            <div class="conversation-info">
                <div class="conversation-name">${escapeHtml(senderName)}</div>
                <div class="conversation-time">now</div>
            </div>
            <span class="unread-badge">1</span>
        `;

        list.insertBefore(newItem, list.firstChild);
        updateOnlineStatus(userId);
    }
}

// ==================== TYPING INDICATORS ====================

function showTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) {
        indicator.classList.remove('d-none');
        scrollToBottom();
    }
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(hideTypingIndicator, 2000);
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) indicator.classList.add('d-none');
}

// Send typing whisper when user types
document.getElementById('message-input')?.addEventListener('input', function() {
    const now = Date.now();
    if (activeUserId && activeChannel && now - lastTypingTime > 500) {
        lastTypingTime = now;
        try {
            window.Echo.private(activeChannel).whisper('typing', { userId: currentUserId });
        } catch (e) {}
    }
});

// ==================== ONLINE STATUS ====================

function updateAllOnlineStatus() {
    document.querySelectorAll('.online-dot').forEach(dot => {
        const userId = parseInt(dot.dataset.userId);
        if (userId) updateOnlineStatus(userId);
    });
}

function updateOnlineStatus(userId) {
    const isOnline = window.isUserOnline && window.isUserOnline(userId);

    // Update all dots for this user
    document.querySelectorAll(`.online-dot[data-user-id="${userId}"]`).forEach(dot => {
        dot.classList.toggle('online', isOnline);
    });

    // Update active chat status text
    if (userId === activeUserId) {
        const statusEl = document.getElementById('active-user-status');
        if (statusEl) {
            statusEl.textContent = isOnline ? 'Online' : 'Offline';
            statusEl.classList.toggle('online', isOnline);
        }
    }
}

window.addEventListener('online-users-updated', updateAllOnlineStatus);
setTimeout(updateAllOnlineStatus, 500);

// ==================== MOBILE ====================

function showSidebar() {
    document.getElementById('conversations-sidebar').classList.remove('hidden');
}

// ==================== HELPERS ====================

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}
</script>
@endpush
@endsection

@extends('layout')

@section('title', 'Chat with ' . $user->name)

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="container my-4" style="max-width: 900px;">
    <!-- Header -->
    <div class="card shadow-sm mb-3" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-3">
            <div class="d-flex align-items-center">
                <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="position-relative me-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; font-size: 1.25rem; font-weight: bold;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <span id="online-status-dot" class="position-absolute bottom-0 end-0 rounded-circle border border-2 border-white"
                          style="width: 14px; height: 14px; background: #6c757d;"></span>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="color: var(--text-primary);">{{ $user->name }}</div>
                    <div class="small" id="online-status-text" style="color: #6c757d;">
                        Offline
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary rounded-circle p-2" id="voice-call-btn" title="Voice Call" onclick="window.openCallWindow({{ $user->id }}, 'voice')">
                        <i class="bi bi-telephone-fill"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-circle p-2" id="video-call-btn" title="Video Call" onclick="window.openCallWindow({{ $user->id }}, 'video')">
                        <i class="bi bi-camera-video-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <div class="card shadow-sm mb-3" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-4" id="message-container" style="height: 500px; overflow-y: auto;">
            <div id="messages-list">
                @foreach($messages as $message)
                    @include('messages.partials.message', ['message' => $message])
                @endforeach
            </div>
            <!-- Typing Indicator -->
            <div id="typing-indicator" class="d-none mb-2">
                <div class="d-flex align-items-center text-secondary small">
                    <span class="typing-dots me-2">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </span>
                    <span>{{ $user->name }} is typing...</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        .typing-dots .dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: var(--primary-color);
            border-radius: 50%;
            animation: typing 1.4s infinite;
            margin-right: 2px;
        }
        .typing-dots .dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots .dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
            30% { opacity: 1; transform: translateY(-4px); }
        }
    </style>

    <!-- Send Message Form -->
    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-3">
            <form action="{{ route('messages.send', $user) }}" method="POST" id="message-form">
                @csrf
                <div class="d-flex gap-2">
                    <input type="text"
                           name="message"
                           id="message-input"
                           class="form-control"
                           placeholder="Type your message..."
                           required
                           autofocus
                           style="border-radius: 2rem;">
                    <button type="submit" class="btn btn-primary" style="border-radius: 2rem; padding: 0.5rem 1.5rem;">
                        <i class="bi bi-send-fill"></i> Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let lastMessageId = {{ $messages->last()?->id ?? 0 }};
const userId = {{ $user->id }};
const currentUserId = {{ Auth::id() }};

// Online status functions
function updateOnlineStatus() {
    const isOnline = window.isUserOnline && window.isUserOnline(userId);
    const dot = document.getElementById('online-status-dot');
    const text = document.getElementById('online-status-text');

    if (isOnline) {
        dot.style.background = '#22c55e';
        text.textContent = 'Online';
        text.style.color = '#22c55e';
    } else {
        dot.style.background = '#6c757d';
        text.textContent = 'Offline';
        text.style.color = '#6c757d';
    }
}

// Listen for online status changes
window.addEventListener('online-users-updated', updateOnlineStatus);

// Initial check after Echo is ready
setTimeout(updateOnlineStatus, 500);

// Scroll to bottom on load
function scrollToBottom() {
    const container = document.getElementById('message-container');
    container.scrollTop = container.scrollHeight;
}
scrollToBottom();

// Submit message via AJAX
let isSending = false; // Prevent duplicate sends
document.getElementById('message-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    e.stopPropagation(); // Stop event from bubbling

    if (isSending) return;

    const input = document.getElementById('message-input');
    const message = input.value.trim();

    if (!message) return;

    isSending = true;

    const formData = new FormData();
    formData.append('message', message);

    try {
        const response = await fetch('{{ route("messages.send", $user) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        if (!response.ok) {
            alert('Error sending message. Please try again.');
            return;
        }

        const data = await response.json();

        if (data.success) {
            // Add message to list immediately for sender
            // (WebSocket toOthers() won't send it back to us)
            appendMessage(data.message);
            input.value = '';
            lastMessageId = data.message.id;
            scrollToBottom();
        } else {
            alert('Failed to send message. Please try again.');
        }
    } catch (error) {
        alert('Error sending message. Please try again.');
    } finally {
        isSending = false; // Reset flag
    }
});

// WebSocket: Listen for new messages and typing events
let channel = null;
let typingTimeout = null;

function setupWebSocket() {
    if (typeof window.Echo === 'undefined') {
        setTimeout(setupWebSocket, 100);
        return;
    }

    const userIds = [currentUserId, userId].sort((a, b) => a - b);
    const channelName = `conversation.${userIds[0]}.${userIds[1]}`;

    channel = window.Echo.private(channelName);

    channel.listen('.message.sent', (event) => {
            appendMessage(event);
            lastMessageId = event.id;
            hideTypingIndicator();
            scrollToBottom();
            // Mark as read if we're the receiver
            if (event.receiver_id === currentUserId) {
                markMessagesAsRead();
            }
        })
        .listen('.messages.read', (event) => {
            // Update read status for messages that were read
            if (event.reader_id === userId) {
                updateReadStatus(event.message_ids);
            }
        })
        .listenForWhisper('typing', (e) => {
            if (e.userId !== currentUserId) {
                showTypingIndicator();
            }
        });
}

// Update read status checkmarks
function updateReadStatus(messageIds) {
    messageIds.forEach(id => {
        const msgEl = document.querySelector(`[data-message-id="${id}"] .read-status`);
        if (msgEl) {
            msgEl.style.color = '#22c55e';
            msgEl.innerHTML = '<i class="bi bi-check2-all"></i>';
            msgEl.dataset.read = 'true';
        }
    });
}

// Mark messages as read via API
function markMessagesAsRead() {
    fetch('{{ route("messages.mark-read", $user) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    });
}

// Typing indicator functions
function showTypingIndicator() {
    document.getElementById('typing-indicator').classList.remove('d-none');
    scrollToBottom();
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(hideTypingIndicator, 2000);
}

function hideTypingIndicator() {
    document.getElementById('typing-indicator').classList.add('d-none');
}

// Send typing event when user types
let lastTypingTime = 0;
document.getElementById('message-input').addEventListener('input', function() {
    const now = Date.now();
    if (channel && now - lastTypingTime > 500) {
        lastTypingTime = now;
        channel.whisper('typing', { userId: currentUserId });
    }
});

// Start trying to setup WebSocket
setupWebSocket();

function appendMessage(message) {
    const messagesList = document.getElementById('messages-list');

    // Prevent duplicates
    if (document.querySelector(`[data-message-id="${message.id}"]`)) return;

    const isOwnMessage = message.sender_id === currentUserId;
    const isRead = message.is_read || false;
    const readStatusHtml = isOwnMessage ? `
        <span class="read-status" data-read="${isRead}" style="color: ${isRead ? '#22c55e' : 'inherit'};">
            <i class="bi bi-check2${isRead ? '-all' : ''}"></i>
        </span>
    ` : '';

    const messageHtml = `
        <div class="mb-3 d-flex ${isOwnMessage ? 'justify-content-end' : 'justify-content-start'}" data-message-id="${message.id}">
            <div style="max-width: 70%;">
                <div class="mb-1">
                    <span class="small fw-semibold" style="color: var(--text-secondary);">
                        ${isOwnMessage ? 'You' : message.sender.name}
                    </span>
                </div>
                <div class="p-3" style="background: ${isOwnMessage ? 'var(--primary-color)' : 'var(--bg-secondary)'};
                     color: ${isOwnMessage ? 'white' : 'var(--text-primary)'};
                     border-radius: 1rem;">
                    ${escapeHtml(message.message)}
                </div>
                <div class="small text-secondary mt-1 d-flex align-items-center gap-1">
                    ${formatTime(message.created_at)}
                    ${readStatusHtml}
                </div>
            </div>
        </div>
    `;

    messagesList.insertAdjacentHTML('beforeend', messageHtml);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

// Calls are handled via popup window - see layout.blade.php for global handler
</script>
@endpush

@endsection

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
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                     style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; font-size: 1.25rem; font-weight: bold;">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <div class="fw-bold" style="color: var(--text-primary);">{{ $user->name }}</div>
                    <div class="small text-secondary">
                        <a href="{{ route('profile.show', $user) }}" class="text-decoration-none" style="color: var(--primary-color);">
                            View Profile
                        </a>
                    </div>
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
        </div>
    </div>

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

    if (isSending) {
        console.log('Already sending, ignoring duplicate request');
        return;
    }

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
            const errorText = await response.text();
            console.error('Server error:', errorText);
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
            console.error('Message send failed:', data);
            alert('Failed to send message. Please try again.');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Error sending message: ' + error.message);
    } finally {
        isSending = false; // Reset flag
    }
});

// WebSocket: Listen for new messages in real-time
function setupWebSocket() {
    if (typeof window.Echo === 'undefined') {
        console.log('Echo not ready yet, waiting...');
        setTimeout(setupWebSocket, 100); // Try again in 100ms
        return;
    }

    console.log('✅ Echo is ready! Setting up WebSocket listener...');

    const userIds = [currentUserId, userId].sort((a, b) => a - b);
    const channelName = `conversation.${userIds[0]}.${userIds[1]}`;

    console.log('Subscribing to channel:', channelName);
    console.log('Current user ID:', currentUserId);
    console.log('Partner user ID:', userId);

    window.Echo.private(channelName)
        .subscribed(() => {
            console.log('✅ Successfully subscribed to channel:', channelName);
        })
        .listen('.message.sent', (event) => {
            console.log('📨 New message received via WebSocket:', event);
            appendMessage(event);
            lastMessageId = event.id;
            scrollToBottom();
        })
        .error((error) => {
            console.error('❌ Echo error:', error);
        });

    console.log('WebSocket listener setup complete');
}

// Start trying to setup WebSocket
setupWebSocket();

function appendMessage(message) {
    const messagesList = document.getElementById('messages-list');

    // Check if message already exists (prevent duplicates)
    if (document.querySelector(`[data-message-id="${message.id}"]`)) {
        console.log('Message already displayed, skipping:', message.id);
        return;
    }

    const isOwnMessage = message.sender_id === currentUserId;

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
                <div class="small text-secondary mt-1">
                    ${formatTime(message.created_at)}
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
</script>
@endpush

@endsection

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
                    <button type="button" class="btn btn-outline-primary rounded-circle p-2" id="voice-call-btn" title="Voice Call" onclick="initiateCall('voice')">
                        <i class="bi bi-telephone-fill"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-circle p-2" id="video-call-btn" title="Video Call" onclick="initiateCall('video')">
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

<!-- Incoming Call Modal -->
<div id="incoming-call-modal" class="d-none position-fixed top-0 start-0 w-100 h-100" style="z-index: 9999; background: rgba(0,0,0,0.8);">
    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-white">
        <div class="rounded-circle d-flex align-items-center justify-content-center mb-4"
             style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); font-size: 3rem; font-weight: bold;">
            {{ substr($user->name, 0, 1) }}
        </div>
        <h3 class="mb-2">{{ $user->name }}</h3>
        <p class="mb-4" id="incoming-call-type">Incoming video call...</p>
        <div class="d-flex gap-4">
            <button type="button" class="btn btn-danger rounded-circle p-4" onclick="rejectIncomingCall()" title="Decline">
                <i class="bi bi-telephone-x-fill fs-3"></i>
            </button>
            <button type="button" class="btn btn-success rounded-circle p-4" onclick="acceptIncomingCall()" title="Accept">
                <i class="bi bi-telephone-fill fs-3"></i>
            </button>
        </div>
    </div>
</div>

<!-- Active Call Interface -->
<div id="call-interface" class="d-none position-fixed top-0 start-0 w-100 h-100" style="z-index: 9998; background: #1a1a2e;">
    <!-- Remote Video (full screen) -->
    <video id="remote-video" autoplay playsinline class="w-100 h-100" style="object-fit: cover; background: #16213e;"></video>

    <!-- Call Info (when no video) -->
    <div id="call-info" class="position-absolute top-50 start-50 translate-middle text-center text-white">
        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
             style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); font-size: 2.5rem; font-weight: bold;">
            {{ substr($user->name, 0, 1) }}
        </div>
        <h4>{{ $user->name }}</h4>
        <p id="call-status" class="text-secondary">Connecting...</p>
        <p id="call-timer" class="d-none">00:00</p>
    </div>

    <!-- Local Video (small, bottom right) -->
    <div class="position-absolute" style="bottom: 100px; right: 20px; width: 150px; height: 200px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
        <video id="local-video" autoplay playsinline muted class="w-100 h-100" style="object-fit: cover; background: #0f3460; transform: scaleX(-1);"></video>
    </div>

    <!-- Call Controls -->
    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4">
        <div class="d-flex gap-3 p-3 rounded-pill" style="background: rgba(0,0,0,0.6);">
            <button type="button" class="btn btn-secondary rounded-circle p-3" id="toggle-audio-btn" onclick="toggleAudio()" title="Mute/Unmute">
                <i class="bi bi-mic-fill fs-5"></i>
            </button>
            <button type="button" class="btn btn-secondary rounded-circle p-3" id="toggle-video-btn" onclick="toggleVideo()" title="Toggle Video">
                <i class="bi bi-camera-video-fill fs-5"></i>
            </button>
            <button type="button" class="btn btn-danger rounded-circle p-3" onclick="endCall()" title="End Call">
                <i class="bi bi-telephone-x-fill fs-5"></i>
            </button>
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

    console.log('Subscribing to channel:', channelName);

    channel = window.Echo.private(channelName);

    // Debug: Log ALL events on the underlying socket
    channel.subscription.bind_global((eventName, data) => {
        console.log('RAW EVENT received:', eventName, data);
    });

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

// ==================== WebRTC Call Handling ====================
let currentCall = null;
let peerConnection = null;
let localStream = null;
let remoteStream = null;
let callTimerInterval = null;
let callStartTime = null;
let isAudioMuted = false;
let isVideoEnabled = true;
let pendingIceCandidates = []; // Queue for ICE candidates that arrive before remote description
let pendingOffer = null; // Queue for offer that arrives before peer connection is ready

const rtcConfig = {
    iceServers: [
        { urls: 'stun:192.168.1.10:3478' },
        {
            urls: 'turn:192.168.1.10:3478',
            username: 'user',
            credential: 'pass'
        }
    ],
    sdpSemantics: 'unified-plan'
};

// Clean SDP - remove ALL a=ssrc lines (Chrome HTTP compatibility issue)
function cleanSdp(sdp) {
    if (!sdp) return sdp;

    // Count lines being removed
    const ssrcLines = sdp.match(/^a=ssrc[^\r\n]*/gm) || [];
    console.log('cleanSdp: Removing', ssrcLines.length, 'a=ssrc/a=ssrc-group lines');

    // Remove ALL a=ssrc-group and a=ssrc lines - Chrome on HTTP can't parse them
    let cleaned = sdp.replace(/^a=ssrc-group:[^\r\n]*[\r\n]*/gm, '');
    cleaned = cleaned.replace(/^a=ssrc:[^\r\n]*[\r\n]*/gm, '');

    return cleaned;
}

// Initiate a call (voice or video)
async function initiateCall(type) {
    try {
        // Get local media stream
        const constraints = {
            audio: true,
            video: type === 'video'
        };
        localStream = await navigator.mediaDevices.getUserMedia(constraints);
        document.getElementById('local-video').srcObject = localStream;

        // If voice call, hide video initially
        if (type === 'voice') {
            isVideoEnabled = false;
            document.getElementById('toggle-video-btn').classList.add('btn-danger');
            document.getElementById('toggle-video-btn').classList.remove('btn-secondary');
            document.getElementById('toggle-video-btn').innerHTML = '<i class="bi bi-camera-video-off-fill fs-5"></i>';
        }

        // Create call on server
        const response = await fetch(`/call/${userId}/initiate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ type })
        });

        const data = await response.json();
        if (data.success) {
            currentCall = data.call;
            currentCall.isCaller = true; // Mark as caller
            showCallInterface();
            document.getElementById('call-status').textContent = 'Calling...';

            // Setup WebRTC (but don't send offer yet - wait for acceptance)
            await setupPeerConnection();
        }
    } catch (error) {
        console.error('Error initiating call:', error);
        alert('Could not start call. Please check camera/microphone permissions.');
        cleanupCall();
    }
}

// Setup WebRTC peer connection
async function setupPeerConnection() {
    peerConnection = new RTCPeerConnection(rtcConfig);

    // Add local tracks to connection
    localStream.getTracks().forEach(track => {
        peerConnection.addTrack(track, localStream);
    });

    // Handle incoming tracks (remote video/audio)
    peerConnection.ontrack = (event) => {
        console.log('Received remote track:', event.track.kind);
        const remoteVideo = document.getElementById('remote-video');
        if (event.streams && event.streams[0]) {
            console.log('Setting remote stream with', event.streams[0].getTracks().length, 'tracks');
            remoteVideo.srcObject = event.streams[0];
            remoteStream = event.streams[0];

            // If we received a video track, hide the call-info overlay to show video
            if (event.track.kind === 'video') {
                console.log('Video track received - hiding call-info overlay');
                document.getElementById('call-info').classList.add('d-none');

                // Listen for track ending (other party disabled video)
                event.track.onended = () => {
                    console.log('Remote video track ended');
                    document.getElementById('call-info').classList.remove('d-none');
                };

                // Also listen for mute (video disabled but track still exists)
                event.track.onmute = () => {
                    console.log('Remote video track muted');
                    document.getElementById('call-info').classList.remove('d-none');
                };

                event.track.onunmute = () => {
                    console.log('Remote video track unmuted');
                    document.getElementById('call-info').classList.add('d-none');
                };
            }
        }
    };

    // Handle ICE candidates
    peerConnection.onicecandidate = async (event) => {
        if (event.candidate) {
            // Parse and log candidate info
            const candidateStr = event.candidate.candidate;
            const parts = candidateStr.split(' ');
            const candidateType = parts.find((p, i) => parts[i-1] === 'typ') || 'unknown';
            const ip = parts[4] || 'unknown';
            console.log(`Local ICE candidate: type=${candidateType}, ip=${ip}`);

            await sendSignal('ice-candidate', {
                candidate: event.candidate.candidate,
                sdpMid: event.candidate.sdpMid,
                sdpMLineIndex: event.candidate.sdpMLineIndex
            });
        } else {
            console.log('ICE gathering complete (null candidate)');
        }
    };

    // Connection state changes
    peerConnection.onconnectionstatechange = () => {
        const state = peerConnection.connectionState;
        console.log('Connection state changed:', state);
        document.getElementById('call-status').textContent = state.charAt(0).toUpperCase() + state.slice(1) + '...';

        if (state === 'connected') {
            document.getElementById('call-status').textContent = 'Connected';
            startCallTimer();
            // Hide call info if video is active
            if (remoteStream && remoteStream.getVideoTracks().length > 0) {
                document.getElementById('call-info').classList.add('d-none');
            }
        } else if (state === 'failed') {
            console.error('WebRTC connection failed');
            endCall();
        }
        // Don't auto-end on 'disconnected' - it might recover
    };

    // ICE connection state changes (more granular)
    peerConnection.oniceconnectionstatechange = () => {
        console.log('ICE connection state:', peerConnection.iceConnectionState);
    };

    // ICE gathering state
    peerConnection.onicegatheringstatechange = () => {
        console.log('ICE gathering state:', peerConnection.iceGatheringState);
    };

    // Signaling state
    peerConnection.onsignalingstatechange = () => {
        console.log('Signaling state:', peerConnection.signalingState);
    };

    // Handle renegotiation needed (when tracks are added/removed mid-call)
    peerConnection.onnegotiationneeded = async () => {
        console.log('Negotiation needed - signalingState:', peerConnection.signalingState, 'connectionState:', peerConnection.connectionState);
    };
}

// Renegotiate connection (for adding/removing tracks mid-call)
async function renegotiate() {
    if (!peerConnection) {
        console.log('No peer connection for renegotiation');
        return;
    }
    console.log('Renegotiating - signalingState:', peerConnection.signalingState);
    try {
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        await sendSignal('offer', { sdp: offer.sdp, type: offer.type });
        console.log('Renegotiation offer sent');
    } catch (error) {
        console.error('Renegotiation failed:', error);
    }
}

// Send WebRTC signal via server
async function sendSignal(signalType, signalData) {
    if (!currentCall) return;
    console.log('Sending signal:', signalType, 'for call:', currentCall.id);
    const headers = {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };
    // Add socket ID so toOthers() works correctly
    if (window.Echo && window.Echo.socketId()) {
        headers['X-Socket-ID'] = window.Echo.socketId();
    }
    try {
        const response = await fetch(`/call/${currentCall.id}/signal`, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ signal_type: signalType, signal_data: signalData })
        });
        const result = await response.json();
        console.log('Signal sent response:', result);
    } catch (error) {
        console.error('Error sending signal:', error);
    }
}

// Handle incoming call
function handleIncomingCall(callData) {
    if (currentCall) return; // Already in a call

    // Map call_id to id for consistency
    currentCall = {
        id: callData.call_id,
        caller_id: callData.caller_id,
        receiver_id: callData.receiver_id,
        type: callData.type,
        caller: callData.caller
    };
    const callTypeText = callData.type === 'video' ? 'Incoming video call...' : 'Incoming voice call...';
    document.getElementById('incoming-call-type').textContent = callTypeText;
    document.getElementById('incoming-call-modal').classList.remove('d-none');
}

// Accept incoming call
async function acceptIncomingCall() {
    try {
        document.getElementById('incoming-call-modal').classList.add('d-none');

        // Get local media
        const constraints = {
            audio: true,
            video: currentCall.type === 'video'
        };
        localStream = await navigator.mediaDevices.getUserMedia(constraints);
        document.getElementById('local-video').srcObject = localStream;

        if (currentCall.type === 'voice') {
            isVideoEnabled = false;
            document.getElementById('toggle-video-btn').classList.add('btn-danger');
            document.getElementById('toggle-video-btn').classList.remove('btn-secondary');
            document.getElementById('toggle-video-btn').innerHTML = '<i class="bi bi-camera-video-off-fill fs-5"></i>';
        }

        // Accept on server
        const acceptHeaders = {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        };
        if (window.Echo && window.Echo.socketId()) {
            acceptHeaders['X-Socket-ID'] = window.Echo.socketId();
        }
        await fetch(`/call/${currentCall.id}/accept`, {
            method: 'POST',
            headers: acceptHeaders
        });

        showCallInterface();
        document.getElementById('call-status').textContent = 'Connecting...';
        await setupPeerConnection();

        // Check if offer arrived before peer connection was ready
        if (pendingOffer) {
            console.log('Processing pending offer');
            try {
                const cleanedSdp = cleanSdp(pendingOffer.sdp);
                await peerConnection.setRemoteDescription({
                    sdp: cleanedSdp,
                    type: pendingOffer.type
                });
                await applyPendingIceCandidates();
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                await sendSignal('answer', { sdp: answer.sdp, type: answer.type });
            } catch (sdpError) {
                console.error('Pending offer SDP Error:', sdpError);
                console.log('Problematic pending SDP:', pendingOffer.sdp);
            }
            pendingOffer = null;
        } else {
            // Send ready signal to caller so they can send the offer
            await sendSignal('ready', {});
        }
    } catch (error) {
        console.error('Error accepting call:', error);
        alert('Could not accept call. Please check camera/microphone permissions.');
        cleanupCall();
    }
}

// Reject incoming call
async function rejectIncomingCall() {
    if (!currentCall) return;
    await fetch(`/call/${currentCall.id}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    });
    document.getElementById('incoming-call-modal').classList.add('d-none');
    currentCall = null;
}

// End current call
async function endCall() {
    if (currentCall) {
        await fetch(`/call/${currentCall.id}/end`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
    }
    cleanupCall();
}

// Cleanup call resources
function cleanupCall() {
    if (peerConnection) {
        peerConnection.close();
        peerConnection = null;
    }
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
    }
    remoteStream = null;
    currentCall = null;
    pendingIceCandidates = [];
    pendingOffer = null;
    if (callTimerInterval) {
        clearInterval(callTimerInterval);
        callTimerInterval = null;
    }
    callStartTime = null;

    // Reset UI
    document.getElementById('call-interface').classList.add('d-none');
    document.getElementById('incoming-call-modal').classList.add('d-none');
    document.getElementById('local-video').srcObject = null;
    document.getElementById('remote-video').srcObject = null;
    document.getElementById('call-info').classList.remove('d-none');
    document.getElementById('call-timer').classList.add('d-none');
    document.getElementById('call-status').textContent = 'Connecting...';

    // Reset button states
    isAudioMuted = false;
    isVideoEnabled = true;
    document.getElementById('toggle-audio-btn').classList.remove('btn-danger');
    document.getElementById('toggle-audio-btn').classList.add('btn-secondary');
    document.getElementById('toggle-audio-btn').innerHTML = '<i class="bi bi-mic-fill fs-5"></i>';
    document.getElementById('toggle-video-btn').classList.remove('btn-danger');
    document.getElementById('toggle-video-btn').classList.add('btn-secondary');
    document.getElementById('toggle-video-btn').innerHTML = '<i class="bi bi-camera-video-fill fs-5"></i>';
}

// Show call interface
function showCallInterface() {
    document.getElementById('call-interface').classList.remove('d-none');
}

// Toggle audio mute
function toggleAudio() {
    if (!localStream) return;
    const audioTrack = localStream.getAudioTracks()[0];
    if (audioTrack) {
        audioTrack.enabled = !audioTrack.enabled;
        isAudioMuted = !audioTrack.enabled;
        const btn = document.getElementById('toggle-audio-btn');
        if (isAudioMuted) {
            btn.classList.add('btn-danger');
            btn.classList.remove('btn-secondary');
            btn.innerHTML = '<i class="bi bi-mic-mute-fill fs-5"></i>';
        } else {
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-secondary');
            btn.innerHTML = '<i class="bi bi-mic-fill fs-5"></i>';
        }
    }
}

// Toggle video
async function toggleVideo() {
    const btn = document.getElementById('toggle-video-btn');
    const videoTrack = localStream?.getVideoTracks()[0];

    if (isVideoEnabled && videoTrack) {
        // Disable video
        videoTrack.enabled = false;
        isVideoEnabled = false;
        btn.classList.add('btn-danger');
        btn.classList.remove('btn-secondary');
        btn.innerHTML = '<i class="bi bi-camera-video-off-fill fs-5"></i>';
    } else {
        // Enable or add video
        try {
            if (videoTrack) {
                videoTrack.enabled = true;
            } else {
                // Need to get video stream and add to connection
                console.log('Adding video track to call...');
                const videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
                const newVideoTrack = videoStream.getVideoTracks()[0];
                localStream.addTrack(newVideoTrack);

                // Add track to peer connection and renegotiate
                if (peerConnection) {
                    peerConnection.addTrack(newVideoTrack, localStream);
                    console.log('Video track added, starting renegotiation...');
                    // Renegotiate to inform remote peer about new track
                    await renegotiate();
                }
                document.getElementById('local-video').srcObject = localStream;
            }
            isVideoEnabled = true;
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-secondary');
            btn.innerHTML = '<i class="bi bi-camera-video-fill fs-5"></i>';
        } catch (error) {
            console.error('Could not enable video:', error);
            alert('Could not enable video. Please check camera permissions.');
        }
    }
}

// Start call timer
function startCallTimer() {
    callStartTime = Date.now();
    document.getElementById('call-timer').classList.remove('d-none');
    document.getElementById('call-status').classList.add('d-none');

    callTimerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - callStartTime) / 1000);
        const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        document.getElementById('call-timer').textContent = `${minutes}:${seconds}`;
    }, 1000);
}

// Apply any pending ICE candidates after remote description is set
async function applyPendingIceCandidates() {
    if (!peerConnection || !peerConnection.remoteDescription) return;

    console.log(`Applying ${pendingIceCandidates.length} pending ICE candidates`);
    for (const candidate of pendingIceCandidates) {
        try {
            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
        } catch (e) {
            console.error('Error applying pending ICE candidate:', e);
        }
    }
    pendingIceCandidates = [];
}

// Handle WebRTC signals from other peer
async function handleWebRTCSignal(data) {
    console.log('handleWebRTCSignal called with:', data.signal_type);
    try {
        if (data.signal_type === 'ready' && currentCall && currentCall.isCaller) {
            // Receiver is ready - now send the offer
            console.log('Receiver ready, creating offer...');
            document.getElementById('call-status').textContent = 'Connecting...';
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            console.log('Sending offer, SDP size:', offer.sdp.length, 'bytes');
            await sendSignal('offer', { sdp: offer.sdp, type: offer.type });
        } else if (data.signal_type === 'offer') {
            console.log('Received offer, creating answer...');
            console.log('peerConnection exists:', !!peerConnection);
            console.log('Offer SDP length:', data.signal_data.sdp?.length);
            // We're receiving an offer - peer connection should be set up already
            if (peerConnection) {
                try {
                    // Clean the SDP to remove problematic msid attributes from a=ssrc lines
                    const cleanedSdp = cleanSdp(data.signal_data.sdp);
                    console.log('Cleaned offer SDP length:', cleanedSdp?.length);
                    // Use direct object instead of RTCSessionDescription constructor
                    await peerConnection.setRemoteDescription({
                        sdp: cleanedSdp,
                        type: data.signal_data.type
                    });
                    console.log('setRemoteDescription succeeded for offer');
                    // Apply any ICE candidates that arrived before the offer
                    await applyPendingIceCandidates();
                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);
                    await sendSignal('answer', { sdp: answer.sdp, type: answer.type });
                } catch (sdpError) {
                    console.error('SDP Error:', sdpError);
                    console.log('Original SDP:', data.signal_data.sdp);
                    console.log('Cleaned SDP that failed:', cleanSdp(data.signal_data.sdp));
                }
            } else {
                console.log('Queueing offer (peerConnection not ready yet)');
                pendingOffer = data.signal_data;
            }
        } else if (data.signal_type === 'answer') {
            console.log('Received answer');
            console.log('Answer SDP length:', data.signal_data.sdp?.length);
            if (peerConnection) {
                try {
                    // Clean the SDP to remove problematic msid attributes from a=ssrc lines
                    const cleanedSdp = cleanSdp(data.signal_data.sdp);
                    // Use direct object instead of RTCSessionDescription constructor
                    await peerConnection.setRemoteDescription({
                        sdp: cleanedSdp,
                        type: data.signal_data.type
                    });
                    // Apply any ICE candidates that arrived before the answer
                    await applyPendingIceCandidates();
                } catch (sdpError) {
                    console.error('Answer SDP Error:', sdpError);
                    console.log('Problematic Answer SDP:', data.signal_data.sdp);
                }
            } else {
                console.error('ANSWER IGNORED: peerConnection is null!');
            }
        } else if (data.signal_type === 'ice-candidate') {
            if (peerConnection) {
                // Queue ICE candidate if remote description is not set yet
                if (!peerConnection.remoteDescription) {
                    console.log('Queueing ICE candidate (remote description not set yet)');
                    pendingIceCandidates.push({
                        candidate: data.signal_data.candidate,
                        sdpMid: data.signal_data.sdpMid,
                        sdpMLineIndex: data.signal_data.sdpMLineIndex
                    });
                } else {
                    try {
                        const candidateStr = data.signal_data.candidate;
                        console.log('Adding ICE candidate:', candidateStr?.substring(0, 80) + '...');
                        await peerConnection.addIceCandidate(new RTCIceCandidate({
                            candidate: data.signal_data.candidate,
                            sdpMid: data.signal_data.sdpMid,
                            sdpMLineIndex: data.signal_data.sdpMLineIndex
                        }));
                        console.log('ICE candidate added successfully');
                    } catch (iceError) {
                        console.error('Error adding ICE candidate:', iceError);
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error handling WebRTC signal:', error);
    }
}

// Setup call event listeners on WebSocket channel
function setupCallListeners() {
    if (typeof window.Echo === 'undefined' || !channel) {
        setTimeout(setupCallListeners, 100);
        return;
    }

    console.log('Setting up call listeners on channel');

    channel
        .listen('.call.initiated', (event) => {
            console.log('Call initiated event received:', event);
            if (event.receiver_id === currentUserId && event.caller_id === userId) {
                handleIncomingCall(event);
            }
        })
        .listen('.call.accepted', (event) => {
            console.log('Call accepted event received:', event);
            if (currentCall && currentCall.id === event.call_id && currentCall.isCaller) {
                // Receiver accepted - waiting for their "ready" signal
                document.getElementById('call-status').textContent = 'Connecting...';
            }
        })
        .listen('.call.rejected', (event) => {
            console.log('Call rejected event received:', event);
            if (currentCall && currentCall.id === event.call_id) {
                alert('Call was declined');
                cleanupCall();
            }
        })
        .listen('.call.ended', (event) => {
            console.log('Call ended event received:', event);
            if (currentCall && currentCall.id === event.call_id) {
                cleanupCall();
            }
        })
        .listen('.webrtc.signal', (event) => {
            console.log('WebRTC signal received:', event);
            console.log('currentCall:', currentCall);
            console.log('Checking: call_id match:', event.call_id, '==', currentCall?.id, '=', event.call_id == currentCall?.id);
            console.log('Checking: from_user_id:', event.from_user_id, '!=', currentUserId, '=', event.from_user_id != currentUserId);
            // Use == for type coercion (call_id might be string or number)
            if (currentCall && event.call_id == currentCall.id && event.from_user_id != currentUserId) {
                console.log('Processing WebRTC signal');
                handleWebRTCSignal(event);
            } else {
                console.log('Ignoring WebRTC signal (conditions not met)');
            }
        });
}

// Start call listeners after main WebSocket setup
setTimeout(() => {
    console.log('Setting up call listeners. Channel:', channel ? 'exists' : 'null');
    const userIds = [currentUserId, userId].sort((a, b) => a - b);
    console.log('Channel name should be: conversation.' + userIds[0] + '.' + userIds[1]);
    setupCallListeners();
}, 500);
</script>
@endpush

@endsection

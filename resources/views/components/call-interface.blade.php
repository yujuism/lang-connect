@props(['partner', 'floating' => false])

<!-- Incoming Call Modal -->
<div id="incoming-call-modal" class="d-none position-fixed top-0 start-0 w-100 h-100" style="z-index: 9999; background: rgba(0,0,0,0.8);">
    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-white">
        <div class="rounded-circle d-flex align-items-center justify-content-center mb-4"
             style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); font-size: 3rem; font-weight: bold;">
            {{ substr($partner->name, 0, 1) }}
        </div>
        <h3 class="mb-2">{{ $partner->name }}</h3>
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

<!-- Video Switch Request -->
<div id="video-request-notification" class="d-none position-fixed top-0 start-0 w-100 h-100" style="z-index: 10000; background: rgba(0,0,0,0.8);">
    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-white">
        <div class="rounded-circle d-flex align-items-center justify-content-center mb-4"
             style="width: 120px; height: 120px; background: linear-gradient(135deg, #6366f1, #4f46e5); font-size: 3rem; font-weight: bold;">
            {{ substr($partner->name, 0, 1) }}
        </div>
        <h3 class="mb-2">{{ $partner->name }}</h3>
        <p class="mb-4">Wants to switch to video call</p>
        <div class="d-flex gap-4">
            <button type="button" class="btn btn-secondary rounded-circle p-4" onclick="dismissVideoRequest()" title="Stay on Voice">
                <i class="bi bi-mic-fill fs-3"></i>
            </button>
            <button type="button" class="btn btn-primary rounded-circle p-4" onclick="acceptVideoRequest()" title="Switch to Video">
                <i class="bi bi-camera-video-fill fs-3"></i>
            </button>
        </div>
    </div>
</div>

<!-- Active Call Interface -->
@if($floating)
{{-- Floating panel for session page --}}
<div id="call-interface" class="d-none" style="position: fixed; z-index: 9998; bottom: 20px; right: 20px; width: 350px; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.4); background: #1a1a2e;">
    <!-- Header with partner name -->
    <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
        <div class="d-flex align-items-center text-white">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                 style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); font-size: 1rem; font-weight: bold;">
                {{ substr($partner->name, 0, 1) }}
            </div>
            <div>
                <div class="fw-semibold">{{ $partner->name }}</div>
                <div id="call-status" class="small opacity-75">Connecting...</div>
            </div>
        </div>
        <div id="call-timer" class="d-none text-white fw-bold" style="font-size: 1.1rem;">00:00</div>
    </div>

    <!-- Remote Video Container -->
    <div id="remote-video-container" style="position: relative; height: 240px; background: #16213e;">
        <video id="remote-video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>

        <!-- Call Info Overlay (when no video) -->
        <div id="call-info" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white" style="background: #1a1a2e;">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                 style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); font-size: 2.5rem; font-weight: bold;">
                {{ substr($partner->name, 0, 1) }}
            </div>
            <p class="text-secondary mb-0">Voice Call</p>
        </div>

        <!-- Local Video (draggable, inside remote container) -->
        <div id="local-video-container" class="d-none"
             style="position: absolute; top: 8px; right: 8px; width: 100px; height: 75px; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.4); cursor: grab; z-index: 10;">
            <video id="local-video" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover; background: #0f3460; transform: scaleX(-1);"></video>
        </div>
    </div>

    <!-- Call Controls -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 16px; padding: 16px; background: #16213e;">
        <button type="button" class="btn rounded-circle d-flex align-items-center justify-content-center" id="toggle-audio-btn" onclick="toggleAudio()" title="Mute/Unmute"
                style="width: 52px; height: 52px; background: #4b5563; border: none; color: white;">
            <i class="bi bi-mic-fill" style="font-size: 1.25rem;"></i>
        </button>
        <button type="button" class="btn rounded-circle d-flex align-items-center justify-content-center" id="toggle-video-btn" onclick="toggleVideo()" title="Toggle Video"
                style="width: 52px; height: 52px; background: #4b5563; border: none; color: white;">
            <i class="bi bi-camera-video-fill" style="font-size: 1.25rem;"></i>
        </button>
        <button type="button" class="btn rounded-circle d-flex align-items-center justify-content-center" onclick="endCall()" title="End Call"
                style="width: 60px; height: 60px; background: #dc2626; border: none; color: white;">
            <i class="bi bi-telephone-x-fill" style="font-size: 1.5rem;"></i>
        </button>
        <button type="button" class="btn rounded-circle d-flex align-items-center justify-content-center" id="fullscreen-btn" onclick="toggleFullscreen()" title="Fullscreen"
                style="width: 52px; height: 52px; background: transparent; border: 2px solid rgba(255,255,255,0.5); color: white;">
            <i class="bi bi-arrows-fullscreen" style="font-size: 1.25rem;"></i>
        </button>
    </div>
</div>
@else
{{-- Fullscreen for messages page --}}
<div id="call-interface" class="d-none position-fixed top-0 start-0 w-100 h-100" style="z-index: 9998; background: #1a1a2e;">
    <!-- Header with partner name -->
    <div class="d-flex align-items-center justify-content-between px-4 py-3" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));">
        <div class="d-flex align-items-center text-white">
            <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                 style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); font-size: 1.25rem; font-weight: bold;">
                {{ substr($partner->name, 0, 1) }}
            </div>
            <div>
                <div class="fw-semibold fs-5">{{ $partner->name }}</div>
                <div id="call-status" class="small opacity-75">Connecting...</div>
            </div>
        </div>
        <div id="call-timer" class="d-none text-white fw-bold fs-4">00:00</div>
    </div>

    <!-- Remote Video Container -->
    <div id="remote-video-container" style="position: relative; height: calc(100vh - 160px); background: #16213e;">
        <video id="remote-video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>

        <!-- Call Info Overlay (when no video) -->
        <div id="call-info" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white" style="background: #1a1a2e;">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-4"
                 style="width: 150px; height: 150px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); font-size: 4rem; font-weight: bold;">
                {{ substr($partner->name, 0, 1) }}
            </div>
            <p class="text-secondary fs-5 mb-0">Voice Call</p>
        </div>

        <!-- Local Video (draggable) -->
        <div id="local-video-container" class="d-none"
             style="position: absolute; bottom: 20px; right: 20px; width: 180px; height: 135px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.4); cursor: grab; z-index: 10;">
            <video id="local-video" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover; background: #0f3460; transform: scaleX(-1);"></video>
        </div>
    </div>

    <!-- Call Controls -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 20px; padding: 20px; background: #16213e;">
        <button type="button" class="btn rounded-circle d-flex align-items-center justify-content-center" id="toggle-audio-btn" onclick="toggleAudio()" title="Mute/Unmute"
                style="width: 60px; height: 60px; background: #4b5563; border: none; color: white;">
            <i class="bi bi-mic-fill" style="font-size: 1.5rem;"></i>
        </button>
        <button type="button" class="btn rounded-circle d-flex align-items-center justify-content-center" id="toggle-video-btn" onclick="toggleVideo()" title="Toggle Video"
                style="width: 60px; height: 60px; background: #4b5563; border: none; color: white;">
            <i class="bi bi-camera-video-fill" style="font-size: 1.5rem;"></i>
        </button>
        <button type="button" class="btn rounded-circle d-flex align-items-center justify-content-center" onclick="endCall()" title="End Call"
                style="width: 70px; height: 70px; background: #dc2626; border: none; color: white;">
            <i class="bi bi-telephone-x-fill" style="font-size: 1.75rem;"></i>
        </button>
    </div>
</div>
@endif

@push('scripts')
<script>
// WebRTC Call Variables
const callPartnerId = {{ $partner->id }};
const callCurrentUserId = {{ auth()->id() }};
const isFloatingMode = {{ $floating ? 'true' : 'false' }};
let currentCall = null;
let peerConnection = null;
let localStream = null;
let remoteStream = null;
let callTimerInterval = null;
let callStartTime = null;
let isAudioMuted = false;
let isVideoEnabled = true;
let pendingIceCandidates = [];
let pendingOffer = null;
let isFullscreen = false;

const rtcConfig = {
    iceServers: [
        { urls: 'stun:192.168.1.10:3478' },
        { urls: 'turn:192.168.1.10:3478', username: 'user', credential: 'pass' }
    ],
    sdpSemantics: 'unified-plan'
};

function cleanSdp(sdp) {
    if (!sdp) return sdp;
    let cleaned = sdp.replace(/^a=ssrc-group:[^\r\n]*[\r\n]*/gm, '');
    cleaned = cleaned.replace(/^a=ssrc:[^\r\n]*[\r\n]*/gm, '');
    return cleaned;
}

async function initiateCall(type) {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: type === 'video' });
        document.getElementById('local-video').srcObject = localStream;

        if (type === 'voice') {
            isVideoEnabled = false;
            const videoBtn = document.getElementById('toggle-video-btn');
            videoBtn.style.background = '#dc2626';
            videoBtn.innerHTML = '<i class="bi bi-camera-video-off-fill" style="font-size: ' + (isFloatingMode ? '1.25rem' : '1.5rem') + ';"></i>';
        }

        const response = await fetch(`/call/${callPartnerId}/initiate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ type })
        });

        const data = await response.json();
        if (data.success) {
            currentCall = data.call;
            currentCall.isCaller = true;
            showCallInterface();
            document.getElementById('call-status').textContent = 'Calling...';
            await setupPeerConnection();
        }
    } catch (error) {
        console.error('Error initiating call:', error);
        alert('Could not start call. Please check camera/microphone permissions.');
        cleanupCall();
    }
}

async function setupPeerConnection() {
    peerConnection = new RTCPeerConnection(rtcConfig);
    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

    peerConnection.ontrack = (event) => {
        const remoteVideo = document.getElementById('remote-video');
        if (event.streams && event.streams[0]) {
            remoteVideo.srcObject = event.streams[0];
            remoteStream = event.streams[0];
            if (event.track.kind === 'video') {
                document.getElementById('call-info').classList.add('d-none');
                event.track.onended = () => document.getElementById('call-info').classList.remove('d-none');
                event.track.onmute = () => document.getElementById('call-info').classList.remove('d-none');
                event.track.onunmute = () => document.getElementById('call-info').classList.add('d-none');
            }
        }
    };

    peerConnection.onicecandidate = async (event) => {
        if (event.candidate) {
            await sendSignal('ice-candidate', {
                candidate: event.candidate.candidate,
                sdpMid: event.candidate.sdpMid,
                sdpMLineIndex: event.candidate.sdpMLineIndex
            });
        }
    };

    peerConnection.onconnectionstatechange = () => {
        const state = peerConnection.connectionState;
        document.getElementById('call-status').textContent = state.charAt(0).toUpperCase() + state.slice(1) + '...';
        if (state === 'connected') {
            document.getElementById('call-status').textContent = 'Connected';
            startCallTimer();
            if (remoteStream && remoteStream.getVideoTracks().length > 0) {
                document.getElementById('call-info').classList.add('d-none');
            }
        } else if (state === 'failed') {
            endCall();
        }
    };
}

async function renegotiate() {
    if (!peerConnection) return;
    try {
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        await sendSignal('offer', { sdp: offer.sdp, type: offer.type });
    } catch (error) {
        console.error('Renegotiation failed:', error);
    }
}

async function sendSignal(signalType, signalData) {
    if (!currentCall) return;
    const headers = { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' };
    if (window.Echo && window.Echo.socketId()) headers['X-Socket-ID'] = window.Echo.socketId();
    try {
        await fetch(`/call/${currentCall.id}/signal`, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ signal_type: signalType, signal_data: signalData })
        });
    } catch (error) {
        console.error('Error sending signal:', error);
    }
}

function handleIncomingCall(callData) {
    if (currentCall) return;
    currentCall = { id: callData.call_id, caller_id: callData.caller_id, receiver_id: callData.receiver_id, type: callData.type, caller: callData.caller };
    document.getElementById('incoming-call-type').textContent = callData.type === 'video' ? 'Incoming video call...' : 'Incoming voice call...';
    document.getElementById('incoming-call-modal').classList.remove('d-none');
}

async function acceptIncomingCall() {
    try {
        document.getElementById('incoming-call-modal').classList.add('d-none');
        localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: currentCall.type === 'video' });
        document.getElementById('local-video').srcObject = localStream;

        if (currentCall.type === 'voice') {
            isVideoEnabled = false;
            const videoBtn = document.getElementById('toggle-video-btn');
            videoBtn.style.background = '#dc2626';
            videoBtn.innerHTML = '<i class="bi bi-camera-video-off-fill" style="font-size: ' + (isFloatingMode ? '1.25rem' : '1.5rem') + ';"></i>';
        }

        const acceptHeaders = { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' };
        if (window.Echo && window.Echo.socketId()) acceptHeaders['X-Socket-ID'] = window.Echo.socketId();
        await fetch(`/call/${currentCall.id}/accept`, { method: 'POST', headers: acceptHeaders });

        showCallInterface();
        document.getElementById('call-status').textContent = 'Connecting...';
        await setupPeerConnection();

        if (pendingOffer) {
            try {
                const cleanedSdp = cleanSdp(pendingOffer.sdp);
                await peerConnection.setRemoteDescription({ sdp: cleanedSdp, type: pendingOffer.type });
                await applyPendingIceCandidates();
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                await sendSignal('answer', { sdp: answer.sdp, type: answer.type });
            } catch (sdpError) {
                console.error('SDP Error:', sdpError);
            }
            pendingOffer = null;
        } else {
            await sendSignal('ready', {});
        }
    } catch (error) {
        console.error('Error accepting call:', error);
        alert('Could not accept call. Please check camera/microphone permissions.');
        cleanupCall();
    }
}

async function rejectIncomingCall() {
    if (!currentCall) return;
    await fetch(`/call/${currentCall.id}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    });
    document.getElementById('incoming-call-modal').classList.add('d-none');
    currentCall = null;
}

async function endCall() {
    if (currentCall) {
        await fetch(`/call/${currentCall.id}/end`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
    }
    cleanupCall();
}

function cleanupCall() {
    if (peerConnection) { peerConnection.close(); peerConnection = null; }
    if (localStream) { localStream.getTracks().forEach(track => track.stop()); localStream = null; }
    remoteStream = null;
    currentCall = null;
    pendingIceCandidates = [];
    pendingOffer = null;
    if (callTimerInterval) { clearInterval(callTimerInterval); callTimerInterval = null; }
    callStartTime = null;

    // Reset fullscreen state for floating mode
    if (isFloatingMode) {
        isFullscreen = false;
        const callInterface = document.getElementById('call-interface');
        callInterface.style.cssText = 'position: fixed; z-index: 9998; bottom: 20px; right: 20px; width: 350px; top: auto; left: auto; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.4); background: #1a1a2e;';
        document.getElementById('remote-video-container').style.height = '240px';
        document.getElementById('local-video-container').style.cssText = 'position: absolute; top: 8px; right: 8px; left: auto; bottom: auto; width: 100px; height: 75px; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.4); cursor: grab; z-index: 10;';
        document.getElementById('fullscreen-btn').innerHTML = '<i class="bi bi-arrows-fullscreen" style="font-size: 1.25rem;"></i>';
    }

    document.getElementById('call-interface').classList.add('d-none');
    document.getElementById('video-request-notification').classList.add('d-none');
    document.getElementById('incoming-call-modal').classList.add('d-none');
    document.getElementById('local-video').srcObject = null;
    document.getElementById('remote-video').srcObject = null;
    document.getElementById('call-info').classList.remove('d-none');
    document.getElementById('call-timer').classList.add('d-none');
    document.getElementById('call-status').textContent = 'Connecting...';

    isAudioMuted = false;
    isVideoEnabled = true;
    const iconSize = isFloatingMode ? '1.25rem' : '1.5rem';
    const audioBtn = document.getElementById('toggle-audio-btn');
    audioBtn.style.background = '#4b5563';
    audioBtn.innerHTML = '<i class="bi bi-mic-fill" style="font-size: ' + iconSize + ';"></i>';
    const videoBtn = document.getElementById('toggle-video-btn');
    videoBtn.style.background = '#4b5563';
    videoBtn.innerHTML = '<i class="bi bi-camera-video-fill" style="font-size: ' + iconSize + ';"></i>';
}

function showCallInterface() {
    document.getElementById('call-interface').classList.remove('d-none');
    if (currentCall && currentCall.type === 'video') {
        document.getElementById('local-video-container').classList.remove('d-none');
    } else {
        document.getElementById('local-video-container').classList.add('d-none');
    }
}

@if($floating)
function toggleFullscreen() {
    const callInterface = document.getElementById('call-interface');
    const remoteContainer = document.getElementById('remote-video-container');
    const localContainer = document.getElementById('local-video-container');
    const btn = document.getElementById('fullscreen-btn');

    if (isFullscreen) {
        callInterface.style.cssText = 'position: fixed; z-index: 9998; bottom: 20px; right: 20px; width: 350px; top: auto; left: auto; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.4); background: #1a1a2e;';
        remoteContainer.style.height = '240px';
        localContainer.style.cssText = 'position: absolute; top: 8px; right: 8px; left: auto; bottom: auto; width: 100px; height: 75px; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.4); cursor: grab; z-index: 10;';
        btn.innerHTML = '<i class="bi bi-arrows-fullscreen" style="font-size: 1.25rem;"></i>';
        isFullscreen = false;
    } else {
        callInterface.style.cssText = 'position: fixed; z-index: 9998; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; border-radius: 0; overflow: hidden; background: #1a1a2e;';
        remoteContainer.style.height = 'calc(100vh - 140px)';
        localContainer.style.cssText = 'position: absolute; bottom: 20px; right: 20px; left: auto; top: auto; width: 180px; height: 135px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.4); cursor: grab; z-index: 10;';
        btn.innerHTML = '<i class="bi bi-fullscreen-exit" style="font-size: 1.25rem;"></i>';
        isFullscreen = true;
    }
}

// Draggable local video
(function() {
    let isDragging = false;
    let offsetX, offsetY;

    function getLocalVideo() {
        return document.getElementById('local-video-container');
    }

    document.addEventListener('mousedown', function(e) {
        const localVideo = getLocalVideo();
        if (!localVideo || localVideo.classList.contains('d-none')) return;
        if (!localVideo.contains(e.target)) return;

        isDragging = true;
        localVideo.style.cursor = 'grabbing';

        const rect = localVideo.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;

        e.preventDefault();
        e.stopPropagation();
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;

        const localVideo = getLocalVideo();
        if (!localVideo) return;

        const parent = document.getElementById('remote-video-container');
        if (!parent) return;

        const parentRect = parent.getBoundingClientRect();

        let newLeft = e.clientX - parentRect.left - offsetX;
        let newTop = e.clientY - parentRect.top - offsetY;

        newLeft = Math.max(0, Math.min(newLeft, parentRect.width - localVideo.offsetWidth));
        newTop = Math.max(0, Math.min(newTop, parentRect.height - localVideo.offsetHeight));

        localVideo.style.left = newLeft + 'px';
        localVideo.style.top = newTop + 'px';
        localVideo.style.right = 'auto';
        localVideo.style.bottom = 'auto';

        e.preventDefault();
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            const localVideo = getLocalVideo();
            if (localVideo) localVideo.style.cursor = 'grab';
        }
    });

    // Touch support
    document.addEventListener('touchstart', function(e) {
        const localVideo = getLocalVideo();
        if (!localVideo || localVideo.classList.contains('d-none')) return;
        if (!localVideo.contains(e.target)) return;

        isDragging = true;
        const touch = e.touches[0];
        const rect = localVideo.getBoundingClientRect();
        offsetX = touch.clientX - rect.left;
        offsetY = touch.clientY - rect.top;
    }, { passive: true });

    document.addEventListener('touchmove', function(e) {
        if (!isDragging) return;

        const localVideo = getLocalVideo();
        if (!localVideo) return;

        const parent = document.getElementById('remote-video-container');
        if (!parent) return;

        const touch = e.touches[0];
        const parentRect = parent.getBoundingClientRect();

        let newLeft = touch.clientX - parentRect.left - offsetX;
        let newTop = touch.clientY - parentRect.top - offsetY;

        newLeft = Math.max(0, Math.min(newLeft, parentRect.width - localVideo.offsetWidth));
        newTop = Math.max(0, Math.min(newTop, parentRect.height - localVideo.offsetHeight));

        localVideo.style.left = newLeft + 'px';
        localVideo.style.top = newTop + 'px';
        localVideo.style.right = 'auto';
        localVideo.style.bottom = 'auto';
    }, { passive: true });

    document.addEventListener('touchend', function() {
        if (isDragging) {
            isDragging = false;
            const localVideo = getLocalVideo();
            if (localVideo) localVideo.style.cursor = 'grab';
        }
    });
})();
@endif

function toggleAudio() {
    if (!localStream) return;
    const audioTrack = localStream.getAudioTracks()[0];
    if (audioTrack) {
        audioTrack.enabled = !audioTrack.enabled;
        isAudioMuted = !audioTrack.enabled;
        const btn = document.getElementById('toggle-audio-btn');
        const iconSize = isFloatingMode ? '1.25rem' : '1.5rem';
        if (isAudioMuted) {
            btn.style.background = '#dc2626';
            btn.innerHTML = '<i class="bi bi-mic-mute-fill" style="font-size: ' + iconSize + ';"></i>';
        } else {
            btn.style.background = '#4b5563';
            btn.innerHTML = '<i class="bi bi-mic-fill" style="font-size: ' + iconSize + ';"></i>';
        }
    }
}

async function toggleVideo() {
    const btn = document.getElementById('toggle-video-btn');
    const videoTrack = localStream?.getVideoTracks()[0];
    const iconSize = isFloatingMode ? '1.25rem' : '1.5rem';

    if (isVideoEnabled && videoTrack) {
        videoTrack.enabled = false;
        isVideoEnabled = false;
        btn.style.background = '#dc2626';
        btn.innerHTML = '<i class="bi bi-camera-video-off-fill" style="font-size: ' + iconSize + ';"></i>';
        document.getElementById('local-video-container').classList.add('d-none');
        await sendSignal('video-disabled', {});
    } else {
        try {
            await sendSignal('video-enabled', {});
            if (videoTrack) {
                videoTrack.enabled = true;
            } else {
                const videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
                const newVideoTrack = videoStream.getVideoTracks()[0];
                localStream.addTrack(newVideoTrack);
                if (peerConnection) {
                    peerConnection.addTrack(newVideoTrack, localStream);
                    await renegotiate();
                }
                document.getElementById('local-video').srcObject = localStream;
            }
            isVideoEnabled = true;
            btn.style.background = '#4b5563';
            btn.innerHTML = '<i class="bi bi-camera-video-fill" style="font-size: ' + iconSize + ';"></i>';
            document.getElementById('local-video-container').classList.remove('d-none');
        } catch (error) {
            console.error('Could not enable video:', error);
            alert('Could not enable video. Please check camera permissions.');
        }
    }
}

function showVideoRequestNotification() {
    document.getElementById('video-request-notification').classList.remove('d-none');
}

function dismissVideoRequest() {
    document.getElementById('video-request-notification').classList.add('d-none');
}

async function acceptVideoRequest() {
    dismissVideoRequest();
    await toggleVideo();
}

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

async function applyPendingIceCandidates() {
    if (!peerConnection || !peerConnection.remoteDescription) return;
    for (const candidate of pendingIceCandidates) {
        try {
            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
        } catch (e) {
            console.error('Error applying ICE candidate:', e);
        }
    }
    pendingIceCandidates = [];
}

async function handleWebRTCSignal(data) {
    try {
        if (data.signal_type === 'ready' && currentCall && currentCall.isCaller) {
            document.getElementById('call-status').textContent = 'Connecting...';
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            await sendSignal('offer', { sdp: offer.sdp, type: offer.type });
        } else if (data.signal_type === 'offer') {
            if (peerConnection) {
                try {
                    const cleanedSdp = cleanSdp(data.signal_data.sdp);
                    await peerConnection.setRemoteDescription({ sdp: cleanedSdp, type: data.signal_data.type });
                    await applyPendingIceCandidates();
                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);
                    await sendSignal('answer', { sdp: answer.sdp, type: answer.type });
                } catch (sdpError) {
                    console.error('SDP Error:', sdpError);
                }
            } else {
                pendingOffer = data.signal_data;
            }
        } else if (data.signal_type === 'answer') {
            if (peerConnection) {
                try {
                    const cleanedSdp = cleanSdp(data.signal_data.sdp);
                    await peerConnection.setRemoteDescription({ sdp: cleanedSdp, type: data.signal_data.type });
                    await applyPendingIceCandidates();
                } catch (sdpError) {
                    console.error('SDP Error:', sdpError);
                }
            }
        } else if (data.signal_type === 'ice-candidate') {
            if (peerConnection) {
                if (!peerConnection.remoteDescription) {
                    pendingIceCandidates.push({ candidate: data.signal_data.candidate, sdpMid: data.signal_data.sdpMid, sdpMLineIndex: data.signal_data.sdpMLineIndex });
                } else {
                    try {
                        await peerConnection.addIceCandidate(new RTCIceCandidate({ candidate: data.signal_data.candidate, sdpMid: data.signal_data.sdpMid, sdpMLineIndex: data.signal_data.sdpMLineIndex }));
                    } catch (iceError) {
                        console.error('Error adding ICE candidate:', iceError);
                    }
                }
            }
        } else if (data.signal_type === 'video-enabled') {
            if (!isVideoEnabled && currentCall) showVideoRequestNotification();
        }
    } catch (error) {
        console.error('Error handling WebRTC signal:', error);
    }
}

function setupCallChannelListeners(channel) {
    channel
        .listen('.call.initiated', (event) => {
            if (event.receiver_id === callCurrentUserId && event.caller_id === callPartnerId) handleIncomingCall(event);
        })
        .listen('.call.accepted', (event) => {
            if (currentCall && currentCall.id === event.call_id && currentCall.isCaller) {
                document.getElementById('call-status').textContent = 'Connecting...';
            }
        })
        .listen('.call.rejected', (event) => {
            if (currentCall && currentCall.id === event.call_id) { alert('Call was declined'); cleanupCall(); }
        })
        .listen('.call.ended', (event) => {
            if (currentCall && currentCall.id === event.call_id) cleanupCall();
        })
        .listen('.webrtc.signal', (event) => {
            if (currentCall && event.call_id == currentCall.id && event.from_user_id != callCurrentUserId) handleWebRTCSignal(event);
        });
}
</script>
@endpush

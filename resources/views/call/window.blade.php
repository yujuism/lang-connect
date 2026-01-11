<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Call with {{ $partner->name }} - LangConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
        }
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #1a1a2e;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .call-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .call-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
            flex-shrink: 0;
        }
        .partner-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .partner-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .video-container {
            flex: 1;
            position: relative;
            background: #16213e;
            min-height: 0; /* Important for flex child to shrink */
            overflow: hidden;
        }
        #remote-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
        }

        /* Local video - responsive sizing */
        #local-video-container {
            position: absolute;
            top: 12px;
            right: 12px;
            width: clamp(80px, 30%, 120px);
            aspect-ratio: 4/3;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            border: 2px solid rgba(255,255,255,0.4);
            cursor: grab;
            z-index: 10;
        }

        /* Responsive styles for smaller windows */
        @media (max-width: 350px) {
            .call-header {
                padding: 8px 12px;
            }
            .partner-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }
            .partner-info {
                gap: 8px;
            }
            .call-controls {
                gap: 8px !important;
                padding: 12px !important;
            }
            .control-btn {
                width: 44px !important;
                height: 44px !important;
            }
            .control-btn i {
                font-size: 1rem !important;
            }
            .control-btn-danger {
                width: 50px !important;
                height: 50px !important;
            }
            .call-info-avatar {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
        }

        @media (max-height: 400px) {
            .call-header {
                padding: 6px 12px;
            }
            .call-controls {
                padding: 10px !important;
            }
            .control-btn {
                width: 44px !important;
                height: 44px !important;
            }
            .control-btn-danger {
                width: 50px !important;
                height: 50px !important;
            }
        }

        /* Fullscreen mode */
        .fullscreen-mode .call-header {
            display: none;
        }
        .fullscreen-mode .call-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 40px 16px 24px;
            z-index: 20;
        }
        .fullscreen-mode .video-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
        }
        .fullscreen-mode #local-video-container {
            width: min(25%, 180px);
            bottom: 120px;
            right: 20px;
            top: auto;
        }
        #call-info {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #1a1a2e;
            color: white;
        }
        .call-info-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 16px;
        }
        #local-video-container {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 120px;
            height: 90px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            border: 2px solid rgba(255,255,255,0.4);
            cursor: grab;
            z-index: 10;
        }
        #local-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
            background: #0f3460;
        }
        .call-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: #16213e;
            flex-wrap: wrap;
            flex-shrink: 0;
        }
        .control-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.15s, background 0.15s;
            color: white;
        }
        .control-btn:hover {
            transform: scale(1.1);
        }
        .control-btn.muted {
            background: #dc2626 !important;
        }
        .control-btn-secondary {
            background: #4b5563;
        }
        .control-btn-danger {
            background: #dc2626;
            width: 64px;
            height: 64px;
        }
        .control-btn-outline {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.5);
        }
        .control-btn-outline:hover {
            background: rgba(255,255,255,0.1);
        }
        .d-none { display: none !important; }

        /* Incoming call overlay */
        .incoming-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
            color: white;
        }
        .pulse-ring {
            animation: pulse 1.5s ease-out infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.3); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Incoming Call Overlay (hidden if auto-accept or if caller) -->
    <div id="incoming-call-overlay" class="incoming-overlay {{ ($isCaller || $autoAccept) ? 'd-none' : '' }}">
        <div class="position-relative mb-4">
            <div class="call-info-avatar pulse-ring position-absolute" style="opacity: 0.3;"></div>
            <div class="call-info-avatar">{{ substr($partner->name, 0, 1) }}</div>
        </div>
        <h3 class="mb-2">{{ $partner->name }}</h3>
        <p class="mb-4 text-secondary" id="incoming-call-type">Incoming call...</p>
        <div class="d-flex gap-4">
            <button class="control-btn control-btn-danger" onclick="rejectCall()" title="Decline">
                <i class="bi bi-telephone-x-fill" style="font-size: 1.5rem;"></i>
            </button>
            <button class="control-btn" style="background: #22c55e;" onclick="acceptCall()" title="Accept">
                <i class="bi bi-telephone-fill" style="font-size: 1.5rem;"></i>
            </button>
        </div>
    </div>

    <!-- Video Request Overlay -->
    <div id="video-request-overlay" class="incoming-overlay d-none">
        <div class="call-info-avatar mb-4">{{ substr($partner->name, 0, 1) }}</div>
        <h3 class="mb-2">{{ $partner->name }}</h3>
        <p class="mb-4 text-secondary">Wants to switch to video</p>
        <div class="d-flex gap-4">
            <button class="control-btn control-btn-secondary" onclick="dismissVideoRequest()" title="Stay on Voice">
                <i class="bi bi-mic-fill" style="font-size: 1.25rem;"></i>
            </button>
            <button class="control-btn" style="background: var(--primary-color);" onclick="acceptVideoRequest()" title="Switch to Video">
                <i class="bi bi-camera-video-fill" style="font-size: 1.25rem;"></i>
            </button>
        </div>
    </div>

    <!-- Main Call Interface -->
    <div class="call-container">
        <div class="call-header">
            <div class="partner-info">
                <div class="partner-avatar">{{ substr($partner->name, 0, 1) }}</div>
                <div>
                    <div class="fw-semibold">{{ $partner->name }}</div>
                    <div id="call-status" class="small opacity-75">Connecting...</div>
                </div>
            </div>
            <div id="call-timer" class="d-none fw-bold" style="font-size: 1.2rem;">00:00</div>
        </div>

        <div class="video-container">
            <video id="remote-video" autoplay playsinline></video>

            <div id="call-info">
                <div class="call-info-avatar">{{ substr($partner->name, 0, 1) }}</div>
                <p class="text-secondary">Voice Call</p>
            </div>

            <div id="local-video-container" class="d-none">
                <video id="local-video" autoplay playsinline muted></video>
            </div>
        </div>

        <div class="call-controls">
            <button class="control-btn control-btn-secondary" id="toggle-audio-btn" onclick="toggleAudio()" title="Mute/Unmute">
                <i class="bi bi-mic-fill" style="font-size: 1.25rem;"></i>
            </button>
            <button class="control-btn control-btn-secondary" id="toggle-video-btn" onclick="toggleVideo()" title="Toggle Video">
                <i class="bi bi-camera-video-fill" style="font-size: 1.25rem;"></i>
            </button>
            <button class="control-btn control-btn-danger" onclick="endCall()" title="End Call">
                <i class="bi bi-telephone-x-fill" style="font-size: 1.5rem;"></i>
            </button>
            <button class="control-btn control-btn-outline" id="toggle-fullscreen-btn" onclick="toggleFullscreen()" title="Fullscreen">
                <i class="bi bi-arrows-fullscreen" style="font-size: 1.25rem;"></i>
            </button>
        </div>
    </div>

    <script>
        const partnerId = {{ $partner->id }};
        const currentUserId = {{ auth()->id() }};
        const initialCallType = '{{ $callType ?? '' }}';
        const initialCallId = {{ $callId ?? 'null' }};
        const isCaller = {{ $isCaller ? 'true' : 'false' }};
        const autoAccept = {{ ($autoAccept ?? false) ? 'true' : 'false' }};

        let currentCall = initialCallId ? { id: initialCallId, type: initialCallType || 'voice', isCaller: isCaller } : null;
        let peerConnection = null;
        let localStream = null;
        let remoteStream = null;
        let callTimerInterval = null;
        let callStartTime = null;
        let isAudioMuted = false;
        let isVideoEnabled = (initialCallType === 'video');
        let pendingIceCandidates = [];
        let wsReady = false;
        let pendingOffer = null;
        let channel = null;

        // Audio Recording for Transcription
        let mediaRecorder = null;
        let recordedChunks = [];
        let chunkNumber = 0;
        let recordingStartTime = null;
        const CHUNK_DURATION = 15 * 60 * 1000; // 15 minutes in milliseconds
        let chunkTimer = null;
        let sessionId = null; // Set this if call is linked to a session

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

        async function startCall() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: initialCallType === 'video'
                });
                document.getElementById('local-video').srcObject = localStream;

                if (initialCallType === 'video') {
                    document.getElementById('local-video-container').classList.remove('d-none');
                } else {
                    isVideoEnabled = false;
                    updateVideoButton();
                }

                if (isCaller) {
                    const response = await fetch(`/call/${partnerId}/initiate`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ type: initialCallType })
                    });
                    const data = await response.json();
                    if (data.success) {
                        currentCall = data.call;
                        currentCall.isCaller = true;
                        document.getElementById('call-status').textContent = 'Calling...';
                        await setupPeerConnection();
                        // Notify parent window
                        if (window.opener) {
                            window.opener.postMessage({ type: 'call-started', callId: currentCall.id }, '*');
                        }
                    }
                }
            } catch (error) {
                console.error('Error starting call:', error);
                alert('Could not start call. Please check camera/microphone permissions.');
                window.close();
            }
        }

        async function acceptCall() {
            console.log('acceptCall() called, currentCall:', currentCall);
            try {
                document.getElementById('incoming-call-overlay').classList.add('d-none');

                const callType = currentCall.type || 'voice';
                console.log('Getting user media, callType:', callType);

                localStream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: callType === 'video'
                });
                document.getElementById('local-video').srcObject = localStream;

                if (callType === 'video') {
                    document.getElementById('local-video-container').classList.remove('d-none');
                    isVideoEnabled = true;
                } else {
                    isVideoEnabled = false;
                    updateVideoButton();
                }

                console.log('Sending accept to server');
                const headers = {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                };
                if (window.Echo && window.Echo.socketId()) {
                    headers['X-Socket-ID'] = window.Echo.socketId();
                }
                const acceptResponse = await fetch(`/call/${currentCall.id}/accept`, { method: 'POST', headers });
                console.log('Accept response:', acceptResponse.status);

                document.getElementById('call-status').textContent = 'Connecting...';
                await setupPeerConnection();

                if (pendingOffer) {
                    console.log('Processing pending offer');
                    try {
                        const cleanedSdp = cleanSdp(pendingOffer.sdp);
                        await peerConnection.setRemoteDescription({ sdp: cleanedSdp, type: pendingOffer.type });
                        await applyPendingIceCandidates();
                        const answer = await peerConnection.createAnswer();
                        await peerConnection.setLocalDescription(answer);
                        await sendSignal('answer', { sdp: answer.sdp, type: answer.type });
                    } catch (e) {
                        console.error('SDP Error:', e);
                    }
                    pendingOffer = null;
                } else {
                    console.log('No pending offer, sending ready signal');
                    await sendSignal('ready', {});
                }
            } catch (error) {
                console.error('Error accepting call:', error);
                alert('Could not accept call. Please check camera/microphone permissions.');
                window.close();
            }
        }

        async function rejectCall() {
            if (currentCall) {
                await fetch(`/call/${currentCall.id}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            }
            window.close();
        }

        async function setupPeerConnection() {
            peerConnection = new RTCPeerConnection(rtcConfig);
            localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

            peerConnection.ontrack = (event) => {
                if (event.streams && event.streams[0]) {
                    document.getElementById('remote-video').srcObject = event.streams[0];
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
                console.log('Connection state changed:', state);
                document.getElementById('call-status').textContent = state.charAt(0).toUpperCase() + state.slice(1) + '...';
                if (state === 'connected') {
                    document.getElementById('call-status').textContent = 'Connected';
                    startCallTimer();
                    if (remoteStream && remoteStream.getVideoTracks().length > 0) {
                        document.getElementById('call-info').classList.add('d-none');
                    }
                    // Start recording for transcription
                    setTimeout(() => startRecording(), 1000);
                } else if (state === 'failed') {
                    console.log('Connection failed');
                    endCall();
                }
                // Don't end call on 'disconnected' - it might reconnect
            };

            peerConnection.oniceconnectionstatechange = () => {
                console.log('ICE connection state:', peerConnection.iceConnectionState);
            };
        }

        async function sendSignal(signalType, signalData) {
            if (!currentCall) return;
            const headers = {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };
            if (window.Echo && window.Echo.socketId()) {
                headers['X-Socket-ID'] = window.Echo.socketId();
            }
            try {
                await fetch(`/call/${currentCall.id}/signal`, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({ signal_type: signalType, signal_data: signalData })
                });
            } catch (error) {
                console.error('Error sending signal:', error);
            }
        }

        async function endCall() {
            if (currentCall) {
                await fetch(`/call/${currentCall.id}/end`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
            }
            cleanup();
            window.close();
        }

        function cleanup() {
            stopRecording(); // Stop audio recording
            if (peerConnection) { peerConnection.close(); peerConnection = null; }
            if (localStream) { localStream.getTracks().forEach(track => track.stop()); localStream = null; }
            if (callTimerInterval) { clearInterval(callTimerInterval); }
        }

        function toggleAudio() {
            if (!localStream) return;
            const audioTrack = localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                isAudioMuted = !audioTrack.enabled;
                const btn = document.getElementById('toggle-audio-btn');
                if (isAudioMuted) {
                    btn.classList.add('muted');
                    btn.innerHTML = '<i class="bi bi-mic-mute-fill" style="font-size: 1.25rem;"></i>';
                } else {
                    btn.classList.remove('muted');
                    btn.innerHTML = '<i class="bi bi-mic-fill" style="font-size: 1.25rem;"></i>';
                }
            }
        }

        function updateVideoButton() {
            const btn = document.getElementById('toggle-video-btn');
            if (!isVideoEnabled) {
                btn.classList.add('muted');
                btn.innerHTML = '<i class="bi bi-camera-video-off-fill" style="font-size: 1.25rem;"></i>';
            } else {
                btn.classList.remove('muted');
                btn.innerHTML = '<i class="bi bi-camera-video-fill" style="font-size: 1.25rem;"></i>';
            }
        }

        async function toggleVideo() {
            const videoTrack = localStream?.getVideoTracks()[0];

            if (isVideoEnabled && videoTrack) {
                videoTrack.enabled = false;
                isVideoEnabled = false;
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
                    document.getElementById('local-video-container').classList.remove('d-none');
                } catch (error) {
                    console.error('Could not enable video:', error);
                    alert('Could not enable video. Please check camera permissions.');
                    return;
                }
            }
            updateVideoButton();
        }

        let isFullscreen = false;

        function toggleFullscreen() {
            const container = document.querySelector('.call-container');
            const btn = document.getElementById('toggle-fullscreen-btn');

            if (!isFullscreen) {
                // Enter fullscreen
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen();
                }
                container.classList.add('fullscreen-mode');
                btn.innerHTML = '<i class="bi bi-fullscreen-exit" style="font-size: 1.25rem;"></i>';
                isFullscreen = true;
            } else {
                // Exit fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                container.classList.remove('fullscreen-mode');
                btn.innerHTML = '<i class="bi bi-arrows-fullscreen" style="font-size: 1.25rem;"></i>';
                isFullscreen = false;
            }
        }

        // Handle fullscreen change events (e.g., pressing Escape)
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                const container = document.querySelector('.call-container');
                const btn = document.getElementById('toggle-fullscreen-btn');
                container.classList.remove('fullscreen-mode');
                btn.innerHTML = '<i class="bi bi-arrows-fullscreen" style="font-size: 1.25rem;"></i>';
                isFullscreen = false;
            }
        });

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

        function showVideoRequestNotification() {
            document.getElementById('video-request-overlay').classList.remove('d-none');
        }

        function dismissVideoRequest() {
            document.getElementById('video-request-overlay').classList.add('d-none');
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
            console.log('handleWebRTCSignal:', data.signal_type, 'peerConnection:', !!peerConnection);
            try {
                if (data.signal_type === 'ready' && currentCall && currentCall.isCaller) {
                    console.log('Received ready signal, creating offer');
                    document.getElementById('call-status').textContent = 'Connecting...';
                    const offer = await peerConnection.createOffer();
                    await peerConnection.setLocalDescription(offer);
                    await sendSignal('offer', { sdp: offer.sdp, type: offer.type });
                } else if (data.signal_type === 'offer') {
                    console.log('Received offer');
                    if (peerConnection) {
                        const cleanedSdp = cleanSdp(data.signal_data.sdp);
                        await peerConnection.setRemoteDescription({ sdp: cleanedSdp, type: data.signal_data.type });
                        await applyPendingIceCandidates();
                        const answer = await peerConnection.createAnswer();
                        await peerConnection.setLocalDescription(answer);
                        await sendSignal('answer', { sdp: answer.sdp, type: answer.type });
                    } else {
                        console.log('No peer connection yet, queueing offer');
                        pendingOffer = data.signal_data;
                    }
                } else if (data.signal_type === 'answer') {
                    console.log('Received answer');
                    if (peerConnection) {
                        const cleanedSdp = cleanSdp(data.signal_data.sdp);
                        await peerConnection.setRemoteDescription({ sdp: cleanedSdp, type: data.signal_data.type });
                        await applyPendingIceCandidates();
                    }
                } else if (data.signal_type === 'ice-candidate') {
                    if (peerConnection) {
                        if (!peerConnection.remoteDescription) {
                            console.log('Queueing ICE candidate');
                            pendingIceCandidates.push(data.signal_data);
                        } else {
                            console.log('Adding ICE candidate');
                            await peerConnection.addIceCandidate(new RTCIceCandidate(data.signal_data));
                        }
                    }
                } else if (data.signal_type === 'video-enabled') {
                    if (!isVideoEnabled && currentCall) showVideoRequestNotification();
                }
            } catch (error) {
                console.error('Error handling WebRTC signal:', error);
            }
        }

        function handleIncomingCall(callData) {
            currentCall = {
                id: callData.call_id,
                type: callData.type,
                caller_id: callData.caller_id,
                receiver_id: callData.receiver_id
            };
            document.getElementById('incoming-call-type').textContent =
                callData.type === 'video' ? 'Incoming video call...' : 'Incoming voice call...';
            document.getElementById('incoming-call-overlay').classList.remove('d-none');
        }

        function setupWebSocket() {
            if (typeof window.Echo === 'undefined') {
                setTimeout(setupWebSocket, 100);
                return;
            }

            const userIds = [currentUserId, partnerId].sort((a, b) => a - b);
            const channelName = `conversation.${userIds[0]}.${userIds[1]}`;
            channel = window.Echo.private(channelName);

            channel
                .listen('.call.initiated', (event) => {
                    if (event.receiver_id === currentUserId && event.caller_id === partnerId) {
                        handleIncomingCall(event);
                    }
                })
                .listen('.call.accepted', (event) => {
                    if (currentCall && currentCall.id === event.call_id && currentCall.isCaller) {
                        document.getElementById('call-status').textContent = 'Connecting...';
                    }
                })
                .listen('.call.rejected', (event) => {
                    if (currentCall && currentCall.id === event.call_id) {
                        alert('Call was declined');
                        window.close();
                    }
                })
                .listen('.call.ended', (event) => {
                    if (currentCall && currentCall.id === event.call_id) {
                        cleanup();
                        window.close();
                    }
                })
                .listen('.webrtc.signal', (event) => {
                    if (currentCall && event.call_id == currentCall.id && event.from_user_id != currentUserId) {
                        handleWebRTCSignal(event);
                    }
                });

            wsReady = true;
            console.log('WebSocket ready, isCaller:', isCaller, 'autoAccept:', autoAccept, 'currentCall:', currentCall);

            // If caller, start the call after WebSocket is ready
            if (isCaller && initialCallType) {
                startCall();
            }
            // If receiver with auto-accept, accept the call automatically (with small delay for stability)
            else if (!isCaller && autoAccept && currentCall) {
                console.log('Auto-accepting call in 500ms:', currentCall);
                setTimeout(() => {
                    if (currentCall) {
                        acceptCall();
                    }
                }, 500);
            }
        }

        // Draggable local video
        (function() {
            let isDragging = false;
            let offsetX, offsetY;

            const localVideo = document.getElementById('local-video-container');
            const container = document.querySelector('.video-container');

            localVideo.addEventListener('mousedown', function(e) {
                isDragging = true;
                localVideo.style.cursor = 'grabbing';
                const rect = localVideo.getBoundingClientRect();
                offsetX = e.clientX - rect.left;
                offsetY = e.clientY - rect.top;
                e.preventDefault();
            });

            document.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                const containerRect = container.getBoundingClientRect();
                let newLeft = e.clientX - containerRect.left - offsetX;
                let newTop = e.clientY - containerRect.top - offsetY;
                newLeft = Math.max(0, Math.min(newLeft, containerRect.width - localVideo.offsetWidth));
                newTop = Math.max(0, Math.min(newTop, containerRect.height - localVideo.offsetHeight));
                localVideo.style.left = newLeft + 'px';
                localVideo.style.top = newTop + 'px';
                localVideo.style.right = 'auto';
                localVideo.style.bottom = 'auto';
            });

            document.addEventListener('mouseup', function() {
                isDragging = false;
                localVideo.style.cursor = 'grab';
            });
        })();

        // Handle window close
        window.addEventListener('beforeunload', function() {
            if (currentCall) {
                navigator.sendBeacon(`/call/${currentCall.id}/end`, new URLSearchParams({
                    '_token': document.querySelector('meta[name="csrf-token"]').content
                }));
            }
            cleanup();
        });

        // Audio Recording Functions
        function startRecording() {
            if (!localStream || mediaRecorder) return;

            try {
                // Create a mixed stream with local and remote audio
                const audioContext = new AudioContext();
                const destination = audioContext.createMediaStreamDestination();

                // Add local audio
                if (localStream.getAudioTracks().length > 0) {
                    const localSource = audioContext.createMediaStreamSource(localStream);
                    localSource.connect(destination);
                }

                // Add remote audio if available
                if (remoteStream && remoteStream.getAudioTracks().length > 0) {
                    const remoteSource = audioContext.createMediaStreamSource(remoteStream);
                    remoteSource.connect(destination);
                }

                const mixedStream = destination.stream;

                // Setup MediaRecorder
                const options = { mimeType: 'audio/webm;codecs=opus' };
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'audio/webm';
                }
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'audio/ogg';
                }

                mediaRecorder = new MediaRecorder(mixedStream, options);
                recordedChunks = [];
                recordingStartTime = Date.now();

                mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    if (recordedChunks.length > 0) {
                        uploadChunk();
                    }
                };

                // Record in 10 second intervals for smoother chunking
                mediaRecorder.start(10000);
                console.log('Audio recording started');

                // Set timer for 15-minute chunks
                chunkTimer = setInterval(() => {
                    if (mediaRecorder && mediaRecorder.state === 'recording') {
                        // Stop current recording, upload, and start new one
                        mediaRecorder.stop();
                        chunkNumber++;
                        // Restart recording after brief pause
                        setTimeout(() => {
                            if (peerConnection && peerConnection.connectionState === 'connected') {
                                recordedChunks = [];
                                mediaRecorder.start(10000);
                            }
                        }, 100);
                    }
                }, CHUNK_DURATION);

            } catch (error) {
                console.error('Error starting recording:', error);
            }
        }

        function stopRecording() {
            if (chunkTimer) {
                clearInterval(chunkTimer);
                chunkTimer = null;
            }
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
        }

        async function uploadChunk() {
            if (recordedChunks.length === 0) return;

            const blob = new Blob(recordedChunks, { type: 'audio/webm' });
            const formData = new FormData();
            formData.append('audio', blob, `call-recording-chunk-${chunkNumber}.webm`);
            formData.append('chunk_number', chunkNumber);
            formData.append('call_id', currentCall?.id || '');
            formData.append('session_id', sessionId || '');

            try {
                const response = await fetch('/api/transcription/upload-chunk', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                if (response.ok) {
                    console.log(`Chunk ${chunkNumber} uploaded successfully`);
                } else {
                    console.error('Failed to upload chunk:', await response.text());
                }
            } catch (error) {
                console.error('Error uploading chunk:', error);
            }
        }

        // Start WebSocket connection
        setupWebSocket();
    </script>
</body>
</html>

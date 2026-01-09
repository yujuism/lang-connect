@extends('layout')

@section('title', 'Practice Session - LangConnect')

@section('content')
<div class="container my-3" style="max-width: 1200px;">
    <!-- Minimal Header Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span style="font-size: 1.25rem;">{{ $session->language->flag_emoji }}</span>
            <span class="fw-semibold" style="color: var(--text-primary);">{{ $session->language->name }}</span>
            <span class="text-secondary">·</span>
            <span class="text-secondary small">{{ $session->topic }}</span>
            @if($session->status === 'in_progress')
                <span class="badge bg-success ms-2"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Live</span>
            @else
                <span class="badge bg-secondary ms-2">Completed</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Timer -->
            <div id="session-timer" class="fw-bold font-monospace" style="font-size: 1.1rem; color: var(--text-primary);">
                00:00:00
            </div>
            @if($session->status === 'in_progress')
                <!-- Controls -->
                <div class="btn-group btn-group-sm">
                    <button id="pause-btn" class="btn btn-outline-secondary" title="Pause">
                        <i class="bi bi-pause-fill"></i>
                    </button>
                    <button id="resume-btn" class="btn btn-outline-success" title="Resume" style="display: none;">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="window.openCallWindow({{ $partner->id }}, 'voice')" title="Voice Call">
                        <i class="bi bi-telephone-fill"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="window.openCallWindow({{ $partner->id }}, 'video')" title="Video Call">
                        <i class="bi bi-camera-video-fill"></i>
                    </button>
                </div>
                <button id="end-session-btn" class="btn btn-sm btn-danger">
                    End
                </button>
            @endif
        </div>
    </div>

    <!-- Canvas/PDF Tabs -->
    <div class="card" style="border-radius: 0.75rem; border: 1px solid var(--border-color); overflow: hidden;">
        <div class="card-header p-0 bg-transparent border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--border-color) !important;">
            <ul class="nav nav-tabs border-0" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active px-4 py-2" id="canvas-tab" data-bs-toggle="tab" data-bs-target="#canvas-panel" type="button" role="tab">
                        <i class="bi bi-brush me-1"></i> Canvas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-4 py-2" id="pdf-tab" data-bs-toggle="tab" data-bs-target="#pdf-panel" type="button" role="tab">
                        <i class="bi bi-file-pdf me-1"></i> PDF
                    </button>
                </li>
            </ul>
            @if($session->status === 'in_progress')
            <div id="pdf-upload-controls" class="pe-3" style="display: none;">
                <label class="btn btn-sm btn-outline-primary mb-0">
                    <i class="bi bi-upload me-1"></i> Upload PDF
                    <input type="file" id="pdf-upload-input" accept=".pdf" style="display: none;">
                </label>
            </div>
            @endif
        </div>
        <div class="card-body p-0 tab-content">
            <!-- Canvas Tab -->
            <div class="tab-pane fade show active" id="canvas-panel" role="tabpanel" style="height: 500px; position: relative; contain: strict;">
                <div id="tldraw-container" style="height: 100%; width: 100%;"></div>
            </div>
            <!-- PDF Tab -->
            <div class="tab-pane fade" id="pdf-panel" role="tabpanel" style="height: 500px; position: relative;">
                <div id="pdf-viewer-container" style="height: 100%; width: 100%;"></div>
            </div>
        </div>
    </div>

    <!-- Session Details -->
    <div class="mt-3">
        <div class="mt-2" id="sessionDetails">
            <div class="card" style="border-radius: 0.75rem; border: 1px solid var(--border-color);">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="small fw-semibold mb-2"><i class="bi bi-person"></i> Partner</h6>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 32px; height: 32px; background: var(--primary-color); color: white; font-size: 0.875rem; font-weight: bold;">
                                    {{ substr($partner->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $partner->name }}</div>
                                    @if($partner->progress)
                                    <div class="text-secondary" style="font-size: 0.75rem;">Level {{ $partner->progress->level }} · {{ $partner->progress->karma_points }} karma</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="small fw-semibold mb-2"><i class="bi bi-info-circle"></i> Details</h6>
                            <div class="small text-secondary">
                                <div>Type: {{ ucfirst($session->session_type) }}</div>
                                <div>Language: {{ $session->language->name }}</div>
                                @if($session->duration_minutes)
                                <div>Duration: {{ $session->duration_minutes }} min</div>
                                @endif
                            </div>
                        </div>
                        @if($session->request)
                        <div class="col-md-4">
                            <h6 class="small fw-semibold mb-2"><i class="bi bi-lightbulb"></i> Topic</h6>
                            <div class="small text-secondary">{{ $session->request->specific_question }}</div>
                            <div class="mt-1">
                                <span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ $session->request->topic_category }}</span>
                                <span class="badge bg-light text-dark" style="font-size: 0.7rem;">{{ $session->request->proficiency_level }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($session->status === 'in_progress')
<script>
// Session Timer
let sessionStartTime = new Date('{{ $session->started_at }}').getTime();
let elapsedSeconds = 0;
let timerInterval = null;
let isPaused = false;
let pausedAt = 0;
let totalPausedTime = 0;

function updateTimer() {
    if (isPaused) return;

    const now = Date.now();
    const elapsed = Math.floor((now - sessionStartTime - totalPausedTime) / 1000);
    elapsedSeconds = elapsed;

    const hours = Math.floor(elapsed / 3600);
    const minutes = Math.floor((elapsed % 3600) / 60);
    const seconds = elapsed % 60;

    const display =
        String(hours).padStart(2, '0') + ':' +
        String(minutes).padStart(2, '0') + ':' +
        String(seconds).padStart(2, '0');

    document.getElementById('session-timer').textContent = display;
}

// Start timer
timerInterval = setInterval(updateTimer, 1000);
updateTimer();

// Pause/Resume buttons
document.getElementById('pause-btn').addEventListener('click', function() {
    isPaused = true;
    pausedAt = Date.now();
    this.style.display = 'none';
    document.getElementById('resume-btn').style.display = 'inline-block';
});

document.getElementById('resume-btn').addEventListener('click', function() {
    totalPausedTime += (Date.now() - pausedAt);
    isPaused = false;
    this.style.display = 'none';
    document.getElementById('pause-btn').style.display = 'inline-block';
});

// End Session button
document.getElementById('end-session-btn').addEventListener('click', function() {
    if (confirm('Are you sure you want to end this session?')) {
        const durationMinutes = Math.round(elapsedSeconds / 60);

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("sessions.complete", $session) }}';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';

        const durationInput = document.createElement('input');
        durationInput.type = 'hidden';
        durationInput.name = 'duration_minutes';
        durationInput.value = durationMinutes;

        form.appendChild(csrfInput);
        form.appendChild(durationInput);
        document.body.appendChild(form);
        form.submit();
    }
});
</script>
@endif


@push('scripts')
@vite(['resources/js/tldraw-canvas.jsx', 'resources/js/pdf-viewer.jsx'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    let pdfViewerRoot = null;

    // Wait for tldraw module to load
    const checkTldraw = setInterval(() => {
        if (typeof window.mountTldrawCanvas === 'function') {
            clearInterval(checkTldraw);

            window.mountTldrawCanvas('tldraw-container', {
                sessionId: {{ $session->id }},
                currentUserId: {{ auth()->id() }},
                partnerName: @json($partner->name),
                partnerId: {{ $partner->id }},
                isReadOnly: {{ $session->status !== 'in_progress' ? 'true' : 'false' }},
                initialData: @json($session->canvas_data),
                csrfToken: '{{ csrf_token() }}',
            });
        }
    }, 100);

    // Mount PDF viewer function
    function mountPdf(url) {
        if (typeof window.mountPdfViewer !== 'function') return;

        // Unmount existing viewer if any
        if (pdfViewerRoot) {
            pdfViewerRoot.unmount();
            pdfViewerRoot = null;
        }

        pdfViewerRoot = window.mountPdfViewer('pdf-viewer-container', {
            sessionId: {{ $session->id }},
            currentUserId: {{ auth()->id() }},
            partnerName: @json($partner->name),
            partnerId: {{ $partner->id }},
            isReadOnly: {{ $session->status !== 'in_progress' ? 'true' : 'false' }},
            pdfUrl: url,
            initialHighlights: @json($session->pdf_highlights ?? []),
            initialDrawings: @json($session->pdf_drawings ?? []),
            csrfToken: '{{ csrf_token() }}',
        });
    }

    // Wait for PDF viewer module to load
    const checkPdfViewer = setInterval(() => {
        if (typeof window.mountPdfViewer === 'function') {
            clearInterval(checkPdfViewer);
            mountPdf(@json($pdfUrl));
        }
    }, 100);

    // Timeout after 10 seconds
    setTimeout(() => {
        clearInterval(checkTldraw);
        clearInterval(checkPdfViewer);
    }, 10000);

    // Show/hide upload controls when switching tabs
    const pdfTab = document.getElementById('pdf-tab');
    const uploadControls = document.getElementById('pdf-upload-controls');
    if (pdfTab && uploadControls) {
        pdfTab.addEventListener('shown.bs.tab', () => uploadControls.style.display = 'block');
        pdfTab.addEventListener('hidden.bs.tab', () => uploadControls.style.display = 'none');
    }

    // Handle PDF upload
    const uploadInput = document.getElementById('pdf-upload-input');
    if (uploadInput) {
        uploadInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('pdf', file);

            const btn = uploadInput.parentElement;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Uploading...';

            try {
                const response = await fetch('/sessions/{{ $session->id }}/pdf/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData,
                });

                const data = await response.json();
                if (data.success) {
                    // Remount PDF viewer with new URL
                    mountPdf(data.pdf_url);
                    btn.innerHTML = '<i class="bi bi-check me-1"></i> Uploaded!';
                    setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);

                    // Broadcast to partner
                    if (window.Echo) {
                        window.Echo.private('session.{{ $session->id }}')
                            .whisper('pdf-changed', {
                                user_id: {{ auth()->id() }},
                                pdf_url: data.pdf_url,
                                highlights: [],
                            });
                    }
                } else {
                    throw new Error('Upload failed');
                }
            } catch (error) {
                console.error('Upload error:', error);
                btn.innerHTML = '<i class="bi bi-x me-1"></i> Failed';
                setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
            }

            uploadInput.value = '';
        });
    }
});
</script>
@endpush
@endsection

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

    <!-- Collaborative Canvas -->
    <div class="card" style="border-radius: 0.75rem; border: 1px solid var(--border-color); overflow: hidden;">
        <div class="card-body p-0" style="height: 500px;">
            <div id="tldraw-container" style="height: 100%; width: 100%;"></div>
        </div>
    </div>

    <!-- Collapsible Details (shown by default) -->
    <div class="mt-3">
        <button class="btn btn-sm btn-link text-secondary p-0" type="button" data-bs-toggle="collapse" data-bs-target="#sessionDetails" aria-expanded="true" id="details-toggle">
            <i class="bi bi-chevron-up" id="toggle-icon"></i> <span id="toggle-text">Hide details</span>
        </button>
        <div class="collapse show mt-2" id="sessionDetails">
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

<script>
// Toggle details text/icon
const sessionDetails = document.getElementById('sessionDetails');
const toggleText = document.getElementById('toggle-text');
const toggleIcon = document.getElementById('toggle-icon');

if (sessionDetails) {
    sessionDetails.addEventListener('hide.bs.collapse', function() {
        toggleText.textContent = 'Show details';
        toggleIcon.classList.remove('bi-chevron-up');
        toggleIcon.classList.add('bi-chevron-down');
    });
    sessionDetails.addEventListener('show.bs.collapse', function() {
        toggleText.textContent = 'Hide details';
        toggleIcon.classList.remove('bi-chevron-down');
        toggleIcon.classList.add('bi-chevron-up');
    });
}
</script>

@push('scripts')
@vite(['resources/js/tldraw-canvas.jsx'])
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    // Timeout after 10 seconds
    setTimeout(() => clearInterval(checkTldraw), 10000);
});
</script>
@endpush
@endsection

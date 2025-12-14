@extends('layout')

@section('title', 'Practice Session - LangConnect')

@section('content')
<div class="container my-4" style="max-width: 1400px;">
    <!-- Session Header -->
    <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color); background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-2">
                        {{ $session->language->flag_emoji }} {{ $session->language->name }} Practice Session
                    </h4>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-tag"></i> {{ $session->topic }}
                    </p>
                </div>
                <div class="text-end">
                    <!-- Session Timer -->
                    <div id="session-timer" class="bg-white text-dark px-4 py-3 rounded-3 mb-2" style="font-size: 2rem; font-weight: bold; font-family: 'Courier New', monospace;">
                        00:00:00
                    </div>
                    <div class="badge bg-white bg-opacity-25 px-3 py-2" style="font-size: 0.9rem;">
                        @if($session->status === 'in_progress')
                            <i class="bi bi-play-circle-fill"></i> In Progress
                        @elseif($session->status === 'completed')
                            <i class="bi bi-check-circle-fill"></i> Completed
                        @else
                            <i class="bi bi-clock-fill"></i> {{ ucfirst($session->status) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Session Area -->
        <div class="col-lg-8">
            <!-- Timer Controls -->
            @if($session->status === 'in_progress')
            <div class="card shadow-sm mb-3" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-center gap-2">
                        <button id="pause-btn" class="btn btn-warning" style="border-radius: 0.5rem;">
                            <i class="bi bi-pause-fill"></i> Pause
                        </button>
                        <button id="resume-btn" class="btn btn-success" style="border-radius: 0.5rem; display: none;">
                            <i class="bi bi-play-fill"></i> Resume
                        </button>
                        <a href="{{ route('messages.show', $partner) }}" class="btn btn-outline-primary" style="border-radius: 0.5rem;">
                            <i class="bi bi-chat-dots"></i> Message Partner
                        </a>
                        <button id="end-session-btn" class="btn btn-danger" style="border-radius: 0.5rem;">
                            <i class="bi bi-stop-circle"></i> End Session
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Practice Topic Card -->
            @if($session->request)
            <div class="card shadow-sm mb-3" style="border-radius: 1rem; border: 1px solid var(--border-color); background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--primary-color);">
                        <i class="bi bi-lightbulb-fill"></i> What You're Practicing Today
                    </h6>
                    <p class="mb-2" style="color: var(--text-primary); font-size: 1.1rem;">
                        {{ $session->request->specific_question }}
                    </p>
                    <div class="d-flex gap-2 mt-3">
                        <span class="badge bg-white bg-opacity-50 text-dark px-3 py-2">
                            {{ ucfirst($session->request->topic_category) }}
                        </span>
                        <span class="badge bg-white bg-opacity-50 text-dark px-3 py-2">
                            Level: {{ $session->request->proficiency_level }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Session Notes -->
            <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                <div class="card-header" style="background: var(--bg-secondary); border-bottom: 1px solid var(--border-color); border-radius: 1rem 1rem 0 0;">
                    <h6 class="mb-0 fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-journal-text"></i> Session Notes & Key Points
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($session->status === 'in_progress')
                        <textarea id="session-notes"
                                  class="form-control"
                                  rows="8"
                                  placeholder="Take notes during your practice session:
• New vocabulary words
• Grammar points learned
• Pronunciation tips
• Questions to review later
• Progress made

Your notes are auto-saved!"
                                  style="border-radius: 0.75rem; font-family: system-ui;">{{ $session->notes ?? '' }}</textarea>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <small class="text-secondary">
                                <i class="bi bi-cloud-check"></i> <span id="save-status">Notes auto-save</span>
                            </small>
                            <button id="save-notes-btn" class="btn btn-sm btn-primary" style="border-radius: 0.5rem;">
                                <i class="bi bi-save"></i> Save Notes
                            </button>
                        </div>
                    @else
                        @if($session->notes)
                            <div class="p-3" style="background: var(--bg-secondary); border-radius: 0.75rem; white-space: pre-wrap; font-family: system-ui;">{{ $session->notes }}</div>
                        @else
                            <p class="text-secondary text-center mb-0">No notes were taken during this session.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Session Info Sidebar -->
        <div class="col-lg-4">
            <!-- Partner Info -->
            <div class="card shadow-sm mb-3" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-person-circle"></i> Your Partner
                    </h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; font-size: 1.5rem; font-weight: bold;">
                            {{ substr($partner->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-semibold" style="color: var(--text-primary);">{{ $partner->name }}</div>
                            @if($partner->progress)
                                <div class="small text-secondary">
                                    <i class="bi bi-star-fill text-warning"></i> Level {{ $partner->progress->level }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($partner->progress)
                        <div class="small">
                            <div class="mb-2">
                                <i class="bi bi-trophy text-warning"></i>
                                <span class="text-secondary">{{ $partner->progress->karma_points }} Karma Points</span>
                            </div>
                            <div class="mb-2">
                                <i class="bi bi-people text-primary"></i>
                                <span class="text-secondary">Helped {{ $partner->progress->members_helped }} members</span>
                            </div>
                            <div>
                                <i class="bi bi-clock text-success"></i>
                                <span class="text-secondary">{{ number_format($partner->progress->contribution_hours, 1) }} hours contributed</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Session Details -->
            <div class="card shadow-sm mb-3" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-info-circle"></i> Session Details
                    </h6>
                    <div class="small">
                        <div class="mb-2">
                            <span class="text-secondary">Type:</span>
                            <span class="fw-semibold" style="color: var(--text-primary);">{{ ucfirst($session->session_type) }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary">Language:</span>
                            <span class="fw-semibold" style="color: var(--text-primary);">
                                {{ $session->language->flag_emoji }} {{ $session->language->name }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="text-secondary">Topic:</span>
                            <span class="fw-semibold" style="color: var(--text-primary);">{{ $session->topic }}</span>
                        </div>
                        @if($session->duration_minutes)
                            <div class="mb-2">
                                <span class="text-secondary">Duration:</span>
                                <span class="fw-semibold" style="color: var(--text-primary);">{{ $session->duration_minutes }} minutes</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color); background: var(--bg-secondary);">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3" style="color: var(--primary-color);">
                        <i class="bi bi-lightbulb"></i> Tips for Success
                    </h6>
                    <ul class="small text-secondary mb-0" style="padding-left: 1.2rem;">
                        <li class="mb-2">Be patient and encouraging</li>
                        <li class="mb-2">Practice pronunciation together</li>
                        <li class="mb-2">Ask questions and clarify doubts</li>
                        <li class="mb-2">Take notes of new words</li>
                        <li>Have fun learning!</li>
                    </ul>
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
    if (confirm('Are you sure you want to end this session? Make sure to save your notes first!')) {
        // Save final duration
        const durationMinutes = Math.round(elapsedSeconds / 60);

        // Create form and submit
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

// Auto-save notes
let saveTimeout = null;
const notesTextarea = document.getElementById('session-notes');
const saveStatus = document.getElementById('save-status');

function saveNotes() {
    const notes = notesTextarea.value;

    saveStatus.innerHTML = '<i class="bi bi-cloud-upload"></i> Saving...';

    fetch('{{ route("sessions.save-notes", $session) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            saveStatus.innerHTML = '<i class="bi bi-cloud-check"></i> Saved';
            setTimeout(() => {
                saveStatus.innerHTML = '<i class="bi bi-cloud-check"></i> Notes auto-save';
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error saving notes:', error);
        saveStatus.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error saving';
    });
}

// Auto-save on typing (debounced)
notesTextarea.addEventListener('input', function() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(saveNotes, 2000); // Save 2 seconds after stopping typing
});

// Manual save button
document.getElementById('save-notes-btn').addEventListener('click', function() {
    clearTimeout(saveTimeout);
    saveNotes();
});

// Save before page unload
window.addEventListener('beforeunload', function(e) {
    if (notesTextarea.value !== '{{ addslashes($session->notes ?? "") }}') {
        saveNotes();
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
@endif

@endsection

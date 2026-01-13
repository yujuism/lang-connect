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

    <!-- Canvas/PDF/Insights Tabs -->
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
                @if($session->status === 'completed')
                <li class="nav-item">
                    <button class="nav-link px-4 py-2" id="insights-tab" data-bs-toggle="tab" data-bs-target="#insights-panel" type="button" role="tab">
                        <i class="bi bi-lightbulb me-1"></i> Insights
                        @if($analysis && $analysis->isCompleted())
                        <span class="badge bg-success ms-1" style="font-size: 0.65rem;">Ready</span>
                        @elseif($transcripts->isNotEmpty())
                        <span class="badge bg-warning ms-1" style="font-size: 0.65rem;">Processing</span>
                        @endif
                    </button>
                </li>
                @endif
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

            @if($session->status === 'completed')
            <!-- Insights Tab -->
            <div class="tab-pane fade" id="insights-panel" role="tabpanel" style="min-height: 500px; max-height: 600px; overflow-y: auto;">
                <div class="p-4">
                    @if($analysis && $analysis->isCompleted())
                        <!-- AI Summary Section -->
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-stars me-2"></i>Session Summary</h5>
                            <div class="card bg-light border-0" style="border-radius: 0.5rem;">
                                <div class="card-body">
                                    <p class="mb-0" style="line-height: 1.7;">{{ $analysis->summary }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Topics Section -->
                        @if(!empty($analysis->topics))
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-chat-dots me-2"></i>Topics Discussed</h5>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($analysis->topics as $topic)
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2" style="font-size: 0.875rem;">
                                    {{ $topic }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Key Phrases Section -->
                        @if(!empty($analysis->key_phrases))
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3"><i class="bi bi-quote me-2"></i>Key Phrases</h5>
                            <div class="row g-2">
                                @foreach($analysis->key_phrases as $phrase)
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light" style="border-radius: 0.5rem;">
                                        <div class="card-body py-2 px-3">
                                            <i class="bi bi-chat-left-quote text-secondary me-2"></i>
                                            <span>{{ $phrase }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Vocabulary Section -->
                        @if(!empty($analysis->vocabulary_extracted))
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3">
                                <i class="bi bi-book me-2"></i>Vocabulary
                                <span class="badge bg-secondary ms-2">{{ count($analysis->vocabulary_extracted) }} words</span>
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Word/Phrase</th>
                                            <th style="width: 50%;">Meaning</th>
                                            <th style="width: 20%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($analysis->vocabulary_extracted as $vocab)
                                        @php
                                            $word = is_array($vocab) ? ($vocab['word'] ?? $vocab['term'] ?? '') : $vocab;
                                            $meaning = is_array($vocab) ? ($vocab['meaning'] ?? $vocab['definition'] ?? '') : '';
                                            $existingFlashcard = $sessionFlashcards->first(function($fc) use ($word) {
                                                return strtolower($fc->front) === strtolower($word);
                                            });
                                        @endphp
                                        <tr>
                                            <td class="fw-medium">{{ $word }}</td>
                                            <td class="text-secondary">{{ $meaning }}</td>
                                            <td>
                                                @if($existingFlashcard)
                                                <span class="badge bg-success"><i class="bi bi-check"></i> Added</span>
                                                @else
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary add-flashcard-btn"
                                                        data-word="{{ $word }}"
                                                        data-meaning="{{ $meaning }}">
                                                    <i class="bi bi-plus"></i> Flashcard
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Transcript Section -->
                        @if($transcripts->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3">
                                <i class="bi bi-file-text me-2"></i>Full Transcript
                                <button class="btn btn-sm btn-outline-secondary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#transcriptCollapse">
                                    <i class="bi bi-chevron-down"></i> Show/Hide
                                </button>
                            </h5>
                            <div class="collapse" id="transcriptCollapse">
                                <div class="card bg-light border-0" style="border-radius: 0.5rem;">
                                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                        @foreach($transcripts as $transcript)
                                        <div class="mb-3">
                                            <div class="text-secondary small mb-1">
                                                <i class="bi bi-clock me-1"></i>
                                                Part {{ $transcript->chunk_number + 1 }}
                                                @if($transcript->duration_seconds)
                                                ({{ floor($transcript->duration_seconds / 60) }}:{{ str_pad($transcript->duration_seconds % 60, 2, '0', STR_PAD_LEFT) }})
                                                @endif
                                            </div>
                                            <p class="mb-0" style="line-height: 1.8; white-space: pre-wrap;">{{ $transcript->transcript }}</p>
                                        </div>
                                        @if(!$loop->last)
                                        <hr class="my-3">
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Session Flashcards -->
                        @if($sessionFlashcards->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3">
                                <i class="bi bi-card-text me-2"></i>Your Flashcards from this Session
                                <span class="badge bg-secondary ms-2">{{ $sessionFlashcards->count() }}</span>
                            </h5>
                            <div class="row g-2">
                                @foreach($sessionFlashcards->take(6) as $flashcard)
                                <div class="col-md-4">
                                    <div class="card h-100" style="border-radius: 0.5rem;">
                                        <div class="card-body py-2 px-3">
                                            <div class="fw-medium">{{ $flashcard->front }}</div>
                                            <div class="text-secondary small">{{ Str::limit($flashcard->back, 50) }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @if($sessionFlashcards->count() > 6)
                            <a href="{{ route('flashcards.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                                View All Flashcards <i class="bi bi-arrow-right"></i>
                            </a>
                            @endif
                        </div>
                        @endif

                    @elseif($transcripts->isNotEmpty())
                        <!-- Processing State -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="fw-semibold mb-2">Analyzing Your Session</h5>
                            <p class="text-secondary mb-0">
                                We're processing your conversation and extracting insights.<br>
                                This usually takes a few minutes.
                            </p>
                            <div class="mt-3">
                                <span class="badge bg-info">{{ $transcripts->count() }} audio chunk(s) transcribed</span>
                            </div>
                        </div>
                    @else
                        <!-- No Recording State -->
                        <div class="text-center py-5">
                            <i class="bi bi-mic-mute" style="font-size: 3rem; color: var(--text-secondary);"></i>
                            <h5 class="fw-semibold mt-3 mb-2">No Recording Available</h5>
                            <p class="text-secondary mb-0">
                                This session wasn't recorded, so there's no transcript to analyze.<br>
                                Enable recording in your next session to get AI-powered insights!
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            @endif
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

    // Handle Add Flashcard buttons
    document.querySelectorAll('.add-flashcard-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const word = this.dataset.word;
            const meaning = this.dataset.meaning;
            const originalHtml = this.innerHTML;

            this.disabled = true;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i>';

            try {
                const response = await fetch('/flashcards', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        front: word,
                        back: meaning,
                        language: '{{ $session->language->name }}',
                        practice_session_id: {{ $session->id }},
                    }),
                });

                if (response.ok) {
                    this.outerHTML = '<span class="badge bg-success"><i class="bi bi-check"></i> Added</span>';
                } else {
                    throw new Error('Failed to create flashcard');
                }
            } catch (error) {
                console.error('Error creating flashcard:', error);
                this.disabled = false;
                this.innerHTML = originalHtml;
                alert('Failed to create flashcard. Please try again.');
            }
        });
    });

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

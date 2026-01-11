@extends('layout')

@section('title', 'Test Transcription')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-bug"></i> Dev Testing - Transcription Pipeline
                </div>
                <div class="card-body">
                    <p class="text-muted">Upload an audio file to test the Groq transcription and GPT analysis pipeline.</p>

                    <form id="testForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="audio" class="form-label">Audio File</label>
                            <input type="file" class="form-control" id="audio" name="audio"
                                   accept=".webm,.mp3,.wav,.m4a,.ogg" required>
                            <div class="form-text">Supported: WebM, MP3, WAV, M4A, OGG (max 50MB)</div>
                        </div>

                        <div class="mb-3">
                            <label for="language" class="form-label">Language Hint (optional)</label>
                            <select class="form-select" id="language" name="language">
                                <option value="">Auto-detect</option>
                                <option value="en">English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                                <option value="de">German</option>
                                <option value="ja">Japanese</option>
                                <option value="zh">Chinese</option>
                                <option value="ko">Korean</option>
                                <option value="pt">Portuguese</option>
                                <option value="it">Italian</option>
                                <option value="ru">Russian</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-play-fill"></i> Test Pipeline
                        </button>
                    </form>

                    <div id="loading" class="d-none mt-4">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border text-primary me-3" role="status"></div>
                            <div>
                                <strong>Processing...</strong>
                                <div class="text-muted small">This may take a minute for long audio files.</div>
                            </div>
                        </div>
                    </div>

                    <div id="results" class="d-none mt-4">
                        <h5><i class="bi bi-check-circle text-success"></i> Results</h5>

                        <div class="card mb-3">
                            <div class="card-header">Transcription</div>
                            <div class="card-body">
                                <p><strong>Language:</strong> <span id="resultLanguage"></span></p>
                                <p><strong>Duration:</strong> <span id="resultDuration"></span> seconds</p>
                                <div class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                    <pre id="resultTranscript" class="mb-0" style="white-space: pre-wrap;"></pre>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">AI Analysis</div>
                            <div class="card-body">
                                <h6>Summary</h6>
                                <p id="resultSummary" class="text-muted"></p>

                                <h6>Topics Covered</h6>
                                <div id="resultTopics" class="mb-3"></div>

                                <h6>Key Phrases</h6>
                                <div id="resultPhrases" class="mb-3"></div>

                                <h6>Vocabulary (Flashcards)</h6>
                                <div id="resultVocabulary"></div>
                            </div>
                        </div>
                    </div>

                    <div id="error" class="d-none mt-4">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span id="errorMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script>
document.getElementById('testForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const loading = document.getElementById('loading');
    const results = document.getElementById('results');
    const error = document.getElementById('error');
    const submitBtn = document.getElementById('submitBtn');

    // Reset
    loading.classList.remove('d-none');
    results.classList.add('d-none');
    error.classList.add('d-none');
    submitBtn.disabled = true;

    try {
        const response = await fetch('/dev/test-transcribe', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Unknown error');
        }

        // Show results
        document.getElementById('resultLanguage').textContent = data.transcription.language;
        document.getElementById('resultDuration').textContent = data.transcription.duration_seconds;
        document.getElementById('resultTranscript').textContent = data.transcription.transcript;

        if (data.analysis) {
            document.getElementById('resultSummary').textContent = data.analysis.summary || 'N/A';

            // Topics
            const topicsHtml = (data.analysis.topics || [])
                .map(t => `<span class="badge bg-primary me-1">${t}</span>`)
                .join('');
            document.getElementById('resultTopics').innerHTML = topicsHtml || '<span class="text-muted">None</span>';

            // Key phrases
            const phrasesHtml = (data.analysis.key_phrases || [])
                .map(p => `<div class="mb-2"><strong>${p.phrase}</strong> - ${p.translation}</div>`)
                .join('');
            document.getElementById('resultPhrases').innerHTML = phrasesHtml || '<span class="text-muted">None</span>';

            // Vocabulary
            const vocabHtml = (data.analysis.vocabulary || [])
                .map(v => `
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span><strong>${v.front}</strong></span>
                        <span class="text-muted">${v.back}</span>
                    </div>
                `)
                .join('');
            document.getElementById('resultVocabulary').innerHTML = vocabHtml || '<span class="text-muted">None</span>';
        }

        results.classList.remove('d-none');
    } catch (err) {
        document.getElementById('errorMessage').textContent = err.message;
        error.classList.remove('d-none');
    } finally {
        loading.classList.add('d-none');
        submitBtn.disabled = false;
    }
});
</script>
@endsection

@extends('layout')

@section('title', 'Flashcards - LangConnect')

@section('content')
<div class="container my-4">
    <div class="mb-4">
        <h2 class="fw-bold" style="color: var(--text-primary);">
            <i class="bi bi-card-text"></i> Flashcards
        </h2>
        <p class="text-secondary">Review vocabulary from your practice sessions</p>
    </div>

    @if(session('message'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Review Button -->
    @if($dueCards > 0)
        <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 2px solid var(--primary-color);">
            <div class="card-body p-4 text-center">
                <div class="display-4 fw-bold mb-2" style="color: var(--primary-color);">
                    {{ $dueCards }}
                </div>
                <p class="text-secondary mb-3">cards due for review</p>
                <a href="{{ route('flashcards.review') }}" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-play-fill me-2"></i>Start Review
                </a>
            </div>
        </div>
    @else
        <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body p-4 text-center">
                <i class="bi bi-check-circle-fill text-success display-4 mb-3"></i>
                <h5 class="fw-bold" style="color: var(--text-primary);">All caught up!</h5>
                <p class="text-secondary mb-0">No cards due for review. Keep practicing to generate more flashcards!</p>
            </div>
        </div>
    @endif

    <!-- Language Decks -->
    @if($languages->isEmpty())
        <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body p-5 text-center">
                <i class="bi bi-inbox display-1 text-secondary mb-3"></i>
                <h5 class="fw-bold" style="color: var(--text-primary);">No flashcards yet</h5>
                <p class="text-secondary mb-0">
                    Flashcards are automatically generated from your practice sessions.
                    <br>Start a conversation to build your vocabulary!
                </p>
            </div>
        </div>
    @else
        <h5 class="fw-bold mb-3" style="color: var(--text-primary);">
            <i class="bi bi-collection"></i> Your Decks
        </h5>

        <div class="row g-3">
            @foreach($languages as $lang)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="fw-bold mb-0" style="color: var(--text-primary);">
                                    {{ strtoupper($lang->target_language) }}
                                </h5>
                                @if($lang->due_count > 0)
                                    <span class="badge bg-danger">{{ $lang->due_count }} due</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between small text-secondary mb-1">
                                    <span>Progress</span>
                                    <span>{{ $lang->total }} cards</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 0.5rem;">
                                    @php
                                        $masteredPct = $lang->total > 0 ? ($lang->mastered_count / $lang->total) * 100 : 0;
                                        $learningPct = $lang->total > 0 ? ($lang->learning_count / $lang->total) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $masteredPct }}%"></div>
                                    <div class="progress-bar bg-warning" style="width: {{ $learningPct }}%"></div>
                                </div>
                            </div>

                            <div class="row text-center small">
                                <div class="col-4">
                                    <div class="fw-bold text-primary">{{ $lang->new_count }}</div>
                                    <div class="text-secondary">New</div>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-warning">{{ $lang->learning_count }}</div>
                                    <div class="text-secondary">Learning</div>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold text-success">{{ $lang->mastered_count }}</div>
                                    <div class="text-secondary">Mastered</div>
                                </div>
                            </div>

                            @if($lang->due_count > 0)
                                <a href="{{ route('flashcards.review', ['language' => $lang->target_language]) }}"
                                   class="btn btn-primary w-100 mt-3">
                                    <i class="bi bi-play-fill me-1"></i>Review {{ $lang->due_count }} cards
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

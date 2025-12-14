@extends('layout')

@section('title', 'Request Details - LangConnect')

@section('content')
<div class="container my-4" style="max-width: 800px;">
    <div class="mb-4">
        @if($isOwner)
            <a href="{{ route('learning-requests.index') }}" class="text-decoration-none" style="color: var(--primary-color);">
                <i class="bi bi-arrow-left"></i> Back to My Requests
            </a>
        @else
            <a href="{{ route('learning-requests.browse') }}" class="text-decoration-none" style="color: var(--primary-color);">
                <i class="bi bi-arrow-left"></i> Back to Browse Requests
            </a>
        @endif
    </div>

    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-5">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h3 class="fw-bold mb-2" style="color: var(--text-primary);">
                        {{ $learningRequest->language->flag_emoji }} {{ $learningRequest->language->name }} Learning Request
                    </h3>
                    <div class="mb-2">
                        <span class="badge" style="background: var(--bg-tertiary); color: var(--text-primary); font-weight: 500; font-size: 0.9rem;">
                            {{ ucfirst($learningRequest->topic_category) }}
                        </span>
                        <span class="badge bg-info text-dark ms-2" style="font-size: 0.9rem;">
                            Level: {{ $learningRequest->proficiency_level }}
                        </span>
                    </div>
                </div>
                <div>
                    @if($learningRequest->status === 'pending')
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="bi bi-clock"></i> Pending
                        </span>
                    @elseif($learningRequest->status === 'matched')
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-check-circle"></i> Matched
                        </span>
                    @elseif($learningRequest->status === 'completed')
                        <span class="badge bg-primary px-3 py-2">
                            <i class="bi bi-check-all"></i> Completed
                        </span>
                    @else
                        <span class="badge bg-secondary px-3 py-2">
                            <i class="bi bi-x-circle"></i> Cancelled
                        </span>
                    @endif
                </div>
            </div>

            @if($learningRequest->topic_name)
                <div class="mb-3">
                    <h6 class="fw-semibold" style="color: var(--text-secondary);">Specific Topic</h6>
                    <p class="mb-0" style="color: var(--text-primary);">
                        <i class="bi bi-tag"></i> {{ $learningRequest->topic_name }}
                    </p>
                </div>
            @endif

            <div class="mb-4">
                <h6 class="fw-semibold" style="color: var(--text-secondary);">What I want to learn</h6>
                <p class="mb-0" style="color: var(--text-primary); font-size: 1.05rem;">
                    {{ $learningRequest->specific_question }}
                </p>
            </div>

            <hr style="opacity: 0.1;">

            <div class="mb-4">
                <div class="small text-secondary">
                    <i class="bi bi-calendar"></i> Posted {{ $learningRequest->created_at->diffForHumans() }}
                    @if($learningRequest->matched_at)
                        <span class="ms-3">
                            <i class="bi bi-check-circle"></i> Matched {{ $learningRequest->matched_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>

            @if($learningRequest->matchedWithUser)
                <div class="alert" style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem;">
                    <h6 class="fw-semibold mb-2" style="color: var(--primary-color);">
                        <i class="bi bi-person-check"></i> Matched Helper
                    </h6>
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="mb-1 fw-semibold" style="color: var(--text-primary);">
                                {{ $learningRequest->matchedWithUser->name }}
                            </p>
                            @if($learningRequest->matchedWithUser->progress)
                                <div class="small text-secondary">
                                    <span class="me-3">
                                        <i class="bi bi-star-fill text-warning"></i> Level {{ $learningRequest->matchedWithUser->progress->level }}
                                    </span>
                                    <span class="me-3">
                                        <i class="bi bi-trophy-fill text-primary"></i> {{ $learningRequest->matchedWithUser->progress->karma_points }} Karma
                                    </span>
                                    <span>
                                        <i class="bi bi-people-fill text-success"></i> Helped {{ $learningRequest->matchedWithUser->progress->members_helped }} members
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('messages.show', $learningRequest->matchedWithUser) }}" class="btn btn-outline-primary">
                                <i class="bi bi-chat-dots"></i> Message {{ $learningRequest->matchedWithUser->name }}
                            </a>
                            <form method="POST" action="{{ route('sessions.start', $learningRequest) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-play-circle"></i> Start Session
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if($learningRequest->status === 'pending')
                <div class="d-flex gap-2">
                    @if($isOwner)
                        <form method="POST" action="{{ route('learning-requests.cancel', $learningRequest) }}" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to cancel this request?')">
                                <i class="bi bi-x-circle"></i> Cancel Request
                            </button>
                        </form>
                    @else
                        <!-- Non-owner view: Show requester info and accept button -->
                        <div class="alert alert-info w-100 mb-3">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-person"></i> Requester: {{ $learningRequest->user->name }}
                            </h6>
                            @if($learningRequest->user->progress)
                                <div class="small">
                                    Level {{ $learningRequest->user->progress->level }} •
                                    {{ $learningRequest->user->progress->karma_points }} Karma
                                </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('learning-requests.accept', $learningRequest) }}" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 btn-lg">
                                <i class="bi bi-check-circle"></i> Accept This Request
                            </button>
                        </form>
                        <a href="{{ route('messages.show', $learningRequest->user) }}" class="btn btn-outline-primary">
                            <i class="bi bi-chat-dots"></i> Message {{ $learningRequest->user->name }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if($potentialMatches->isNotEmpty())
        <div class="card mt-4 shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                    <i class="bi bi-search"></i> Potential Helpers
                </h6>
                <div class="small text-secondary mb-3">
                    Based on our matching algorithm, these users would be great helpers for your request:
                </div>
                <div class="list-group">
                    @foreach($potentialMatches as $match)
                        <div class="list-group-item border-0 mb-2" style="background: var(--bg-secondary); border-radius: 0.5rem;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold" style="color: var(--text-primary);">
                                        {{ $match['user']->name }}
                                    </div>
                                    @if($match['user']->progress)
                                        <div class="small text-secondary mt-1">
                                            Level {{ $match['user']->progress->level }} •
                                            {{ $match['user']->progress->karma_points }} Karma •
                                            Helped {{ $match['user']->progress->members_helped }} members
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="small fw-semibold" style="color: var(--primary-color);">
                                        Match Score: {{ number_format($match['score'], 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

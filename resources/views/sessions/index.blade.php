@extends('layout')

@section('title', 'My Sessions - LangConnect')

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="container my-4">
    <h2 class="fw-bold mb-4" style="color: var(--text-primary);">My Practice Sessions</h2>

    @if($sessions->isEmpty())
        <div class="card shadow-sm text-center py-5" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body">
                <i class="bi bi-chat-dots" style="font-size: 3rem; color: var(--text-secondary);"></i>
                <h5 class="mt-3 mb-2" style="color: var(--text-primary);">No sessions yet</h5>
                <p class="text-secondary mb-4">Start practicing by accepting learning requests!</p>
                <a href="{{ route('learning-requests.browse') }}" class="btn btn-primary">
                    <i class="bi bi-search"></i> Browse Requests
                </a>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($sessions as $session)
                @php
                    $partner = $session->user1_id === Auth::id() ? $session->user2 : $session->user1;
                    $isHelper = $session->user2_id === Auth::id();
                @endphp

                <div class="col-12">
                    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="fw-bold mb-2" style="color: var(--text-primary);">
                                        {{ $session->language->flag_emoji }} {{ $session->language->name }}
                                        @if($isHelper)
                                            <span class="badge bg-success ms-2">Helper</span>
                                        @else
                                            <span class="badge bg-info ms-2">Learner</span>
                                        @endif
                                    </h5>
                                    <p class="text-secondary mb-2">
                                        <i class="bi bi-tag"></i> {{ $session->topic }}
                                    </p>
                                    <div class="small text-secondary">
                                        <i class="bi bi-person-circle"></i> With {{ $partner->name }}
                                        @if($session->duration_minutes)
                                            <span class="ms-3">
                                                <i class="bi bi-clock"></i> {{ $session->duration_minutes }} minutes
                                            </span>
                                        @endif
                                        <span class="ms-3">
                                            <i class="bi bi-calendar"></i> {{ $session->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($session->status === 'in_progress')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-play-circle-fill"></i> In Progress
                                        </span>
                                    @elseif($session->status === 'completed')
                                        <span class="badge bg-primary px-3 py-2">
                                            <i class="bi bi-check-circle-fill"></i> Completed
                                        </span>
                                    @elseif($session->status === 'scheduled')
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bi bi-clock-fill"></i> Scheduled
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2">
                                            {{ ucfirst($session->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                @if($session->status === 'in_progress')
                                    <a href="{{ route('sessions.show', $session) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-play-circle"></i> Continue Session
                                    </a>
                                @elseif($session->status === 'completed')
                                    <a href="{{ route('sessions.show', $session) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                    @php
                                        $hasReviewed = \App\Models\SessionReview::where('session_id', $session->id)
                                            ->where('reviewer_id', Auth::id())
                                            ->exists();
                                    @endphp
                                    @if(!$hasReviewed)
                                        <a href="{{ route('sessions.review', $session) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-star"></i> Leave Review
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('sessions.show', $session) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $sessions->links() }}
        </div>
    @endif
</div>
@endsection

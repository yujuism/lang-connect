@extends('layout')

@section('title', 'My Learning Requests - LangConnect')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color: var(--text-primary);">My Learning Requests</h2>
        <a href="{{ route('learning-requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Request
        </a>
    </div>

    @if($requests->isEmpty())
        <div class="card shadow-sm text-center py-5" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body">
                <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-secondary);"></i>
                <h5 class="mt-3 mb-2" style="color: var(--text-primary);">No requests yet</h5>
                <p class="text-secondary mb-4">Start your learning journey by creating your first request!</p>
                <a href="{{ route('learning-requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Request
                </a>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($requests as $request)
                <div class="col-12">
                    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-2" style="color: var(--text-primary);">
                                        {{ $request->language->flag_emoji }} {{ $request->language->name }}
                                        <span class="badge" style="background: var(--bg-tertiary); color: var(--text-primary); font-weight: 500;">
                                            {{ ucfirst($request->topic_category) }}
                                        </span>
                                    </h5>
                                    @if($request->topic_name)
                                        <p class="text-secondary mb-2">
                                            <i class="bi bi-tag"></i> {{ $request->topic_name }}
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                    @elseif($request->status === 'matched')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Matched
                                        </span>
                                    @elseif($request->status === 'completed')
                                        <span class="badge bg-primary">
                                            <i class="bi bi-check-all"></i> Completed
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Cancelled
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="mb-3" style="color: var(--text-primary);">{{ $request->specific_question }}</p>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-secondary">
                                    <i class="bi bi-calendar"></i> {{ $request->created_at->diffForHumans() }}
                                    @if($request->matchedWithUser)
                                        <span class="ms-3">
                                            <i class="bi bi-person"></i> Matched with {{ $request->matchedWithUser->name }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('learning-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                    @if($request->status === 'pending')
                                        <form method="POST" action="{{ route('learning-requests.cancel', $request) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this request?')">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

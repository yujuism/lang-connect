@extends('layout')

@section('title', 'Browse Requests - LangConnect')

@section('content')
<div class="container my-4">
    <div class="mb-4">
        <h2 class="fw-bold" style="color: var(--text-primary);">Browse Learning Requests</h2>
        <p class="text-secondary">Help others learn by accepting their requests</p>
    </div>

    @if($requests->isEmpty())
        <div class="card shadow-sm text-center py-5" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body">
                <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-secondary);"></i>
                <h5 class="mt-3 mb-2" style="color: var(--text-primary);">No pending requests</h5>
                <p class="text-secondary">Check back later for new learning requests!</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($requests as $request)
                <div class="col-12">
                    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="fw-bold mb-0 me-3" style="color: var(--text-primary);">
                                            {{ $request->language->flag_emoji }} {{ $request->language->name }}
                                        </h5>
                                        <span class="badge" style="background: var(--bg-tertiary); color: var(--text-primary); font-weight: 500;">
                                            {{ ucfirst($request->topic_category) }}
                                        </span>
                                        <span class="badge bg-info text-dark ms-2">
                                            {{ $request->proficiency_level }}
                                        </span>
                                    </div>
                                    @if($request->topic_name)
                                        <p class="text-secondary mb-0">
                                            <i class="bi bi-tag"></i> {{ $request->topic_name }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <p class="mb-3" style="color: var(--text-primary);">{{ $request->specific_question }}</p>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-secondary">
                                    <i class="bi bi-person-circle"></i> {{ $request->user->name }}
                                    <span class="ms-3">
                                        <i class="bi bi-calendar"></i> {{ $request->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('learning-requests.accept', $request) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-hand-thumbs-up"></i> Accept & Help
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection

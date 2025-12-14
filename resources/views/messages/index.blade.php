@extends('layout')

@section('title', 'Messages')

@section('content')
<div class="container my-4" style="max-width: 1200px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color: var(--text-primary);">
            <i class="bi bi-chat-dots"></i> Messages
        </h2>
    </div>

    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-0">
            @forelse($conversations as $conversation)
                <a href="{{ route('messages.show', $conversation->user) }}"
                   class="d-flex align-items-center p-4 text-decoration-none border-bottom hover-bg"
                   style="color: var(--text-primary); transition: background 0.2s;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; font-size: 1.5rem; font-weight: bold; flex-shrink: 0;">
                        {{ substr($conversation->user->name, 0, 1) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold mb-1">{{ $conversation->user->name }}</div>
                                <div class="small text-secondary">
                                    {{ \Carbon\Carbon::parse($conversation->last_message_at)->diffForHumans() }}
                                </div>
                            </div>
                            @if($conversation->unread_count > 0)
                                <span class="badge rounded-pill"
                                      style="background: var(--primary-color); color: white;">
                                    {{ $conversation->unread_count }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5">
                    <div class="mb-3" style="font-size: 4rem; color: var(--text-tertiary);">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h5 style="color: var(--text-secondary);">No messages yet</h5>
                    <p class="text-secondary">Start a conversation by visiting someone's profile</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.hover-bg:hover {
    background: var(--bg-secondary) !important;
}
</style>
@endsection

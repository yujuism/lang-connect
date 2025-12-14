@extends('layout')

@section('title', 'Notifications')

@section('content')
<div class="container my-4" style="max-width: 800px;">
    <h2 class="fw-bold mb-4" style="color: var(--text-primary);">
        <i class="bi bi-bell"></i> Notifications
    </h2>

    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-0">
            @forelse($notifications as $notification)
                <div class="p-4 border-bottom {{ $notification->is_read ? '' : 'bg-light' }}">
                    <div class="d-flex">
                        <div class="me-3">
                            @if($notification->type === 'new_message')
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px; background: var(--primary-light); color: var(--primary-color);">
                                    <i class="bi bi-chat-dots-fill"></i>
                                </div>
                            @elseif($notification->type === 'achievement_unlocked')
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px; background: #fef3c7; color: #f59e0b;">
                                    <i class="bi bi-trophy-fill"></i>
                                </div>
                            @elseif($notification->type === 'learning_request' || $notification->type === 'request_matched')
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px; background: #dbeafe; color: #3b82f6;">
                                    <i class="bi bi-person-raised-hand"></i>
                                </div>
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px; background: var(--bg-tertiary); color: var(--text-secondary);">
                                    <i class="bi bi-bell-fill"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold mb-1" style="color: var(--text-primary);">
                                {{ $notification->title }}
                            </div>
                            <div class="text-secondary mb-2">
                                {{ $notification->message }}
                            </div>

                            @if($notification->type === 'learning_request' && isset($notification->data['request_id']))
                                <a href="{{ route('learning-requests.browse') }}" class="btn btn-sm btn-primary">
                                    View Request
                                </a>
                            @elseif($notification->type === 'request_matched' && isset($notification->data['request_id']))
                                <a href="{{ route('learning-requests.show', $notification->data['request_id']) }}" class="btn btn-sm btn-primary">
                                    View Match
                                </a>
                            @elseif($notification->type === 'new_message' && isset($notification->data['user_id']))
                                <a href="{{ route('messages.show', $notification->data['user_id']) }}" class="btn btn-sm btn-primary">
                                    View Message
                                </a>
                            @endif

                            <div class="small text-secondary mt-2">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <div class="mb-3" style="font-size: 4rem; color: var(--text-tertiary);">
                        <i class="bi bi-bell-slash"></i>
                    </div>
                    <h5 style="color: var(--text-secondary);">No notifications yet</h5>
                    <p class="text-secondary">We'll notify you when something happens</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

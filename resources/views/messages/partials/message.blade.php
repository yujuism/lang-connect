@php
    use Illuminate\Support\Facades\Auth;
    $isOwnMessage = $message->sender_id === Auth::id();
@endphp

<div class="mb-3 d-flex {{ $isOwnMessage ? 'justify-content-end' : 'justify-content-start' }}" data-message-id="{{ $message->id }}">
    <div style="max-width: 70%;">
        <div class="mb-1">
            <span class="small fw-semibold" style="color: var(--text-secondary);">
                {{ $isOwnMessage ? 'You' : $message->sender->name }}
            </span>
        </div>
        <div class="p-3" style="background: {{ $isOwnMessage ? 'var(--primary-color)' : 'var(--bg-secondary)' }};
             color: {{ $isOwnMessage ? 'white' : 'var(--text-primary)' }};
             border-radius: 1rem;">
            {{ $message->message }}
        </div>
        <div class="small text-secondary mt-1">
            {{ $message->created_at->format('g:i A') }}
        </div>
    </div>
</div>

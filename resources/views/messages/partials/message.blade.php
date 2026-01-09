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
        <div class="small text-secondary mt-1 d-flex align-items-center gap-1">
            {{ $message->created_at->format('g:i A') }}
            @if($isOwnMessage)
                <span class="read-status" data-read="{{ $message->is_read ? 'true' : 'false' }}"
                      style="color: {{ $message->is_read ? '#22c55e' : 'inherit' }};">
                    @if($message->is_read)
                        <i class="bi bi-check2-all"></i>
                    @else
                        <i class="bi bi-check2"></i>
                    @endif
                </span>
            @endif
        </div>
    </div>
</div>

@extends('layout')

@section('title', 'Review Session - LangConnect')

@section('content')
<div class="container my-4" style="max-width: 700px;">
    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; font-size: 2rem; font-weight: bold;">
                    {{ substr($reviewedUser->name, 0, 1) }}
                </div>
                <h3 class="fw-bold mb-2" style="color: var(--text-primary);">Rate Your Session</h3>
                <p class="text-secondary">How was your practice session with {{ $reviewedUser->name }}?</p>
            </div>

            <form method="POST" action="{{ route('reviews.store', $session) }}">
                @csrf

                <!-- Overall Rating -->
                <div class="mb-4">
                    <label class="form-label fw-semibold d-block text-center" style="color: var(--text-primary);">Overall Experience</label>
                    <div class="d-flex justify-content-center gap-2 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <input type="radio" class="btn-check" name="overall_rating" id="overall_{{ $i }}" value="{{ $i }}" required>
                            <label class="btn btn-outline-warning" for="overall_{{ $i }}" style="font-size: 1.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-star-fill"></i>
                            </label>
                        @endfor
                    </div>
                    @error('overall_rating')
                        <div class="text-danger small text-center">{{ $message }}</div>
                    @enderror
                </div>

                <hr style="opacity: 0.1;" class="my-4">

                <!-- Detailed Ratings -->
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3" style="color: var(--text-primary);">Detailed Ratings</h6>

                    <!-- Helpfulness -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-secondary);">
                            <i class="bi bi-hand-thumbs-up"></i> Helpfulness
                        </label>
                        <div class="d-flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <input type="radio" class="btn-check" name="helpfulness_rating" id="helpful_{{ $i }}" value="{{ $i }}" required>
                                <label class="btn btn-outline-primary btn-sm" for="helpful_{{ $i }}">{{ $i }}</label>
                            @endfor
                        </div>
                    </div>

                    <!-- Patience -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-secondary);">
                            <i class="bi bi-heart"></i> Patience
                        </label>
                        <div class="d-flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <input type="radio" class="btn-check" name="patience_rating" id="patience_{{ $i }}" value="{{ $i }}" required>
                                <label class="btn btn-outline-primary btn-sm" for="patience_{{ $i }}">{{ $i }}</label>
                            @endfor
                        </div>
                    </div>

                    <!-- Clarity -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-secondary);">
                            <i class="bi bi-chat-text"></i> Clarity
                        </label>
                        <div class="d-flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <input type="radio" class="btn-check" name="clarity_rating" id="clarity_{{ $i }}" value="{{ $i }}" required>
                                <label class="btn btn-outline-primary btn-sm" for="clarity_{{ $i }}">{{ $i }}</label>
                            @endfor
                        </div>
                    </div>

                    <!-- Engagement -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-secondary);">
                            <i class="bi bi-lightning"></i> Engagement
                        </label>
                        <div class="d-flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <input type="radio" class="btn-check" name="engagement_rating" id="engagement_{{ $i }}" value="{{ $i }}" required>
                                <label class="btn btn-outline-primary btn-sm" for="engagement_{{ $i }}">{{ $i }}</label>
                            @endfor
                        </div>
                    </div>
                </div>

                <hr style="opacity: 0.1;" class="my-4">

                <!-- Topics Rated Well -->
                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-bookmark-check"></i> What did they excel at?
                    </label>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="topics_rated_well[]" value="grammar" id="topic_grammar">
                            <label class="form-check-label" for="topic_grammar">Grammar</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="topics_rated_well[]" value="pronunciation" id="topic_pronunciation">
                            <label class="form-check-label" for="topic_pronunciation">Pronunciation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="topics_rated_well[]" value="vocabulary" id="topic_vocabulary">
                            <label class="form-check-label" for="topic_vocabulary">Vocabulary</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="topics_rated_well[]" value="conversation" id="topic_conversation">
                            <label class="form-check-label" for="topic_conversation">Conversation</label>
                        </div>
                    </div>
                </div>

                <!-- Comment -->
                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-chat-left-quote"></i> Your Feedback (Optional)
                    </label>
                    <textarea name="comment" class="form-control" rows="4"
                              placeholder="Share your experience... What went well? Any suggestions?"
                              style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);"></textarea>
                    <div class="form-text text-secondary small">
                        Your feedback helps {{ $reviewedUser->name }} improve and helps others choose great partners.
                    </div>
                    @error('comment')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Public Review -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_public" id="is_public" checked>
                        <label class="form-check-label" for="is_public">
                            <span class="fw-semibold" style="color: var(--text-primary);">Make this review public</span>
                            <div class="small text-secondary">Public reviews appear on {{ $reviewedUser->name }}'s profile</div>
                        </label>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" style="padding: 0.75rem; font-weight: 600;">
                        <i class="bi bi-send"></i> Submit Review
                    </button>
                    <a href="{{ route('sessions.index') }}" class="btn btn-outline-secondary" style="padding: 0.75rem;">
                        Skip
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card mt-4 shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color); background: var(--bg-secondary);">
        <div class="card-body p-4">
            <h6 class="fw-semibold mb-3" style="color: var(--primary-color);">
                <i class="bi bi-trophy"></i> Karma Points Reward
            </h6>
            <div class="small text-secondary">
                <div class="mb-1">⭐⭐⭐⭐⭐ (5 stars) = +20 karma</div>
                <div class="mb-1">⭐⭐⭐⭐ (4 stars) = +15 karma</div>
                <div class="mb-1">⭐⭐⭐ (3 stars) = +10 karma</div>
                <div class="mb-1">⭐⭐ (2 stars) = +5 karma</div>
                <div>⭐ (1 star) = +0 karma</div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Star rating hover effect */
    .btn-check:checked + .btn-outline-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: white;
    }
    .btn-outline-warning:hover {
        background-color: #ffc107;
        border-color: #ffc107;
        color: white;
    }

    /* Number rating hover effect */
    .btn-check:checked + .btn-outline-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
</style>
@endsection

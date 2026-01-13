@extends('layout')

@section('title', 'Review Flashcards - LangConnect')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: var(--text-primary);">
                <i class="bi bi-card-text"></i> Review
            </h2>
            <p class="text-secondary mb-0">
                @if($language)
                    Reviewing {{ strtoupper($language) }} cards
                @else
                    Reviewing all languages
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Streak Counter -->
            <div id="streak-counter" class="d-flex align-items-center gap-2" style="display: none !important;">
                <i class="bi bi-fire text-warning"></i>
                <span class="fw-bold" id="streak-number">0</span>
            </div>
            <a href="{{ route('flashcards.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i> Exit
            </a>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="mb-4">
        <div class="d-flex justify-content-between small text-secondary mb-1">
            <span>Progress</span>
            <span id="progress-text">0 / {{ $totalDue }}</span>
        </div>
        <div class="progress" style="height: 8px; border-radius: 0.5rem;">
            <div id="progress-bar" class="progress-bar bg-success" style="width: 0%; transition: width 0.3s;"></div>
        </div>
    </div>

    <!-- Flashcard Container -->
    <div class="d-flex justify-content-center">
        <div id="flashcard-container" class="flashcard-container" onclick="flipCard()">
            <div class="flashcard" id="flashcard">
                <div class="flashcard-front">
                    <div class="card-lang-badge" id="front-lang"></div>
                    <div class="mastery-indicator" id="mastery-indicator"></div>
                    <div class="card-content" id="front-content"></div>
                    <div class="tap-hint">
                        <span class="d-none d-sm-inline"><kbd>Space</kbd> to reveal</span>
                        <span class="d-sm-none">Tap to reveal</span>
                    </div>
                </div>
                <div class="flashcard-back">
                    <div class="card-lang-badge" id="back-lang"></div>
                    <div class="card-content" id="back-content"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Buttons (shown after flip) -->
    <div id="rating-buttons" class="text-center mt-4" style="display: none;">
        <p class="text-secondary mb-3">How well did you remember?</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
            <button class="btn btn-danger rating-btn px-4" onclick="submitAnswer(0)" data-key="1">
                <i class="bi bi-x-circle"></i> Forgot
                <span class="d-none d-sm-inline ms-1 opacity-75">[1]</span>
            </button>
            <button class="btn btn-warning rating-btn px-4" onclick="submitAnswer(2)" data-key="2">
                <i class="bi bi-question-circle"></i> Hard
                <span class="d-none d-sm-inline ms-1 opacity-75">[2]</span>
            </button>
            <button class="btn btn-info rating-btn px-4" onclick="submitAnswer(3)" data-key="3">
                <i class="bi bi-check-circle"></i> Good
                <span class="d-none d-sm-inline ms-1 opacity-75">[3]</span>
            </button>
            <button class="btn btn-success rating-btn px-4" onclick="submitAnswer(5)" data-key="4">
                <i class="bi bi-star-fill"></i> Easy
                <span class="d-none d-sm-inline ms-1 opacity-75">[4]</span>
            </button>
        </div>
        <!-- Next Review Preview -->
        <div id="review-preview" class="text-secondary small" style="display: none;">
            <i class="bi bi-clock me-1"></i>
            Next review: <span id="next-review-text"></span>
        </div>
    </div>

    <!-- Done Message -->
    <div id="done-message" class="text-center" style="display: none;">
        <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body p-5">
                <i class="bi bi-trophy-fill text-warning display-1 mb-3"></i>
                <h4 class="fw-bold" style="color: var(--text-primary);">Review Complete!</h4>
                <p class="text-secondary mb-2" id="stats-message"></p>
                <div id="streak-message" class="mb-4"></div>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('flashcards.index') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Back to Flashcards
                    </a>
                    @if($language)
                    <a href="{{ route('flashcards.review', ['language' => $language]) }}" class="btn btn-outline-primary btn-lg" id="review-again-btn" style="display: none;">
                        <i class="bi bi-arrow-repeat me-2"></i>Review Again
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .flashcard-container {
        perspective: 1000px;
        width: 100%;
        max-width: 500px;
        height: 300px;
        cursor: pointer;
    }

    .flashcard {
        width: 100%;
        height: 100%;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 0.5s cubic-bezier(0.4, 0.0, 0.2, 1);
    }

    .flashcard.flipped {
        transform: rotateY(180deg);
    }

    .flashcard.slide-out {
        animation: slideOut 0.3s ease-in forwards;
    }

    .flashcard.slide-in {
        animation: slideIn 0.3s ease-out forwards;
    }

    @keyframes slideOut {
        0% { transform: translateX(0) rotateY(180deg); opacity: 1; }
        100% { transform: translateX(100px) rotateY(180deg); opacity: 0; }
    }

    @keyframes slideIn {
        0% { transform: translateX(-100px) rotateY(0); opacity: 0; }
        100% { transform: translateX(0) rotateY(0); opacity: 1; }
    }

    .flashcard-front, .flashcard-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }

    .flashcard-front {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark, #4a5bc7));
        color: white;
    }

    .flashcard-back {
        background: white;
        color: var(--text-primary);
        transform: rotateY(180deg);
        border: 2px solid var(--border-color);
    }

    .card-lang-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .flashcard-back .card-lang-badge {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }

    .mastery-indicator {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        gap: 3px;
    }

    .mastery-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
    }

    .mastery-dot.filled {
        background: rgba(255,255,255,0.9);
    }

    .card-content {
        font-size: 1.75rem;
        font-weight: 600;
        text-align: center;
        word-break: break-word;
        max-height: 200px;
        overflow-y: auto;
    }

    .tap-hint {
        position: absolute;
        bottom: 1rem;
        font-size: 0.875rem;
        opacity: 0.7;
    }

    kbd {
        background: rgba(255,255,255,0.2);
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-family: inherit;
        font-size: 0.8rem;
    }

    .rating-btn {
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .rating-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .rating-btn:active {
        transform: translateY(0);
    }

    #streak-counter {
        background: var(--bg-secondary);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 1.1rem;
    }

    @media (max-width: 576px) {
        .flashcard-container {
            height: 250px;
        }
        .card-content {
            font-size: 1.25rem;
        }
        .rating-btn {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem;
        }
    }
</style>

<script>
    // Card data from server
    const initialCards = @json($cards);
    let cards = [...initialCards];
    let currentCardIndex = 0;
    let reviewed = 0;
    let isFlipped = false;
    let streak = 0;
    let maxStreak = 0;
    const totalCards = {{ $totalDue }};
    const language = @json($language);

    function showCard(card) {
        document.getElementById('front-content').textContent = card.front;
        document.getElementById('back-content').textContent = card.back;
        document.getElementById('front-lang').textContent = card.target_language;
        document.getElementById('back-lang').textContent = 'Answer';

        // Show mastery indicator
        const masteryContainer = document.getElementById('mastery-indicator');
        masteryContainer.innerHTML = '';
        for (let i = 0; i < 5; i++) {
            const dot = document.createElement('div');
            dot.className = 'mastery-dot' + (i < card.mastery_level ? ' filled' : '');
            masteryContainer.appendChild(dot);
        }

        // Reset card state
        document.getElementById('flashcard').classList.remove('flipped', 'slide-out', 'slide-in');
        document.getElementById('rating-buttons').style.display = 'none';
        document.getElementById('review-preview').style.display = 'none';
        isFlipped = false;
    }

    function flipCard() {
        if (!cards[currentCardIndex]) return;

        const flashcard = document.getElementById('flashcard');
        flashcard.classList.toggle('flipped');
        isFlipped = !isFlipped;

        if (isFlipped) {
            document.getElementById('rating-buttons').style.display = 'block';
        }
    }

    async function submitAnswer(quality) {
        const card = cards[currentCardIndex];
        if (!card) return;

        // Disable buttons during request
        const buttons = document.querySelectorAll('#rating-buttons button');
        buttons.forEach(b => b.disabled = true);

        try {
            const response = await fetch(`/flashcards/${card.id}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ quality }),
            });

            if (!response.ok) throw new Error('Failed to save answer');

            const result = await response.json();

            // Update streak
            if (quality >= 3) {
                streak++;
                maxStreak = Math.max(maxStreak, streak);
                updateStreakDisplay();
            } else {
                streak = 0;
                document.getElementById('streak-counter').style.display = 'none';
            }

            // Show next review preview
            showNextReviewPreview(result.interval_days);

            reviewed++;
            updateProgress();

            // Animate card out
            const flashcard = document.getElementById('flashcard');
            flashcard.classList.add('slide-out');

            await new Promise(resolve => setTimeout(resolve, 250));

            // Move to next card
            currentCardIndex++;

            if (currentCardIndex >= cards.length) {
                await fetchMoreCards();
            }

            if (currentCardIndex < cards.length) {
                flashcard.classList.remove('slide-out');
                flashcard.classList.add('slide-in');
                showCard(cards[currentCardIndex]);
                setTimeout(() => flashcard.classList.remove('slide-in'), 300);
            } else {
                showDone();
            }

        } catch (error) {
            console.error('Error:', error);
            alert('Failed to save your answer. Please try again.');
        } finally {
            buttons.forEach(b => b.disabled = false);
        }
    }

    function showNextReviewPreview(intervalDays) {
        const preview = document.getElementById('review-preview');
        const text = document.getElementById('next-review-text');

        if (intervalDays === 0) {
            text.textContent = 'in a few minutes';
        } else if (intervalDays === 1) {
            text.textContent = 'tomorrow';
        } else if (intervalDays < 7) {
            text.textContent = `in ${intervalDays} days`;
        } else if (intervalDays < 30) {
            const weeks = Math.round(intervalDays / 7);
            text.textContent = `in ${weeks} week${weeks > 1 ? 's' : ''}`;
        } else {
            const months = Math.round(intervalDays / 30);
            text.textContent = `in ${months} month${months > 1 ? 's' : ''}`;
        }

        preview.style.display = 'block';
    }

    function updateStreakDisplay() {
        if (streak >= 3) {
            const counter = document.getElementById('streak-counter');
            counter.style.display = 'flex !important';
            counter.style.cssText = 'display: flex !important;';
            document.getElementById('streak-number').textContent = streak;
        }
    }

    async function fetchMoreCards() {
        const excludeIds = cards.map(c => c.id);
        const url = new URL('/flashcards/next-card', window.location.origin);
        if (language) url.searchParams.set('language', language);
        excludeIds.forEach(id => url.searchParams.append('exclude[]', id));

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (!data.done && data.card) {
                cards.push(data.card);
            }
        } catch (error) {
            console.error('Error fetching more cards:', error);
        }
    }

    function updateProgress() {
        const percentage = (reviewed / totalCards) * 100;
        document.getElementById('progress-bar').style.width = percentage + '%';
        document.getElementById('progress-text').textContent = `${reviewed} / ${totalCards}`;
    }

    function showDone() {
        document.getElementById('flashcard-container').style.display = 'none';
        document.getElementById('rating-buttons').style.display = 'none';
        document.getElementById('done-message').style.display = 'block';
        document.getElementById('stats-message').textContent = `You reviewed ${reviewed} cards. Great job!`;

        if (maxStreak >= 3) {
            document.getElementById('streak-message').innerHTML =
                `<span class="text-warning"><i class="bi bi-fire"></i> Best streak: ${maxStreak} cards in a row!</span>`;
        }
    }

    // Initialize
    if (cards.length > 0) {
        showCard(cards[0]);
    } else {
        showDone();
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ignore if typing in an input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        if (e.key === ' ' || e.key === 'Enter') {
            e.preventDefault();
            if (!isFlipped) {
                flipCard();
            }
        } else if (isFlipped) {
            switch(e.key) {
                case '1': submitAnswer(0); break;
                case '2': submitAnswer(2); break;
                case '3': submitAnswer(3); break;
                case '4': submitAnswer(5); break;
            }
        }
    });
</script>
@endsection

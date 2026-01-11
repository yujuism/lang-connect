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
        <a href="{{ route('flashcards.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> Exit
        </a>
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
                    <div class="card-content" id="front-content"></div>
                    <div class="tap-hint">Tap to reveal</div>
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
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <button class="btn btn-danger px-4" onclick="submitAnswer(0)">
                <i class="bi bi-x-circle"></i> Forgot
            </button>
            <button class="btn btn-warning px-4" onclick="submitAnswer(2)">
                <i class="bi bi-question-circle"></i> Hard
            </button>
            <button class="btn btn-info px-4" onclick="submitAnswer(3)">
                <i class="bi bi-check-circle"></i> Good
            </button>
            <button class="btn btn-success px-4" onclick="submitAnswer(5)">
                <i class="bi bi-star-fill"></i> Easy
            </button>
        </div>
    </div>

    <!-- Done Message -->
    <div id="done-message" class="text-center" style="display: none;">
        <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body p-5">
                <i class="bi bi-trophy-fill text-warning display-1 mb-3"></i>
                <h4 class="fw-bold" style="color: var(--text-primary);">Review Complete!</h4>
                <p class="text-secondary mb-4" id="stats-message"></p>
                <a href="{{ route('flashcards.index') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>Back to Flashcards
                </a>
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
        transition: transform 0.6s;
    }

    .flashcard.flipped {
        transform: rotateY(180deg);
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
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .flashcard-front {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
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

    .card-content {
        font-size: 1.75rem;
        font-weight: 600;
        text-align: center;
        word-break: break-word;
    }

    .tap-hint {
        position: absolute;
        bottom: 1rem;
        font-size: 0.875rem;
        opacity: 0.7;
    }

    @media (max-width: 576px) {
        .flashcard-container {
            height: 250px;
        }
        .card-content {
            font-size: 1.25rem;
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
    const totalCards = {{ $totalDue }};
    const language = @json($language);

    function showCard(card) {
        document.getElementById('front-content').textContent = card.front;
        document.getElementById('back-content').textContent = card.back;
        document.getElementById('front-lang').textContent = card.target_language;
        document.getElementById('back-lang').textContent = card.native_language;

        // Reset card state
        document.getElementById('flashcard').classList.remove('flipped');
        document.getElementById('rating-buttons').style.display = 'none';
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

            reviewed++;
            updateProgress();

            // Move to next card
            currentCardIndex++;

            if (currentCardIndex >= cards.length) {
                // Try to fetch more cards
                await fetchMoreCards();
            }

            if (currentCardIndex < cards.length) {
                showCard(cards[currentCardIndex]);
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
    }

    // Initialize
    if (cards.length > 0) {
        showCard(cards[0]);
    } else {
        showDone();
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
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

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@if(session('new_achievements') || session('new_achievements_helper') || session('new_achievements_learner'))
    @php
        $achievements = session('new_achievements', []);
        if (session('new_achievements_helper') && Auth::check() && session('user_is_helper')) {
            $achievements = array_merge($achievements, session('new_achievements_helper', []));
        }
        if (session('new_achievements_learner') && Auth::check() && !session('user_is_helper')) {
            $achievements = array_merge($achievements, session('new_achievements_learner', []));
        }
    @endphp

    @if(!empty($achievements))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999; max-width: 400px;">
            @foreach($achievements as $index => $achievement)
                <div class="toast show mb-2 achievement-toast"
                     role="alert"
                     aria-live="assertive"
                     aria-atomic="true"
                     data-bs-delay="8000"
                     style="animation: slideInRight 0.5s ease-out {{ $index * 0.2 }}s both;">
                    <div class="toast-header"
                         style="background: linear-gradient(135deg,
                         @if($achievement->rarity === 'mythical') #9333ea, #7e22ce
                         @elseif($achievement->rarity === 'legendary') #f59e0b, #d97706
                         @elseif($achievement->rarity === 'epic') #8b5cf6, #7c3aed
                         @elseif($achievement->rarity === 'rare') #3b82f6, #2563eb
                         @elseif($achievement->rarity === 'uncommon') #10b981, #059669
                         @else #6b7280, #4b5563
                         @endif
                         );
                         color: white;
                         border: none;">
                        <strong class="me-auto">
                            <i class="bi bi-trophy-fill me-2"></i>
                            Achievement Unlocked!
                        </strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body p-4" style="background: white;">
                        <div class="d-flex align-items-start">
                            <div class="me-3" style="font-size: 3rem;">{{ $achievement->icon }}</div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1" style="color: var(--text-primary);">{{ $achievement->name }}</h6>
                                <p class="mb-2 small text-secondary">{{ $achievement->description }}</p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge"
                                          style="background:
                                          @if($achievement->rarity === 'mythical') linear-gradient(135deg, #9333ea, #7e22ce)
                                          @elseif($achievement->rarity === 'legendary') linear-gradient(135deg, #f59e0b, #d97706)
                                          @elseif($achievement->rarity === 'epic') linear-gradient(135deg, #8b5cf6, #7c3aed)
                                          @elseif($achievement->rarity === 'rare') linear-gradient(135deg, #3b82f6, #2563eb)
                                          @elseif($achievement->rarity === 'uncommon') linear-gradient(135deg, #10b981, #059669)
                                          @else linear-gradient(135deg, #6b7280, #4b5563)
                                          @endif;
                                          color: white;
                                          font-weight: 600;
                                          text-transform: uppercase;
                                          font-size: 0.7rem;">
                                        {{ ucfirst($achievement->rarity) }}
                                    </span>
                                    <span class="small">
                                        <i class="bi bi-trophy text-warning"></i>
                                        @php
                                            $karmaBonus = match($achievement->rarity) {
                                                'common' => 10,
                                                'uncommon' => 25,
                                                'rare' => 50,
                                                'epic' => 100,
                                                'legendary' => 200,
                                                'mythical' => 500,
                                                default => 10,
                                            };
                                        @endphp
                                        <strong>+{{ $karmaBonus }}</strong> Karma
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <style>
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            .achievement-toast {
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                border-radius: 0.75rem;
                overflow: hidden;
            }

            .achievement-toast .toast-header {
                border-bottom: none;
                font-weight: 600;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toasts = document.querySelectorAll('.achievement-toast');
                toasts.forEach(function(toastEl) {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                });
            });
        </script>
    @endif
@endif

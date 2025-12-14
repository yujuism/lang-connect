@extends('layout')

@section('title', $user->name . ' - Profile')

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="container my-4" style="max-width: 1000px;">
    <!-- Header Section -->
    <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-5">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; font-size: 3rem; font-weight: bold;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                </div>
                <div class="col">
                    <h2 class="fw-bold mb-2" style="color: var(--text-primary);">{{ $user->name }}</h2>
                    @if($user->bio)
                        <p class="text-secondary mb-3">{{ $user->bio }}</p>
                    @endif

                    @if($user->progress)
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px; background: var(--bg-tertiary);">
                                    <span class="fw-bold" style="color: var(--primary-color);">{{ $user->progress->level }}</span>
                                </div>
                                <div>
                                    <div class="small text-secondary">Level</div>
                                    <div class="fw-semibold" style="color: var(--text-primary);">{{ $user->progress->level }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px; background: var(--bg-tertiary);">
                                    <i class="bi bi-trophy-fill" style="color: #fbbf24;"></i>
                                </div>
                                <div>
                                    <div class="small text-secondary">Karma</div>
                                    <div class="fw-semibold" style="color: var(--text-primary);">{{ number_format($user->progress->karma_points) }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px; background: var(--bg-tertiary);">
                                    <i class="bi bi-people-fill" style="color: var(--primary-color);"></i>
                                </div>
                                <div>
                                    <div class="small text-secondary">Helped</div>
                                    <div class="fw-semibold" style="color: var(--text-primary);">{{ $user->progress->members_helped }} members</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px; background: var(--bg-tertiary);">
                                    <i class="bi bi-clock-fill" style="color: #10b981;"></i>
                                </div>
                                <div>
                                    <div class="small text-secondary">Hours</div>
                                    <div class="fw-semibold" style="color: var(--text-primary);">{{ number_format($user->progress->contribution_hours, 1) }}h</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(Auth::id() === $user->id)
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                    @else
                        <a href="{{ route('messages.show', $user) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-chat-dots"></i> Send Message
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-4">
            <!-- Languages Card -->
            <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-globe"></i> Languages
                    </h6>
                    @if($user->languages->isNotEmpty())
                        <div class="d-flex flex-column gap-3">
                            @foreach($user->languages as $language)
                                <div class="d-flex align-items-center">
                                    <div class="me-2" style="font-size: 1.5rem;">{{ $language->flag_emoji }}</div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold" style="color: var(--text-primary);">{{ $language->name }}</div>
                                        <div class="small text-secondary">{{ $language->pivot->proficiency_level }}</div>
                                    </div>
                                    @if($language->pivot->can_help)
                                        <span class="badge bg-success">Can Help</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-secondary small mb-0">No languages added yet</p>
                    @endif
                </div>
            </div>

            <!-- Level Progression Card -->
            @if($user->progress)
                <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                            <i class="bi bi-bar-chart-fill"></i> Level Progression
                        </h6>
                        <x-level-progress :progress="$user->progress" :showDetails="Auth::id() === $user->id" />
                    </div>
                </div>
            @endif

            <!-- Recent Activity Card -->
            <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                        <i class="bi bi-activity"></i> Recent Activity
                    </h6>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small">Sessions this month</span>
                        <span class="fw-semibold" style="color: var(--primary-color);">{{ $recentSessionsCount }}</span>
                    </div>
                    @if($user->progress)
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-secondary small">Total sessions</span>
                            <span class="fw-semibold" style="color: var(--primary-color);">{{ $user->progress->total_sessions }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Ratings Card -->
            @if($avgRatings && $avgRatings->total_reviews > 0)
                <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                            <i class="bi bi-star-fill text-warning"></i> Ratings
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-center p-3" style="background: var(--bg-secondary); border-radius: 0.75rem;">
                                    <div class="display-4 fw-bold mb-1" style="color: var(--primary-color);">
                                        {{ number_format($avgRatings->avg_overall, 1) }}
                                    </div>
                                    <div class="mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star-fill" style="color: {{ $i <= round($avgRatings->avg_overall) ? '#fbbf24' : '#d1d5db' }};"></i>
                                        @endfor
                                    </div>
                                    <div class="small text-secondary">{{ $avgRatings->total_reviews }} reviews</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-secondary">Helpfulness</span>
                                        <div>
                                            <span class="fw-semibold" style="color: var(--primary-color);">{{ number_format($avgRatings->avg_helpfulness, 1) }}</span>
                                            <span class="text-secondary small">/5</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-secondary">Patience</span>
                                        <div>
                                            <span class="fw-semibold" style="color: var(--primary-color);">{{ number_format($avgRatings->avg_patience, 1) }}</span>
                                            <span class="text-secondary small">/5</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-secondary">Clarity</span>
                                        <div>
                                            <span class="fw-semibold" style="color: var(--primary-color);">{{ number_format($avgRatings->avg_clarity, 1) }}</span>
                                            <span class="text-secondary small">/5</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-secondary">Engagement</span>
                                        <div>
                                            <span class="fw-semibold" style="color: var(--primary-color);">{{ number_format($avgRatings->avg_engagement, 1) }}</span>
                                            <span class="text-secondary small">/5</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reviews Card -->
            @if($user->reviewsReceived->isNotEmpty())
                <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                            <i class="bi bi-chat-left-quote"></i> Reviews
                        </h6>
                        <div class="d-flex flex-column gap-3">
                            @foreach($user->reviewsReceived as $review)
                                <div class="p-3" style="background: var(--bg-secondary); border-radius: 0.75rem;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-semibold mb-1" style="color: var(--text-primary);">{{ $review->reviewer->name }}</div>
                                            <div class="mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star-fill" style="color: {{ $i <= $review->overall_rating ? '#fbbf24' : '#d1d5db' }}; font-size: 0.875rem;"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if($review->session && $review->session->language)
                                                <div class="small text-secondary">{{ $review->session->language->flag_emoji }} {{ $review->session->language->name }}</div>
                                            @endif
                                            <div class="small text-secondary">{{ $review->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    @if($review->comment)
                                        <p class="mb-2 small" style="color: var(--text-primary);">{{ $review->comment }}</p>
                                    @endif
                                    @if($review->topics_rated_well)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($review->topics_rated_well as $topic)
                                                <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary); font-weight: 500;">
                                                    {{ ucfirst($topic) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Achievements Card -->
            @if($achievements->isNotEmpty())
                <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3" style="color: var(--text-primary);">
                            <i class="bi bi-trophy"></i> Achievements
                        </h6>
                        <div class="row g-3">
                            @foreach($achievements as $userAchievement)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3" style="background: var(--bg-secondary); border-radius: 0.75rem;">
                                        <div class="me-3" style="font-size: 2rem;">{{ $userAchievement->achievement->icon }}</div>
                                        <div>
                                            <div class="fw-semibold" style="color: var(--text-primary);">{{ $userAchievement->achievement->name }}</div>
                                            <div class="small text-secondary">{{ $userAchievement->achievement->description }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

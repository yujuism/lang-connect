@extends('layout')

@section('title', 'Achievements - LangConnect')

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="container my-4">
    <div class="mb-4">
        <h2 class="fw-bold" style="color: var(--text-primary);">
            <i class="bi bi-trophy"></i> Achievements
        </h2>
        <p class="text-secondary">Track your progress and unlock rewards</p>
    </div>

    <!-- Progress Overview -->
    <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="fw-semibold mb-3" style="color: var(--text-primary);">Overall Progress</h6>
                    <div class="progress" style="height: 30px; border-radius: 1rem;">
                        <div class="progress-bar" role="progressbar"
                             style="width: {{ $completionPercentage }}%; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));"
                             aria-valuenow="{{ $completionPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            <span class="fw-semibold">{{ $completionPercentage }}%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="display-6 fw-bold" style="color: var(--primary-color);">
                        {{ $unlockedCount }}/{{ $totalAchievements }}
                    </div>
                    <div class="small text-secondary">Achievements Unlocked</div>
                </div>
            </div>
        </div>
    </div>

    @foreach($achievementsWithProgress as $category => $achievements)
        <div class="mb-4">
            <h5 class="fw-bold mb-3 text-capitalize" style="color: var(--text-primary);">
                @if($category === 'helper')
                    <i class="bi bi-hand-thumbs-up"></i> Helper Achievements
                @elseif($category === 'streak')
                    <i class="bi bi-fire"></i> Streak Achievements
                @elseif($category === 'mastery')
                    <i class="bi bi-mortarboard"></i> Mastery Achievements
                @elseif($category === 'community')
                    <i class="bi bi-people"></i> Community Achievements
                @elseif($category === 'special')
                    <i class="bi bi-star-fill"></i> Special Achievements
                @else
                    {{ ucfirst($category) }} Achievements
                @endif
            </h5>

            <div class="row g-3">
                @foreach($achievements as $item)
                    @php
                        $achievement = $item['achievement'];
                        $unlocked = $item['unlocked'];
                        $progress = $item['progress'];
                    @endphp

                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm {{ $unlocked ? '' : 'achievement-locked' }}"
                             style="border-radius: 1rem; border: 1px solid var(--border-color); {{ $unlocked ? '' : 'opacity: 0.7;' }}">
                            <div class="card-body p-4">
                                <!-- Rarity Badge -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div style="font-size: 2.5rem; {{ $unlocked ? '' : 'filter: grayscale(100%);' }}">
                                        {{ $achievement->icon }}
                                    </div>
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
                                </div>

                                <!-- Achievement Info -->
                                <h6 class="fw-bold mb-2" style="color: var(--text-primary);">
                                    @if($unlocked)
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    @endif
                                    {{ $achievement->name }}
                                </h6>
                                <p class="small text-secondary mb-3">{{ $achievement->description }}</p>

                                @if(!$unlocked)
                                    <!-- Progress Bar -->
                                    <div class="mb-2">
                                        <div class="progress" style="height: 20px; border-radius: 0.5rem;">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                 style="width: {{ $progress['percentage'] }}%;"
                                                 aria-valuenow="{{ $progress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                                                <span class="small fw-semibold">{{ $progress['percentage'] }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="small text-secondary">
                                        {{ number_format($progress['current'], 1) }} / {{ number_format($progress['required'], 0) }}
                                    </div>
                                @else
                                    <!-- Unlocked Info -->
                                    @php
                                        $userAchievement = Auth::user()->achievements()
                                            ->where('achievement_id', $achievement->id)
                                            ->first();
                                    @endphp
                                    @if($userAchievement)
                                        <div class="small text-success">
                                            <i class="bi bi-calendar-check"></i> Unlocked {{ $userAchievement->pivot->unlocked_at->diffForHumans() }}
                                        </div>
                                    @endif
                                @endif

                                <!-- Karma Reward -->
                                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                                    <div class="small">
                                        <i class="bi bi-trophy text-warning"></i>
                                        Reward:
                                        <strong>
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
                                            +{{ $karmaBonus }} Karma
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<style>
    .achievement-locked {
        background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
    }

    .achievement-locked:hover {
        transform: translateY(-2px);
        transition: transform 0.3s ease;
    }
</style>
@endsection

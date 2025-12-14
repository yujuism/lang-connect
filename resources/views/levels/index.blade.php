@extends('layout')

@section('title', 'Level System - LangConnect')

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="container my-4" style="max-width: 1000px;">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold mb-2" style="color: var(--text-primary);">
            <i class="bi bi-bar-chart-fill"></i> Level System
        </h1>
        <p class="text-secondary">Progress through levels by helping others and contributing to the community</p>
    </div>

    <!-- User's Current Progress (if authenticated) -->
    @if($userProgress)
        <div class="card shadow-sm mb-5" style="border-radius: 1rem; border: 1px solid var(--border-color);">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--text-primary);">
                    <i class="bi bi-person-fill"></i> Your Progress
                </h5>
                <x-level-progress :progress="$userProgress" :showDetails="true" />
            </div>
        </div>
    @endif

    <!-- Level Milestones -->
    <div class="mb-4">
        <h4 class="fw-bold mb-4" style="color: var(--text-primary);">
            <i class="bi bi-flag-fill"></i> Level Milestones
        </h4>
    </div>

    <div class="row g-4">
        @foreach($milestones as $level => $milestone)
            @php
                $isCurrentLevel = $userProgress && $userProgress->level === $level;
                $isUnlocked = $userProgress && $userProgress->level >= $level;
                $isNextLevel = $userProgress && $userProgress->level + 1 === $level;
            @endphp

            <div class="col-md-6">
                <div class="card h-100 {{ $isCurrentLevel ? 'border-primary' : '' }}"
                     style="border-radius: 1rem; border-width: {{ $isCurrentLevel ? '3px' : '1px' }}; {{ $isUnlocked ? '' : 'opacity: 0.7;' }} box-shadow: {{ $isCurrentLevel ? '0 8px 24px rgba(99, 102, 241, 0.2)' : 'var(--shadow-sm)' }};">
                    <div class="card-body p-4">
                        <!-- Level Badge & Title -->
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, {{ $milestone['color'] }} 0%, {{ $milestone['color'] }}dd 100%); display: flex; align-items: center; justify-content: center; flex-direction: column; color: white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
                                <div style="font-size: 1.5rem;">{{ $milestone['icon'] }}</div>
                                <div style="font-size: 0.75rem; font-weight: 600;">{{ $level }}</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="mb-1 fw-bold" style="color: {{ $milestone['color'] }};">
                                        {{ $milestone['title'] }}
                                    </h5>
                                    @if($isCurrentLevel)
                                        <span class="badge bg-primary">Current</span>
                                    @elseif($isNextLevel)
                                        <span class="badge" style="background: {{ $milestone['color'] }};">Next</span>
                                    @elseif($isUnlocked)
                                        <span class="badge bg-success">Unlocked</span>
                                    @else
                                        <span class="badge bg-secondary">Locked</span>
                                    @endif
                                </div>
                                <p class="mb-0 small text-secondary">
                                    <i class="bi bi-clock"></i> {{ $milestone['hours'] }} hours required
                                </p>
                            </div>
                        </div>

                        <!-- Benefits -->
                        <div class="mb-3">
                            <p class="mb-2 small fw-semibold" style="color: var(--text-primary);">
                                <i class="bi bi-gift-fill"></i> Benefits:
                            </p>
                            <ul class="mb-0" style="font-size: 0.875rem; color: var(--text-secondary);">
                                @foreach($milestone['benefits'] as $benefit)
                                    <li class="mb-1">{{ $benefit }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Progress indicator for next level -->
                        @if($isNextLevel && $progressData)
                            <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-semibold" style="color: var(--text-secondary);">Your Progress</span>
                                    <span class="small fw-bold" style="color: {{ $milestone['color'] }};">{{ $progressData['progress_percentage'] }}%</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 10px;">
                                    <div class="progress-bar" style="width: {{ $progressData['progress_percentage'] }}%; background: {{ $milestone['color'] }};" role="progressbar"></div>
                                </div>
                                <p class="mb-0 mt-1 small text-secondary">
                                    {{ number_format($progressData['hours_remaining'], 1) }} hours remaining
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- How it Works -->
    <div class="card shadow-sm mt-5" style="border-radius: 1rem; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border: none;">
        <div class="card-body p-5">
            <h5 class="fw-bold mb-4" style="color: var(--text-primary);">
                <i class="bi bi-question-circle-fill"></i> How It Works
            </h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="mb-3" style="font-size: 2.5rem;">📚</div>
                        <h6 class="fw-bold mb-2" style="color: var(--text-primary);">Help Others Learn</h6>
                        <p class="mb-0 small text-secondary">Complete practice sessions as a helper to earn contribution hours</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="mb-3" style="font-size: 2.5rem;">⏱️</div>
                        <h6 class="fw-bold mb-2" style="color: var(--text-primary);">Track Your Hours</h6>
                        <p class="mb-0 small text-secondary">Each minute you spend helping counts towards your next level</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="mb-3" style="font-size: 2.5rem;">🎁</div>
                        <h6 class="fw-bold mb-2" style="color: var(--text-primary);">Unlock Rewards</h6>
                        <p class="mb-0 small text-secondary">Reach new levels to unlock badges, bonuses, and special features</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

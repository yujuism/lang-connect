@props(['progress', 'showDetails' => true])

@php
    use App\Services\LevelProgressionService;
    $service = new LevelProgressionService();
    $progressData = $service->getProgressToNextLevel($progress);
    $currentMilestone = $service->getMilestoneForLevel($progressData['current_level']);
    $nextMilestone = $progressData['next_level'] ? $service->getMilestoneForLevel($progressData['next_level']) : null;
@endphp

<div class="level-progress-container">
    <!-- Current Level Badge -->
    <div class="d-flex align-items-center mb-3">
        <div class="level-badge me-3" style="background: linear-gradient(135deg, {{ $currentMilestone['color'] ?? '#6b7280' }} 0%, {{ $currentMilestone['color'] ?? '#6b7280' }}dd 100%);">
            <div class="level-icon">{{ $currentMilestone['icon'] ?? '🌱' }}</div>
            <div class="level-number">{{ $progressData['current_level'] }}</div>
        </div>
        <div class="flex-grow-1">
            <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">
                Level {{ $progressData['current_level'] }} - {{ $currentMilestone['title'] ?? 'Beginner' }}
            </h5>
            <p class="mb-0 text-secondary small">
                {{ number_format($progressData['current_hours'], 1) }} contribution hours
            </p>
        </div>
    </div>

    @if(!$progressData['is_max_level'])
        <!-- Progress Bar -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-semibold" style="color: var(--text-secondary);">
                    Progress to Level {{ $progressData['next_level'] }}
                </span>
                <span class="small fw-bold" style="color: {{ $currentMilestone['color'] ?? '#6b7280' }};">
                    {{ $progressData['progress_percentage'] }}%
                </span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 10px; background-color: #e5e7eb;">
                <div class="progress-bar"
                     role="progressbar"
                     style="width: {{ $progressData['progress_percentage'] }}%; background: linear-gradient(90deg, {{ $currentMilestone['color'] ?? '#6b7280' }} 0%, {{ $nextMilestone['color'] ?? '#6b7280' }} 100%); border-radius: 10px; transition: width 0.6s ease;"
                     aria-valuenow="{{ $progressData['progress_percentage'] }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span class="small text-secondary">
                    {{ number_format($progressData['hours_in_current_level'], 1) }}h / {{ $progressData['hours_needed_for_level'] }}h
                </span>
                <span class="small text-secondary">
                    {{ number_format($progressData['hours_remaining'], 1) }}h remaining
                </span>
            </div>
        </div>

        @if($showDetails && $nextMilestone)
            <!-- Next Level Preview -->
            <div class="next-level-preview p-3" style="background: linear-gradient(135deg, {{ $nextMilestone['color'] }}15 0%, {{ $nextMilestone['color'] }}05 100%); border-radius: 0.75rem; border-left: 3px solid {{ $nextMilestone['color'] }};">
                <div class="d-flex align-items-center mb-2">
                    <span style="font-size: 1.5rem; margin-right: 0.5rem;">{{ $nextMilestone['icon'] }}</span>
                    <strong style="color: var(--text-primary);">Next: {{ $nextMilestone['title'] }}</strong>
                </div>
                <p class="mb-1 small text-secondary">
                    <i class="bi bi-clock"></i> {{ $progressData['hours_for_next'] }} total hours required
                </p>
                <div class="mt-2">
                    <p class="mb-1 small fw-semibold" style="color: var(--text-primary);">Unlocks:</p>
                    <ul class="mb-0 small" style="color: var(--text-secondary); padding-left: 1.25rem;">
                        @foreach($nextMilestone['benefits'] as $benefit)
                            <li>{{ $benefit }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    @else
        <!-- Max Level Achieved -->
        <div class="text-center p-4" style="background: linear-gradient(135deg, {{ $currentMilestone['color'] }}20 0%, {{ $currentMilestone['color'] }}10 100%); border-radius: 0.75rem; border: 2px solid {{ $currentMilestone['color'] }};">
            <div style="font-size: 3rem;">{{ $currentMilestone['icon'] }}</div>
            <h4 class="fw-bold mt-2" style="color: {{ $currentMilestone['color'] }};">Maximum Level Reached!</h4>
            <p class="mb-0 text-secondary">You've achieved the highest level. Continue contributing to help others grow!</p>
        </div>
    @endif
</div>

<style>
    .level-badge {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        position: relative;
    }

    .level-icon {
        font-size: 2rem;
        line-height: 1;
    }

    .level-number {
        font-size: 0.9rem;
        font-weight: 700;
        margin-top: 2px;
    }

    .progress-bar {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .next-level-preview ul li {
        margin-bottom: 0.25rem;
    }
</style>

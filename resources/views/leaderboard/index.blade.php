@extends('layout')

@section('title', 'Leaderboard - LangConnect')

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="container my-4">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold mb-2" style="color: var(--text-primary);">
            <i class="bi bi-trophy-fill" style="color: #f59e0b;"></i> Community Leaderboard
        </h1>
        <p class="text-secondary">Celebrate our top contributors and most active learners</p>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-body text-center">
                    <div class="text-primary mb-2" style="font-size: 2rem;"><i class="bi bi-people-fill"></i></div>
                    <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ number_format($stats['total_users']) }}</h3>
                    <small class="text-secondary">Members</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-body text-center">
                    <div class="mb-2" style="font-size: 2rem; color: #f59e0b;"><i class="bi bi-star-fill"></i></div>
                    <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ number_format($stats['total_karma_distributed']) }}</h3>
                    <small class="text-secondary">Total Karma</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-body text-center">
                    <div class="text-success mb-2" style="font-size: 2rem;"><i class="bi bi-calendar-check"></i></div>
                    <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ number_format($stats['total_sessions']) }}</h3>
                    <small class="text-secondary">Sessions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
                <div class="card-body text-center">
                    <div class="text-info mb-2" style="font-size: 2rem;"><i class="bi bi-clock-fill"></i></div>
                    <h3 class="fw-bold mb-0" style="color: var(--text-primary);">{{ number_format($stats['total_hours']) }}</h3>
                    <small class="text-secondary">Total Hours</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Tabs -->
    <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
        <div class="card-body p-0">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs px-4 pt-4 border-0" role="tablist" style="border-bottom: 2px solid var(--border-color) !important;">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'karma' ? 'active' : '' }}"
                       href="{{ route('leaderboard.index', ['type' => 'karma']) }}"
                       style="border: none; {{ $type === 'karma' ? 'border-bottom: 3px solid var(--primary-color) !important; color: var(--primary-color); font-weight: 600;' : 'color: var(--text-secondary);' }}">
                        <i class="bi bi-star-fill"></i> Karma Points
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'level' ? 'active' : '' }}"
                       href="{{ route('leaderboard.index', ['type' => 'level']) }}"
                       style="border: none; {{ $type === 'level' ? 'border-bottom: 3px solid var(--primary-color) !important; color: var(--primary-color); font-weight: 600;' : 'color: var(--text-secondary);' }}">
                        <i class="bi bi-award-fill"></i> Level Rankings
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'helper' ? 'active' : '' }}"
                       href="{{ route('leaderboard.index', ['type' => 'helper']) }}"
                       style="border: none; {{ $type === 'helper' ? 'border-bottom: 3px solid var(--primary-color) !important; color: var(--primary-color); font-weight: 600;' : 'color: var(--text-secondary);' }}">
                        <i class="bi bi-heart-fill"></i> Top Helpers
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'monthly' ? 'active' : '' }}"
                       href="{{ route('leaderboard.index', ['type' => 'monthly']) }}"
                       style="border: none; {{ $type === 'monthly' ? 'border-bottom: 3px solid var(--primary-color) !important; color: var(--primary-color); font-weight: 600;' : 'color: var(--text-secondary);' }}">
                        <i class="bi bi-fire"></i> This Month
                    </a>
                </li>
            </ul>

            <!-- Your Rank Card (if authenticated) -->
            @if($userRank)
                <div class="mx-4 mt-4 p-3" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 0.75rem; border-left: 4px solid var(--primary-color);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong style="color: var(--text-primary);">Your Rank:</strong>
                            <span class="badge bg-primary ms-2" style="font-size: 1rem;">#{{ $userRank['rank'] }}</span>
                        </div>
                        <div class="text-end">
                            <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                @if($type === 'karma')
                                    <strong>{{ number_format($userRank['value']) }}</strong> Karma Points
                                @elseif($type === 'level')
                                    Level <strong>{{ $userRank['value'] }}</strong>
                                @elseif($type === 'helper')
                                    <strong>{{ $userRank['value'] }}</strong> Sessions Helped
                                @elseif($type === 'monthly')
                                    <strong>{{ $userRank['value'] }}</strong> Sessions This Month
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Leaderboard Table -->
            <div class="p-4">
                @if($leaderboard->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-trophy" style="font-size: 4rem; color: var(--text-secondary); opacity: 0.3;"></i>
                        <p class="text-secondary mt-3">No rankings available yet. Start contributing to appear on the leaderboard!</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead style="background: var(--bg-secondary);">
                                <tr>
                                    <th style="border: none; padding: 1rem;">Rank</th>
                                    <th style="border: none; padding: 1rem;">User</th>
                                    @if($type === 'karma')
                                        <th style="border: none; padding: 1rem;">Karma Points</th>
                                        <th style="border: none; padding: 1rem;">Level</th>
                                    @elseif($type === 'level')
                                        <th style="border: none; padding: 1rem;">Level</th>
                                        <th style="border: none; padding: 1rem;">Karma</th>
                                    @elseif($type === 'helper')
                                        <th style="border: none; padding: 1rem;">Sessions Given</th>
                                        <th style="border: none; padding: 1rem;">Hours</th>
                                        <th style="border: none; padding: 1rem;">Members Helped</th>
                                    @elseif($type === 'monthly')
                                        <th style="border: none; padding: 1rem;">Sessions</th>
                                        <th style="border: none; padding: 1rem;">Level</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaderboard as $entry)
                                    <tr style="{{ Auth::check() && $entry['user']->id === Auth::id() ? 'background: rgba(99, 102, 241, 0.05);' : '' }}">
                                        <td style="padding: 1rem;">
                                            <span class="fw-bold" style="font-size: 1.1rem; color: {{ $entry['rank'] <= 3 ? 'var(--primary-color)' : 'var(--text-secondary)' }};">
                                                @if($entry['badge'])
                                                    {{ $entry['badge'] }}
                                                @else
                                                    #{{ $entry['rank'] }}
                                                @endif
                                            </span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <a href="{{ route('profile.show', $entry['user']) }}" class="text-decoration-none d-flex align-items-center">
                                                <div class="me-2" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                                    {{ strtoupper(substr($entry['user']->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold" style="color: var(--text-primary);">
                                                        {{ $entry['user']->name }}
                                                        @if(Auth::check() && $entry['user']->id === Auth::id())
                                                            <span class="badge bg-primary" style="font-size: 0.7rem;">You</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        @if($type === 'karma')
                                            <td style="padding: 1rem;">
                                                <span class="fw-bold" style="color: #f59e0b;">{{ number_format($entry['value']) }}</span>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    Level {{ $entry['level'] }}
                                                </span>
                                            </td>
                                        @elseif($type === 'level')
                                            <td style="padding: 1rem;">
                                                <span class="badge bg-primary" style="font-size: 1rem;">Level {{ $entry['value'] }}</span>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <span style="color: #f59e0b;">{{ number_format($entry['karma']) }} karma</span>
                                            </td>
                                        @elseif($type === 'helper')
                                            <td style="padding: 1rem;">
                                                <span class="fw-bold text-success">{{ number_format($entry['value']) }}</span>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <span class="text-secondary">{{ $entry['hours'] }}h</span>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <span class="text-secondary">{{ number_format($entry['members_helped']) }}</span>
                                            </td>
                                        @elseif($type === 'monthly')
                                            <td style="padding: 1rem;">
                                                <span class="fw-bold text-info">{{ number_format($entry['value']) }}</span>
                                            </td>
                                            <td style="padding: 1rem;">
                                                <span class="badge bg-secondary">Level {{ $entry['level'] }}</span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Motivational Message -->
    <div class="text-center mt-4">
        <p class="text-secondary small">
            <i class="bi bi-lightbulb"></i> Keep practicing and helping others to climb the ranks!
        </p>
    </div>
</div>
@endsection

@extends('layout')

@section('title', 'Browse Members')

@php
    use Illuminate\Support\Facades\Auth;
@endphp

@section('content')
<div class="container my-4">
    <h2 class="fw-bold mb-4" style="color: var(--text-primary);">
        <i class="bi bi-people"></i> Browse Members
    </h2>

    <!-- Search & Filters -->
    <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('members') }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Search by name..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="language_id" class="form-select">
                            <option value="">All Languages</option>
                            @foreach($languages as $language)
                                <option value="{{ $language->id }}" {{ request('language_id') == $language->id ? 'selected' : '' }}>
                                    {{ $language->flag_emoji }} {{ $language->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Members Grid -->
    <div class="row g-4">
        @forelse($users as $user)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; font-size: 2rem; font-weight: bold;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <h5 class="fw-bold mb-1" style="color: var(--text-primary);">
                                <a href="{{ route('profile.show', $user) }}" class="text-decoration-none" style="color: var(--text-primary);">
                                    {{ $user->name }}
                                </a>
                            </h5>
                            @if($user->progress)
                                <div class="mb-2">
                                    <span class="badge" style="background: var(--primary-color); color: white;">
                                        Level {{ $user->progress->level }}
                                    </span>
                                    <span class="badge bg-warning text-dark">
                                        {{ number_format($user->progress->karma_points) }} Karma
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($user->bio)
                            <p class="text-secondary small mb-3">{{ Str::limit($user->bio, 100) }}</p>
                        @endif

                        <!-- Languages -->
                        @if($user->languages->isNotEmpty())
                            <div class="mb-3">
                                <div class="small text-secondary mb-2">Languages:</div>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($user->languages->take(5) as $language)
                                        <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary);">
                                            {{ $language->flag_emoji }} {{ $language->name }}
                                        </span>
                                    @endforeach
                                    @if($user->languages->count() > 5)
                                        <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary);">
                                            +{{ $user->languages->count() - 5 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <a href="{{ route('profile.show', $user) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-person"></i> View Profile
                            </a>
                            @auth
                                @if(Auth::id() !== $user->id)
                                    <a href="{{ route('messages.show', $user) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-chat-dots"></i> Send Message
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
                    <div class="card-body text-center py-5">
                        <div class="mb-3" style="font-size: 4rem; color: var(--text-tertiary);">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 style="color: var(--text-secondary);">No members found</h5>
                        <p class="text-secondary">Try adjusting your search filters</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection

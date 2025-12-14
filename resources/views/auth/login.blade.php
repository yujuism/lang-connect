@extends('layout')

@section('title', 'Login - LangConnect')

@section('content')
<div class="container" style="max-width: 450px; margin-top: 80px; margin-bottom: 80px;">
    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold mb-2" style="color: var(--text-primary);">Welcome Back</h2>
                <p class="text-secondary">Sign in to continue your language journey</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success mb-4" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold" style="color: var(--text-primary);">Email</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold" style="color: var(--text-primary);">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" required autocomplete="current-password"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                    <label class="form-check-label text-secondary" for="remember_me">
                        Remember me
                    </label>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: var(--primary-color); font-size: 0.875rem;">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3" style="padding: 0.75rem; font-weight: 600;">
                    Sign In
                </button>

                <div class="text-center">
                    <span class="text-secondary" style="font-size: 0.875rem;">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: var(--primary-color); font-size: 0.875rem;">
                        Create Account
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

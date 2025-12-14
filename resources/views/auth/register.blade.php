@extends('layout')

@section('title', 'Register - LangConnect')

@section('content')
<div class="container" style="max-width: 450px; margin-top: 80px; margin-bottom: 80px;">
    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold mb-2" style="color: var(--text-primary);">Create Account</h2>
                <p class="text-secondary">Join the LangConnect community today</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold" style="color: var(--text-primary);">Name</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold" style="color: var(--text-primary);">Email</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autocomplete="username"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold" style="color: var(--text-primary);">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                           name="password" required autocomplete="new-password"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label fw-semibold" style="color: var(--text-primary);">Confirm Password</label>
                    <input id="password_confirmation" type="password" class="form-control"
                           name="password_confirmation" required autocomplete="new-password"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3" style="padding: 0.75rem; font-weight: 600;">
                    Create Account
                </button>

                <div class="text-center">
                    <span class="text-secondary" style="font-size: 0.875rem;">Already have an account?</span>
                    <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: var(--primary-color); font-size: 0.875rem;">
                        Sign In
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

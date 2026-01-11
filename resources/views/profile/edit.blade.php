@extends('layout')

@section('title', 'Edit Profile - LangConnect')

@section('content')
<div class="container my-4" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: var(--text-primary);">Edit Profile</h2>
        <a href="{{ route('profile.show', $user) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Profile
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Basic Information -->
    <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-5">
            <h5 class="fw-bold mb-4" style="color: var(--text-primary);">
                <i class="bi bi-person"></i> Basic Information
            </h5>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label for="name" class="form-label fw-semibold" style="color: var(--text-primary);">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                           value="{{ old('name', $user->name) }}" required
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold" style="color: var(--text-primary);">Email</label>
                    <input type="email" class="form-control" id="email" value="{{ $user->email }}" disabled
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-secondary);">
                    <div class="form-text text-secondary small">Email cannot be changed</div>
                </div>

                <div class="mb-4">
                    <label for="bio" class="form-label fw-semibold" style="color: var(--text-primary);">Bio</label>
                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="4"
                              placeholder="Tell others about yourself, your language goals, and interests..."
                              style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-secondary small">Max 1000 characters</div>
                </div>

                <div class="mb-4">
                    <label for="timezone" class="form-label fw-semibold" style="color: var(--text-primary);">Timezone</label>
                    <input type="text" class="form-control @error('timezone') is-invalid @enderror" id="timezone" name="timezone"
                           value="{{ old('timezone', $user->timezone) }}"
                           placeholder="e.g., Asia/Jakarta, America/New_York"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('timezone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="avatar" class="form-label fw-semibold" style="color: var(--text-primary);">Profile Picture</label>
                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/*"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('avatar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-secondary small">Max 2MB. JPG, PNG, or GIF.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-envelope"></i> Email Notifications
                    </label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="email_notifications_enabled"
                               name="email_notifications_enabled" value="1"
                               {{ old('email_notifications_enabled', $user->email_notifications_enabled ?? true) ? 'checked' : '' }}
                               style="cursor: pointer; width: 3em; height: 1.5em;">
                        <label class="form-check-label" for="email_notifications_enabled" style="cursor: pointer;">
                            Send me email notifications when my learning requests are matched
                        </label>
                    </div>
                    <div class="form-text text-secondary small">
                        <i class="bi bi-info-circle"></i> You'll still receive in-app notifications regardless of this setting
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-record-circle"></i> Call Recording
                    </label>
                    <select class="form-select @error('recording_preference') is-invalid @enderror" id="recording_preference" name="recording_preference"
                            style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                        <option value="ask" {{ old('recording_preference', $user->recording_preference ?? 'ask') === 'ask' ? 'selected' : '' }}>
                            Ask me each time
                        </option>
                        <option value="always" {{ old('recording_preference', $user->recording_preference ?? 'ask') === 'always' ? 'selected' : '' }}>
                            Always record calls
                        </option>
                        <option value="never" {{ old('recording_preference', $user->recording_preference ?? 'ask') === 'never' ? 'selected' : '' }}>
                            Never record calls
                        </option>
                    </select>
                    @error('recording_preference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-secondary small">
                        <i class="bi bi-info-circle"></i> Recorded calls are transcribed and analyzed to help track your learning progress
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 600;">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Languages -->
    <div class="card shadow-sm mb-4" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-5">
            <h5 class="fw-bold mb-4" style="color: var(--text-primary);">
                <i class="bi bi-globe"></i> My Languages
            </h5>
            <form method="POST" action="{{ route('profile.update-languages') }}" id="languagesForm">
                @csrf

                <div id="languagesList">
                    @forelse($user->userLanguages as $index => $userLang)
                        <div class="language-item mb-3 p-3" style="background: var(--bg-secondary); border-radius: 0.75rem;">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold">Language</label>
                                    <select class="form-select" name="languages[{{ $index }}][language_id]" required>
                                        @foreach($availableLanguages as $lang)
                                            <option value="{{ $lang->id }}" {{ $userLang->language_id == $lang->id ? 'selected' : '' }}>
                                                {{ $lang->flag_emoji }} {{ $lang->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Proficiency</label>
                                    <select class="form-select" name="languages[{{ $index }}][proficiency_level]" required>
                                        <option value="A1" {{ $userLang->proficiency_level == 'A1' ? 'selected' : '' }}>A1 - Beginner</option>
                                        <option value="A2" {{ $userLang->proficiency_level == 'A2' ? 'selected' : '' }}>A2 - Elementary</option>
                                        <option value="B1" {{ $userLang->proficiency_level == 'B1' ? 'selected' : '' }}>B1 - Intermediate</option>
                                        <option value="B2" {{ $userLang->proficiency_level == 'B2' ? 'selected' : '' }}>B2 - Upper Intermediate</option>
                                        <option value="C1" {{ $userLang->proficiency_level == 'C1' ? 'selected' : '' }}>C1 - Advanced</option>
                                        <option value="C2" {{ $userLang->proficiency_level == 'C2' ? 'selected' : '' }}>C2 - Proficient</option>
                                        <option value="Native" {{ $userLang->proficiency_level == 'Native' ? 'selected' : '' }}>Native</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Options</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="languages[{{ $index }}][can_help]" value="1"
                                               {{ $userLang->can_help ? 'checked' : '' }}>
                                        <label class="form-check-label small">Can help others</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="language-item mb-3 p-3" style="background: var(--bg-secondary); border-radius: 0.75rem;">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold">Language</label>
                                    <select class="form-select" name="languages[0][language_id]" required>
                                        <option value="">Select a language</option>
                                        @foreach($availableLanguages as $lang)
                                            <option value="{{ $lang->id }}">{{ $lang->flag_emoji }} {{ $lang->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Proficiency</label>
                                    <select class="form-select" name="languages[0][proficiency_level]" required>
                                        <option value="A1">A1 - Beginner</option>
                                        <option value="A2">A2 - Elementary</option>
                                        <option value="B1">B1 - Intermediate</option>
                                        <option value="B2">B2 - Upper Intermediate</option>
                                        <option value="C1">C1 - Advanced</option>
                                        <option value="C2">C2 - Proficient</option>
                                        <option value="Native">Native</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Options</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="languages[0][can_help]" value="1">
                                        <label class="form-check-label small">Can help others</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 600;">
                    <i class="bi bi-save"></i> Save Languages
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

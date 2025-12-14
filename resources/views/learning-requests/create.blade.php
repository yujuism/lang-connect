@extends('layout')

@section('title', 'Create Learning Request - LangConnect')

@section('content')
<div class="container" style="max-width: 700px; margin-top: 40px; margin-bottom: 80px;">
    <div class="card shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color);">
        <div class="card-body p-5">
            <h2 class="fw-bold mb-2" style="color: var(--text-primary);">Request Learning Help</h2>
            <p class="text-secondary mb-4">Tell us what you want to learn and we'll match you with a helper</p>

            <form method="POST" action="{{ route('learning-requests.store') }}">
                @csrf

                <!-- Language -->
                <div class="mb-4">
                    <label for="language_id" class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-translate"></i> Language
                    </label>
                    <select id="language_id" name="language_id" class="form-select @error('language_id') is-invalid @enderror" required
                            style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                        <option value="">Select a language...</option>
                        @foreach($languages as $language)
                            <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>
                                {{ $language->flag_emoji }} {{ $language->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('language_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Proficiency Level -->
                <div class="mb-4">
                    <label for="proficiency_level" class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-bar-chart"></i> Your Current Level
                    </label>
                    <select id="proficiency_level" name="proficiency_level" class="form-select @error('proficiency_level') is-invalid @enderror" required
                            style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                        <option value="">Select your level...</option>
                        @foreach($proficiencyLevels as $key => $label)
                            <option value="{{ $key }}" {{ old('proficiency_level') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('proficiency_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Topic Category -->
                <div class="mb-4">
                    <label for="topic_category" class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-bookmarks"></i> Topic Category
                    </label>
                    <select id="topic_category" name="topic_category" class="form-select @error('topic_category') is-invalid @enderror" required
                            style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                        <option value="">Select a category...</option>
                        @foreach($topicCategories as $key => $label)
                            <option value="{{ $key }}" {{ old('topic_category') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('topic_category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Topic Name (Optional) -->
                <div class="mb-4">
                    <label for="topic_name" class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-tag"></i> Specific Topic (Optional)
                    </label>
                    <input type="text" id="topic_name" name="topic_name"
                           class="form-control @error('topic_name') is-invalid @enderror"
                           value="{{ old('topic_name') }}"
                           placeholder="e.g., Past tense, Pronunciation of 'r' sound"
                           style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                    @error('topic_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Specific Question -->
                <div class="mb-4">
                    <label for="specific_question" class="form-label fw-semibold" style="color: var(--text-primary);">
                        <i class="bi bi-chat-left-quote"></i> What do you want to learn?
                    </label>
                    <textarea id="specific_question" name="specific_question" rows="4"
                              class="form-control @error('specific_question') is-invalid @enderror" required
                              placeholder="Describe what you want to learn or practice. Be specific so we can match you with the right helper!"
                              style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">{{ old('specific_question') }}</textarea>
                    @error('specific_question')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text text-secondary">
                        <i class="bi bi-lightbulb"></i> Tip: The more specific you are, the better we can match you with an expert!
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" style="padding: 0.75rem; font-weight: 600;">
                        <i class="bi bi-send"></i> Submit Request
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary" style="padding: 0.75rem;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card mt-4 shadow-sm" style="border-radius: 1rem; border: 1px solid var(--border-color); background: var(--bg-secondary);">
        <div class="card-body p-4">
            <h6 class="fw-semibold mb-3" style="color: var(--primary-color);">
                <i class="bi bi-info-circle"></i> How It Works
            </h6>
            <div class="small text-secondary">
                <div class="mb-2"><i class="bi bi-1-circle-fill text-primary"></i> Submit your learning request</div>
                <div class="mb-2"><i class="bi bi-2-circle-fill text-primary"></i> We'll match you with an expert helper</div>
                <div class="mb-2"><i class="bi bi-3-circle-fill text-primary"></i> Get notified when matched</div>
                <div><i class="bi bi-4-circle-fill text-primary"></i> Start your learning session!</div>
            </div>
        </div>
    </div>
</div>
@endsection

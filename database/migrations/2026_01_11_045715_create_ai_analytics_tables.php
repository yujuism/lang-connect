<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Session transcripts - stores audio chunks and their transcriptions
        Schema::create('session_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_session_id')->constrained()->onDelete('cascade');
            $table->integer('chunk_number')->default(0); // For chunked recordings
            $table->string('audio_path')->nullable(); // Path in MinIO
            $table->longText('transcript')->nullable();
            $table->string('language', 10)->nullable(); // Detected language
            $table->integer('duration_seconds')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['practice_session_id', 'chunk_number']);
        });

        // Session analyses - AI-generated summaries and insights
        Schema::create('session_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_session_id')->constrained()->onDelete('cascade');
            $table->longText('full_transcript')->nullable(); // Merged transcript
            $table->text('summary')->nullable();
            $table->json('topics')->nullable(); // Array of topics covered
            $table->json('key_phrases')->nullable(); // Important phrases learned
            $table->text('pronunciation_notes')->nullable();
            $table->json('vocabulary_extracted')->nullable(); // Words to add to flashcards
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique('practice_session_id');
        });

        // Flashcards - vocabulary cards extracted from sessions
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('practice_session_id')->nullable()->constrained()->onDelete('set null');
            $table->string('front', 500); // Target language
            $table->string('back', 500); // Native language / translation
            $table->string('language', 10); // Language code
            $table->string('context')->nullable(); // Example sentence
            $table->integer('mastery_level')->default(0); // SM-2 level (0-5)
            $table->float('easiness_factor')->default(2.5); // SM-2 EF
            $table->integer('repetitions')->default(0);
            $table->integer('interval_days')->default(1);
            $table->timestamp('next_review_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'next_review_at']);
            $table->index(['user_id', 'language']);
        });

        // Vocabulary entries - track unique words used
        Schema::create('vocabulary_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('word', 100);
            $table->string('language', 10);
            $table->integer('times_used')->default(1);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['user_id', 'word', 'language']);
            $table->index(['user_id', 'language']);
        });

        // Weekly reports - AI-generated progress summaries
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('week_start');
            $table->integer('sessions_count')->default(0);
            $table->integer('practice_minutes')->default(0);
            $table->integer('words_learned')->default(0);
            $table->integer('flashcards_reviewed')->default(0);
            $table->text('report_content')->nullable(); // AI-generated insights
            $table->json('highlights')->nullable(); // Key achievements
            $table->json('suggestions')->nullable(); // AI suggestions for improvement
            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
            $table->index('week_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
        Schema::dropIfExists('vocabulary_entries');
        Schema::dropIfExists('flashcards');
        Schema::dropIfExists('session_analyses');
        Schema::dropIfExists('session_transcripts');
    }
};

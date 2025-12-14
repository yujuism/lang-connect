<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained();
            $table->enum('topic_category', ['grammar', 'vocabulary', 'pronunciation', 'expression', 'conversation', 'other']);
            $table->string('topic_name')->nullable(); // e.g., "past_tense", "ordering_food"
            $table->text('specific_question')->nullable(); // e.g., "How to use fue vs era?"
            $table->json('keywords')->nullable(); // Auto-extracted keywords for matching
            $table->string('proficiency_level', 10)->default('A1');
            $table->enum('status', ['pending', 'matched', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('matched_with_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('matched_at')->nullable();
            $table->timestamps();

            $table->index(['language_id', 'topic_category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_requests');
    }
};

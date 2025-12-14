<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_mastery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained();
            $table->string('topic_name'); // e.g., "past_tense", "pronunciation_r"
            $table->integer('sessions_practiced')->default(0);
            $table->integer('mastery_percentage')->default(0); // 0-100
            $table->integer('streak_days')->default(0);
            $table->date('last_practiced')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'language_id', 'topic_name']);
            $table->index(['user_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_mastery');
    }
};

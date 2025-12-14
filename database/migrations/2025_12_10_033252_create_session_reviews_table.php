<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('practice_sessions')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_user_id')->constrained('users')->onDelete('cascade');
            $table->integer('overall_rating'); // 1-5
            $table->integer('helpfulness_rating')->nullable();
            $table->integer('patience_rating')->nullable();
            $table->integer('clarity_rating')->nullable();
            $table->integer('engagement_rating')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_public')->default(true);
            $table->json('topics_rated_well')->nullable(); // Topics they excelled at
            $table->timestamps();

            $table->unique(['session_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_reviews');
    }
};

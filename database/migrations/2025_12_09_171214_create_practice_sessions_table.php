<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->nullable()->constrained('learning_requests')->onDelete('set null');
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('language_id')->constrained();
            $table->string('topic')->nullable(); // Main topic discussed
            $table->enum('session_type', ['random', 'scheduled', 'workshop'])->default('random');
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('duration_minutes')->nullable(); // Actual duration
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable(); // Optional session notes
            $table->timestamps();

            $table->index(['user1_id', 'status']);
            $table->index(['user2_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};

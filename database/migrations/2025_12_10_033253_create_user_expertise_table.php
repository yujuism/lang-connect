<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_expertise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained();
            $table->string('topic_name'); // What topic they're expert in
            $table->integer('times_helped')->default(0); // How many times helped with this topic
            $table->decimal('average_rating', 3, 2)->default(0.00); // Average rating for this topic
            $table->enum('specialization_level', ['beginner', 'competent', 'proficient', 'expert', 'master'])->default('beginner');
            $table->timestamps();

            $table->unique(['user_id', 'language_id', 'topic_name']);
            $table->index(['language_id', 'topic_name', 'specialization_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_expertise');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('icon', 50); // Emoji or icon class
            $table->enum('category', ['helper', 'streak', 'mastery', 'community', 'special']);
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary', 'mythical'])->default('common');
            $table->string('requirement_type'); // hours, sessions, rating, streak, etc.
            $table->string('requirement_value'); // The threshold
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};

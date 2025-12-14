<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->enum('proficiency_level', ['native', 'C2', 'C1', 'B2', 'B1', 'A2', 'A1'])->default('A1');
            $table->boolean('is_native')->default(false);
            $table->boolean('is_learning')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_languages');
    }
};

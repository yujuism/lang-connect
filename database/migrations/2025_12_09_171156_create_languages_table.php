<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Spanish, English, French, etc.
            $table->string('code', 5); // es, en, fr, etc.
            $table->string('flag_emoji', 10)->nullable(); // 🇪🇸, 🇺🇸, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};

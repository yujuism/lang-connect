<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('contribution_hours', 8, 2)->default(0); // Hours helping others
            $table->integer('level')->default(1); // 1-10 based on contribution
            $table->integer('karma_points')->default(0); // Social status points
            $table->integer('total_sessions')->default(0);
            $table->integer('members_helped')->default(0);
            $table->integer('sessions_received')->default(0); // Balance tracker
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};

<?php
//2025_10_21_164103_create_crash_games_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crash_games', function (Blueprint $table) {
            $table->id();
            $table->string('game_hash', 64)->unique(); // Provably fair hash
            $table->decimal('crash_point', 10, 2); // যেখানে crash হবে
            $table->enum('status', ['pending', 'running', 'crashed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('crashed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crash_games');
    }
};

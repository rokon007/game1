<?php

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
        Schema::create('hajari_game_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hajari_game_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['invited', 'accepted', 'declined', 'joined'])->default('invited');
            $table->integer('position')->nullable(); // 1-4 for seating position
            $table->json('cards')->nullable(); // Player's cards
            $table->integer('score')->default(0);
            $table->boolean('is_ready')->default(false);
            $table->integer('total_points')->default(0); // Total points for Hazari
            $table->integer('rounds_won')->default(0); // Number of rounds won
            $table->json('round_scores')->nullable(); // Scores for each round
            $table->integer('hazari_count')->default(0); // Special Hazari combinations
            $table->timestamps();

            $table->unique(['hajari_game_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hajari_game_participants');
    }
};

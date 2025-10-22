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
        Schema::create('crash_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('crash_game_id')->constrained()->onDelete('cascade');
            $table->decimal('bet_amount', 15, 2);
            $table->decimal('cashout_at', 10, 2)->nullable(); // কোন multiplier এ cashout করেছে
            $table->decimal('profit', 15, 2)->default(0); // লাভ/লোকসান
            $table->enum('status', ['pending', 'playing', 'won', 'lost'])->default('pending');
            $table->timestamp('cashed_out_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'crash_game_id']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crash_bets');
    }
};

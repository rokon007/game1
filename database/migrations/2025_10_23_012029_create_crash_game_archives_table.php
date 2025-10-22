<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('crash_game_archives', function (Blueprint $table) {
            $table->id();
            $table->date('archive_date')->unique();
            $table->integer('total_games');
            $table->integer('total_bets');
            $table->decimal('total_bet_amount', 16, 8)->default(0);
            $table->decimal('total_payout', 16, 8)->default(0);
            $table->decimal('house_profit', 16, 8)->default(0);
            $table->decimal('average_crash_point', 10, 2)->default(0);
            $table->decimal('highest_crash_point', 10, 2)->default(0);
            $table->decimal('lowest_crash_point', 10, 2)->default(0);
            $table->json('additional_stats')->nullable();
            $table->timestamps();

            $table->index('archive_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('crash_game_archives');
    }
};

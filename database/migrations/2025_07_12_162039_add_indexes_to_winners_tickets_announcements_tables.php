<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('winners', function (Blueprint $table) {
            // Add composite index for faster winner queries
            $table->index(['game_id', 'pattern'], 'winners_game_pattern_index');
            $table->index(['game_id', 'pattern', 'prize_processed'], 'winners_game_pattern_processed_index');
        });

        Schema::table('tickets', function (Blueprint $table) {
            // Add index for faster ticket queries
            $table->index(['game_id', 'is_winner'], 'tickets_game_winner_index');
        });

        Schema::table('announcements', function (Blueprint $table) {
            // Add index for faster announcement queries
            $table->index(['game_id'], 'announcements_game_index');
        });
    }

    public function down()
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->dropIndex('winners_game_pattern_index');
            $table->dropIndex('winners_game_pattern_processed_index');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_game_winner_index');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('announcements_game_index');
        });
    }
};

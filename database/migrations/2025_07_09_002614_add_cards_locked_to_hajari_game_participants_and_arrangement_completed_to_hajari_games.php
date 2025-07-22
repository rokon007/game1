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
        Schema::table('hajari_game_participants', function (Blueprint $table) {
            $table->boolean('cards_locked')->default(false)->after('cards');
        });

        Schema::table('hajari_games', function (Blueprint $table) {
            $table->boolean('arrangement_completed')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hajari_game_participants', function (Blueprint $table) {
            $table->dropColumn('cards_locked');
        });

        Schema::table('hajari_games', function (Blueprint $table) {
            $table->dropColumn('arrangement_completed');
        });
    }
};

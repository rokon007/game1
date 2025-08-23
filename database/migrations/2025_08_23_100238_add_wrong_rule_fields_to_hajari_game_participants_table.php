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
            $table->boolean('is_wrong')->default(false)->after('cards_locked');
            $table->json('last_combination')->nullable()->after('is_wrong');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hajari_game_participants', function (Blueprint $table) {
            $table->dropColumn('is_wrong');
            $table->dropColumn('last_combination');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, update any existing 'joined' status to 'accepted' if needed
        DB::table('hajari_game_participants')
            ->where('status', 'joined')
            ->update(['status' => 'accepted']);

        // Modify the enum to include all necessary values
        DB::statement("ALTER TABLE hajari_game_participants MODIFY COLUMN status ENUM('invited', 'accepted', 'declined', 'joined', 'playing', 'finished') DEFAULT 'invited'");
    }

    public function down()
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE hajari_game_participants MODIFY COLUMN status ENUM('invited', 'accepted', 'declined', 'joined') DEFAULT 'invited'");
    }
};

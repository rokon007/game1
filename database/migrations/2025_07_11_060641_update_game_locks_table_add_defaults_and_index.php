<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ১. created_at ফিল্ডে default CURRENT_TIMESTAMP সেট করা
        DB::statement("ALTER TABLE game_locks MODIFY created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // ২. expires_at ফিল্ডে আলাদা ইনডেক্স যোগ করা
        Schema::table('game_locks', function (Blueprint $table) {
            $table->index('expires_at', 'idx_expires_at');
        });
    }

    public function down(): void
    {
        // ইনডেক্স রিমুভ করো
        Schema::table('game_locks', function (Blueprint $table) {
            $table->dropIndex('idx_expires_at');
        });

        // created_at এর default সরিয়ে ফেলো (অপশনাল)
        DB::statement("ALTER TABLE game_locks MODIFY created_at TIMESTAMP NOT NULL");
    }
};

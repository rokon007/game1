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
        Schema::create('game_locks', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('lock_key', 255)->unique(); // VARCHAR(255) NOT NULL UNIQUE
            $table->timestamp('created_at'); // TIMESTAMP NOT NULL
            $table->timestamp('expires_at'); // TIMESTAMP NOT NULL
            $table->index(['lock_key', 'expires_at'], 'idx_lock_key_expires'); // INDEX idx_lock_key_expires
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_locks');
    }
};

<?php
//2025_10_24_160458_create_crash_game_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crash_game_settings', function (Blueprint $table) {
            $table->id();

            // Basic Settings
            $table->decimal('house_edge', 5, 4)->default(0.05);
            $table->decimal('min_multiplier', 8, 2)->default(1.01);
            $table->decimal('max_multiplier', 8, 2)->default(100.00);
            $table->integer('bet_waiting_time')->default(10);
            $table->decimal('min_bet_amount', 10, 2)->default(1.00);
            $table->decimal('max_bet_amount', 10, 2)->default(10000.00);
            $table->boolean('is_active')->default(true);

            // Multiplier Speed Control
            $table->decimal('multiplier_increment', 8, 4)->default(0.01);
            $table->integer('multiplier_interval_ms')->default(100);
            $table->decimal('max_speed_multiplier', 8, 2)->default(5.00);
            $table->boolean('enable_auto_acceleration')->default(true);
            $table->enum('speed_profile', ['slow', 'medium', 'fast', 'custom'])->default('medium');

            $table->timestamps();
        });

        // Insert default settings
        DB::table('crash_game_settings')->insert([
            'house_edge' => 0.05,
            'min_multiplier' => 1.01,
            'max_multiplier' => 100.00,
            'bet_waiting_time' => 10,
            'min_bet_amount' => 1.00,
            'max_bet_amount' => 10000.00,
            'is_active' => true,
            'multiplier_increment' => 0.01,
            'multiplier_interval_ms' => 100,
            'max_speed_multiplier' => 5.00,
            'enable_auto_acceleration' => true,
            'speed_profile' => 'medium',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('crash_game_settings');
    }
};

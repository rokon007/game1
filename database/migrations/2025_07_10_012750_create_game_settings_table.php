<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, float, boolean
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('game_settings')->insert([
            [
                'key' => 'arrange_time_seconds',
                'value' => '240', // 4 minutes default
                'type' => 'integer',
                'description' => 'Time in seconds for players to arrange their cards',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'admin_commission_percentage',
                'value' => '5.0', // 5% default
                'type' => 'float',
                'description' => 'Admin commission percentage from winner prize',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('game_settings');
    }
};

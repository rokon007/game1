<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('welcome_bonus_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();
        });

        // default row create করা
        \DB::table('welcome_bonus_settings')->insert([
            'is_active' => false,
            'amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('welcome_bonus_settings');
    }
};


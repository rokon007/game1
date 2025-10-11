<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_refill_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refill_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bikash_number')->nullable();
            $table->string('nagad_number')->nullable();
            $table->string('rocket_number')->nullable();
            $table->string('upay_number')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Default data insert
        DB::table('refill_settings')->insert([
            'bikash_number' => '01711111111',
            'nagad_number' => '01711111111',
            'rocket_number' => '01711111111',
            'upay_number' => '01711111111',
            'instructions' => 'Please double-check the transaction number before proceeding.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('refill_settings');
    }
};

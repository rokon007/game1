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
        Schema::create('referral_settings', function (Blueprint $table) {
            $table->id(); // প্রাথমিক কী
            $table->decimal('commission_percentage', 5, 2)->default(0.00); // কমিশন শতাংশ (যেমন, ৫.০০ মানে ৫%)
            $table->integer('max_commission_count')->default(0); // সর্বাধিক কমিশন সংখ্যা
            $table->timestamps(); // তৈরি এবং আপডেটের সময়
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_settings');
    }
};

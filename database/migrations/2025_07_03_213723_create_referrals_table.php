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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id(); // প্রাথমিক কী
            $table->unsignedBigInteger('referrer_id'); // রেফারারের ইউজার আইডি
            $table->unsignedBigInteger('referred_user_id'); // রেফার করা ইউজারের আইডি
            $table->integer('commission_count')->default(0); // কমিশন পাওয়ার সংখ্যা
            $table->timestamps(); // তৈরি এবং আপডেটের সময়

            // ফরেন কী সংযোগ
            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};

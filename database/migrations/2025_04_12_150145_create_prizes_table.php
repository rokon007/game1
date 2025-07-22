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
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // যেমনঃ Full House, Top Line ইত্যাদি
            $table->unsignedInteger('amount'); // ক্রেডিট এমাউন্ট
            $table->string('description')->nullable(); // ঐচ্ছিক বর্ণনা
            $table->boolean('is_active')->default(true); // পুরস্কারটি চালু আছে কিনা
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};

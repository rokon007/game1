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
        Schema::create('how_to_guides', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // যেমন: How to Register?
            $table->text('description')->nullable(); // ব্যাখ্যা
            $table->string('video_url')->nullable(); // ইউটিউব লিংক
            $table->boolean('is_active')->default(true); // চাইলে hide/show করা যাবে
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('how_to_guides');
    }
};

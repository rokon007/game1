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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->dateTime('scheduled_at'); // কখন গেমটি শুরু হবে
            $table->decimal('ticket_price', 10, 2);
            $table->boolean('is_active')->default(true); // গেমটি সক্রিয় কিনা
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};

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
        Schema::table('hajari_games', function (Blueprint $table) {
            // গেম কখন স্বয়ংক্রিয়ভাবে বাতিল হবে তা ট্র্যাক করার জন্য
            $table->timestamp('cancellation_time')->nullable()->after('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hajari_games', function (Blueprint $table) {
            $table->dropColumn('cancellation_time');
        });
    }
};

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
        Schema::table('system_pools', function (Blueprint $table) {
            if (!Schema::hasColumn('system_pools', 'last_jackpot_at')) {
                $table->timestamp('last_jackpot_at')->nullable()->after('total_collected');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_pools', function (Blueprint $table) {
            if (Schema::hasColumn('system_pools', 'last_jackpot_at')) {
                $table->dropColumn('last_jackpot_at');
            }
        });
    }
};

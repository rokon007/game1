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
        Schema::table('games', function (Blueprint $table) {
            $table->decimal('corner_prize', 10, 2)->nullable()->after('ticket_price');
            $table->decimal('top_line_prize', 10, 2)->nullable()->after('corner_prize');
            $table->decimal('middle_line_prize', 10, 2)->nullable()->after('top_line_prize');
            $table->decimal('bottom_line_prize', 10, 2)->nullable()->after('middle_line_prize');
            $table->decimal('full_house_prize', 10, 2)->nullable()->after('bottom_line_prize');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'corner_prize',
                'top_line_prize',
                'middle_line_prize',
                'bottom_line_prize',
                'full_house_prize',
            ]);
        });
    }
};

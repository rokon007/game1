<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Modify the type enum to include admin_commission
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('debit', 'credit', 'admin_commission') NOT NULL");
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert back to original enum values
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('debit', 'credit') NOT NULL");
        });
    }
};

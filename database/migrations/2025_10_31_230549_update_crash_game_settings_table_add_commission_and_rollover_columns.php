<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crash_game_settings', function (Blueprint $table) {

            // ✅ Commission Configuration
            if (!Schema::hasColumn('crash_game_settings', 'admin_commission_rate')) {
                $table->decimal('admin_commission_rate', 5, 2)->default(10.00)->after('speed_profile');
            }
            if (!Schema::hasColumn('crash_game_settings', 'commission_type')) {
                $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage')->after('admin_commission_rate');
            }
            if (!Schema::hasColumn('crash_game_settings', 'fixed_commission_amount')) {
                $table->decimal('fixed_commission_amount', 10, 2)->default(0)->after('commission_type');
            }

            // ✅ Pool Management
            if (!Schema::hasColumn('crash_game_settings', 'min_pool_amount')) {
                $table->decimal('min_pool_amount', 10, 2)->default(100.00)->after('fixed_commission_amount');
            }
            if (!Schema::hasColumn('crash_game_settings', 'max_payout_ratio')) {
                $table->decimal('max_payout_ratio', 5, 2)->default(0.90)->after('min_pool_amount');
            }
            if (!Schema::hasColumn('crash_game_settings', 'enable_dynamic_crash')) {
                $table->boolean('enable_dynamic_crash')->default(true)->after('max_payout_ratio');
            }
            if (!Schema::hasColumn('crash_game_settings', 'crash_increase_per_cashout')) {
                $table->decimal('crash_increase_per_cashout', 5, 2)->default(0.50)->after('enable_dynamic_crash');
            }

            // ✅ Rollover Configuration
            if (!Schema::hasColumn('crash_game_settings', 'enable_pool_rollover')) {
                $table->boolean('enable_pool_rollover')->default(true)->after('crash_increase_per_cashout');
            }
            if (!Schema::hasColumn('crash_game_settings', 'rollover_percentage')) {
                $table->decimal('rollover_percentage', 5, 2)->default(100.00)->after('enable_pool_rollover');
            }
            if (!Schema::hasColumn('crash_game_settings', 'min_rollover_amount')) {
                $table->decimal('min_rollover_amount', 10, 2)->default(10.00)->after('rollover_percentage');
            }
            if (!Schema::hasColumn('crash_game_settings', 'max_rollover_amount')) {
                $table->decimal('max_rollover_amount', 10, 2)->default(10000.00)->after('min_rollover_amount');
            }
            if (!Schema::hasColumn('crash_game_settings', 'rollover_includes_commission')) {
                $table->boolean('rollover_includes_commission')->default(false)->after('max_rollover_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crash_game_settings', function (Blueprint $table) {
            $table->dropColumn([
                'admin_commission_rate',
                'commission_type',
                'fixed_commission_amount',
                'min_pool_amount',
                'max_payout_ratio',
                'enable_dynamic_crash',
                'crash_increase_per_cashout',
                'enable_pool_rollover',
                'rollover_percentage',
                'min_rollover_amount',
                'max_rollover_amount',
                'rollover_includes_commission',
            ]);
        });
    }
};

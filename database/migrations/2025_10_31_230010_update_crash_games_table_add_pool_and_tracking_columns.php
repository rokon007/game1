<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crash_games', function (Blueprint $table) {

            // ✅ Basic Game Update
            if (!Schema::hasColumn('crash_games', 'initial_crash_point')) {
                $table->decimal('initial_crash_point', 10, 2)->nullable()->after('crash_point');
            }

            // ✅ Bet Pool Management
            if (!Schema::hasColumn('crash_games', 'total_bet_pool')) {
                $table->decimal('total_bet_pool', 15, 2)->default(0)->after('crashed_at');
            }
            if (!Schema::hasColumn('crash_games', 'previous_rollover')) {
                $table->decimal('previous_rollover', 15, 2)->default(0)->after('total_bet_pool');
            }
            if (!Schema::hasColumn('crash_games', 'current_round_bets')) {
                $table->decimal('current_round_bets', 15, 2)->default(0)->after('previous_rollover');
            }
            if (!Schema::hasColumn('crash_games', 'max_possible_payout')) {
                $table->decimal('max_possible_payout', 15, 2)->default(0)->after('current_round_bets');
            }
            if (!Schema::hasColumn('crash_games', 'total_participants')) {
                $table->integer('total_participants')->default(0)->after('max_possible_payout');
            }
            if (!Schema::hasColumn('crash_games', 'active_participants')) {
                $table->integer('active_participants')->default(0)->after('total_participants');
            }

            // ✅ Commission Tracking
            if (!Schema::hasColumn('crash_games', 'admin_commission_amount')) {
                $table->decimal('admin_commission_amount', 15, 2)->default(0)->after('active_participants');
            }
            if (!Schema::hasColumn('crash_games', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(10.00)->after('admin_commission_amount');
            }

            // ✅ Payout Tracking
            if (!Schema::hasColumn('crash_games', 'total_payout')) {
                $table->decimal('total_payout', 15, 2)->default(0)->after('commission_rate');
            }
            if (!Schema::hasColumn('crash_games', 'remaining_pool')) {
                $table->decimal('remaining_pool', 15, 2)->default(0)->after('total_payout');
            }
            if (!Schema::hasColumn('crash_games', 'rollover_to_next')) {
                $table->decimal('rollover_to_next', 15, 2)->default(0)->after('remaining_pool');
            }

            // ✅ Pool Status
            if (!Schema::hasColumn('crash_games', 'pool_locked')) {
                $table->boolean('pool_locked')->default(false)->after('rollover_to_next');
            }
            if (!Schema::hasColumn('crash_games', 'pool_locked_at')) {
                $table->timestamp('pool_locked_at')->nullable()->after('pool_locked');
            }

            // ✅ Indexes
            $table->index('pool_locked');
        });
    }

    public function down(): void
    {
        Schema::table('crash_games', function (Blueprint $table) {
            $table->dropColumn([
                'initial_crash_point',
                'total_bet_pool',
                'previous_rollover',
                'current_round_bets',
                'max_possible_payout',
                'total_participants',
                'active_participants',
                'admin_commission_amount',
                'commission_rate',
                'total_payout',
                'remaining_pool',
                'rollover_to_next',
                'pool_locked',
                'pool_locked_at',
            ]);
        });
    }
};

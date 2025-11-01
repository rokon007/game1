<?php
// app/Services/CrashBetPoolService.php - FIXED VERSION

namespace App\Services;

use App\Models\CrashGame;
use App\Models\CrashBet;
use App\Models\CrashGameSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrashBetPoolService
{
    private $settings;

    public function __construct()
    {
        $this->settings = CrashGameSetting::first();
    }

    /**
     * 🔒 Lock bet pool when game starts
     */
    public function lockBetPool(CrashGame $game): void
    {
        // Get current round bets
        $currentBets = $game->bets()->where('status', 'pending')->sum('bet_amount');
        $participants = $game->bets()->where('status', 'pending')->count();

        // Get rollover from previous game
        $previousRollover = CrashGame::getLastRolloverAmount();

        // 🆕 NEW LOGIC: Total pool = current bets + previous rollover
        $totalPool = $currentBets + $previousRollover;

        // 🆕 Calculate MAX commission (10% of total pool)
        $maxCommission = $totalPool * ($this->settings->admin_commission_rate / 100);

        // Lock the pool
        $game->update([
            'current_round_bets' => $currentBets,
            'previous_rollover' => $previousRollover,
            'total_bet_pool' => $totalPool,
            'total_participants' => $participants,
            'active_participants' => $participants,
            'admin_commission_amount' => $maxCommission,
            'commission_rate' => $this->settings->admin_commission_rate ?? 10.00,
            'pool_locked' => true,
            'pool_locked_at' => now(),
        ]);

        // 🆕 Calculate initial crash point
        $initialCrashPoint = $this->calculateDynamicCrashPoint($game);

        $game->update([
            'initial_crash_point' => $initialCrashPoint,
            'crash_point' => $initialCrashPoint
        ]);

        Log::info("🔒 Pool locked", [
            'game_id' => $game->id,
            'current_bets' => $currentBets,
            'previous_rollover' => $previousRollover,
            'total_pool' => $totalPool,
            'max_commission' => $maxCommission,
            'participants' => $participants,
            'initial_crash' => $initialCrashPoint
        ]);
    }

    /**
     * 🆕 Dynamic crash point calculation
     */
    public function calculateDynamicCrashPoint(CrashGame $game): float
    {
        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        $availablePool = $totalPool - $maxCommission;

        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        if ($totalActiveBets <= 0) {
            return $this->settings->max_multiplier ?? 100.00;
        }

        $targetCrashPoint = $availablePool / $totalActiveBets;

        $minMultiplier = $this->settings->min_multiplier ?? 1.01;
        $maxMultiplier = $this->settings->max_multiplier ?? 100.00;

        $crashPoint = max($minMultiplier, min($maxMultiplier, $targetCrashPoint));

        return round($crashPoint, 2);
    }

    /**
     * 🆕 Recalculate crash point after cashout
     */
    public function recalculateCrashPoint(CrashGame $game): float
    {
        $game->refresh();

        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        // Calculate actual commission paid so far
        $wonBets = $game->wonBets()->get();
        $actualCommissionPaid = 0;
        foreach ($wonBets as $bet) {
            $actualCommissionPaid += ($bet->profit * 0.10);
        }

        $remainingCommission = max(0, $maxCommission - $actualCommissionPaid);

        // Calculate what's been paid out
        $totalPaid = $wonBets->sum('profit');

        // Available pool = Total - Paid profits - Remaining commission reserve
        $availablePool = $totalPool - $totalPaid - $remainingCommission;

        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        if ($totalActiveBets <= 0) {
            return $this->settings->max_multiplier ?? 100.00;
        }

        $newCrashPoint = $availablePool / $totalActiveBets;

        $minMultiplier = $this->settings->min_multiplier ?? 1.01;
        $maxMultiplier = $this->settings->max_multiplier ?? 100.00;

        $finalCrashPoint = max($minMultiplier, min($maxMultiplier, $newCrashPoint));

        Log::info("🔄 Crash point recalculated", [
            'total_pool' => $totalPool,
            'total_paid' => $totalPaid,
            'available_pool' => $availablePool,
            'total_active_bets' => $totalActiveBets,
            'new_crash' => $finalCrashPoint,
        ]);

        return round($finalCrashPoint, 2);
    }

    /**
     * 💰 Calculate commission
     */
    public function calculateCommission(float $betAmount): float
    {
        $commissionType = $this->settings->commission_type ?? 'percentage';

        if ($commissionType === 'fixed') {
            return (float) $this->settings->fixed_commission_amount;
        }

        $rate = ($this->settings->admin_commission_rate ?? 10.00) / 100;
        return round($betAmount * $rate, 2);
    }

    /**
     * 📈 Update crash point when someone cashes out
     */
    public function increaseCrashPoint(CrashGame $game, CrashBet $cashedOutBet): float
    {
        $game->decrement('active_participants');

        $newCrashPoint = $this->recalculateCrashPoint($game);

        $game->update(['crash_point' => $newCrashPoint]);

        return $newCrashPoint;
    }

    /**
     * 🎯 Check if all users cashed out
     */
    public function checkAndExtendCrashPoint(CrashGame $game): float
    {
        $activeBets = $game->activeBets()->count();

        if ($activeBets === 0 && $game->active_participants === 0) {
            $maxMultiplier = $this->settings->max_multiplier ?? 100.00;

            $game->update(['crash_point' => $maxMultiplier]);

            Log::info("🚀 All users cashed out - Extended to max", [
                'game_id' => $game->id,
                'new_crash' => $maxMultiplier
            ]);

            return $maxMultiplier;
        }

        return $game->crash_point;
    }

    /**
     * ✅✅✅ FIXED: Calculate final commission and rollover
     */
    public function calculateAndSetRollover(CrashGame $game): float
    {
        // ✅ Force refresh
        $game->refresh();

        $totalPool = (float) $game->total_bet_pool;
        $maxCommission = (float) $game->admin_commission_amount;

        // ✅ Validate total pool
        if ($totalPool <= 0) {
            Log::error("❌ Total pool is zero", [
                'game_id' => $game->id,
                'current_round_bets' => $game->current_round_bets,
                'previous_rollover' => $game->previous_rollover
            ]);

            $totalPool = $game->current_round_bets + $game->previous_rollover;

            if ($totalPool <= 0) {
                Log::error("❌ Still zero after recalculation");
                return 0;
            }
        }

        // ✅ Calculate actual commission (10% of each winner's profit)
        $wonBets = $game->wonBets()->get();
        $actualCommission = 0;

        foreach ($wonBets as $bet) {
            $actualCommission += ($bet->profit * 0.10);
        }

        // Cap at max commission
        $actualCommission = min($actualCommission, $maxCommission);

        // ✅ Calculate total payout (শুধু profit, bet amount নয়)
        $totalPayout = $wonBets->sum('profit');

        // ✅ Update game
        $game->update([
            'admin_commission_amount' => $actualCommission,
            'total_payout' => $totalPayout
        ]);

        // ✅ Refresh again
        $game->refresh();

        // ✅ CORRECT CALCULATION:
        // Remaining = Total Pool - Total Payout - Actual Commission
        $remaining = $totalPool - $totalPayout - $actualCommission;

        // ✅ Update remaining pool
        $game->update(['remaining_pool' => $remaining]);

        // ✅ Calculate rollover
        if (!$this->settings->enable_pool_rollover) {
            $game->update(['rollover_to_next' => 0]);

            Log::info("📊 No rollover (disabled)", [
                'game_id' => $game->id,
                'total_pool' => $totalPool,
                'total_payout' => $totalPayout,
                'actual_commission' => $actualCommission,
                'remaining' => $remaining,
            ]);

            return 0;
        }

        $rolloverAmount = $this->settings->calculateRollover($remaining, 0);

        $game->update(['rollover_to_next' => $rolloverAmount]);

        // ✅ DETAILED LOG
        Log::info("📊 Rollover Calculation Complete", [
            'game_id' => $game->id,
            '1_total_pool' => $totalPool,
            '2_max_commission' => $maxCommission,
            '3_actual_commission' => $actualCommission,
            '4_total_payout' => $totalPayout,
            '5_remaining_pool' => $remaining,
            '6_rollover_amount' => $rolloverAmount,
            '7_admin_keeps' => $remaining - $rolloverAmount + $actualCommission,
        ]);

        return $rolloverAmount;
    }

    /**
     * 📊 Get pool statistics
     */
    public function getPoolStats(CrashGame $game): array
    {
        $game->refresh();

        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;
        $actualCommission = 0;

        if ($game->status === 'crashed') {
            $actualCommission = $game->admin_commission_amount;
        } else {
            foreach ($game->wonBets as $bet) {
                $actualCommission += $bet->profit * 0.10;
            }
            $actualCommission = min($actualCommission, $maxCommission);
        }

        $availablePool = $totalPool - $actualCommission;
        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        return [
            'total_pool' => $totalPool,
            'current_round_bets' => $game->current_round_bets,
            'previous_rollover' => $game->previous_rollover,
            'max_commission' => $maxCommission,
            'actual_commission' => $actualCommission,
            'available_pool' => $availablePool,
            'participants' => $game->total_participants,
            'active_participants' => $game->active_participants,
            'total_active_bets' => $totalActiveBets,
            'crash_point' => $game->crash_point,
            'initial_crash_point' => $game->initial_crash_point,
            'total_payout' => $game->total_payout ?? 0,
            'remaining_pool' => $game->remaining_pool ?? 0,
            'rollover_to_next' => $game->rollover_to_next ?? 0,
        ];
    }

    /**
     * 🔄 Reload settings
     */
    public function reloadSettings(): void
    {
        $this->settings = CrashGameSetting::first();
    }
}

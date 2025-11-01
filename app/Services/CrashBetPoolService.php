<?php
// app/Services/CrashBetPoolService.php - NEW DYNAMIC LOGIC

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
            'admin_commission_amount' => $maxCommission, // MAX possible commission
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

        Log::info("🔒 Pool locked with new logic", [
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
     * 🆕 NEW: Dynamic crash point calculation
     * Formula: Available Pool ÷ Total Active Bets
     */
    public function calculateDynamicCrashPoint(CrashGame $game): float
    {
        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        // Available pool = Total - Max Commission
        $availablePool = $totalPool - $maxCommission;

        // Get total active bets (only playing bets)
        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        if ($totalActiveBets <= 0) {
            // No bets, return max multiplier
            return $this->settings->max_multiplier ?? 100.00;
        }

        // 🎯 TARGET CRASH POINT = Available Pool ÷ Total Active Bets
        $targetCrashPoint = $availablePool / $totalActiveBets;

        // Apply min/max limits
        $minMultiplier = $this->settings->min_multiplier ?? 1.01;
        $maxMultiplier = $this->settings->max_multiplier ?? 100.00;

        $crashPoint = max($minMultiplier, min($maxMultiplier, $targetCrashPoint));

        Log::info("💡 Crash point calculated", [
            'total_pool' => $totalPool,
            'max_commission' => $maxCommission,
            'available_pool' => $availablePool,
            'total_active_bets' => $totalActiveBets,
            'target_crash' => $targetCrashPoint,
            'final_crash' => $crashPoint,
        ]);

        return round($crashPoint, 2);
    }

    /**
     * 🆕 UPDATED: Recalculate crash point after cashout
     */
    public function recalculateCrashPoint(CrashGame $game): float
    {
        // Get updated data
        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        // Calculate actual commission paid so far
        $actualCommissionPaid = $game->wonBets()->sum(DB::raw('profit * 0.10')); // 10% of profit

        // Remaining commission allowance
        $remainingCommission = max(0, $maxCommission - $actualCommissionPaid);

        // Available pool = Total Pool - Already Paid - Remaining Commission Reserve
        $totalPaid = $game->wonBets()->sum('profit');
        $availablePool = $totalPool - $totalPaid - $remainingCommission;

        // Get remaining active bets
        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        if ($totalActiveBets <= 0) {
            // All cashed out, go to max
            return $this->settings->max_multiplier ?? 100.00;
        }

        // Recalculate crash point
        $newCrashPoint = $availablePool / $totalActiveBets;

        // Apply limits
        $minMultiplier = $this->settings->min_multiplier ?? 1.01;
        $maxMultiplier = $this->settings->max_multiplier ?? 100.00;

        $finalCrashPoint = max($minMultiplier, min($maxMultiplier, $newCrashPoint));

        Log::info("🔄 Crash point recalculated", [
            'available_pool' => $availablePool,
            'total_active_bets' => $totalActiveBets,
            'new_crash' => $newCrashPoint,
            'final_crash' => $finalCrashPoint,
        ]);

        return round($finalCrashPoint, 2);
    }

    /**
     * 💰 Calculate commission (NOT used upfront anymore)
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
        // Reduce active participants
        $game->decrement('active_participants');

        // 🆕 Recalculate crash point based on remaining pool
        $newCrashPoint = $this->recalculateCrashPoint($game);

        // Update game
        $game->update(['crash_point' => $newCrashPoint]);

        Log::info("📈 Crash point updated after cashout", [
            'game_id' => $game->id,
            'user_id' => $cashedOutBet->user_id,
            'new_crash' => $newCrashPoint,
        ]);

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
     * 🆕 Calculate final commission and rollover
     */
    public function calculateAndSetRollover(CrashGame $game): float
    {
        //$game->refresh();

        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        // Calculate actual commission collected (10% of each cashout profit)
        $actualCommission = 0;
        foreach ($game->wonBets as $bet) {
            $commission = $bet->profit * 0.10; // 10% of profit
            $actualCommission += $commission;
        }

        // Cap at max commission
        $actualCommission = min($actualCommission, $maxCommission);

        // Update game with actual commission
        //$game->update(['admin_commission_amount' => $actualCommission]);

        // Calculate total paid (including commission)
        //$totalPaidToWinners = $game->wonBets()->sum('profit') + $game->wonBets()->sum('bet_amount');
        //$game->update(['total_payout' => $totalPaidToWinners]);

         $totalPaidToWinners = 0;
            foreach ($game->wonBets as $bet) {
                $totalPaidToWinners += $bet->bet_amount + $bet->profit;
            }

            $game->update([
                'admin_commission_amount' => $actualCommission,
                'total_payout' => $totalPaidToWinners  // 🎯 এখন bet amount + profit থাকবে
            ]);



       // $game->refresh();
        // Remaining pool = Total Pool - Paid to Winners - Actual Commission
        $remaining = $totalPool - $totalPaidToWinners - $actualCommission;
        $game->update(['remaining_pool' => $remaining]);

        // Calculate rollover
        if (!$this->settings->enable_pool_rollover) {
            $game->update(['rollover_to_next' => 0]);
            return 0;
        }

        $rolloverAmount = $this->settings->calculateRollover($remaining, 0); // No commission in rollover base

        $game->update(['rollover_to_next' => $rolloverAmount]);

        Log::info("🔄 Rollover calculated with new logic", [
            'game_id' => $game->id,
            'total_pool' => $totalPool,
            'max_commission' => $maxCommission,
            'actual_commission' => $actualCommission,
            'paid_to_winners' => $totalPaidToWinners,
            'remaining' => $remaining,
            'rollover' => $rolloverAmount,
        ]);

        return $rolloverAmount;
    }


    // CrashBetPoolService.php - calculateAndSetRollover মেথডে
    // এইটি সঠিক ভাবে কাজ না করায় কমেন্ট করা হলো
    // public function calculateAndSetRollover(CrashGame $game): float
    // {
    //     // ✅ ADD THIS: Force refresh before calculation
    //     $game->refresh();

    //     $totalPool = $game->total_bet_pool;
    //     $maxCommission = $game->admin_commission_amount;

    //     // Calculate actual commission collected (10% of each cashout profit)
    //     $actualCommission = 0;
    //     foreach ($game->wonBets as $bet) {
    //         $commission = $bet->profit * 0.10; // 10% of profit
    //         $actualCommission += $commission;
    //     }

    //     // Cap at max commission
    //     $actualCommission = min($actualCommission, $maxCommission);

    //     // ✅ ADD VALIDATION: Ensure totalPool is not zero
    //     if ($totalPool <= 0) {
    //         Log::error("❌ Total pool is zero or negative in calculateAndSetRollover", [
    //             'game_id' => $game->id,
    //             'total_pool' => $totalPool,
    //             'current_round_bets' => $game->current_round_bets,
    //             'previous_rollover' => $game->previous_rollover
    //         ]);

    //         // Fallback calculation
    //         $totalPool = $game->current_round_bets + $game->previous_rollover;
    //     }

    //     // Update game with actual commission
    //     $game->update([
    //         'admin_commission_amount' => $actualCommission,
    //         'total_payout' => $game->wonBets()->sum('profit')
    //     ]);

    //     // ✅ REFRESH AGAIN: Get updated data
    //     $game->refresh();

    //     // Remaining pool = Total Pool - Paid to Winners - Actual Commission
    //     $remaining = $totalPool - $game->total_payout - $actualCommission;

    //     $game->update(['remaining_pool' => $remaining]);

    //     // Calculate rollover
    //     if (!$this->settings->enable_pool_rollover) {
    //         $game->update(['rollover_to_next' => 0]);
    //         return 0;
    //     }

    //     $rolloverAmount = $this->settings->calculateRollover($remaining, 0);

    //     $game->update(['rollover_to_next' => $rolloverAmount]);

    //     Log::info("🔄 Rollover calculated", [
    //         'game_id' => $game->id,
    //         'total_pool' => $totalPool,
    //         'max_commission' => $maxCommission,
    //         'actual_commission' => $actualCommission,
    //         'paid_to_winners' => $game->total_payout,
    //         'remaining' => $remaining,
    //         'rollover' => $rolloverAmount,
    //     ]);

    //     return $rolloverAmount;
    // }

    // public function calculateAndSetRollover(CrashGame $game): float
    // {
    //     $game->refresh();

    //     $totalPool = $game->total_bet_pool;
    //     $maxCommission = $game->admin_commission_amount;

    //     // Calculate actual commission collected (10% of each cashout profit)
    //     $actualCommission = 0;
    //     foreach ($game->wonBets as $bet) {
    //         $commission = $bet->profit * 0.10; // 10% of profit
    //         $actualCommission += $commission;
    //     }

    //     // Cap at max commission
    //     $actualCommission = min($actualCommission, $maxCommission);

    //     // ✅ CHANGED: Calculate TOTAL WIN AMOUNT (bet amount + profit)
    //     $totalWinAmount = 0;
    //     foreach ($game->wonBets as $bet) {
    //         $totalWinAmount += $bet->profit;
    //     }

    //     // Alternative method using query (if you prefer):
    //     // $totalWinAmount = $game->wonBets()->sum(DB::raw('bet_amount + profit'));

    //     // ✅ CHANGED: Update game with actual commission and TOTAL WIN AMOUNT
    //     $game->update([
    //         'admin_commission_amount' => $actualCommission,
    //         'total_payout' => $totalWinAmount  // 🎯 এখন bet amount + profit থাকবে
    //     ]);

    //     $game->refresh();

    //     // ✅ CHANGED: Remaining pool = Total Pool - Total Win Amount - Actual Commission
    //     $remaining = $totalPool - $game->total_payout - $actualCommission;

    //     $game->update(['remaining_pool' => $remaining]);

    //     // Calculate rollover
    //     if (!$this->settings->enable_pool_rollover) {
    //         $game->update(['rollover_to_next' => 0]);
    //         return 0;
    //     }

    //     $rolloverAmount = $this->settings->calculateRollover($remaining, 0);
    //     $game->update(['rollover_to_next' => $rolloverAmount]);

    //     Log::info("🔄 Rollover calculated", [
    //         'game_id' => $game->id,
    //         'total_pool' => $totalPool,
    //         'max_commission' => $maxCommission,
    //         'actual_commission' => $actualCommission,
    //         'total_win_amount' => $totalWinAmount, // ✅ নতুন লগ
    //         'paid_to_winners' => $game->total_payout,
    //         'remaining' => $remaining,
    //         'rollover' => $rolloverAmount,
    //     ]);

    //     return $rolloverAmount;
    // }

    /**
     * 📊 Get pool statistics
     */
    public function getPoolStats(CrashGame $game): array
    {
        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;
        $actualCommission = 0;

        if ($game->status === 'crashed') {
            $actualCommission = $game->admin_commission_amount;
        } else {
            // Calculate current commission
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

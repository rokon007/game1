<?php
// app/Services/CrashBetPoolService.php

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

        // ✅ Total pool = current bets + previous rollover
        $totalPool = $currentBets + $previousRollover;

        // ✅ Calculate MAX commission (10% of total pool)
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

        // 🎲 Calculate RANDOM crash point (FIXED, no recalculation)
        $crashPoint = $this->generateRandomCrashPoint($game);

        $game->update([
            'initial_crash_point' => $crashPoint,
            'crash_point' => $crashPoint
        ]);

        Log::info("🔒 Pool locked with RANDOM crash point", [
            'game_id' => $game->id,
            'current_bets' => $currentBets,
            'previous_rollover' => $previousRollover,
            'total_pool' => $totalPool,
            'max_commission' => $maxCommission,
            'participants' => $participants,
            'crash_point' => $crashPoint,
            'generation_method' => $participants > 0 ? 'pool_based_random' : 'pure_random'
        ]);
    }

    /**
     * 🎲 Generate random crash point
     */
    private function generateRandomCrashPoint(CrashGame $game): float
    {
        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;
        $availablePool = $totalPool - $maxCommission;

        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        // ✅ If there are bets, calculate pool-based max crash
        if ($totalActiveBets > 0 && $availablePool > 0) {
            // Maximum possible crash point based on pool
            $maxPoolBasedCrash = $availablePool / $totalActiveBets;

            // Apply limits
            $minMultiplier = $this->settings->min_multiplier ?? 1.01;
            $maxMultiplier = min(
                $this->settings->max_multiplier ?? 100.00,
                $maxPoolBasedCrash
            );

            // 🎲 Generate random crash point between min and max
            $crashPoint = $this->generateWeightedRandomCrash($minMultiplier, $maxMultiplier);

            Log::info("🎲 Pool-based random crash generated", [
                'total_pool' => $totalPool,
                'available_pool' => $availablePool,
                'total_bets' => $totalActiveBets,
                'max_pool_crash' => $maxPoolBasedCrash,
                'min_crash' => $minMultiplier,
                'max_crash' => $maxMultiplier,
                'final_crash' => $crashPoint
            ]);

            return round($crashPoint, 2);
        }

        // ✅ If NO bets, generate pure random crash
        $minMultiplier = $this->settings->min_multiplier ?? 1.01;
        $maxMultiplier = $this->settings->max_multiplier ?? 100.00;

        $crashPoint = $this->generateWeightedRandomCrash($minMultiplier, $maxMultiplier);

        Log::info("🎲 Pure random crash generated (no bets)", [
            'min_crash' => $minMultiplier,
            'max_crash' => $maxMultiplier,
            'final_crash' => $crashPoint
        ]);

        return round($crashPoint, 2);
    }

    /**
     * 🎯 Generate weighted random crash point
     * Lower crashes are more common, higher crashes are rare
     */
    private function generateWeightedRandomCrash(float $min, float $max): float
    {
        // 🎲 Weighted distribution:
        // 60% chance: 1.00x - 2.00x (Low)
        // 30% chance: 2.00x - 5.00x (Medium)
        // 8% chance: 5.00x - 10.00x (High)
        // 2% chance: 10.00x - max (Very High)

        $rand = mt_rand(1, 100);

        if ($rand <= 60) {
            // Low crash (60%)
            $rangeMin = max($min, 1.01);
            $rangeMax = min($max, 2.00);
            return $this->randomFloat($rangeMin, $rangeMax);
        }
        elseif ($rand <= 90) {
            // Medium crash (30%)
            $rangeMin = max($min, 2.00);
            $rangeMax = min($max, 5.00);
            return $this->randomFloat($rangeMin, $rangeMax);
        }
        elseif ($rand <= 98) {
            // High crash (8%)
            $rangeMin = max($min, 5.00);
            $rangeMax = min($max, 10.00);
            return $this->randomFloat($rangeMin, $rangeMax);
        }
        else {
            // Very high crash (2%)
            $rangeMin = max($min, 10.00);
            $rangeMax = $max;
            return $this->randomFloat($rangeMin, $rangeMax);
        }
    }

    /**
     * 🎲 Generate random float between min and max
     */
    private function randomFloat(float $min, float $max): float
    {
        if ($min >= $max) {
            return $min;
        }

        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    /**
     * ✅ REMOVED: Recalculate crash point (NO LONGER NEEDED)
     */
    // public function recalculateCrashPoint() { ... } ❌ DELETED

    /**
     * ✅ REMOVED: Increase crash point on cashout (NO LONGER NEEDED)
     */
    // public function increaseCrashPoint() { ... } ❌ DELETED

    /**
     * ✅ REMOVED: Check and extend crash point (NO LONGER NEEDED)
     */
    // public function checkAndExtendCrashPoint() { ... } ❌ DELETED

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
     * ✅ Calculate final commission and rollover
     */
    public function calculateAndSetRollover(CrashGame $game): float
    {
        $game->refresh();

        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        // Calculate actual commission collected and total wins paid
        $actualCommission = 0;
        $totalWinsPaid = 0;

        foreach ($game->wonBets as $bet) {
            $winAmount = $bet->bet_amount * $bet->cashout_at;
            $profit = $bet->profit;
            $commission = $profit * 0.10;

            $totalWinsPaid += $winAmount;
            $actualCommission += $commission;
        }

        // Cap commission at max
        $actualCommission = min($actualCommission, $maxCommission);

        // Validate total pool
        if ($totalPool <= 0) {
            Log::error("❌ Total pool is zero in calculateAndSetRollover", [
                'game_id' => $game->id,
                'current_round_bets' => $game->current_round_bets,
                'previous_rollover' => $game->previous_rollover
            ]);

            $totalPool = $game->current_round_bets + $game->previous_rollover;
        }

        // Update game
        $game->update([
            'admin_commission_amount' => $actualCommission,
            'total_payout' => $totalWinsPaid
        ]);

        $game->refresh();

        // Remaining pool = Total Pool - Total Wins Paid - Commission
        $remaining = $totalPool - $totalWinsPaid - $actualCommission;

        $game->update(['remaining_pool' => $remaining]);

        // Calculate rollover
        if (!$this->settings->enable_pool_rollover) {
            $game->update(['rollover_to_next' => 0]);
            return 0;
        }

        $rolloverAmount = $this->settings->calculateRollover($remaining, 0);

        $game->update(['rollover_to_next' => $rolloverAmount]);

        Log::info("🔄 Rollover calculated", [
            'game_id' => $game->id,
            'total_pool' => $totalPool,
            'max_commission' => $maxCommission,
            'actual_commission' => $actualCommission,
            'total_wins_paid' => $totalWinsPaid,
            'remaining_pool' => $remaining,
            'rollover' => $rolloverAmount,
            'crash_point' => $game->crash_point,
            'formula' => "Pool ($totalPool) - Wins ($totalWinsPaid) - Commission ($actualCommission) = $remaining"
        ]);

        return $rolloverAmount;
    }

    /**
     * 📊 Get pool statistics
     */
    public function getPoolStats(CrashGame $game): array
    {
        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;
        $actualCommission = 0;
        $totalWinsPaid = 0;

        if ($game->status === 'crashed') {
            $actualCommission = $game->admin_commission_amount;
            $totalWinsPaid = $game->total_payout;
        } else {
            foreach ($game->wonBets as $bet) {
                $winAmount = $bet->bet_amount * $bet->cashout_at;
                $profit = $bet->profit;
                $commission = $profit * 0.10;

                $totalWinsPaid += $winAmount;
                $actualCommission += $commission;
            }
            $actualCommission = min($actualCommission, $maxCommission);
        }

        $availablePool = $totalPool - $totalWinsPaid - $actualCommission;
        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        return [
            'total_pool' => $totalPool,
            'current_round_bets' => $game->current_round_bets,
            'previous_rollover' => $game->previous_rollover,
            'max_commission' => $maxCommission,
            'actual_commission' => $actualCommission,
            'total_wins_paid' => $totalWinsPaid,
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

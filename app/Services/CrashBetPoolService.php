<?php
// app/Services/CrashBetPoolService.php - WITH ROLLOVER SUPPORT

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
     * 🔒 Lock bet pool when game starts - WITH ROLLOVER
     */
    public function lockBetPool(CrashGame $game): void
    {
        // Get current round bets
        $currentBets = $game->bets()->where('status', 'pending')->sum('bet_amount');
        $participants = $game->bets()->where('status', 'pending')->count();

        // 🆕 Get rollover from previous game
        $previousRollover = CrashGame::getLastRolloverAmount();

        // Total pool = current bets + previous rollover
        $totalPool = $currentBets + $previousRollover;

        // Calculate commission (only on current bets, not rollover)
        $commission = $this->calculateCommission($currentBets);

        // Lock the pool
        $game->update([
            'current_round_bets' => $currentBets,
            'previous_rollover' => $previousRollover,
            'total_bet_pool' => $totalPool,
            'total_participants' => $participants,
            'active_participants' => $participants,
            'admin_commission_amount' => $commission,
            'commission_rate' => $this->settings->admin_commission_rate ?? 10.00,
            'pool_locked' => true,
            'pool_locked_at' => now(),
        ]);

        // Calculate and set initial crash point
        $initialCrashPoint = $this->calculateDynamicCrashPoint($game);

        $game->update([
            'initial_crash_point' => $initialCrashPoint,
            'crash_point' => $initialCrashPoint
        ]);

        Log::info("🔒 Bet pool locked with rollover", [
            'game_id' => $game->id,
            'current_bets' => $currentBets,
            'previous_rollover' => $previousRollover,
            'total_pool' => $totalPool,
            'participants' => $participants,
            'commission' => $commission,
            'crash_point' => $initialCrashPoint
        ]);
    }

    /**
     * 📊 Calculate initial crash point based on bet pool (with rollover)
     */
    public function calculateDynamicCrashPoint(CrashGame $game): float
    {
        $totalBetPool = $game->total_bet_pool;
        $participants = $game->total_participants;

        if ($totalBetPool <= 0 || $participants <= 0) {
            return $this->getDefaultCrashPoint();
        }

        // Step 1: Calculate available pool (total - commission)
        $commission = $game->admin_commission_amount;
        $availablePool = $totalBetPool - $commission;

        // Step 2: Calculate maximum safe payout
        $maxPayoutRatio = $this->settings->max_payout_ratio ?? 0.90;
        $maxSafePayout = $availablePool * $maxPayoutRatio;

        // Step 3: Calculate base crash point
        // If all players hold till crash, payout = total_pool * crash_point
        // We want: total_pool * crash_point <= maxSafePayout
        // So: crash_point <= maxSafePayout / total_pool
        $baseCrashPoint = $maxSafePayout / $totalBetPool;

        // Step 4: Apply house edge
        $houseEdge = $this->settings->house_edge ?? 0.05;
        $adjustedCrashPoint = $baseCrashPoint * (1 - $houseEdge);

        // Step 5: Apply min/max limits
        $minMultiplier = $this->settings->min_multiplier ?? 1.01;
        $maxMultiplier = $this->settings->max_multiplier ?? 100.00;

        $crashPoint = max($minMultiplier, min($maxMultiplier, $adjustedCrashPoint));

        // Step 6: Add randomness for fairness (±10%)
        $randomFactor = mt_rand(90, 110) / 100;
        $finalCrashPoint = $crashPoint * $randomFactor;

        $result = round(max($minMultiplier, min($maxMultiplier, $finalCrashPoint)), 2);

        Log::info("💡 Crash point calculated", [
            'total_pool' => $totalBetPool,
            'available_pool' => $availablePool,
            'max_safe_payout' => $maxSafePayout,
            'base_crash' => $baseCrashPoint,
            'adjusted_crash' => $adjustedCrashPoint,
            'final_crash' => $result,
        ]);

        return $result;
    }

    /**
     * 💰 Calculate admin commission
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
     * 📈 Increase crash point when someone cashes out
     */
    public function increaseCrashPoint(CrashGame $game, CrashBet $cashedOutBet): float
    {
        if (!$this->settings->enable_dynamic_crash) {
            return $game->crash_point;
        }

        // Reduce active participants
        $game->decrement('active_participants');

        // Calculate increase amount
        $increaseRate = $this->settings->crash_increase_per_cashout ?? 0.50;
        $newCrashPoint = $game->crash_point + $increaseRate;

        // Apply max limit
        $maxMultiplier = $this->settings->max_multiplier ?? 100.00;
        $finalCrashPoint = min($newCrashPoint, $maxMultiplier);

        // Update game
        $game->update(['crash_point' => $finalCrashPoint]);

        Log::info("📈 Crash point increased", [
            'game_id' => $game->id,
            'old_crash' => $game->crash_point,
            'new_crash' => $finalCrashPoint,
            'cashout_user' => $cashedOutBet->user_id
        ]);

        return $finalCrashPoint;
    }

    /**
     * 🎯 Check if all users cashed out - extend to max
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
     * 🆕 Calculate rollover for next game
     */
    public function calculateAndSetRollover(CrashGame $game): float
    {
        if (!$this->settings->enable_pool_rollover) {
            $game->update(['rollover_to_next' => 0]);
            return 0;
        }

        // Calculate total paid to winners
        $totalPaid = $game->wonBets()->sum('profit');
        $game->update(['total_payout' => $totalPaid]);

        // Calculate remaining pool
        $availablePool = $game->total_bet_pool - $game->admin_commission_amount;
        $remaining = max(0, $availablePool - $totalPaid);
        $game->update(['remaining_pool' => $remaining]);

        // Calculate rollover amount
        $rolloverAmount = $this->settings->calculateRollover(
            $remaining,
            $game->admin_commission_amount
        );

        // Save rollover
        $game->update(['rollover_to_next' => $rolloverAmount]);

        Log::info("🔄 Rollover calculated", [
            'game_id' => $game->id,
            'total_pool' => $game->total_bet_pool,
            'commission' => $game->admin_commission_amount,
            'paid_to_winners' => $totalPaid,
            'remaining' => $remaining,
            'rollover_to_next' => $rolloverAmount,
            'admin_keeps' => $remaining - $rolloverAmount + $game->admin_commission_amount,
        ]);

        return $rolloverAmount;
    }

    /**
     * 📊 Get pool statistics
     */
    public function getPoolStats(CrashGame $game): array
    {
        $totalPool = $game->total_bet_pool;
        $commission = $game->admin_commission_amount;
        $availablePool = $totalPool - $commission;
        $currentPotentialPayout = $this->calculateCurrentPotentialPayout($game);

        return [
            'total_pool' => $totalPool,
            'current_round_bets' => $game->current_round_bets,
            'previous_rollover' => $game->previous_rollover,
            'commission' => $commission,
            'available_pool' => $availablePool,
            'participants' => $game->total_participants,
            'active_participants' => $game->active_participants,
            'current_potential_payout' => $currentPotentialPayout,
            'pool_safety' => $this->calculatePoolSafety($game),
            'crash_point' => $game->crash_point,
            'initial_crash_point' => $game->initial_crash_point,
            'total_payout' => $game->total_payout,
            'remaining_pool' => $game->remaining_pool,
            'rollover_to_next' => $game->rollover_to_next,
        ];
    }

    /**
     * 💸 Calculate current potential payout
     */
    private function calculateCurrentPotentialPayout(CrashGame $game): float
    {
        return $game->activeBets()
            ->sum(DB::raw('bet_amount * ' . $game->crash_point));
    }

    /**
     * 🛡️ Calculate pool safety percentage
     */
    private function calculatePoolSafety(CrashGame $game): float
    {
        $availablePool = $game->total_bet_pool - $game->admin_commission_amount;
        $potentialPayout = $this->calculateCurrentPotentialPayout($game);

        if ($potentialPayout <= 0) {
            return 100.00;
        }

        return round(($availablePool / $potentialPayout) * 100, 2);
    }

    /**
     * 🎲 Get default crash point (fallback)
     */
    private function getDefaultCrashPoint(): float
    {
        $random = $this->getSecureRandomFloat();
        $houseEdge = $this->settings->house_edge ?? 0.05;
        $adjustedRandom = $random * (1 - $houseEdge);

        if ($adjustedRandom <= 0) {
            return $this->settings->min_multiplier ?? 1.01;
        }

        $crashPoint = 1 / $adjustedRandom;

        return round(max(
            $this->settings->min_multiplier ?? 1.01,
            min($this->settings->max_multiplier ?? 100.00, $crashPoint)
        ), 2);
    }

    /**
     * 🔐 Secure random float generator
     */
    private function getSecureRandomFloat(): float
    {
        try {
            $randomBytes = random_bytes(4);
            $randomInt = unpack('L', $randomBytes)[1];
            return $randomInt / 0xFFFFFFFF;
        } catch (\Exception $e) {
            return mt_rand() / mt_getrandmax();
        }
    }

    /**
     * 🔄 Reload settings
     */
    public function reloadSettings(): void
    {
        $this->settings = CrashGameSetting::first();
    }
}

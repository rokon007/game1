<?php
// app/Services/CrashBetPoolService.php - POOL LOSES WIN + COMMISSION

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

        // Calculate initial crash point
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
     * 🎯 Dynamic crash point calculation
     */
    public function calculateDynamicCrashPoint(CrashGame $game): float
    {
        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        // Available pool = Total - Max Commission
        $availablePool = $totalPool - $maxCommission;

        // Get total active bets
        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        if ($totalActiveBets <= 0) {
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
     * ✅ FIXED: Recalculate crash point - Pool loses Win + Commission
     */
    public function recalculateCrashPoint(CrashGame $game, float $latestWinAmount = 0, float $latestCommission = 0): float
    {
        $game->refresh();

        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        // ✅ Calculate total WIN AMOUNT paid out so far
        $totalWinsPaid = 0;
        $totalCommissionCollected = 0;

        foreach ($game->wonBets as $bet) {
            $winAmount = $bet->bet_amount * $bet->cashout_at;
            $profit = $bet->profit;
            $commission = $profit * 0.10;

            $totalWinsPaid += $winAmount;
            $totalCommissionCollected += $commission;
        }

        // Add latest cashout
        $totalWinsPaid += $latestWinAmount;
        $totalCommissionCollected += $latestCommission;

        // Remaining commission allowance
        $remainingCommission = max(0, $maxCommission - $totalCommissionCollected);

        // ✅ Available pool = Total Pool - Total Wins Paid - Total Commission - Remaining Commission Reserve
        $availablePool = $totalPool - $totalWinsPaid - $totalCommissionCollected - $remainingCommission;

        // Get remaining active bets
        $totalActiveBets = $game->activeBets()->sum('bet_amount');

        if ($totalActiveBets <= 0) {
            return $this->settings->max_multiplier ?? 100.00;
        }

        // Recalculate crash point
        $newCrashPoint = $availablePool / $totalActiveBets;

        // Apply limits
        $minMultiplier = $this->settings->min_multiplier ?? 1.01;
        $maxMultiplier = $this->settings->max_multiplier ?? 100.00;

        $finalCrashPoint = max($minMultiplier, min($maxMultiplier, $newCrashPoint));

        Log::info("🔄 Crash point recalculated", [
            'total_pool' => $totalPool,
            'total_wins_paid' => $totalWinsPaid,
            'total_commission_collected' => $totalCommissionCollected,
            'remaining_commission' => $remainingCommission,
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

        // Calculate amounts
        $winAmount = $cashedOutBet->bet_amount * $cashedOutBet->cashout_at;
        $profit = $cashedOutBet->profit;
        $commission = $profit * 0.10;

        $newCrashPoint = $this->recalculateCrashPoint($game, $winAmount, $commission);

        $game->update(['crash_point' => $newCrashPoint]);

        Log::info("📈 Crash point updated after cashout", [
            'game_id' => $game->id,
            'user_id' => $cashedOutBet->user_id,
            'win_amount' => $winAmount,
            'commission_deducted' => $commission,
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
     * ✅ FIXED: Calculate final commission and rollover - Pool loses Win + Commission
     */
    public function calculateAndSetRollover(CrashGame $game): float
    {
        $game->refresh();

        $totalPool = $game->total_bet_pool;
        $maxCommission = $game->admin_commission_amount;

        // ✅ Calculate actual commission collected and total wins paid
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

        // ✅ Validate total pool
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
            'total_payout' => $totalWinsPaid // Total wins paid to users
        ]);

        $game->refresh();

        // ✅ Remaining pool = Total Pool - Total Wins Paid - Commission
        $remaining = $totalPool - $totalWinsPaid - $actualCommission;

        $game->update(['remaining_pool' => $remaining]);

        // Calculate rollover
        if (!$this->settings->enable_pool_rollover) {
            $game->update(['rollover_to_next' => 0]);
            return 0;
        }

        $rolloverAmount = $this->settings->calculateRollover($remaining, 0);

        $game->update(['rollover_to_next' => $rolloverAmount]);

        Log::info("🔄 Rollover calculated - Pool loses Win + Commission", [
            'game_id' => $game->id,
            'total_pool' => $totalPool,
            'max_commission' => $maxCommission,
            'actual_commission' => $actualCommission,
            'total_wins_paid' => $totalWinsPaid,
            'remaining_pool' => $remaining,
            'rollover' => $rolloverAmount,
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

<?php
// app/Services/CrashGameService.php - UPDATED WITH NEW LOGIC

namespace App\Services;

use App\Models\CrashGame;
use App\Models\CrashBet;
use App\Models\User;
use App\Models\CrashGameSetting;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class CrashGameService
{
    private $settings;
    private $lastSettingsHash;
    private $betPoolService;

    public function __construct()
    {
        $this->loadSettings();
        $this->betPoolService = new CrashBetPoolService();
    }

    private function loadSettings(): void
    {
        $this->settings = CrashGameSetting::first();
        if (!$this->settings) {
            $this->settings = CrashGameSetting::create([]);
        }
        $this->lastSettingsHash = $this->getSettingsHash();
    }

    public function checkAndReloadSettings(): bool
    {
        $currentHash = $this->getSettingsHash();

        if ($currentHash !== $this->lastSettingsHash) {
            $this->loadSettings();
            $this->betPoolService->reloadSettings();
            return true;
        }

        return false;
    }

    private function getSettingsHash(): string
    {
        if (!$this->settings) {
            return '';
        }

        $settingsData = [
            'house_edge' => $this->settings->house_edge,
            'min_multiplier' => $this->settings->min_multiplier,
            'max_multiplier' => $this->settings->max_multiplier,
            'admin_commission_rate' => $this->settings->admin_commission_rate,
            'updated_at' => $this->settings->updated_at->timestamp,
        ];

        return md5(serialize($settingsData));
    }

    public function getBetWaitingTime(): int
    {
        return 10;
    }

    public function isGameActive(): bool
    {
        return (bool) $this->settings->is_active;
    }

    /**
     * Create a new game
     */
    public function createGame(): CrashGame
    {
        if (!$this->isGameActive()) {
            throw new Exception('Crash game is currently inactive');
        }

        $gameHash = hash('sha256', uniqid('crash_', true) . microtime(true));

        return CrashGame::create([
            'game_hash' => $gameHash,
            'crash_point' => 1.01,
            'initial_crash_point' => null,
            'status' => 'pending',
            'total_bet_pool' => 0,
            'previous_rollover' => 0,
            'current_round_bets' => 0,
            'total_participants' => 0,
            'active_participants' => 0,
            'admin_commission_amount' => 0,
            'commission_rate' => $this->settings->admin_commission_rate ?? 10.00,
            'pool_locked' => false,
            'total_payout' => 0,
            'remaining_pool' => 0,
            'rollover_to_next' => 0,
        ]);
    }

    public function getCurrentGame(): ?CrashGame
    {
        return CrashGame::whereIn('status', ['pending', 'running'])
            ->latest()
            ->first();
    }

    /**
     * Start the game - LOCK BET POOL
     */
    public function startGame(CrashGame $game): bool
    {
        if ($game->status !== 'pending') {
            return false;
        }

        // Lock bet pool (calculates crash point)
        $this->betPoolService->lockBetPool($game);

        return $game->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function startBets(CrashGame $game): void
    {
        $game->bets()
            ->where('status', 'pending')
            ->update(['status' => 'playing']);
    }

    /**
     * Crash the game
     */
    public function crashGame(CrashGame $game): bool
    {
        if ($game->status !== 'running') {
            return false;
        }

        return DB::transaction(function () use ($game) {
            // Mark all pending/playing bets as lost
            $game->activeBets()->update([
                'status' => 'lost',
                'profit' => DB::raw('-bet_amount'),
            ]);

            // ✅ ADD THIS: Refresh game data before calculation
            $game->refresh();

            // Calculate final commission and rollover
            $rolloverAmount = $this->betPoolService->calculateAndSetRollover($game);

            // Update game status
            $game->update([
                'status' => 'crashed',
                'crashed_at' => now(),
            ]);

            // 🆕 NEW: Commission is already deducted from pool, no transfer needed
            // Remaining pool (if any) goes to admin

            $this->transferRemainingToAdmin($game);

            Log::info("💥 Game crashed", [
                'game_id' => $game->id,
                'crash_point' => $game->crash_point,
                'total_payout' => $game->total_payout,
                'actual_commission' => $game->admin_commission_amount,
                'rollover' => $rolloverAmount,
            ]);

            return true;
        });
    }

    /**
     * 🆕 Transfer remaining pool to admin
     */
    private function transferRemainingToAdmin(CrashGame $game): void
    {
        $totalPool = $game->total_bet_pool;
        $totalPaid = $game->total_payout;
        $rollover = $game->rollover_to_next;

        // Admin gets: Total Pool - Paid to Winners - Rollover
        $adminAmount = $totalPool - $totalPaid - $rollover;

        if ($adminAmount > 0) {
            $admin = User::find(1);
            if ($admin) {
                // Note: Bets already added to admin when placed
                // So we only need to log this

                Log::info("💰 Admin profit from game", [
                    'game_id' => $game->id,
                    'total_pool' => $totalPool,
                    'paid_to_winners' => $totalPaid,
                    'rollover' => $rollover,
                    'admin_keeps' => $adminAmount,
                ]);
            }
        }
    }

    public function placeBet(User $user, CrashGame $game, float $betAmount): CrashBet
    {
        if (!$this->isGameActive()) {
            throw new Exception('Crash game is currently inactive');
        }

        if ($game->status !== 'pending') {
            throw new Exception('Game has already started');
        }

        if ($game->pool_locked) {
            throw new Exception('Bet pool is locked');
        }

        if ($betAmount < $this->settings->min_bet_amount) {
            throw new Exception("Minimum bet amount is ৳{$this->settings->min_bet_amount}");
        }

        if ($betAmount > $this->settings->max_bet_amount) {
            throw new Exception("Maximum bet amount is ৳{$this->settings->max_bet_amount}");
        }

        if ($user->credit < $betAmount) {
            throw new Exception('Insufficient balance');
        }

        return DB::transaction(function () use ($user, $game, $betAmount) {
            // Deduct credit from user
            $user->decrement('credit', $betAmount);

            // 🆕 NEW: Add to admin immediately (as before)
            if ($user->id !== 1) {
                User::where('id', 1)->increment('credit', $betAmount);
            }

            // Create bet
            return CrashBet::create([
                'user_id' => $user->id,
                'crash_game_id' => $game->id,
                'bet_amount' => $betAmount,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * 🆕 UPDATED: Cashout with dynamic crash point recalculation
     */
    // public function cashout(CrashBet $bet, float $currentMultiplier): bool
    // {
    //     if ($bet->status !== 'playing') {
    //         throw new Exception('Cannot cashout this bet');
    //     }

    //     if ($currentMultiplier >= $bet->game->crash_point) {
    //         throw new Exception('Game has already crashed');
    //     }

    //     return DB::transaction(function () use ($bet, $currentMultiplier) {
    //         $winAmount = $bet->bet_amount * $currentMultiplier;
    //         $profit = $winAmount - $bet->bet_amount;

    //         // 🆕 Calculate commission on profit (10%)
    //         $commission = $profit * 0.10;

    //         // Update bet
    //         $bet->update([
    //             'cashout_at' => $currentMultiplier,
    //             'profit' => $profit,
    //             'status' => 'won',
    //             'cashed_out_at' => now(),
    //         ]);

    //         // 🆕 Add winnings to user (full amount, commission already in pool)
    //         $bet->user->increment('credit', $winAmount);

    //         User::where('id', 1)->decrement('credit', $winAmount);


    //         // 🆕 Recalculate crash point
    //         $newCrashPoint = $this->betPoolService->recalculateCrashPoint($bet->game);

    //         // Update active participants
    //         $bet->game->decrement('active_participants');

    //         // Update crash point
    //         $bet->game->update(['crash_point' => $newCrashPoint]);

    //         // Check if all cashed out
    //         $this->betPoolService->checkAndExtendCrashPoint($bet->game);

    //         Log::info("💵 User cashed out", [
    //             'game_id' => $bet->game->id,
    //             'user_id' => $bet->user_id,
    //             'multiplier' => $currentMultiplier,
    //             'bet_amount' => $bet->bet_amount,
    //             'win_amount' => $winAmount,
    //             'profit' => $profit,
    //             'commission' => $commission,
    //             'new_crash_point' => $newCrashPoint
    //         ]);

    //         return true;
    //     });
    // }

     /**
     * 🆕 COMPLETELY FIXED: Cashout with proper credit transfer
     */
    public function cashout(CrashBet $bet, float $currentMultiplier): bool
    {
        if ($bet->status !== 'playing') {
            throw new Exception('Cannot cashout this bet');
        }

        if ($currentMultiplier >= $bet->game->crash_point) {
            throw new Exception('Game has already crashed');
        }

        return DB::transaction(function () use ($bet, $currentMultiplier) {
            // ✅ Refresh models to avoid stale data
            $bet->refresh();
            $user = $bet->user()->lockForUpdate()->first();
            $admin = User::where('id', 1)->lockForUpdate()->first();

            // Validate user and admin exist
            if (!$user || !$admin) {
                throw new Exception('User or admin not found');
            }

            // Calculate amounts
            $betAmount = (float) $bet->bet_amount;
            $winAmount = $betAmount * $currentMultiplier; // Total return to user
            $profit = $winAmount - $betAmount; // Actual profit
            $commission = $profit * 0.10;

            // ✅ CRITICAL DEBUG LOG
            Log::info("🔍 Cashout calculation", [
                'bet_amount' => $betAmount,
                'multiplier' => $currentMultiplier,
                'win_amount_calculated' => $winAmount,
                'profit_calculated' => $profit,
                'commission' => $commission,
            ]);

            // ✅ Check admin has sufficient balance
            if ($admin->credit < $winAmount) {
                Log::error("❌ Admin insufficient balance for cashout", [
                    'admin_balance' => $admin->credit,
                    'required' => $winAmount,
                    'game_id' => $bet->game->id,
                    'user_id' => $user->id
                ]);
                throw new Exception('System error: Insufficient pool balance');
            }

            // Update bet status FIRST
            $bet->update([
                'cashout_at' => $currentMultiplier,
                'profit' => $profit,
                'status' => 'won',
                'cashed_out_at' => now(),
            ]);

            // ✅ SOLUTION 1: Use prepared statements (MOST RELIABLE)
            $affectedUser = DB::update(
                'UPDATE users SET credit = credit + ?, updated_at = ? WHERE id = ?',
                [$winAmount, now(), $user->id]
            );

            $affectedAdmin = DB::update(
                'UPDATE users SET credit = credit - ?, updated_at = ? WHERE id = ?',
                [$winAmount, now(), $admin->id]
            );

            // ✅ Verify updates were successful
            if ($affectedUser === 0) {
                Log::error("❌ Failed to update user credit", [
                    'user_id' => $user->id,
                    'win_amount' => $winAmount
                ]);
                throw new Exception('Failed to update user balance');
            }

            if ($affectedAdmin === 0) {
                Log::error("❌ Failed to update admin credit", [
                    'admin_id' => $admin->id,
                    'win_amount' => $winAmount
                ]);
                throw new Exception('Failed to update admin balance');
            }

            // ✅ Refresh to get updated values
            $user->refresh();
            $admin->refresh();

            // ✅ VERIFICATION LOG
            Log::info("✅ Credit transfer verified", [
                'user_id' => $user->id,
                'user_balance_after' => $user->credit,
                'admin_id' => $admin->id,
                'admin_balance_after' => $admin->credit,
                'win_amount_transferred' => $winAmount,
            ]);

            // Recalculate crash point
            $newCrashPoint = $this->betPoolService->recalculateCrashPoint($bet->game);

            // Update active participants
            $bet->game->decrement('active_participants');

            // Update crash point
            $bet->game->update(['crash_point' => $newCrashPoint]);

            // Check if all cashed out
            $this->betPoolService->checkAndExtendCrashPoint($bet->game);

            Log::info("💵 User cashed out successfully", [
                'game_id' => $bet->game->id,
                'user_id' => $user->id,
                'multiplier' => $currentMultiplier,
                'bet_amount' => $betAmount,
                'win_amount' => $winAmount,
                'profit' => $profit,
                'commission' => $commission,
                'new_crash_point' => $newCrashPoint,
                'user_balance_after' => $user->credit,
                'admin_balance_after' => $admin->credit,
            ]);

            return true;
        });
    }

    /**
     * Calculate house profit
     */
    public function calculateHouseProfit(CrashGame $game): float
    {
        return $game->getAdminProfit();
    }

    /**
     * Get pool statistics
     */
    public function getPoolStats(CrashGame $game): array
    {
        return $this->betPoolService->getPoolStats($game);
    }

    public function getSettings(): CrashGameSetting
    {
        return $this->settings;
    }

    public function ensureCorrectWaitingTime(): void
    {
        $currentValue = $this->settings->bet_waiting_time;

        if ($currentValue != 10) {
            Log::warning("Fixing bet_waiting_time from {$currentValue} to 10");
            $this->settings->update(['bet_waiting_time' => 10]);
            $this->loadSettings();
        }
    }
}

<?php
// app/Services/CrashGameService.php - FIXED CASHOUT METHOD

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

            // ✅ Refresh game data before calculation
            $game->refresh();

            // Calculate final commission and rollover
            $rolloverAmount = $this->betPoolService->calculateAndSetRollover($game);

            // Update game status
            $game->update([
                'status' => 'crashed',
                'crashed_at' => now(),
            ]);

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

            // Add to admin immediately
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
     * ✅✅✅ COMPLETELY FIXED: Cashout with CORRECT AMOUNT
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

            if (!$user || !$admin) {
                throw new Exception('User or admin not found');
            }

            // ✅ CORRECT CALCULATION
            $betAmount = (float) $bet->bet_amount;
            $winAmount = $betAmount * $currentMultiplier;  // পুরো return amount
            $profit = $winAmount - $betAmount;             // শুধু profit
            $commission = $profit * 0.10;                  // profit এর 10%

            // ✅ CRITICAL LOG
            Log::info("💵 Cashout Calculation", [
                'bet_amount' => $betAmount,
                'multiplier' => $currentMultiplier,
                'win_amount' => $winAmount,      // এটাই user পাবে
                'profit' => $profit,
                'commission' => $commission,
            ]);

            // ✅ Update bet status
            $bet->update([
                'cashout_at' => $currentMultiplier,
                'profit' => $profit,  // শুধু profit store করি
                'status' => 'won',
                'cashed_out_at' => now(),
            ]);

            // ✅ SOLUTION: Use DB::raw for atomic operation
            // User কে পুরো winAmount দিতে হবে
            $affectedUser = DB::update(
                'UPDATE users SET credit = credit + ?, updated_at = ? WHERE id = ?',
                [$winAmount, now(), $user->id]
            );

            // Admin থেকে পুরো winAmount বাদ দিতে হবে
            $affectedAdmin = DB::update(
                'UPDATE users SET credit = credit - ?, updated_at = ? WHERE id = ?',
                [$winAmount, now(), $admin->id]
            );

            // ✅ Verify updates
            if ($affectedUser === 0 || $affectedAdmin === 0) {
                Log::error("❌ Credit update failed", [
                    'user_affected' => $affectedUser,
                    'admin_affected' => $affectedAdmin,
                ]);
                throw new Exception('Failed to update balances');
            }

            // ✅ Refresh to get updated values
            $user->refresh();
            $admin->refresh();

            // ✅ Recalculate crash point
            $newCrashPoint = $this->betPoolService->recalculateCrashPoint($bet->game);

            // Update active participants
            $bet->game->decrement('active_participants');

            // Update crash point
            $bet->game->update(['crash_point' => $newCrashPoint]);

            // Check if all cashed out
            $this->betPoolService->checkAndExtendCrashPoint($bet->game);

            // ✅ FINAL VERIFICATION LOG
            Log::info("✅ Cashout Successful", [
                'game_id' => $bet->game->id,
                'user_id' => $user->id,
                'bet_amount' => $betAmount,
                'multiplier' => $currentMultiplier,
                'win_amount_given' => $winAmount,  // পুরো amount
                'profit_earned' => $profit,
                'commission_deducted' => $commission,
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

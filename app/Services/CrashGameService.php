<?php
// app/Services/CrashGameService.php - FIXED WITH FULL WIN PAYOUT

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
            $lostBets = $game->activeBets()->get();

            foreach ($lostBets as $bet) {
                $bet->update([
                    'status' => 'lost',
                    'profit' => -$bet->bet_amount,
                ]);
            }

            // Refresh game data
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

            // ✅ Add to admin immediately
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
     * ✅ FIXED: Cashout with FULL win amount to user
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
            $betAmount = $bet->bet_amount;
            $fullWinAmount = $betAmount * $currentMultiplier; // ✅ Full win (e.g., ৳300)
            $profit = $fullWinAmount - $betAmount; // ✅ Profit (e.g., ৳200)

            // ✅ Commission on profit (10%)
            $commission = $profit * 0.10; // e.g., ৳20

            // Update bet record with PROFIT (for statistics)
            $bet->update([
                'cashout_at' => $currentMultiplier,
                'profit' => $profit, // ✅ Store full profit (৳200)
                'status' => 'won',
                'cashed_out_at' => now(),
            ]);

            // ✅ Admin pays FULL WIN AMOUNT
            $admin = User::find(1);
            if ($admin) {
                $admin->decrement('credit', $fullWinAmount); // ✅ Deduct ৳300

                Log::info("💰 Admin paid full win", [
                    'game_id' => $bet->game->id,
                    'user_id' => $bet->user_id,
                    'paid_amount' => $fullWinAmount,
                    'admin_balance_after' => $admin->fresh()->credit,
                ]);
            }

            // ✅ User receives FULL WIN AMOUNT
            $bet->user->increment('credit', $fullWinAmount); // ✅ User gets ৳300

            // ✅ Recalculate crash point - Pool loses Win + Commission
            $newCrashPoint = $this->betPoolService->recalculateCrashPoint($bet->game, $fullWinAmount, $commission);

            // Update active participants
            $bet->game->decrement('active_participants');

            // Update crash point
            $bet->game->update(['crash_point' => $newCrashPoint]);

            // Check if all cashed out
            $this->betPoolService->checkAndExtendCrashPoint($bet->game);

            Log::info("💵 User cashed out - FULL WIN", [
                'game_id' => $bet->game->id,
                'user_id' => $bet->user_id,
                'multiplier' => $currentMultiplier,
                'bet_amount' => $betAmount,
                'full_win_amount' => $fullWinAmount,
                'profit' => $profit,
                'commission' => $commission,
                'new_crash_point' => $newCrashPoint,
            ]);

            return true;
        });
    }

    /**
     * ✅ UPDATED: Cashout WITHOUT crash point recalculation
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
    //         $betAmount = $bet->bet_amount;
    //         $fullWinAmount = $betAmount * $currentMultiplier;
    //         $profit = $fullWinAmount - $betAmount;

    //         // Commission on profit (10%)
    //         $commission = $profit * 0.10;

    //         // Update bet record with PROFIT (for statistics)
    //         $bet->update([
    //             'cashout_at' => $currentMultiplier,
    //             'profit' => $profit,
    //             'status' => 'won',
    //             'cashed_out_at' => now(),
    //         ]);

    //         // Admin pays FULL WIN AMOUNT
    //         $admin = User::find(1);
    //         if ($admin) {
    //             $admin->decrement('credit', $fullWinAmount);

    //             Log::info("💰 Admin paid full win", [
    //                 'game_id' => $bet->game->id,
    //                 'user_id' => $bet->user_id,
    //                 'paid_amount' => $fullWinAmount,
    //                 'admin_balance_after' => $admin->fresh()->credit,
    //             ]);
    //         }

    //         // User receives FULL WIN AMOUNT
    //         $bet->user->increment('credit', $fullWinAmount);

    //         // ✅ Update active participants (NO crash point recalculation)
    //         $bet->game->decrement('active_participants');

    //         Log::info("💵 User cashed out - FIXED CRASH POINT", [
    //             'game_id' => $bet->game->id,
    //             'user_id' => $bet->user_id,
    //             'multiplier' => $currentMultiplier,
    //             'bet_amount' => $betAmount,
    //             'full_win_amount' => $fullWinAmount,
    //             'profit' => $profit,
    //             'commission' => $commission,
    //             'crash_point' => $bet->game->crash_point, // ✅ Remains unchanged
    //             'note' => 'Crash point is FIXED, no recalculation'
    //         ]);

    //         return true;
    //     });
    // }

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

<?php
// app/Services/CrashGameService.php - WITH ROLLOVER SUPPORT

namespace App\Services;

use App\Models\CrashGame;
use App\Models\CrashBet;
use App\Models\User;
use App\Models\CrashGameSetting;
use Illuminate\Support\Facades\DB;
use Exception;

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
            'commission_type' => $this->settings->commission_type,
            'enable_dynamic_crash' => $this->settings->enable_dynamic_crash,
            'enable_pool_rollover' => $this->settings->enable_pool_rollover,
            'rollover_percentage' => $this->settings->rollover_percentage,
            'updated_at' => $this->settings->updated_at->timestamp,
        ];

        return md5(serialize($settingsData));
    }

    public function getBetWaitingTime(): int
    {
        return 10; // Always 10 seconds
    }

    public function isGameActive(): bool
    {
        return (bool) $this->settings->is_active;
    }

    /**
     * ✅ Create a new game
     */
    public function createGame(): CrashGame
    {
        if (!$this->isGameActive()) {
            throw new Exception('Crash game is currently inactive');
        }

        $gameHash = hash('sha256', uniqid('crash_', true) . microtime(true));

        return CrashGame::create([
            'game_hash' => $gameHash,
            'crash_point' => 1.01, // Temporary
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
     * ✅ Start the game - LOCK BET POOL & CALCULATE CRASH POINT
     */
    public function startGame(CrashGame $game): bool
    {
        if ($game->status !== 'pending') {
            return false;
        }

        // 🔒 Lock bet pool and calculate crash point (with rollover)
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
     * ✅ Crash the game - WITH ROLLOVER CALCULATION
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

            // 🆕 Calculate rollover for next game
            $rolloverAmount = $this->betPoolService->calculateAndSetRollover($game);

            // Update game status
            $game->update([
                'status' => 'crashed',
                'crashed_at' => now(),
            ]);

            // Transfer commission + kept pool to admin
            $this->transferProfitToAdmin($game);

            Log::info("💥 Game crashed", [
                'game_id' => $game->id,
                'crash_point' => $game->crash_point,
                'total_payout' => $game->total_payout,
                'rollover' => $rolloverAmount,
                'admin_profit' => $game->getAdminProfit(),
            ]);

            return true;
        });
    }

    /**
     * 💰 Transfer profit to admin
     */
    private function transferProfitToAdmin(CrashGame $game): void
    {
        // Admin gets: Commission + (Pool - Payouts - Rollover)
        $adminProfit = $game->getAdminProfit();

        if ($adminProfit > 0) {
            $admin = User::find(1);
            if ($admin) {
                $admin->increment('credit', $adminProfit);

                Log::info("💰 Profit transferred to admin", [
                    'game_id' => $game->id,
                    'amount' => $adminProfit,
                    'breakdown' => [
                        'commission' => $game->admin_commission_amount,
                        'kept_from_pool' => $game->remaining_pool - $game->rollover_to_next,
                        'total' => $adminProfit,
                    ]
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
     * ✅ Cashout with dynamic crash point increase
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
            $winAmount = $bet->bet_amount * $currentMultiplier;
            $profit = $winAmount - $bet->bet_amount;

            // Update bet
            $bet->update([
                'cashout_at' => $currentMultiplier,
                'profit' => $profit,
                'status' => 'won',
                'cashed_out_at' => now(),
            ]);

            // Add winnings to user credit
            $bet->user->increment('credit', $winAmount);

            // ✅ Increase crash point dynamically
            $newCrashPoint = $this->betPoolService->increaseCrashPoint($bet->game, $bet);

            // ✅ Check if all users cashed out
            $this->betPoolService->checkAndExtendCrashPoint($bet->game);

            Log::info("💵 User cashed out", [
                'game_id' => $bet->game->id,
                'user_id' => $bet->user_id,
                'multiplier' => $currentMultiplier,
                'win_amount' => $winAmount,
                'new_crash_point' => $newCrashPoint
            ]);

            return true;
        });
    }

    /**
     * 📊 Calculate house profit with commission
     */
    public function calculateHouseProfit(CrashGame $game): float
    {
        return $game->getAdminProfit();
    }

    /**
     * 📊 Get pool statistics
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

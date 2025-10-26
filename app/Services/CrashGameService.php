<?php
// app/Services/CrashGameService.php

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

    public function __construct()
    {
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        $this->settings = CrashGameSetting::first();
        if (!$this->settings) {
            // Create default settings if not exists
            $this->settings = CrashGameSetting::create([]);
        }

        // Store settings hash for change detection
        $this->lastSettingsHash = $this->getSettingsHash();
    }

    /**
     * Check if settings have changed and reload if needed
     */
    public function checkAndReloadSettings(): bool
    {
        $currentHash = $this->getSettingsHash();

        if ($currentHash !== $this->lastSettingsHash) {
            $this->loadSettings();
            return true; // Settings were reloaded
        }

        return false; // No changes
    }

    /**
     * Generate a unique hash for current settings
     */
    private function getSettingsHash(): string
    {
        if (!$this->settings) {
            return '';
        }

        $settingsData = [
            'house_edge' => $this->settings->house_edge,
            'min_multiplier' => $this->settings->min_multiplier,
            'max_multiplier' => $this->settings->max_multiplier,
            'bet_waiting_time' => $this->settings->bet_waiting_time,
            'min_bet_amount' => $this->settings->min_bet_amount,
            'max_bet_amount' => $this->settings->max_bet_amount,
            'is_active' => $this->settings->is_active,
            'multiplier_increment' => $this->settings->multiplier_increment,
            'multiplier_interval_ms' => $this->settings->multiplier_interval_ms,
            'max_speed_multiplier' => $this->settings->max_speed_multiplier,
            'enable_auto_acceleration' => $this->settings->enable_auto_acceleration,
            'speed_profile' => $this->settings->speed_profile,
            'updated_at' => $this->settings->updated_at->timestamp,
        ];

        return md5(serialize($settingsData));
    }


    private function getHouseEdge(): float
    {
        return (float) $this->settings->house_edge;
    }

    private function getMinMultiplier(): float
    {
        return (float) $this->settings->min_multiplier;
    }

    private function getMaxMultiplier(): float
    {
        return (float) $this->settings->max_multiplier;
    }

    public function getBetWaitingTime(): int
    {
        return (int) $this->settings->bet_waiting_time;
    }

    public function isGameActive(): bool
    {
        return (bool) $this->settings->is_active;
    }

    /**
     * Generate crash point with house edge consideration
     */
    public function generateCrashPoint(): float
    {
        $random = $this->getSecureRandomFloat();
        $houseEdge = $this->getHouseEdge();
        $adjustedRandom = $random * (1 - $houseEdge);

        if ($adjustedRandom <= 0) {
            return $this->getMinMultiplier();
        }

        $crashPoint = (1 / $adjustedRandom);
        $crashPoint = max(
            $this->getMinMultiplier(),
            min($this->getMaxMultiplier(), $crashPoint)
        );

        return round($crashPoint, 2);
    }

    /**
     * Create a new game
     */
    public function createGame(): CrashGame
    {
        if (!$this->isGameActive()) {
            throw new Exception('Crash game is currently inactive');
        }

        $crashPoint = $this->generateCrashPoint();
        $gameHash = hash('sha256', uniqid('crash_', true) . microtime(true));

        return CrashGame::create([
            'game_hash' => $gameHash,
            'crash_point' => $crashPoint,
            'status' => 'pending',
        ]);
    }

    /**
     * Get current active game
     */
    public function getCurrentGame(): ?CrashGame
    {
        return CrashGame::whereIn('status', ['pending', 'running'])
            ->latest()
            ->first();
    }

    /**
     * Start the game
     */
    public function startGame(CrashGame $game): bool
    {
        if ($game->status !== 'pending') {
            return false;
        }

        return $game->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Start bets when game starts
     */
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
            // Update game status
            $game->update([
                'status' => 'crashed',
                'crashed_at' => now(),
            ]);

            // Mark all pending/playing bets as lost
            $game->activeBets()->update([
                'status' => 'lost',
                'profit' => DB::raw('-bet_amount'),
            ]);

            return true;
        });
    }

    /**
     * Place a bet
     */
    public function placeBet(User $user, CrashGame $game, float $betAmount): CrashBet
    {
        if (!$this->isGameActive()) {
            throw new Exception('Crash game is currently inactive');
        }

        if ($game->status !== 'pending') {
            throw new Exception('Game has already started');
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

            // Add bet amount to admin (user id 1)
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
     * Cashout before crash
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

            // Deduct win amount from admin (user id 1) if user is not admin
            if ($bet->user_id !== 1) {
                User::where('id', 1)->decrement('credit', $winAmount);
            }

            return true;
        });
    }

    /**
     * Get secure random float between 0 and 1
     */
    private function getSecureRandomFloat(): float
    {
        try {
            $randomBytes = random_bytes(4);
            $randomInt = unpack('L', $randomBytes)[1];
            return $randomInt / 0xFFFFFFFF;
        } catch (Exception $e) {
            // Fallback to mt_rand
            return mt_rand() / mt_getrandmax();
        }
    }

    /**
     * Calculate house profit for a game
     */
    public function calculateHouseProfit(CrashGame $game): float
    {
        $totalBets = $game->total_bet_amount;
        $totalPayouts = $game->total_payout;

        return $totalBets - $totalPayouts;
    }

    /**
     * Get current settings
     */
    public function getSettings(): CrashGameSetting
    {
        return $this->settings;
    }
}

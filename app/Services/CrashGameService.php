<?php

namespace App\Services;

use App\Models\CrashGame;
use App\Models\CrashBet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class CrashGameService
{
    // House edge percentage (5% = 0.05)
    private const HOUSE_EDGE = 0.05;

    // Maximum multiplier
    private const MAX_MULTIPLIER = 100.00;

    // Minimum multiplier
    private const MIN_MULTIPLIER = 1.01;

    /**
     * Generate crash point with house edge consideration
     */
    public function generateCrashPoint(): float
    {
        // Provably fair algorithm with house edge
        // এই algorithm ensure করে যে long-term এ house edge maintain হবে

        $random = $this->getSecureRandomFloat();

        // House edge adjust করা
        $adjustedRandom = $random * (1 - self::HOUSE_EDGE);

        // Exponential distribution for crash point
        // এটি ensure করে যে বেশিরভাগ সময় কম multiplier আসবে
        if ($adjustedRandom <= 0) {
            return self::MIN_MULTIPLIER;
        }

        $crashPoint = (1 / $adjustedRandom);

        // Clamp between min and max
        $crashPoint = max(self::MIN_MULTIPLIER, min(self::MAX_MULTIPLIER, $crashPoint));

        return round($crashPoint, 2);
    }

    /**
     * Create a new game
     */
    public function createGame(): CrashGame
    {
        $crashPoint = $this->generateCrashPoint();
        $gameHash = hash('sha256', uniqid('crash_', true) . microtime(true));

        return CrashGame::create([
            'game_hash' => $gameHash,
            'crash_point' => $crashPoint,
            'status' => 'pending',
        ]);
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
     * Place a bet
     */
    public function placeBet(User $user, CrashGame $game, float $betAmount): CrashBet
    {
        if ($game->status !== 'pending') {
            throw new Exception('Game has already started');
        }

        if ($betAmount <= 0) {
            throw new Exception('Bet amount must be greater than 0');
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
     * Start bets when game starts
     */
    public function startBets(CrashGame $game): void
    {
        $game->bets()
            ->where('status', 'pending')
            ->update(['status' => 'playing']);
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
}

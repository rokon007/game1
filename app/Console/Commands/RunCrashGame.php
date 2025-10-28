<?php
// app/Console/Commands/RunCrashGame.php

namespace App\Console\Commands;

use App\Models\CrashGame;
use App\Services\CrashGameService;
use App\Services\CrashGameSpeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RunCrashGame extends Command
{
    protected $signature = 'crash:run';
    protected $description = 'Run the crash game loop';

    private CrashGameService $gameService;
    private CrashGameSpeedService $speedService;
    private bool $settingsReloaded = false;

    public function __construct(CrashGameService $gameService)
    {
        parent::__construct();
        $this->gameService = $gameService;
        $this->speedService = new CrashGameSpeedService();
    }

    public function handle(): int
    {
        $pid = getmypid();
        $this->info("=== Crash Game Process Started ===");
        $this->info("Process PID: {$pid}");
        $this->info("Memory Usage: " . memory_get_usage(true) / 1024 / 1024 . " MB");
        $this->info("Game Active: " . ($this->gameService->isGameActive() ? 'Yes' : 'No'));

        if (!$this->gameService->isGameActive()) {
            $this->error('Crash game is not active!');
            Log::warning('Crash game started but is not active in settings');
            return Command::FAILURE;
        }

        $this->info('Starting Crash Game Loop...');

        Cache::put('crash_game_running', true, 3600);
        Cache::put('crash_game_pid', $pid, 3600);
        Cache::put('crash_game_started_at', now()->toDateTimeString(), 3600);

        $this->info("Process info stored in cache");

        register_shutdown_function(function () {
            $this->cleanup();
        });

        $cycleCount = 0;
        while (true) {
            $cycleCount++;
            $this->info("=== Game Cycle #{$cycleCount} ===");

            if (Cache::get('crash_game_stop')) {
                $this->info('Stop signal received. Stopping game...');
                break;
            }

            try {
                if ($this->gameService->checkAndReloadSettings()) {
                    $this->settingsReloaded = true;
                    $this->info('Settings changes detected! New settings will be applied to the next game.');
                    $this->speedService = new CrashGameSpeedService();
                }

                $this->runGameCycle();
            } catch (\Exception $e) {
                Log::error('Crash Game Error: ' . $e->getMessage());
                $this->error('Error: ' . $e->getMessage());
                sleep(5);
            }

            // ✅ CRITICAL: Cycle শেষে ছোট delay
            sleep(1);
        }

        $this->cleanup();
        $this->info("=== Crash Game Process Ended ===");
        return Command::SUCCESS;
    }

    private function runGameCycle(): void
    {
        $game = $this->gameService->getCurrentGame();

        // ✅ FIX: Game না থাকলে বা crashed হলে নতুন game তৈরি করুন
        if (!$game || $game->isCrashed()) {
            if ($this->settingsReloaded) {
                $this->info('Applying new settings to the game...');
                $this->settingsReloaded = false;
            }

            // ✅ CRITICAL: নতুন game তৈরি করার আগে cache clear করুন
            cache()->forget('crash_game_current');
            cache()->forget('crash_game_waiting_start');
            cache()->forget('crash_game_waiting_duration');
            cache()->forget('crash_game_waiting_end');

            // Small delay after crash before creating new game
            if ($game && $game->isCrashed()) {
                $this->info("Previous game crashed. Waiting 2 seconds before next game...");
                sleep(2);
            }

            // Create new game
            $game = $this->gameService->createGame();
            $this->info("New game created: #{$game->id} - Crash Point: {$game->crash_point}x");

            // ✅ Set waiting state IMMEDIATELY
            $waitingStartTime = microtime(true);
            $waitingTime = $this->gameService->getBetWaitingTime();
            $waitingEndTime = $waitingStartTime + $waitingTime;

            // Store in cache
            cache()->put('crash_game_waiting_start', $waitingStartTime, 60);
            cache()->put('crash_game_waiting_duration', $waitingTime, 60);
            cache()->put('crash_game_waiting_end', $waitingEndTime, 60);

            // ✅ CRITICAL: Broadcast waiting state with exact timing
            $this->broadcastGameUpdate($game, 1.00, 'waiting', [
                'waiting_start' => $waitingStartTime,
                'waiting_duration' => $waitingTime,
                'waiting_end' => $waitingEndTime
            ]);

            $this->info("Waiting for bets for {$waitingTime} seconds...");
            $this->info("Waiting will end at: " . date('H:i:s', (int)$waitingEndTime));

            // Precise waiting loop
            while (microtime(true) < $waitingEndTime) {
                if (Cache::get('crash_game_stop')) {
                    $this->info('Stop signal received during betting period.');
                    return;
                }

                $remainingTime = $waitingEndTime - microtime(true);
                if ($remainingTime > 0) {
                    // Sleep in small chunks for responsiveness
                    usleep(min(100000, $remainingTime * 1000000));
                }
            }

            $actualWaitTime = microtime(true) - $waitingStartTime;
            $this->info("Actual waiting time: " . round($actualWaitTime, 3) . " seconds");

            // ✅ Clear waiting cache after completion
            cache()->forget('crash_game_waiting_start');
            cache()->forget('crash_game_waiting_duration');
            cache()->forget('crash_game_waiting_end');
        }

        // Start the game if pending
        if ($game->isPending()) {
            $this->gameService->startGame($game);
            $this->gameService->startBets($game);
            $this->info("Game #{$game->id} started!");

            // ✅ Small delay to ensure state transition
            usleep(500000); // 500ms
        }

        // Run the game if running
        if ($game->isRunning()) {
            $this->runGame($game);
        }
    }

    private function runGame(CrashGame $game): void
    {
        $currentMultiplier = 1.00;
        $crashPoint = (float) $game->crash_point;

        $speedProfile = $this->speedService->getSpeedProfileName();
        $estimatedDuration = $this->speedService->estimateGameDuration($crashPoint);
        $this->info("Running game #{$game->id} - Will crash at {$crashPoint}x - Speed: {$speedProfile}");
        $this->info("Estimated duration: {$estimatedDuration} seconds");

        event(new \App\Events\CrashGameStarted($game, $currentMultiplier));

        $lastBroadcastTime = microtime(true);
        $broadcastIntervalMs = 100;

        // Game loop
        while ($currentMultiplier < $crashPoint) {
            if (Cache::get('crash_game_stop')) {
                $this->info('Stop signal received during game execution.');
                break;
            }

            $increment = $this->speedService->calculateDynamicIncrement($currentMultiplier);
            $currentMultiplier += $increment;

            if ($currentMultiplier > $crashPoint) {
                $currentMultiplier = $crashPoint;
            }

            // Controlled broadcasting
            $currentTime = microtime(true);
            if (($currentTime - $lastBroadcastTime) * 1000 >= $broadcastIntervalMs) {
                $this->broadcastGameUpdate($game, $currentMultiplier, 'running');
                $lastBroadcastTime = $currentTime;
            }

            if (fmod($currentMultiplier, 1.0) < $increment) {
                $this->line("Current: " . number_format($currentMultiplier, 2) . "x");
            }

            $interval = $this->speedService->getCurrentInterval();
            usleep($interval * 1000);
        }

        // Crash handling
        if (!Cache::get('crash_game_stop') && $currentMultiplier >= $crashPoint) {
            // Final broadcast at crash point
            $this->broadcastGameUpdate($game, $crashPoint, 'running');
            usleep(500000); // 500ms pause

            // Crash the game
            $this->gameService->crashGame($game);

            // ✅ CRITICAL: Broadcast crashed state
            $this->broadcastGameUpdate($game, $crashPoint, 'crashed');

            event(new \App\Events\CrashGameCrashed($game));

            $this->error("CRASHED at {$crashPoint}x!");

            // Show crash for 3 seconds
            sleep(3);

            $houseProfit = $this->gameService->calculateHouseProfit($game);
            $this->info("House Profit: ৳{$houseProfit}");

            // ✅ IMPORTANT: Clear all game state before next cycle
            cache()->forget('crash_game_current');
            cache()->forget('crash_game_waiting_start');
            cache()->forget('crash_game_waiting_duration');
            cache()->forget('crash_game_waiting_end');

            $this->info("Game state cleared. Ready for next game.");
        }
    }

    private function broadcastGameUpdate(CrashGame $game, float $multiplier, string $status, array $extra = []): void
    {
        $data = [
            'game_id' => $game->id,
            'multiplier' => round($multiplier, 2),
            'status' => $status,
            'crash_point' => $game->crash_point,
            'updated_at' => microtime(true), // ✅ Use microtime for precision
        ];

        $data = array_merge($data, $extra);
        cache()->put('crash_game_current', $data, 60);

        $this->info("Broadcasted: Status={$status}, Multiplier={$multiplier}");
    }

    private function cleanup(): void
    {
        $this->info("Cleaning up process resources...");
        Cache::forget('crash_game_running');
        Cache::forget('crash_game_pid');
        Cache::forget('crash_game_started_at');
        Cache::forget('crash_game_stop');
        Cache::forget('crash_game_current');
        Cache::forget('crash_game_waiting_start');
        Cache::forget('crash_game_waiting_duration');
        Cache::forget('crash_game_waiting_end');
        $this->info('Game process cleanup completed.');
    }
}

/*
 * ✅ KEY CHANGES MADE:
 *
 * 1. Cache Clear করা হয়েছে game cycle শুরুর আগে
 * 2. Crashed হওয়ার পর 2 second delay দেওয়া হয়েছে
 * 3. Waiting state immediately broadcast করা হচ্ছে
 * 4. Game state transition-এ 500ms delay যোগ করা হয়েছে
 * 5. Microtime ব্যবহার করা হচ্ছে precision-এর জন্য
 * 6. Cleanup function-এ সব cache clear করা হচ্ছে
 *
 * এতে আপনার সমস্যা solve হবে:
 * - Crash → Waiting transition smooth হবে
 * - Countdown সঠিক সময় থেকে শুরু হবে
 * - Running state skip হবে না
 */

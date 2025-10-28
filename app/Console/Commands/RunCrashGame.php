<?php

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

        if (!$this->gameService->isGameActive()) {
            $this->error('Crash game is not active!');
            return Command::FAILURE;
        }

        Cache::put('crash_game_running', true, 3600);
        Cache::put('crash_game_pid', $pid, 3600);
        Cache::put('crash_game_started_at', now()->toDateTimeString(), 3600);

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
                    $this->info('Settings changes detected!');
                    $this->speedService = new CrashGameSpeedService();
                }

                $this->runGameCycle();
            } catch (\Exception $e) {
                Log::error('Crash Game Error: ' . $e->getMessage());
                $this->error('Error: ' . $e->getMessage());
                sleep(5);
            }

            sleep(1);
        }

        $this->cleanup();
        return Command::SUCCESS;
    }

    private function runGameCycle(): void
    {
        $game = $this->gameService->getCurrentGame();

        // ✅ Create new game if needed
        if (!$game || $game->isCrashed()) {
            if ($this->settingsReloaded) {
                $this->info('Applying new settings...');
                $this->settingsReloaded = false;
            }

            // ✅ CRITICAL: Complete cleanup before new game
            $this->clearAllGameCache();

            // Delay after crash
            if ($game && $game->isCrashed()) {
                $this->info("Previous game crashed. 3 second pause...");
                sleep(3);
            }

            // Create new game
            $game = $this->gameService->createGame();
            $this->info("New game created: #{$game->id} - Crash Point: {$game->crash_point}x");

            // ✅ EXACT 10 SECOND WAITING - START
            $this->executeExactWaiting($game);
            // ✅ EXACT 10 SECOND WAITING - END

            // Clear waiting cache after completion
            $this->clearWaitingCache();
        }

        // Start the game if pending
        if ($game->isPending()) {
            $this->gameService->startGame($game);
            $this->gameService->startBets($game);
            $this->info("Game #{$game->id} started!");
            usleep(300000); // 300ms transition delay
        }

        // Run the game
        if ($game->isRunning()) {
            $this->runGame($game);
        }
    }

    // ✅ NEW: Exact 10 second waiting method
    private function executeExactWaiting(CrashGame $game): void
    {
        $waitingTime = 10.0; // ✅ EXACT 10 seconds
        $waitingStartTime = microtime(true);
        $waitingEndTime = $waitingStartTime + $waitingTime;

        // Store in cache
        cache()->put('crash_game_waiting_start', $waitingStartTime, 60);
        cache()->put('crash_game_waiting_duration', $waitingTime, 60);
        cache()->put('crash_game_waiting_end', $waitingEndTime, 60);

        // ✅ Broadcast waiting state immediately
        $this->broadcastGameUpdate($game, 1.00, 'waiting', [
            'waiting_start' => $waitingStartTime,
            'waiting_duration' => $waitingTime,
            'waiting_end' => $waitingEndTime
        ]);

        $this->info("⏱️  EXACT 10.00 second waiting started");
        $this->info("Start: " . date('H:i:s', (int)$waitingStartTime) . "." . sprintf('%03d', ($waitingStartTime - floor($waitingStartTime)) * 1000));
        $this->info("End:   " . date('H:i:s', (int)$waitingEndTime) . "." . sprintf('%03d', ($waitingEndTime - floor($waitingEndTime)) * 1000));

        // ✅ High precision waiting loop
        while (true) {
            $currentTime = microtime(true);
            $remainingTime = $waitingEndTime - $currentTime;

            // ✅ Break exactly when time is up
            if ($remainingTime <= 0) {
                break;
            }

            // Check stop signal
            if (Cache::get('crash_game_stop')) {
                $this->info('Stop signal received during betting period.');
                return;
            }

            // ✅ Sleep in micro-chunks for precision
            if ($remainingTime > 0.1) {
                usleep(50000); // 50ms chunks when time remaining
            } else {
                usleep(10000); // 10ms chunks for final precision
            }
        }

        $actualWaitTime = microtime(true) - $waitingStartTime;
        $deviation = abs($actualWaitTime - 10.0);

        $this->info("✅ Completed: " . number_format($actualWaitTime, 4) . " seconds");

        if ($deviation < 0.05) {
            $this->info("✅ PERFECT TIMING! (deviation: " . number_format($deviation * 1000, 2) . "ms)");
        } else {
            $this->warn("⚠️  Deviation: " . number_format($deviation * 1000, 2) . "ms");
        }
    }

    // ✅ UPDATED: Instant crash at exact point
    private function runGame(CrashGame $game): void
    {
        $currentMultiplier = 1.00;
        $crashPoint = (float) $game->crash_point;

        $speedProfile = $this->speedService->getSpeedProfileName();
        $this->info("Running game #{$game->id} - Crash at {$crashPoint}x - Speed: {$speedProfile}");

        // ✅ Broadcast initial running state
        $this->broadcastGameUpdate($game, 1.00, 'running');

        event(new \App\Events\CrashGameStarted($game, $currentMultiplier));

        $lastBroadcastTime = microtime(true);
        $broadcastIntervalMs = 100;

        // ✅ Game loop - stop BEFORE crash point
        while ($currentMultiplier < $crashPoint) {
            if (Cache::get('crash_game_stop')) {
                $this->info('Stop signal received.');
                break;
            }

            $increment = $this->speedService->calculateDynamicIncrement($currentMultiplier);
            $nextMultiplier = $currentMultiplier + $increment;

            // ✅ CRITICAL: Stop exactly at crash point, don't overshoot
            if ($nextMultiplier >= $crashPoint) {
                $currentMultiplier = $crashPoint;
                break; // ✅ Exit loop immediately
            }

            $currentMultiplier = $nextMultiplier;

            // Broadcast updates
            $currentTime = microtime(true);
            if (($currentTime - $lastBroadcastTime) * 1000 >= $broadcastIntervalMs) {
                $this->broadcastGameUpdate($game, $currentMultiplier, 'running');
                $lastBroadcastTime = $currentTime;
            }

            // Log progress
            if (fmod($currentMultiplier, 1.0) < $increment) {
                $this->line("Current: " . number_format($currentMultiplier, 2) . "x");
            }

            // Dynamic delay
            $interval = $this->speedService->getCurrentInterval();
            usleep($interval * 1000);
        }

        // ✅ INSTANT CRASH - No pause at crash point
        if (!Cache::get('crash_game_stop') && $currentMultiplier >= $crashPoint) {
            // ✅ Set to exact crash point
            $currentMultiplier = $crashPoint;

            // ✅ Broadcast running at crash point (brief)
            $this->broadcastGameUpdate($game, $crashPoint, 'running');
            usleep(100000); // ✅ Only 100ms pause (was 500ms)

            // ✅ CRASH IMMEDIATELY
            $this->gameService->crashGame($game);
            $this->broadcastGameUpdate($game, $crashPoint, 'crashed');

            event(new \App\Events\CrashGameCrashed($game));

            $this->error("💥 CRASHED at {$crashPoint}x!");

            // Show crash message
            sleep(2); // ✅ Reduced from 3 to 2 seconds

            $houseProfit = $this->gameService->calculateHouseProfit($game);
            $this->info("House Profit: ৳{$houseProfit}");

            // ✅ Clear everything for next cycle
            $this->clearAllGameCache();
        }
    }

    private function broadcastGameUpdate(CrashGame $game, float $multiplier, string $status, array $extra = []): void
    {
        $data = [
            'game_id' => $game->id,
            'multiplier' => round($multiplier, 2),
            'status' => $status,
            'crash_point' => $game->crash_point,
            'updated_at' => microtime(true),
        ];

        $data = array_merge($data, $extra);
        cache()->put('crash_game_current', $data, 60);

        $this->info("📡 Broadcast: {$status} @ {$multiplier}x");
    }

    // ✅ NEW: Clear all game cache
    private function clearAllGameCache(): void
    {
        cache()->forget('crash_game_current');
        $this->clearWaitingCache();
    }

    // ✅ NEW: Clear only waiting cache
    private function clearWaitingCache(): void
    {
        cache()->forget('crash_game_waiting_start');
        cache()->forget('crash_game_waiting_duration');
        cache()->forget('crash_game_waiting_end');
    }

    private function cleanup(): void
    {
        $this->info("Cleaning up...");
        Cache::forget('crash_game_running');
        Cache::forget('crash_game_pid');
        Cache::forget('crash_game_started_at');
        Cache::forget('crash_game_stop');
        $this->clearAllGameCache();
        $this->info('Cleanup completed.');
    }
}

// ============================================
// KEY CHANGES SUMMARY:
// ============================================

/*
✅ WAITING FIX:
   - executeExactWaiting() method যোগ করা হয়েছে
   - microtime(true) দিয়ে exact timing
   - High precision loop (10ms chunks শেষে)
   - Deviation tracking and logging

✅ INSTANT CRASH FIX:
   - Loop থেকে exact crash point এ exit
   - No overshoot (nextMultiplier check)
   - 100ms pause only (500ms থেকে কমানো)
   - Immediate crash after reaching point
   - 2 second crash display (3 থেকে কমানো)

✅ CACHE MANAGEMENT:
   - clearAllGameCache() method
   - clearWaitingCache() method
   - Proper cleanup after each phase

📊 EXPECTED BEHAVIOR:
   1. Crash → 2 sec pause
   2. Waiting → EXACTLY 10.00 seconds
   3. Running → Smooth increment
   4. Crash point → INSTANT crash (no pause)
   5. Repeat
*/

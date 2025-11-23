<?php
// app/Console/Commands/RunCrashGame.php - FIXED FOR VPS TIMING

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
    protected $description = 'Run the crash game loop with exact timing';

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
        $this->info("Server Time: " . now()->toDateTimeString());

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
                    $this->info('⚙️ Settings changes detected!');
                    $this->speedService = new CrashGameSpeedService();
                }

                $this->runGameCycle();
            } catch (\Exception $e) {
                Log::error('Crash Game Error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error('Error: ' . $e->getMessage());
                $this->preciseSleep(5.0);
            }

            $this->preciseSleep(1.0);
        }

        $this->cleanup();
        return Command::SUCCESS;
    }

    private function runGameCycle(): void
    {
        $game = $this->gameService->getCurrentGame();

        if (!$game || $game->isCrashed()) {
            if ($this->settingsReloaded) {
                $this->info('Applying new settings...');
                $this->settingsReloaded = false;
            }

            $this->clearAllGameCache();

            if ($game && $game->isCrashed()) {
                $this->displayGameSummary($game);

                // ✅ EXACT 2 second pause (not 3)
                $this->info("⏸️  2 second pause before next game...");
                $this->preciseSleep(2.0);
            }

            $game = $this->gameService->createGame();

            $previousRollover = \App\Models\CrashGame::getLastRolloverAmount();
            if ($previousRollover > 0) {
                $this->info("🔄 Rollover from previous game: ৳{$previousRollover}");
            } else {
                $this->info("💰 Starting fresh - No rollover");
            }

            $this->info("📝 New game created: #{$game->id}");

            // ✅ CRITICAL: Execute exactly 10 seconds
            $this->executeExactWaiting($game);
            $this->clearWaitingCache();
        }

        if ($game->isPending()) {
            $this->gameService->startGame($game);
            $this->gameService->startBets($game);

            $this->displayPoolInfo($game->fresh());

            $this->info("🎮 Game #{$game->id} started!");
            usleep(300000);
        }

        if ($game->isRunning()) {
            $this->runGame($game);
        }
    }

    /**
     * ✅ FIXED: Execute EXACTLY 10.0 seconds waiting with validation
     */
    private function executeExactWaiting(CrashGame $game): void
    {
        $waitingTime = 10.0; // EXACTLY 10 seconds
        $waitingStartTime = microtime(true);
        $waitingEndTime = $waitingStartTime + $waitingTime;

        // ✅ Store in cache with validation
        cache()->put('crash_game_waiting_start', $waitingStartTime, 60);
        cache()->put('crash_game_waiting_duration', $waitingTime, 60);
        cache()->put('crash_game_waiting_end', $waitingEndTime, 60);

        // ✅ Verify cache was stored correctly
        $verifyStart = cache()->get('crash_game_waiting_start');
        $verifyEnd = cache()->get('crash_game_waiting_end');

        if (!$verifyStart || !$verifyEnd) {
            $this->error("❌ Cache storage failed! Retrying...");

            // Retry cache storage
            cache()->put('crash_game_waiting_start', $waitingStartTime, 60);
            cache()->put('crash_game_waiting_end', $waitingEndTime, 60);

            $verifyStart = cache()->get('crash_game_waiting_start');
            $verifyEnd = cache()->get('crash_game_waiting_end');
        }

        $cachedDuration = $verifyEnd - $verifyStart;
        $deviation = abs($cachedDuration - 10.0);

        if ($deviation > 0.1) {
            $this->error("⚠️ Cache timing deviation: {$deviation}s");
            Log::warning("Cache timing issue", [
                'expected' => 10.0,
                'cached' => $cachedDuration,
                'deviation' => $deviation
            ]);
        }

        $this->broadcastGameUpdate($game, 1.00, 'waiting', [
            'waiting_start' => $waitingStartTime,
            'waiting_duration' => $waitingTime,
            'waiting_end' => $waitingEndTime
        ]);

        $this->info("⏱️  EXACT 10.00 second waiting started");
        $this->info("    Start:  " . date('H:i:s.u', (int)$waitingStartTime));
        $this->info("    End:    " . date('H:i:s.u', (int)$waitingEndTime));

        // ✅ High-precision waiting loop
        $checkCount = 0;
        while (true) {
            $currentTime = microtime(true);
            $remainingTime = $waitingEndTime - $currentTime;

            if ($remainingTime <= 0) {
                break;
            }

            if (Cache::get('crash_game_stop')) {
                $this->info('Stop signal received during betting period.');
                return;
            }

            // ✅ Display progress every second
            if ($checkCount % 20 === 0) {
                $elapsed = $currentTime - $waitingStartTime;
                $this->line(sprintf(
                    "    Elapsed: %.2fs | Remaining: %.2fs",
                    $elapsed,
                    $remainingTime
                ));
            }

            // ✅ Dynamic sleep based on remaining time
            if ($remainingTime > 0.5) {
                usleep(50000); // 50ms
            } elseif ($remainingTime > 0.1) {
                usleep(20000); // 20ms
            } else {
                usleep(5000); // 5ms for final precision
            }

            $checkCount++;
        }

        $actualWaitTime = microtime(true) - $waitingStartTime;
        $timingDeviation = abs($actualWaitTime - 10.0);

        $this->info("✅ Waiting completed!");
        $this->info("    Expected: 10.0000s");
        $this->info("    Actual:   " . number_format($actualWaitTime, 4) . "s");
        $this->info("    Deviation: " . number_format($timingDeviation * 1000, 2) . "ms");

        if ($timingDeviation > 0.1) {
            $this->warn("⚠️ Timing drift detected!");
            Log::warning("Waiting time drift", [
                'expected' => 10.0,
                'actual' => $actualWaitTime,
                'deviation_ms' => $timingDeviation * 1000
            ]);
        } else {
            $this->info("🎯 Perfect timing!");
        }
    }

    /**
     * ✅ FIXED: Game running with fixed crash point
     */
    private function runGame(CrashGame $game): void
    {
        $currentMultiplier = 1.00;
        $crashPoint = (float) $game->crash_point;

        $speedProfile = $this->speedService->getSpeedProfileName();
        $this->info("🚀 Running game #{$game->id} - Target Crash: {$crashPoint}x - Speed: {$speedProfile}");

        $this->broadcastGameUpdate($game, 1.00, 'running');

        event(new \App\Events\CrashGameStarted($game, $currentMultiplier));

        $lastBroadcastTime = microtime(true);
        $broadcastIntervalMs = 100;

        while ($currentMultiplier < $crashPoint) {
            if (Cache::get('crash_game_stop')) {
                $this->info('Stop signal received.');
                break;
            }

            $increment = $this->speedService->calculateDynamicIncrement($currentMultiplier);
            $nextMultiplier = $currentMultiplier + $increment;

            if ($nextMultiplier >= $crashPoint) {
                $currentMultiplier = $crashPoint;
                break;
            }

            $currentMultiplier = $nextMultiplier;

            $currentTime = microtime(true);
            if (($currentTime - $lastBroadcastTime) * 1000 >= $broadcastIntervalMs) {
                // ✅ Reload game to check if crash point changed (due to cashouts)
                $game->refresh();
                $crashPoint = (float) $game->crash_point;

                $this->broadcastGameUpdate($game, $currentMultiplier, 'running');
                $lastBroadcastTime = $currentTime;
            }

            if (fmod($currentMultiplier, 1.0) < $increment) {
                $activePlayers = $game->active_participants;
                $this->line("Current: " . number_format($currentMultiplier, 2) . "x (Target: {$crashPoint}x, Active: {$activePlayers})");
            }

            $interval = $this->speedService->getCurrentInterval();
            usleep($interval * 1000);
        }

        if (!Cache::get('crash_game_stop') && $currentMultiplier >= $crashPoint) {
            $currentMultiplier = $crashPoint;

            $this->broadcastGameUpdate($game, $crashPoint, 'running');
            usleep(100000);

            $this->gameService->crashGame($game);
            $this->broadcastGameUpdate($game, $crashPoint, 'crashed');

            event(new \App\Events\CrashGameCrashed($game));

            $this->error("💥 CRASHED at {$crashPoint}x!");

            // ✅ EXACT 2 second display time
            $this->preciseSleep(2.0);

            $game->refresh();

            $houseProfit = $this->gameService->calculateHouseProfit($game);
            $this->info("💰 Admin Profit: ৳{$houseProfit}");

            $this->clearAllGameCache();
        }
    }

    /**
     * ✅ NEW: Precise sleep function
     */
    private function preciseSleep(float $seconds): void
    {
        $target = microtime(true) + $seconds;

        while (microtime(true) < $target) {
            $remaining = $target - microtime(true);

            if ($remaining > 0.01) {
                usleep(10000); // 10ms
            } else {
                usleep(1000); // 1ms for final precision
            }
        }
    }

    private function displayGameSummary(CrashGame $game): void
    {
        $this->line("┌─────────────────────────────────────┐");
        $this->info("📊 Game #{$game->id} Summary:");
        $this->line("└─────────────────────────────────────┘");

        $this->line("💰 Pool Composition:");
        $this->line("   Current Round Bets:  ৳{$game->current_round_bets}");
        $this->line("   Previous Rollover:   ৳{$game->previous_rollover}");
        $this->line("   ─────────────────────────────────────");
        $this->info("   Total Pool:          ৳{$game->total_bet_pool}");

        $cashedOut = $game->total_participants - $game->active_participants;
        $this->line("");
        $this->line("👥 Participants:");
        $this->line("   Total:               {$game->total_participants}");
        $this->line("   Cashed Out:          {$cashedOut} ✅");
        $this->line("   Crashed:             {$game->active_participants} ❌");

        $this->line("");
        $this->line("💵 Financial Summary:");
        $this->line("   Total Paid to Winners: ৳{$game->total_payout}");

        $wonBets = $game->wonBets;
        $totalProfit = $wonBets->sum('profit');
        $actualCommission = $game->admin_commission_amount;

        $this->line("");
        $this->line("📊 Commission Details:");
        $this->line("   Total Profit Paid:   ৳{$totalProfit}");
        $this->line("   Actual Commission:   ৳{$actualCommission} (10% of profits)");

        $this->line("");
        $this->line("💰 Pool Status:");
        $this->line("   Started with:        ৳{$game->total_bet_pool}");
        $this->line("   Paid to winners:     ৳{$game->total_payout}");
        $this->line("   Commission:          ৳{$actualCommission}");
        $this->line("   Remaining:           ৳{$game->remaining_pool}");

        if ($game->rollover_to_next > 0) {
            $this->info("   🔄 Rollover to Next:  ৳{$game->rollover_to_next}");
        } else {
            $this->line("   ⚠️  No Rollover (below minimum or disabled)");
        }

        $adminKeeps = $game->remaining_pool - $game->rollover_to_next + $actualCommission;
        $this->info("");
        $this->info("   ✅ Admin Keeps:       ৳{$adminKeeps}");

        $this->line("┌─────────────────────────────────────┐");
    }

    private function displayPoolInfo(CrashGame $game): void
    {
        $this->line("┌─────────────────────────────────────┐");
        $this->info("🔒 Pool Locked - Game #{$game->id}");
        $this->line("└─────────────────────────────────────┘");

        $this->line("📊 Pool Composition:");
        if ($game->previous_rollover > 0) {
            $this->line("   Previous Rollover:   ৳{$game->previous_rollover}");
        }
        $this->line("   Current Round Bets:  ৳{$game->current_round_bets}");
        $this->line("   ─────────────────────────────────────");
        $this->info("   Total Pool:          ৳{$game->total_bet_pool}");

        $this->line("");
        $this->line("💰 Commission Calculation:");
        $this->line("   Max Commission (10%): ৳{$game->admin_commission_amount}");
        $availablePool = $game->total_bet_pool - $game->admin_commission_amount;
        $this->info("   Available Pool:      ৳{$availablePool}");

        $this->line("");
        $this->line("👥 Participants:         {$game->total_participants} players");

        $totalActiveBets = $game->bets()->where('status', 'pending')->sum('bet_amount');
        $this->line("   Total Active Bets:   ৳{$totalActiveBets}");

        $this->line("");
        $this->line("🎯 Crash Point Calculation:");
        $this->line("   Formula: Available Pool ÷ Total Active Bets");
        $this->line("   = ৳{$availablePool} ÷ ৳{$totalActiveBets}");
        $this->info("   = {$game->crash_point}x ✅");

        $this->line("┌─────────────────────────────────────┐");
    }

    private function broadcastGameUpdate(CrashGame $game, float $multiplier, string $status, array $extra = []): void
    {
        $data = [
            'game_id' => $game->id,
            'multiplier' => round($multiplier, 2),
            'status' => $status,
            'crash_point' => $game->crash_point,
            'initial_crash_point' => $game->initial_crash_point,
            'updated_at' => microtime(true),
            'total_bet_pool' => $game->total_bet_pool,
            'current_round_bets' => $game->current_round_bets,
            'previous_rollover' => $game->previous_rollover,
            'total_participants' => $game->total_participants,
            'active_participants' => $game->active_participants,
        ];

        $data = array_merge($data, $extra);

        // ✅ Store with validation
        cache()->put('crash_game_current', $data, 60);

        // ✅ Verify cache was stored
        $verify = cache()->get('crash_game_current');
        if (!$verify) {
            $this->error("⚠️ Cache broadcast failed! Retrying...");
            cache()->put('crash_game_current', $data, 60);
        }
    }

    private function clearAllGameCache(): void
    {
        cache()->forget('crash_game_current');
        $this->clearWaitingCache();
        $this->info("🧹 Cache cleared");
    }

    private function clearWaitingCache(): void
    {
        cache()->forget('crash_game_waiting_start');
        cache()->forget('crash_game_waiting_duration');
        cache()->forget('crash_game_waiting_end');
    }

    private function cleanup(): void
    {
        $this->info("🧹 Cleaning up...");
        Cache::forget('crash_game_running');
        Cache::forget('crash_game_pid');
        Cache::forget('crash_game_started_at');
        Cache::forget('crash_game_stop');
        $this->clearAllGameCache();
        $this->info('✅ Cleanup completed.');
    }
}

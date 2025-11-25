<?php
// app/Console/Commands/RunCrashGame.php - UPDATED WITH NEW LOGIC DISPLAY

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
    protected $description = 'Run the crash game loop with dynamic crash point';

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

        if (!$game || $game->isCrashed()) {
            if ($this->settingsReloaded) {
                $this->info('Applying new settings...');
                $this->settingsReloaded = false;
            }

            $this->clearAllGameCache();

            if ($game && $game->isCrashed()) {
                $this->displayGameSummary($game);
                $this->info("Previous game crashed. 3 second pause...");
                sleep(3);
            }

            $game = $this->gameService->createGame();

            $previousRollover = \App\Models\CrashGame::getLastRolloverAmount();
            if ($previousRollover > 0) {
                $this->info("🔄 Rollover from previous game: ৳{$previousRollover}");
            } else {
                $this->info("💰 Starting fresh - No rollover");
            }

            $this->info("New game created: #{$game->id}");

            $this->executeExactWaiting($game);
            $this->clearWaitingCache();
        }

        if ($game->isPending()) {
            $this->gameService->startGame($game);
            $this->gameService->startBets($game);

            $this->displayPoolInfo($game->fresh());

            $this->info("Game #{$game->id} started!");
            usleep(300000);
        }

        if ($game->isRunning()) {
            $this->runGame($game);
        }
    }

    // 🆕 UPDATED: Display with new logic
    private function displayGameSummary(CrashGame $game): void
    {
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Game #{$game->id} Summary:");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // Pool breakdown
        $this->line("💰 Pool Composition:");
        $this->line("   Current Round Bets:  ৳{$game->current_round_bets}");
        $this->line("   Previous Rollover:   ৳{$game->previous_rollover}");
        $this->line("   ─────────────────────────────────────");
        $this->info("   Total Pool:          ৳{$game->total_bet_pool}");

        // Participants
        $cashedOut = $game->total_participants - $game->active_participants;
        $this->line("");
        $this->line("👥 Participants:");
        $this->line("   Total:               {$game->total_participants}");
        $this->line("   Cashed Out:          {$cashedOut} ✅");
        $this->line("   Crashed:             {$game->active_participants} ❌");

        // Financial summary
        $this->line("");
        $this->line("💵 Financial Summary:");
        $this->line("   Total Paid to Winners: ৳{$game->total_payout}");

        // Commission calculation
        $wonBets = $game->wonBets;
        $totalProfit = $wonBets->sum('profit');
        $actualCommission = $game->admin_commission_amount;

        $this->line("");
        $this->line("📊 Commission Details:");
        $this->line("   Total Profit Paid:   ৳{$totalProfit}");
        $this->line("   Actual Commission:   ৳{$actualCommission} (10% of profits)");

        // Pool status
        $this->line("");
        $this->line("💰 Pool Status:");
        $this->line("   Started with:        ৳{$game->total_bet_pool}");
        $this->line("   Paid to winners:     ৳{$game->total_payout}");
        $this->line("   Commission:          ৳{$actualCommission}");
        $this->line("   Remaining:           ৳{$game->remaining_pool}");

        // Rollover
        if ($game->rollover_to_next > 0) {
            $this->info("   🔄 Rollover to Next:  ৳{$game->rollover_to_next}");
        } else {
            $this->line("   ⚠️  No Rollover (below minimum or disabled)");
        }

        // Admin keeps
        $adminKeeps = $game->remaining_pool - $game->rollover_to_next + $actualCommission;
        $this->info("");
        $this->info("   ✅ Admin Keeps:       ৳{$adminKeeps}");

        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }

    // 🆕 UPDATED: Display with calculation
    private function displayPoolInfo(CrashGame $game): void
    {
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🔒 Pool Locked - Game #{$game->id}");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

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

        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }

    // private function displayPoolInfo(CrashGame $game): void
    // {
    //     $this->line("┌─────────────────────────────────────┐");
    //     $this->info("🔒 Pool Locked - Game #{$game->id}");
    //     $this->line("└─────────────────────────────────────┘");

    //     $this->line("📊 Pool Composition:");
    //     if ($game->previous_rollover > 0) {
    //         $this->line("   Previous Rollover:   ৳{$game->previous_rollover}");
    //     }
    //     $this->line("   Current Round Bets:  ৳{$game->current_round_bets}");
    //     $this->line("   ─────────────────────────────────────");
    //     $this->info("   Total Pool:          ৳{$game->total_bet_pool}");

    //     $this->line("");
    //     $this->line("💰 Commission Calculation:");
    //     $this->line("   Max Commission (10%): ৳{$game->admin_commission_amount}");
    //     $availablePool = $game->total_bet_pool - $game->admin_commission_amount;
    //     $this->info("   Available Pool:      ৳{$availablePool}");

    //     $this->line("");
    //     $this->line("👥 Participants:         {$game->total_participants} players");

    //     $totalActiveBets = $game->bets()->where('status', 'pending')->sum('bet_amount');
    //     $this->line("   Total Active Bets:   ৳{$totalActiveBets}");

    //     $this->line("");

    //     // ✅ Show random crash point generation
    //     if ($totalActiveBets > 0) {
    //         $maxPoolCrash = $availablePool / $totalActiveBets;
    //         $this->line("🎯 Crash Point Generation:");
    //         $this->line("   Max Pool Crash:      {$maxPoolCrash}x");
    //         $this->line("   Random Method:       Weighted Distribution");
    //         $this->info("   Final Crash Point:   {$game->crash_point}x ✅ (FIXED)");
    //     } else {
    //         $this->line("🎯 Crash Point Generation:");
    //         $this->line("   No bets placed");
    //         $this->line("   Random Method:       Pure Random");
    //         $this->info("   Final Crash Point:   {$game->crash_point}x ✅ (FIXED)");
    //     }

    //     $this->line("┌─────────────────────────────────────┐");
    // }

    private function executeExactWaiting(CrashGame $game): void
    {
        $waitingTime = 10.0;
        $waitingStartTime = microtime(true);
        $waitingEndTime = $waitingStartTime + $waitingTime;

        cache()->put('crash_game_waiting_start', $waitingStartTime, 60);
        cache()->put('crash_game_waiting_duration', $waitingTime, 60);
        cache()->put('crash_game_waiting_end', $waitingEndTime, 60);

        $this->broadcastGameUpdate($game, 1.00, 'waiting', [
            'waiting_start' => $waitingStartTime,
            'waiting_duration' => $waitingTime,
            'waiting_end' => $waitingEndTime
        ]);

        $this->info("⏱️  EXACT 10.00 second waiting started");

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

            if ($remainingTime > 0.1) {
                usleep(50000);
            } else {
                usleep(10000);
            }
        }

        $actualWaitTime = microtime(true) - $waitingStartTime;
        $this->info("✅ Completed: " . number_format($actualWaitTime, 4) . " seconds");
    }

    private function runGame(CrashGame $game): void
    {
        $currentMultiplier = 1.00;
        $crashPoint = (float) $game->crash_point;

        $speedProfile = $this->speedService->getSpeedProfileName();
        $this->info("Running game #{$game->id} - Target Crash: {$crashPoint}x - Speed: {$speedProfile}");

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
                // Reload game to get updated crash point
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

            sleep(2);

            $game->refresh();

            $houseProfit = $this->gameService->calculateHouseProfit($game);
            $this->info("Admin Profit: ৳{$houseProfit}");

            $this->clearAllGameCache();
        }
    }

    // private function runGame(CrashGame $game): void
    // {
    //     $currentMultiplier = 1.00;
    //     $crashPoint = (float) $game->crash_point; // ✅ Fixed, never changes

    //     $speedProfile = $this->speedService->getSpeedProfileName();
    //     $this->info("Running game #{$game->id} - Fixed Crash: {$crashPoint}x - Speed: {$speedProfile}");

    //     $this->broadcastGameUpdate($game, 1.00, 'running');

    //     event(new \App\Events\CrashGameStarted($game, $currentMultiplier));

    //     $lastBroadcastTime = microtime(true);
    //     $broadcastIntervalMs = 100;

    //     while ($currentMultiplier < $crashPoint) {
    //         if (Cache::get('crash_game_stop')) {
    //             $this->info('Stop signal received.');
    //             break;
    //         }

    //         $increment = $this->speedService->calculateDynamicIncrement($currentMultiplier);
    //         $nextMultiplier = $currentMultiplier + $increment;

    //         if ($nextMultiplier >= $crashPoint) {
    //             $currentMultiplier = $crashPoint;
    //             break;
    //         }

    //         $currentMultiplier = $nextMultiplier;

    //         $currentTime = microtime(true);
    //         if (($currentTime - $lastBroadcastTime) * 1000 >= $broadcastIntervalMs) {
    //             // ✅ NO crash point reload - it's fixed!
    //             $this->broadcastGameUpdate($game, $currentMultiplier, 'running');
    //             $lastBroadcastTime = $currentTime;
    //         }

    //         if (fmod($currentMultiplier, 1.0) < $increment) {
    //             $activePlayers = $game->active_participants;
    //             $this->line("Current: " . number_format($currentMultiplier, 2) . "x (Fixed Crash: {$crashPoint}x, Active: {$activePlayers})");
    //         }

    //         $interval = $this->speedService->getCurrentInterval();
    //         usleep($interval * 1000);
    //     }

    //     if (!Cache::get('crash_game_stop') && $currentMultiplier >= $crashPoint) {
    //         $currentMultiplier = $crashPoint;

    //         $this->broadcastGameUpdate($game, $crashPoint, 'running');
    //         usleep(100000);

    //         $this->gameService->crashGame($game);
    //         $this->broadcastGameUpdate($game, $crashPoint, 'crashed');

    //         event(new \App\Events\CrashGameCrashed($game));

    //         $this->error("💥 CRASHED at {$crashPoint}x!");

    //         sleep(2);

    //         $game->refresh();

    //         $houseProfit = $this->gameService->calculateHouseProfit($game);
    //         $this->info("Admin Profit: ৳{$houseProfit}");

    //         $this->clearAllGameCache();
    //     }
    // }

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
        cache()->put('crash_game_current', $data, 60);

        $this->info("📡 Broadcast: {$status} @ {$multiplier}x");
    }

    private function clearAllGameCache(): void
    {
        cache()->forget('crash_game_current');
        $this->clearWaitingCache();
    }

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

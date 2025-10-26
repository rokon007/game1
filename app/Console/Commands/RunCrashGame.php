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

    // Add the missing property
    private bool $settingsReloaded = false;

    public function __construct(CrashGameService $gameService)
    {
        parent::__construct();
        $this->gameService = $gameService;
        $this->speedService = new CrashGameSpeedService();
    }

    public function handle(): int
    {
        // Get current PID and log it
        $pid = getmypid();
        $this->info("=== Crash Game Process Started ===");
        $this->info("Process PID: {$pid}");
        $this->info("Memory Usage: " . memory_get_usage(true) / 1024 / 1024 . " MB");
        $this->info("Game Active: " . ($this->gameService->isGameActive() ? 'Yes' : 'No'));

        // Check if game is active
        if (!$this->gameService->isGameActive()) {
            $this->error('Crash game is not active!');
            Log::warning('Crash game started but is not active in settings');
            return Command::FAILURE;
        }

        $this->info('Starting Crash Game Loop...');

        // Store process info
        Cache::put('crash_game_running', true, 3600);
        Cache::put('crash_game_pid', $pid, 3600);
        Cache::put('crash_game_started_at', now()->toDateTimeString(), 3600);

        $this->info("Process info stored in cache");

        // Register shutdown function for cleanup
        register_shutdown_function(function () {
            $this->cleanup();
        });

        $cycleCount = 0;
        while (true) {
            $cycleCount++;
            $this->info("=== Game Cycle #{$cycleCount} ===");

            // Check stop signal
            if (Cache::get('crash_game_stop')) {
                $this->info('Stop signal received. Stopping game...');
                break;
            }

            try {
                // Check for settings changes before each game cycle
                if ($this->gameService->checkAndReloadSettings()) {
                    $this->settingsReloaded = true;
                    $this->info('Settings changes detected! New settings will be applied to the next game.');

                    // Also reload speed service with new settings
                    $this->speedService = new CrashGameSpeedService();
                }

                $this->runGameCycle();
            } catch (\Exception $e) {
                Log::error('Crash Game Error: ' . $e->getMessage());
                $this->error('Error: ' . $e->getMessage());

                // Small delay before retry
                sleep(5);
            }

            // Small delay between cycles
            sleep(2);
        }

        $this->cleanup();
        $this->info("=== Crash Game Process Ended ===");
        return Command::SUCCESS;
    }

    private function runGameCycle(): void
    {
        // Get or create current game
        $game = $this->gameService->getCurrentGame();

        if (!$game || $game->isCrashed()) {
            // If settings were reloaded, log it
            if ($this->settingsReloaded) {
                $this->info('Applying new settings to the game...');
                $this->settingsReloaded = false; // Reset the flag
            }

            // Create new game
            $game = $this->gameService->createGame();
            $this->info("New game created: #{$game->id} - Crash Point: {$game->crash_point}x");

            // Wait for bets (dynamic waiting time)
            $waitingTime = $this->gameService->getBetWaitingTime();
            $this->info("Waiting for bets for {$waitingTime} seconds...");

            // Check for stop signal during waiting period
            $waitStart = time();
            while ((time() - $waitStart) < $waitingTime) {
                if (Cache::get('crash_game_stop')) {
                    $this->info('Stop signal received during betting period.');
                    return;
                }
                sleep(1);
            }
        }

        // Start the game if it's pending
        if ($game->isPending()) {
            $this->gameService->startGame($game);
            $this->gameService->startBets($game);
            $this->info("Game #{$game->id} started!");
        }

        // Run the game
        if ($game->isRunning()) {
            $this->runGame($game);
        }
    }

    private function runGame(CrashGame $game): void
    {
        $currentMultiplier = 1.00;
        $crashPoint = (float) $game->crash_point;

        $speedProfile = $this->speedService->getSpeedProfileName();
        $this->info("Running game #{$game->id} - Will crash at {$crashPoint}x - Speed: {$speedProfile}");

        // Dispatch game started event
        event(new \App\Events\CrashGameStarted($game, $currentMultiplier));

        // Increase multiplier until crash
        while ($currentMultiplier < $crashPoint) {
            // Check for stop signal
            if (Cache::get('crash_game_stop')) {
                $this->info('Stop signal received during game execution.');
                break;
            }

            $increment = $this->speedService->calculateDynamicIncrement($currentMultiplier);
            $currentMultiplier += $increment;

            // Broadcast current multiplier
            $this->broadcastGameUpdate($game, $currentMultiplier, 'running');

            // Dispatch running event (optional - every 0.5x)
            if (fmod($currentMultiplier, 0.5) < $increment) {
                $this->line("Current: {$currentMultiplier}x (Increment: {$increment})");
                event(new \App\Events\CrashGameStarted($game, $currentMultiplier));
            }

            // Dynamic delay based on settings
            $interval = $this->speedService->getCurrentInterval();
            usleep($interval * 1000); // Convert ms to microseconds
        }

        // Only crash if we didn't receive a stop signal
        if (!Cache::get('crash_game_stop') && $currentMultiplier >= $crashPoint) {
            // Crash the game
            $this->gameService->crashGame($game);
            $this->broadcastGameUpdate($game, $crashPoint, 'crashed');

            // Dispatch game crashed event
            event(new \App\Events\CrashGameCrashed($game));

            $this->error("CRASHED at {$crashPoint}x!");

            // Calculate house profit
            $houseProfit = $this->gameService->calculateHouseProfit($game);
            $this->info("House Profit: ৳{$houseProfit}");
        }
    }

    private function broadcastGameUpdate(CrashGame $game, float $multiplier, string $status): void
    {
        cache()->put('crash_game_current', [
            'game_id' => $game->id,
            'multiplier' => round($multiplier, 2),
            'status' => $status,
            'crash_point' => $game->crash_point,
            'updated_at' => now()->timestamp,
        ], 60);
    }

    private function cleanup(): void
    {
        $this->info("Cleaning up process resources...");
        Cache::forget('crash_game_running');
        Cache::forget('crash_game_pid');
        Cache::forget('crash_game_started_at');
        Cache::forget('crash_game_stop');
        $this->info('Game process cleanup completed.');
    }
}

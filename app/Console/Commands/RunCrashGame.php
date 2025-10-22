<?php

namespace App\Console\Commands;

use App\Models\CrashGame;
use App\Services\CrashGameService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunCrashGame extends Command
{
    protected $signature = 'crash:run';
    protected $description = 'Run the crash game loop';

    private CrashGameService $gameService;

    public function __construct(CrashGameService $gameService)
    {
        parent::__construct();
        $this->gameService = $gameService;
    }

    public function handle(): int
    {
        $this->info('Starting Crash Game Loop...');

        while (true) {
            try {
                $this->runGameCycle();
            } catch (\Exception $e) {
                Log::error('Crash Game Error: ' . $e->getMessage());
                $this->error('Error: ' . $e->getMessage());
            }

            // Small delay between cycles
            sleep(3);
        }

        return Command::SUCCESS;
    }

    private function runGameCycle(): void
    {
        // Get or create current game
        $game = $this->gameService->getCurrentGame();

        if (!$game || $game->isCrashed()) {
            // Create new game
            $game = $this->gameService->createGame();
            $this->info("New game created: #{$game->id} - Crash Point: {$game->crash_point}x");

            // Wait for bets (10 seconds)
            $this->info('Waiting for bets...');
            sleep(10);
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

        $this->info("Running game #{$game->id} - Will crash at {$crashPoint}x");

        // Dispatch game started event
        event(new \App\Events\CrashGameStarted($game, $currentMultiplier));

        // Increase multiplier until crash
        while ($currentMultiplier < $crashPoint) {
            $currentMultiplier += 0.01;

            // Broadcast current multiplier
            $this->broadcastGameUpdate($game, $currentMultiplier, 'running');

            // Dispatch running event (optional - every 0.5x)
            if (fmod($currentMultiplier, 0.5) < 0.01) {
                $this->line("Current: {$currentMultiplier}x");
                event(new \App\Events\CrashGameStarted($game, $currentMultiplier));
            }

            // Small delay for realistic speed
            usleep(100000); // 0.1 second
        }

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

    private function broadcastGameUpdate(CrashGame $game, float $multiplier, string $status): void
    {
        // এখানে আপনি Laravel Broadcasting ব্যবহার করতে পারেন
        // অথবা Livewire Events dispatch করতে পারেন

        // Example: Cache-based approach for simple implementation
        cache()->put('crash_game_current', [
            'game_id' => $game->id,
            'multiplier' => round($multiplier, 2),
            'status' => $status,
            'crash_point' => $game->crash_point,
            'updated_at' => now()->timestamp,
        ], 60);
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StartCrashGameDaemon extends Command
{
    protected $signature = 'crash:daemon';
    protected $description = 'Start crash game as daemon (auto-restart if stopped)';

    public function handle(): int
    {
        // Check if already running
        $pid = Cache::get('crash_game_pid');

        if ($pid && $this->isProcessRunning($pid)) {
            // Already running, no need to start
            return Command::SUCCESS;
        }

        // Previous process died, start new one
        $this->info("🚀 Starting crash game daemon...");
        Log::info("Starting crash game daemon");

        // Start in background
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows
            pclose(popen('start /B php artisan crash:run', 'r'));
            $this->info("✅ Crash game started (Windows)");
        } else {
            // Linux/Unix
            $artisanPath = base_path('artisan');
            $logPath = storage_path('logs/crash-game.log');

            // Execute in background and get PID
            $cmd = sprintf(
                'nohup php %s crash:run >> %s 2>&1 & echo $!',
                $artisanPath,
                $logPath
            );

            exec($cmd, $output);
            $newPid = $output[0] ?? null;

            if ($newPid) {
                Cache::put('crash_game_pid', $newPid, 3600);
                $this->info("✅ Crash game started (PID: {$newPid})");
                Log::info("Crash game started with PID: {$newPid}");
            } else {
                $this->error("❌ Failed to start crash game");
                Log::error("Failed to start crash game");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Check if process is running
     */
    private function isProcessRunning($pid): bool
    {
        if (!$pid) {
            return false;
        }

        try {
            // Check if PID exists
            return posix_kill($pid, 0);
        } catch (\Exception $e) {
            return false;
        }
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('users:update-online-status')->everyMinute();
        $schedule->command('game-locks:cleanup')->everyMinute();
        // $schedule->command('lottery:run-draws')->everyMinute();
        // $schedule->command('lottery:complete-stuck-draws')->everyMinute();

        // Run lottery draws every 2 minutes (reduced frequency to prevent overlap)
        $schedule->command('lottery:run-draws')
            ->everyTwoMinutes()
            ->withoutOverlapping(10); // Prevent overlapping for up to 10 minutes

        // Check for stuck draws every 3 minutes
        $schedule->command('lottery:complete-stuck-draws')
            ->everyThreeMinutes()
            ->withoutOverlapping(5); // Prevent overlapping for up to 5 minutes
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

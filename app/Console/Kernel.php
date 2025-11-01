<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\RunCrashGame::class,
        // অন্যান্য commands এখানে যোগ করুন
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('users:update-online-status')->everyMinute();
        $schedule->command('game-locks:cleanup')->everyMinute();

        // Run lottery draws every 2 minutes (reduced frequency to prevent overlap)
        $schedule->command('lottery:run-draws')
            ->everyTwoMinutes()
            ->withoutOverlapping(10); // Prevent overlapping for up to 10 minutes

        // Check for stuck draws every 3 minutes
        $schedule->command('lottery:complete-stuck-draws')
            ->everyThreeMinutes()
            ->withoutOverlapping(5); // Prevent overlapping for up to 5 minutes

        $schedule->command('hajari-games:cancel-old')->everyMinute();

        $schedule->command('crash:cleanup --minutes=30 --quiet')
             ->everyMinute()
             ->withoutOverlapping();

        // Crash game related schedules (যদি প্রয়োজন হয়)
        $schedule->command('crash:run')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

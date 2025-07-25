<?php

namespace App\Helpers;

class LotteryHelper
{
    /**
     * Calculate draw duration based on prize count
     */
    public static function calculateDrawDuration(int $prizeCount): array
    {
        // Base time: 2 minutes for setup and completion
        $baseMinutes = 2;

        // Per prize timing:
        // - 8 seconds animation
        // - 10 seconds countdown (except last prize)
        // - 2 seconds buffer
        // Total: ~20 seconds per prize = 0.33 minutes
        $minutesPerPrize = 0.5; // Rounded up for safety

        // Calculate total duration
        $totalMinutes = $baseMinutes + ($prizeCount * $minutesPerPrize);

        // Set reasonable limits
        $minDuration = 3; // Minimum 3 minutes
        $maxDuration = 20; // Maximum 20 minutes

        $finalDuration = max($minDuration, min($totalMinutes, $maxDuration));

        return [
            'duration_minutes' => (int) ceil($finalDuration),
            'duration_seconds' => (int) ceil($finalDuration * 60),
            'prize_count' => $prizeCount,
            'base_time' => $baseMinutes,
            'time_per_prize' => $minutesPerPrize
        ];
    }

    /**
     * Get human readable duration text
     */
    public static function getDurationText(int $prizeCount): string
    {
        $info = self::calculateDrawDuration($prizeCount);
        $minutes = $info['duration_minutes'];

        if ($minutes < 5) {
            return "Approximately {$minutes} minutes";
        } elseif ($minutes < 10) {
            return "About {$minutes} minutes";
        } else {
            return "Up to {$minutes} minutes";
        }
    }
}

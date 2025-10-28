<?php

namespace App\Services;

use App\Models\CrashGameSetting;

class CrashGameSpeedService
{
    private $settings;

    public function __construct()
    {
        $this->settings = CrashGameSetting::first();
    }

    public function getSpeedConfig(): array
    {
        $baseConfig = [
            'slow' => [
                'increment' => 0.01,
                'interval_ms' => 150,
                'auto_acceleration' => true,
                'name' => 'Slow & Realistic'
            ],
            'medium' => [
                'increment' => 0.02,
                'interval_ms' => 100,
                'auto_acceleration' => true,
                'name' => 'Medium Balanced'
            ],
            'fast' => [
                'increment' => 0.05,
                'interval_ms' => 50,
                'auto_acceleration' => true,
                'name' => 'Fast & Exciting'
            ],
            'custom' => [
                'increment' => $this->settings->multiplier_increment,
                'interval_ms' => $this->settings->multiplier_interval_ms,
                'auto_acceleration' => $this->settings->enable_auto_acceleration,
                'name' => 'Custom Settings'
            ]
        ];

        return $baseConfig[$this->settings->speed_profile] ?? $baseConfig['medium'];
    }

    public function calculateDynamicIncrement(float $currentMultiplier): float
    {
        $config = $this->getSpeedConfig();
        $baseIncrement = $config['increment'];

        if (!$config['auto_acceleration']) {
            return $baseIncrement;
        }

        $maxSpeedMultiplier = $this->settings->max_speed_multiplier;

        // যদি target crash point এর কাছাকাছি আসে তাহলে স্পিড কমিয়ে দিন
        // if ($currentMultiplier >= $maxSpeedMultiplier) {
        //     return $baseIncrement;
        // }

        // Progressive acceleration - আরও দ্রুত বৃদ্ধি
        if ($currentMultiplier > 20.00) {
            return $baseIncrement * 8; // খুব দ্রুত
        } elseif ($currentMultiplier > 10.00) {
            return $baseIncrement * 6;
        } elseif ($currentMultiplier > 5.00) {
            return $baseIncrement * 4;
        } elseif ($currentMultiplier > 3.00) {
            return $baseIncrement * 3;
        } elseif ($currentMultiplier > 2.00) {
            return $baseIncrement * 2;
        } elseif ($currentMultiplier > 1.50) {
            return $baseIncrement * 1.5;
        }

        return $baseIncrement;
    }

    // public function calculateDynamicIncrement(float $currentMultiplier): float
    // {
    //     $config = $this->getSpeedConfig();
    //     $baseIncrement = $config['increment'];

    //     // যদি auto acceleration বন্ধ থাকে, সরাসরি base increment ব্যবহার করো
    //     if (!$config['auto_acceleration']) {
    //         return $baseIncrement;
    //     }

    //     $maxSpeedMultiplier = $this->settings->max_speed_multiplier;

    //     // যদি সর্বোচ্চ সীমায় পৌঁছে যায়
    //     if ($currentMultiplier >= $maxSpeedMultiplier) {
    //         return $baseIncrement;
    //     }

    //     // ✅ 1x থেকে 10x পর্যন্ত কোনো acceleration থাকবে না
    //     if ($currentMultiplier <= 10.00) {
    //         return $baseIncrement; // স্থির গতিতে বাড়বে
    //     }

    //     // ✅ 10x এর পর থেকে progressive acceleration শুরু হবে
    //     if ($currentMultiplier > 10.00 && $currentMultiplier <= 20.00) {
    //         return $baseIncrement * 2; // মাঝারি গতি
    //     } elseif ($currentMultiplier > 20.00 && $currentMultiplier <= 40.00) {
    //         return $baseIncrement * 4; // দ্রুত
    //     } elseif ($currentMultiplier > 40.00 && $currentMultiplier <= 70.00) {
    //         return $baseIncrement * 6; // খুব দ্রুত
    //     } elseif ($currentMultiplier > 70.00) {
    //         return $baseIncrement * 8; // চরম গতি
    //     }

    //     return $baseIncrement;
    // }


    public function getCurrentInterval(): int
    {
        $config = $this->getSpeedConfig();
        return $config['interval_ms'];
    }

    public function getSpeedProfileName(): string
    {
        $config = $this->getSpeedConfig();
        return $config['name'];
    }

    /**
     * Calculate estimated time to reach crash point
     */
    public function estimateGameDuration(float $crashPoint): float
    {
        $config = $this->getSpeedConfig();
        $baseIncrement = $config['increment'];
        $intervalMs = $config['interval_ms'];

        $currentMultiplier = 1.00;
        $totalTime = 0;

        while ($currentMultiplier < $crashPoint) {
            $increment = $this->calculateDynamicIncrement($currentMultiplier);
            $currentMultiplier += $increment;
            $totalTime += $intervalMs;
        }

        return $totalTime / 1000; // Convert to seconds
    }
}

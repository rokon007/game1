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

    /**
     * ✅ UPDATED: maxSpeedMultiplier চেক সম্পূর্ণ রিমুভ করা হয়েছে
     */
    public function calculateDynamicIncrement(float $currentMultiplier): float
    {
        $config = $this->getSpeedConfig();
        $baseIncrement = $config['increment'];

        if (!$config['auto_acceleration']) {
            return $baseIncrement;
        }

        // Progressive acceleration - আরো দ্রুত বৃদ্ধি (কোনো স্পিড লিমিট নেই)
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


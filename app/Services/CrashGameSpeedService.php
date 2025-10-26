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
                'increment' => 0.005,
                'interval_ms' => 200,
                'auto_acceleration' => false,
                'name' => 'Slow & Realistic'
            ],
            'medium' => [
                'increment' => 0.01,
                'interval_ms' => 100,
                'auto_acceleration' => true,
                'name' => 'Medium Balanced'
            ],
            'fast' => [
                'increment' => 0.02,
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

        if (!$config['auto_acceleration']) {
            return $config['increment'];
        }

        $baseIncrement = $config['increment'];
        $maxSpeedMultiplier = $this->settings->max_speed_multiplier;

        if ($currentMultiplier >= $maxSpeedMultiplier) {
            return $baseIncrement;
        }

        // Progressive speed increase
        if ($currentMultiplier > 10.00) {
            return $baseIncrement * 3;
        } elseif ($currentMultiplier > 5.00) {
            return $baseIncrement * 2;
        } elseif ($currentMultiplier > 3.00) {
            return $baseIncrement * 1.5;
        } elseif ($currentMultiplier > 2.00) {
            return $baseIncrement * 1.2;
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
}

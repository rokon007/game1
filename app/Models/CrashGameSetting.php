<?php
// app/Models/CrashGameSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrashGameSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic Settings
        'house_edge',
        'min_multiplier',
        'max_multiplier',
        'bet_waiting_time',
        'min_bet_amount',
        'max_bet_amount',
        'is_active',

        // Speed Control
        'multiplier_increment',
        'multiplier_interval_ms',
        'max_speed_multiplier',
        'enable_auto_acceleration',
        'speed_profile',

        // ✅ Commission
        'admin_commission_rate',
        'commission_type',
        'fixed_commission_amount',

        // ✅ Pool Management
        'min_pool_amount',
        'max_payout_ratio',
        'enable_dynamic_crash',
        'crash_increase_per_cashout',

        // 🆕 Rollover Configuration
        'enable_pool_rollover',
        'rollover_percentage',
        'min_rollover_amount',
        'max_rollover_amount',
        'rollover_includes_commission',
    ];

    protected $casts = [
        // Basic
        'house_edge' => 'decimal:4',
        'min_multiplier' => 'decimal:2',
        'max_multiplier' => 'decimal:2',
        'bet_waiting_time' => 'integer',
        'min_bet_amount' => 'decimal:2',
        'max_bet_amount' => 'decimal:2',
        'is_active' => 'boolean',

        // Speed
        'multiplier_increment' => 'decimal:4',
        'multiplier_interval_ms' => 'integer',
        'max_speed_multiplier' => 'decimal:2',
        'enable_auto_acceleration' => 'boolean',

        // Commission
        'admin_commission_rate' => 'decimal:2',
        'fixed_commission_amount' => 'decimal:2',

        // Pool
        'min_pool_amount' => 'decimal:2',
        'max_payout_ratio' => 'decimal:2',
        'enable_dynamic_crash' => 'boolean',
        'crash_increase_per_cashout' => 'decimal:2',

        // 🆕 Rollover
        'enable_pool_rollover' => 'boolean',
        'rollover_percentage' => 'decimal:2',
        'min_rollover_amount' => 'decimal:2',
        'max_rollover_amount' => 'decimal:2',
        'rollover_includes_commission' => 'boolean',
    ];

    // ===== Singleton Pattern =====

    /**
     * Get the single settings instance
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            static::getDefaultSettings()
        );
    }

    /**
     * Default settings
     */
    public static function getDefaultSettings(): array
    {
        return [
            // Basic
            'house_edge' => 0.05,
            'min_multiplier' => 1.01,
            'max_multiplier' => 100.00,
            'bet_waiting_time' => 10,
            'min_bet_amount' => 1.00,
            'max_bet_amount' => 10000.00,
            'is_active' => true,

            // Speed
            'multiplier_increment' => 0.01,
            'multiplier_interval_ms' => 100,
            'max_speed_multiplier' => 5.00,
            'enable_auto_acceleration' => true,
            'speed_profile' => 'medium',

            // Commission
            'admin_commission_rate' => 10.00,
            'commission_type' => 'percentage',
            'fixed_commission_amount' => 0,

            // Pool
            'min_pool_amount' => 100.00,
            'max_payout_ratio' => 0.90,
            'enable_dynamic_crash' => true,
            'crash_increase_per_cashout' => 0.50,

            // Rollover
            'enable_pool_rollover' => true,
            'rollover_percentage' => 100.00,
            'min_rollover_amount' => 10.00,
            'max_rollover_amount' => 10000.00,
            'rollover_includes_commission' => false,
        ];
    }

    // ===== 🆕 Rollover Helper Methods =====

    /**
     * Check if rollover is enabled
     */
    public function isRolloverEnabled(): bool
    {
        return $this->enable_pool_rollover === true;
    }

    /**
     * Calculate rollover amount from remaining pool
     */
    public function calculateRollover(float $remainingPool, float $commission = 0): float
    {
        if (!$this->isRolloverEnabled()) {
            return 0;
        }

        // Include commission if configured
        $totalAvailable = $remainingPool;
        if ($this->rollover_includes_commission) {
            $totalAvailable += $commission;
        }

        // Apply percentage
        $rolloverAmount = $totalAvailable * ($this->rollover_percentage / 100);

        // Check minimum
        if ($rolloverAmount < $this->min_rollover_amount) {
            return 0; // Too small, don't bother
        }

        // Apply maximum
        return min($rolloverAmount, $this->max_rollover_amount);
    }

    /**
     * Get rollover display info
     */
    public function getRolloverInfo(): array
    {
        return [
            'enabled' => $this->isRolloverEnabled(),
            'percentage' => $this->rollover_percentage,
            'min_amount' => $this->min_rollover_amount,
            'max_amount' => $this->max_rollover_amount,
            'includes_commission' => $this->rollover_includes_commission,
        ];
    }

    // ===== Commission Helper Methods =====

    /**
     * Calculate commission from bet amount
     */
    public function calculateCommission(float $betAmount): float
    {
        if ($this->commission_type === 'fixed') {
            return (float) $this->fixed_commission_amount;
        }

        return round($betAmount * ($this->admin_commission_rate / 100), 2);
    }

    /**
     * Get commission display text
     */
    public function getCommissionDisplay(): string
    {
        if ($this->commission_type === 'fixed') {
            return '৳' . number_format($this->fixed_commission_amount, 2);
        }

        return $this->admin_commission_rate . '%';
    }

    // ===== Speed Profile Methods =====

    /**
     * Get speed configuration
     */
    public function getSpeedConfig(): array
    {
        $profiles = [
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
                'increment' => $this->multiplier_increment,
                'interval_ms' => $this->multiplier_interval_ms,
                'auto_acceleration' => $this->enable_auto_acceleration,
                'name' => 'Custom Settings'
            ]
        ];

        return $profiles[$this->speed_profile] ?? $profiles['medium'];
    }

    // ===== Validation Methods =====

    /**
     * Validate rollover settings
     */
    public function validateRolloverSettings(): array
    {
        $errors = [];

        if ($this->rollover_percentage < 0 || $this->rollover_percentage > 100) {
            $errors[] = 'Rollover percentage must be between 0-100%';
        }

        if ($this->min_rollover_amount < 0) {
            $errors[] = 'Minimum rollover amount cannot be negative';
        }

        if ($this->max_rollover_amount < $this->min_rollover_amount) {
            $errors[] = 'Maximum rollover must be greater than minimum';
        }

        return $errors;
    }

    /**
     * Validate commission settings
     */
    public function validateCommissionSettings(): array
    {
        $errors = [];

        if ($this->commission_type === 'percentage') {
            if ($this->admin_commission_rate < 0 || $this->admin_commission_rate > 50) {
                $errors[] = 'Commission rate must be between 0-50%';
            }
        }

        if ($this->commission_type === 'fixed') {
            if ($this->fixed_commission_amount < 0) {
                $errors[] = 'Fixed commission cannot be negative';
            }
        }

        return $errors;
    }

    // ===== Statistics Methods =====

    /**
     * Calculate estimated game duration
     */
    public function estimateGameDuration(float $targetMultiplier = 10.0): float
    {
        $config = $this->getSpeedConfig();
        $increment = $config['increment'];
        $intervalMs = $config['interval_ms'];

        $steps = ($targetMultiplier - 1.0) / $increment;
        $totalTimeMs = $steps * $intervalMs;

        return round($totalTimeMs / 1000, 1); // Convert to seconds
    }

    /**
     * Get recommended settings for different strategies
     */
    public static function getRecommendedSettings(string $strategy = 'balanced'): array
    {
        $strategies = [
            'conservative' => [
                'admin_commission_rate' => 15.00,
                'max_payout_ratio' => 0.70,
                'rollover_percentage' => 100.00,
                'description' => 'Maximum admin protection, lower player payouts'
            ],
            'balanced' => [
                'admin_commission_rate' => 10.00,
                'max_payout_ratio' => 0.90,
                'rollover_percentage' => 80.00,
                'description' => 'Good balance between profit and player experience'
            ],
            'aggressive' => [
                'admin_commission_rate' => 5.00,
                'max_payout_ratio' => 0.95,
                'rollover_percentage' => 50.00,
                'description' => 'Player-friendly, relies on volume'
            ],
        ];

        return $strategies[$strategy] ?? $strategies['balanced'];
    }

    // ===== Cache Management =====

    /**
     * Clear settings cache
     */
    public function clearCache(): void
    {
        cache()->forget('crash_game_settings');
        cache()->forget('crash_game_settings_hash');
    }

    /**
     * Boot method - clear cache on save
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->clearCache();
        });
    }
}

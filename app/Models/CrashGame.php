<?php
// app/Models/CrashGame.php - FIXED VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrashGame extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic
        'game_hash',
        'crash_point',
        'initial_crash_point',
        'status',
        'started_at',
        'crashed_at',

        // Pool Management
        'total_bet_pool',
        'previous_rollover',
        'current_round_bets',
        'max_possible_payout',
        'total_participants',
        'active_participants',

        // Commission
        'admin_commission_amount',
        'commission_rate',

        // Payouts
        'total_payout',
        'remaining_pool',
        'rollover_to_next',

        // Status
        'pool_locked',
        'pool_locked_at',
    ];

    protected $casts = [
        'crash_point' => 'decimal:2',
        'initial_crash_point' => 'decimal:2',
        'started_at' => 'datetime',
        'crashed_at' => 'datetime',

        // Pool
        'total_bet_pool' => 'decimal:2',
        'previous_rollover' => 'decimal:2',
        'current_round_bets' => 'decimal:2',
        'max_possible_payout' => 'decimal:2',
        'total_participants' => 'integer',
        'active_participants' => 'integer',

        // Commission
        'admin_commission_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',

        // Payouts
        'total_payout' => 'decimal:2',
        'remaining_pool' => 'decimal:2',
        'rollover_to_next' => 'decimal:2',

        // Status
        'pool_locked' => 'boolean',
        'pool_locked_at' => 'datetime',
    ];

    // ===== Relationships =====

    public function bets(): HasMany
    {
        return $this->hasMany(CrashBet::class);
    }

    public function activeBets(): HasMany
    {
        return $this->hasMany(CrashBet::class)
            ->whereIn('status', ['pending', 'playing']);
    }

    public function wonBets(): HasMany
    {
        return $this->hasMany(CrashBet::class)
            ->where('status', 'won');
    }

    public function lostBets(): HasMany
    {
        return $this->hasMany(CrashBet::class)
            ->where('status', 'lost');
    }

    // ===== ✅ FIXED: Accessors (Safe Property Access) =====

    public function getTotalBetAmountAttribute(): float
    {
        return (float) ($this->attributes['total_bet_pool'] ?? 0);
    }

    public function getTotalPayoutAttribute(): float
    {
        // ✅ CRITICAL FIX: Check if column exists in attributes
        if (isset($this->attributes['total_payout'])) {
            return (float) $this->attributes['total_payout'];
        }

        // Fallback: Calculate from won bets
        return (float) $this->wonBets()->sum('profit');
    }

    // ===== Status Checkers =====

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCrashed(): bool
    {
        return $this->status === 'crashed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPoolLocked(): bool
    {
        return ($this->attributes['pool_locked'] ?? false) === true;
    }

    // ===== Rollover Methods =====

    /**
     * পূর্ববর্তী গেম থেকে rollover পাওয়া
     */
    public static function getLastRolloverAmount(): float
    {
        $lastGame = self::where('status', 'crashed')
            ->latest()
            ->first();

        return $lastGame ? (float) ($lastGame->rollover_to_next ?? 0) : 0;
    }

    /**
     * মোট pool (current bets + rollover)
     */
    public function getTotalPoolWithRollover(): float
    {
        $currentBets = (float) ($this->attributes['current_round_bets'] ?? 0);
        $rollover = (float) ($this->attributes['previous_rollover'] ?? 0);
        return $currentBets + $rollover;
    }

    /**
     * Available pool (after commission)
     */
    public function getAvailablePool(): float
    {
        $totalPool = (float) ($this->attributes['total_bet_pool'] ?? 0);
        $commission = (float) ($this->attributes['admin_commission_amount'] ?? 0);
        return $totalPool - $commission;
    }

    /**
     * ✅ FIXED: Pool থেকে অবশিষ্ট amount (Safe calculation)
     */
    public function calculateRemainingPool(): float
    {
        $availablePool = $this->getAvailablePool();

        // Check if total_payout exists in attributes
        if (isset($this->attributes['total_payout'])) {
            $totalPaid = (float) $this->attributes['total_payout'];
        } else {
            // Calculate from database
            $totalPaid = (float) $this->wonBets()->sum('profit');
        }

        return max(0, $availablePool - $totalPaid);
    }

    /**
     * পরবর্তী রাউন্ডে rollover হবে কত
     */
    public function calculateRolloverAmount(): float
    {
        $settings = \App\Models\CrashGameSetting::first();

        if (!$settings || !$settings->enable_pool_rollover) {
            return 0;
        }

        $remaining = $this->calculateRemainingPool();

        // Commission include করবে কিনা
        if ($settings->rollover_includes_commission) {
            $commission = (float) ($this->attributes['admin_commission_amount'] ?? 0);
            $remaining += $commission;
        }

        // Percentage apply
        $rolloverAmount = $remaining * ($settings->rollover_percentage / 100);

        // Min/Max limits
        if ($rolloverAmount < $settings->min_rollover_amount) {
            return 0; // Too small, don't rollover
        }

        return min($rolloverAmount, $settings->max_rollover_amount);
    }

    /**
     * ✅ FIXED: Admin এর actual profit (Safe calculation)
     */
    public function getAdminProfit(): float
    {
        $totalCollected = (float) ($this->attributes['total_bet_pool'] ?? 0);

        // Check if total_payout exists
        if (isset($this->attributes['total_payout'])) {
            $totalPaid = (float) $this->attributes['total_payout'];
        } else {
            $totalPaid = (float) $this->wonBets()->sum('profit');
        }

        $rolledOver = (float) ($this->attributes['rollover_to_next'] ?? 0);

        return $totalCollected - $totalPaid - $rolledOver;
    }

    // ===== Scopes =====

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'running']);
    }

    public function scopeCrashed($query)
    {
        return $query->where('status', 'crashed');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ===== Statistics Methods =====

    /**
     * Pool efficiency percentage
     */
    public function getPoolEfficiency(): float
    {
        $totalPool = (float) ($this->attributes['total_bet_pool'] ?? 0);

        if ($totalPool <= 0) {
            return 0;
        }

        $totalPayout = $this->getTotalPayoutAttribute();
        return ($totalPayout / $totalPool) * 100;
    }

    /**
     * House profit percentage
     */
    public function getHouseProfitPercentage(): float
    {
        $totalPool = (float) ($this->attributes['total_bet_pool'] ?? 0);

        if ($totalPool <= 0) {
            return 0;
        }

        return ($this->getAdminProfit() / $totalPool) * 100;
    }

    /**
     * Average bet per player
     */
    public function getAverageBet(): float
    {
        $participants = (int) ($this->attributes['total_participants'] ?? 0);
        $currentBets = (float) ($this->attributes['current_round_bets'] ?? 0);

        if ($participants <= 0) {
            return 0;
        }

        return $currentBets / $participants;
    }

    /**
     * Cashout rate (% of players who cashed out)
     */
    public function getCashoutRate(): float
    {
        $totalParticipants = (int) ($this->attributes['total_participants'] ?? 0);

        if ($totalParticipants <= 0) {
            return 0;
        }

        $cashedOut = $this->wonBets()->count();
        return ($cashedOut / $totalParticipants) * 100;
    }

    /**
     * ✅ NEW: Safe attribute getter
     */
    private function safeGetAttribute(string $key, $default = 0)
    {
        return $this->attributes[$key] ?? $default;
    }
}

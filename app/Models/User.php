<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unique_id',
        'name',
        'email',
        'mobile',
        'password',
        'avatar',
        'role',
        'credit',
        'bonus_credit',
        'status',
        'is_online',
        'last_seen_at',
        'last_login_ip',
        'last_login_location',
        'latitude',
        'longitude',
        'referred_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
            'is_online' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];

    // public function getTotalBalanceAttribute(): float
    // {
    //     return $this->credit + $this->bonus_credit;
    // }

    public function getTotalBalanceAttribute(): float
    {
        return $this->available_balance + $this->bonus_credit;
    }

    public function addBonusCredit(float $amount, string $details = null): void
    {
        $this->increment('bonus_credit', $amount);

        Transaction::create([
            'user_id' => $this->id,
            'type' => 'credit',
            'amount' => $amount,
            'details' => $details
        ]);
    }


    public function deductBonusCredit(float $amount, string $details = null): void
    {
        $this->decrement('bonus_credit', $amount);

        Transaction::create([
            'user_id' => $this->id,
            'type' => 'debit',
            'amount' => $amount,
            'details' => $details
        ]);
    }

    public function spendBalance(float $amount, string $details = null): void
    {
        if ($this->total_balance < $amount) {
            throw new \Exception('Insufficient balance!');
        }

        // আগে bonus থেকে কাটবে
        $bonusUsed = min($amount, $this->bonus_credit);
        if ($bonusUsed > 0) {
            $this->deductBonusCredit($bonusUsed, $details ?? 'Bonus used for purchase');
            $amount -= $bonusUsed;
        }

        // বাকি মেইন ক্রেডিট থেকে কাটবে
        if ($amount > 0) {
            $this->deductCredit($amount, $details ?? 'Credit used for purchase');
        }
    }




    //---------------------------

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
    public function winnings()
    {
        return $this->hasMany(Winner::class);
    }

    //relations
    public function messageRelation()
    {
        return $this->hasMany(Message::class);
    }

    public function userRelation()
    {
        return $this->hasMany(Conversation::class);

    }

    /**
     * রেফারেল রেকর্ডগুলোর সাথে সম্পর্ক।
     */
    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * রেফারার ইউজারের সাথে সম্পর্ক।
     */
    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by', 'unique_id');
    }



    public function lotteryTickets(): HasMany
    {
        return $this->hasMany(LotteryTicket::class);
    }

    public function lotteryResults(): HasMany
    {
        return $this->hasMany(LotteryResult::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasEnoughCredit(float $amount): bool
    {
        return $this->credit >= $amount;
    }

    public function deductCredit(float $amount, string $details = null): void
    {
        $this->decrement('credit', $amount);

        Transaction::create([
            'user_id' => $this->id,
            'type' => 'debit',
            'amount' => $amount,
            'details' => $details
        ]);
    }

    public function addCredit(float $amount, string $details = null): void
    {
        $this->increment('credit', $amount);

        Transaction::create([
            'user_id' => $this->id,
            'type' => 'credit',
            'amount' => $amount,
            'details' => $details
        ]);
    }

    /**
     * Get all crash bets for this user
     */
    public function crashBets()
    {
        return $this->hasMany(\App\Models\CrashBet::class);
    }

    /**
     * Get active crash bets
     */
    public function activeCrashBets()
    {
        return $this->hasMany(\App\Models\CrashBet::class)
            ->whereIn('status', ['pending', 'playing']);
    }

    /**
     * Get total crash game winnings
     */
    public function getTotalCrashWinningsAttribute()
    {
        return $this->crashBets()
            ->where('status', 'won')
            ->sum('profit');
    }

    /**
     * Get total crash game losses
     */
    public function getTotalCrashLossesAttribute()
    {
        return abs($this->crashBets()
            ->where('status', 'lost')
            ->sum('profit'));
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function getAvailableBalanceAttribute()
    {
        $pending = $this->withdrawalRequests()
                        ->where('status', 'pending')
                        ->sum('amount') ?? 0;

        $available = $this->credit - $pending;

        return max($available, 0);
    }




}

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

}

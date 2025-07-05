<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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

    // ইউজারের সাথে ডাইরেক্ট কনভারসেশন খুঁজে বের করা
    // public function getConversationWith($userId)
    // {
    //     return $this->conversations()
    //         ->whereHas('users', function ($query) use ($userId) {
    //             $query->where('users.id', $userId);
    //         })
    //         ->where('is_group', false)
    //         ->first();
    // }

    // ইউজারের অপঠিত মেসেজ সংখ্যা
    // public function unreadMessagesCount()
    // {
    //     return Message::whereHas('conversation.users', function ($query) {
    //         $query->where('users.id', $this->id);
    //     })
    //     ->where('user_id', '!=', $this->id)
    //     ->where(function ($query) {
    //         $query->whereRaw('messages.created_at > (SELECT last_read_at FROM conversation_user WHERE conversation_id = messages.conversation_id AND user_id = ?)', [$this->id])
    //             ->orWhereNull('last_read_at');
    //     })
    //     ->count();
    // }

}

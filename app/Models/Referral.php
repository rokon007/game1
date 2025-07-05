<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;
    /**
     * পূরণযোগ্য ফিল্ডগুলো।
     */
    protected $fillable = ['referrer_id', 'referred_user_id', 'commission_count'];

    /**
     * রেফারার ইউজারের সাথে সম্পর্ক।
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * রেফার করা ইউজারের সাথে সম্পর্ক।
     */
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}

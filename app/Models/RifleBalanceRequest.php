<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RifleBalanceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'amount_rifle',
        'sending_mobile',
        'sending_method',
        'status',
        'transaction_id',
        'screenshot',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

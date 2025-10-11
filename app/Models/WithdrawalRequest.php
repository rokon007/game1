<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'status', 'method','account_number','user_notes',];

    // A withdrawal request belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditTransfer extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'receiver_id', 'amount'];

    // A credit transfer belongs to the sender (user)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // A credit transfer belongs to the receiver (user)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function unreadMessages()
    {
        return $this->hasMany(Message::class)->where('read', 0);
    }

    public function receiverInverseRelation()
    {
        return $this->belongsTo(User::class, 'receiver_id');

    }

    public function senderInverseRelation()
    {
        return $this->belongsTo(User::class, 'sender_id');

    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\MessageSent;
use App\Events\MessageRead;

class Message extends Model
{
    use HasFactory;
    protected $guarded = [];



    public function conversationInverseRelation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function userInverseRelation()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    protected static function booted()
    {
        // static::created(function ($message) {
        //     if ($message->conversation) {
        //         broadcast(new MessageSent($message->conversation->sender_id, [
        //             'conversation_id' => $message->conversation_id,
        //             'message_id' => $message->id
        //         ]));
        //     }
        // });

    //     static::updated(function ($message) {
    //         if ($message->isDirty('read') && $message->read) {
    //             broadcast(new MessageRead($message->conversation->sender_id, [
    //                 'conversation_id' => $message->conversation_id,
    //                 'message_id' => $message->id
    //             ]));
    //         }
    //     });
    // }

    static::updated(function (Message $message) {
        // Only proceed if read status changed to true
        if (!$message->isDirty('read') || !$message->read) {
            return;
        }

        // Safely get sender_id with null coalescing
        $senderId = optional($message->conversation)->sender_id;

        if ($senderId) {
            broadcast(new MessageRead($senderId, [
                'conversation_id' => $message->conversation_id,
                'message_id' => $message->id
            ]));
        }
    });
}



}

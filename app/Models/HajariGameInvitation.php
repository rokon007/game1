<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HajariGameInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'hajari_game_id',
        'inviter_id',
        'invitee_id',
        'status',
        'message',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function hajarigame()
    {
        return $this->belongsTo(HajariGame::class,'hajari_game_id');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}

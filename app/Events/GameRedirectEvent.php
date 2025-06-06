<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;

class GameRedirectEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $redirectData;

    public function __construct($gameId)
    {
        $this->gameId = $gameId;
        $this->prepareRedirectData();
    }

    protected function prepareRedirectData()
    {
        $this->redirectData = Ticket::where('game_id', $this->gameId)
            ->select([
                'user_id',
                DB::raw("SUBSTRING_INDEX(ticket_number, '-', 1) as sheet_id"),
                'ticket_number'
            ])
            ->get()
            ->mapWithKeys(function ($ticket) {
                return [
                    $ticket->user_id => [
                        'sheet_id' => $ticket->sheet_id,
                        'full_ticket' => $ticket->ticket_number
                    ]
                ];
            })
            ->toArray();

        Log::info('GameRedirectEvent prepared', [
            'game_id' => $this->gameId,
            'redirect_data' => $this->redirectData,
        ]);
    }

    public function broadcastAs()
    {
        return 'game.redirect';
    }

    public function broadcastOn()
    {
        return new Channel('game.redirect.' . $this->gameId);
    }

    public function broadcastWith()
    {
        return [
            'game_id' => $this->gameId,
            'redirect_data' => $this->redirectData,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    public function broadcastWhen()
    {
        return !empty($this->redirectData);
    }
}

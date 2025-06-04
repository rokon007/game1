<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;

class GameRedirectEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The game ID for which redirect is being initiated
     * @var int
     */
    public $gameId;

    /**
     * Array of user-specific redirect data
     * @var array
     */
    public $redirectData;

    /**
     * Create a new event instance.
     *
     * @param int $gameId
     */
    public function __construct($gameId)
    {
        $this->gameId = $gameId;
        $this->prepareRedirectData();
    }

    /**
     * Prepare redirect data by extracting sheet_id from ticket_number
     */
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
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'game.redirect';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('game.redirect.'.$this->gameId);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'game_id' => $this->gameId,
            'redirect_data' => $this->redirectData,
            'timestamp' => now()->toDateTimeString()
        ];
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return !empty($this->redirectData);
    }
}

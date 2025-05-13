<?php

namespace App\Livewire\Frontend;

use App\Models\Announcement;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;


class GameRoom extends Component
{
    public $games_Id;
    public $sheet_Id;
    public $announcedNumbers = [];
    public $userTickets = [];
    public $sheetTickets = [];

    protected $listeners = ['numberReceived' => 'onNumberReceived'];


    public function mount($gameId, $sheetId = null)
    {
        $this->sheet_Id = $sheetId;
        $this->games_Id = $gameId;
        $this->loadNumbers();

    }

    public function onNumberReceived($number)
    {
        $this->announcedNumbers[] = $number;
    }

    public function checkWinner()
    {
        foreach ($this->userTickets as $ticket) {
            $numbers = json_decode($ticket->numbers, true);
            $matched = 0;

            foreach ($numbers as $row) {
                foreach ($row as $num) {
                    if (in_array($num, $this->announcedNumbers)) {
                        $matched++;
                    }
                }
            }

            if ($matched === 15) {
                session()->flash('winner', 'Congratulations! You are a winner!');
                // You can broadcast the winner here
                break;
            }
        }
    }


    public function loadNumbers()
    {
        $this->announcedNumbers = Announcement::where('game_id', $this->games_Id)->pluck('number')->toArray();
        $this->sheetTickets = Ticket::where('user_id', Auth::id())
            ->where('ticket_number', 'LIKE', $this->sheet_Id . '-%')
            ->orderBy('ticket_number') // টিকেট নম্বর অনুসারে সাজানো
            ->get()
            ->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'number' => $ticket->ticket_number,
                    'numbers' => is_string($ticket->numbers)
                                ? json_decode($ticket->numbers, true)
                                : $ticket->numbers,
                    'is_winner' => $ticket->is_winner,
                    'created_at' => $ticket->created_at->format('d M Y h:i A'),
                    'game' => $ticket->game, // এখানে game রিলেশন অ্যাক্সেস করা হচ্ছে
                ];
            })
            ->toArray();
    }



    public function render()
    {
        return view('livewire.frontend.game-room')->layout('livewire.layout.frontend.base');
    }
}

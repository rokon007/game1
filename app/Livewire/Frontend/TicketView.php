<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketView extends Component
{
    public $sheets = [];
    public $loading = true;
    public $selectedSheet = null;
    public $sheetTickets = [];

    public function mount()
    {
        $this->loadUserSheets();
    }

    public function loadUserSheets()
    {
        $this->loading = true;

        $this->sheets = Ticket::with(['game' => function($query) {
                        $query->select('id', 'title', 'scheduled_at');
                    }])
                    ->where('user_id', Auth::id())
                    ->selectRaw("
                        SUBSTRING_INDEX(ticket_number, '-', 1) as sheet_id,
                        MIN(created_at) as created_at,
                        COUNT(*) as ticket_count,
                        MAX(game_id) as game_id
                    ")
                    ->groupBy('sheet_id')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'sheet_id' => $item->sheet_id,
                            'created_at' => $item->created_at,
                            'ticket_count' => $item->ticket_count,
                            'game' => $item->game ? [
                                'title' => $item->game->title,
                                'scheduled_at' => $item->game->scheduled_at
                            ] : null
                        ];
                    })
                    ->toArray();

        $this->loading = false;
    }

    public function showSheet($sheetId)
    {
        $this->selectedSheet = $sheetId;

        $this->sheetTickets = Ticket::where('user_id', Auth::id())
            ->where('ticket_number', 'LIKE', $sheetId . '-%')
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

    public function backToList()
    {
        $this->selectedSheet = null;
        $this->sheetTickets = [];
    }


    public function render()
    {
        return view('livewire.frontend.ticket-view')->layout('livewire.layout.frontend.base');
    }
}

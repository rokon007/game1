<?php

namespace App\Livewire\Frontend;

use App\Models\Transaction;
use App\Models\Game;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Component;

class BuyTicketSheet extends Component
{
    public $buyMode=true;
    public $sheetShowMode=false;
    public $selectedGameId, $blance;
    public $tickets;
    public $sheetUid;

    public function sheetShow($sheetUid)
    {
        $this->sheetUid = $sheetUid;
        $this->tickets = Ticket::where('user_id', Auth::id())
                            ->where('ticket_number', 'LIKE', $sheetUid . '-%')
                            ->get()
                            ->map(function ($ticket) {
                                if (is_string($ticket->numbers)) {
                                    $ticket->numbers = json_decode($ticket->numbers);
                                }
                                return $ticket;
                            });

        $this->sheetShowMode = true;
    }


    public function mount()
    {
        $this->getCredit();
    }

    public function getCredit()
    {
        $this->blance=auth()->user()->credit;
    }

    public function buySheet()
    {
        $user = Auth::user();

        $game = Game::where('id', $this->selectedGameId)
                    ->where('is_active', true)
                    ->first();

        if (!$game) {
            session()->flash('error', 'Selected game not found or inactive.');
            return;
        }

        if ($user->credit < $game->ticket_price) {
            session()->flash('error', 'Insufficient balance!');
            return;
        }

        // ক্রেডিট কর্তন
        $user->decrement('credit', $game->ticket_price);

        // ট্রান্সাকশন লগ
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => $game->ticket_price,
            'details' => 'Ticket Sheet purchase for game ID: ' . $game->id,
        ]);

        // ইউনিক শীট নাম্বার
        // $sheetUid = 'SHEET-' . strtoupper(Str::random(8));
        $sheetUid = strtoupper(Str::random(8));

        // Reset static variables before generating tickets
        $this->resetTicketGenerator();

        // ৬টি টিকিট তৈরি
        for ($i = 0; $i < 6; $i++) {
            Ticket::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'ticket_number' => $sheetUid . '-' . ($i + 1),
                'numbers' => json_encode($this->generateHousieTicket()),
            ]);
        }

        session()->flash('success', 'Ticket Sheet Purchased Successfully!');
        $this->sheetShow($sheetUid);
        $this->selectedGameId = null;
        $this->getCredit();
    }

    private function resetTicketGenerator()
{
    // This will reset the static variables when starting a new sheet
    $this->generateHousieTicket();
}

    private function generateHousieTicket()
{
    static $usedNumbers = []; // Track used numbers across all tickets in this sheet
    static $initialized = false;

    // Reset for each new sheet
    if (!$initialized) {
        $usedNumbers = [];
        $initialized = true;
    }

    $ticket = array_fill(0, 3, array_fill(0, 9, null));
    $availableNumbers = array_diff(range(1, 90), $usedNumbers);

    // If not enough numbers left, reset (shouldn't happen as we're making exactly 6 tickets)
    if (count($availableNumbers) < 15) {
        $usedNumbers = [];
        $availableNumbers = range(1, 90);
    }

    // Group available numbers by column
    $columnNumbers = array_fill(0, 9, []);
    foreach ($availableNumbers as $number) {
        $col = min(8, floor(($number - 1) / 10));
        $columnNumbers[$col][] = $number;
    }

    // 1. Ensure each column has at least one number
    for ($col = 0; $col < 9; $col++) {
        if (empty($columnNumbers[$col])) continue;

        $row = rand(0, 2);
        $number = array_pop($columnNumbers[$col]);
        $ticket[$row][$col] = $number;
        $usedNumbers[] = $number;
    }

    // 2. Fill remaining numbers (total 15 per ticket)
    $numbersToAdd = 6; // Already placed 9 numbers (one per column)
    $attempts = 0;
    $maxAttempts = 100;

    while ($numbersToAdd > 0 && $attempts < $maxAttempts) {
        $attempts++;
        $col = rand(0, 8);

        if (!empty($columnNumbers[$col])) {
            $number = array_pop($columnNumbers[$col]);

            // Find suitable row
            $availableRows = array_filter([0, 1, 2], function($row) use ($ticket, $col) {
                return is_null($ticket[$row][$col]) &&
                       count(array_filter($ticket[$row])) < 5;
            });

            if (!empty($availableRows)) {
                $row = $availableRows[array_rand($availableRows)];
                $ticket[$row][$col] = $number;
                $usedNumbers[] = $number;
                $numbersToAdd--;
            }
        }
    }

    // Sort columns
    foreach ($ticket as &$row) {
        ksort($row);
    }

    return $ticket;
}

    public function render()
    {
        $availableGames = Game::where('scheduled_at', '>=', now()) // এখনো শুরু হয়নি এমন গেম
            ->where('is_active', true)
            ->orderBy('scheduled_at')
            ->get();

        return view('livewire.frontend.buy-ticket-sheet', [
            'games' => $availableGames,
        ])->layout('livewire.layout.frontend.base');
    }

}

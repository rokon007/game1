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

        // ৬টি টিকিট তৈরি (প্রত্যেকটির একটি আলাদা ticket_number হবে)
        for ($i = 0; $i < 6; $i++) {
            Ticket::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'ticket_number' => $sheetUid . '-' . ($i + 1),
                'numbers' => json_encode($this->generateHousieTicket()), // ✅ এটা যুক্ত করুন
            ]);

        }

        session()->flash('success', 'Ticket Sheet Purchased Successfully!');
        $this->sheetShow($sheetUid);
        $this->selectedGameId = null;
        $this->getCredit();
    }

    private function generateHousieTicket()
{
    $ticket = array_fill(0, 3, array_fill(0, 9, null));
    $usedNumbers = [];

    // 1. Generate all possible numbers grouped by columns
    $columns = [];
    for ($i = 0; $i < 9; $i++) {
        $start = $i * 10 + 1;
        $end = ($i == 8) ? 90 : ($i + 1) * 10;
        $columns[$i] = range($start, $end);
        shuffle($columns[$i]);
    }

    // 2. First pass: ensure at least one number per column
    for ($col = 0; $col < 9; $col++) {
        $row = rand(0, 2);
        $number = array_pop($columns[$col]);
        $ticket[$row][$col] = $number;
        $usedNumbers[] = $number;
    }

    // 3. Second pass: fill remaining numbers (total 15 per ticket)
    $numbersToAdd = 6; // 15 total - 9 already placed
    $attempts = 0;

    while ($numbersToAdd > 0 && $attempts < 50) {
        $attempts++;
        $col = rand(0, 8);

        if (!empty($columns[$col])) {
            $number = array_pop($columns[$col]);

            // Find suitable row
            $availableRows = [];
            for ($row = 0; $row < 3; $row++) {
                if (is_null($ticket[$row][$col]) &&
                    count(array_filter($ticket[$row])) < 5) {
                    $availableRows[] = $row;
                }
            }

            if (!empty($availableRows)) {
                $row = $availableRows[array_rand($availableRows)];
                $ticket[$row][$col] = $number;
                $usedNumbers[] = $number;
                $numbersToAdd--;
            }
        }
    }

    // 4. Sort numbers in each row
    foreach ($ticket as &$row) {
        ksort($row);
    }

    return $ticket;
}




    // private function generateHousieTicket()
    // {
    //     $ticket = array_fill(0, 3, array_fill(0, 9, null));

    //     // Step 1: প্রতিটি কলামের জন্য সংখ্যা রেঞ্জ নির্ধারণ
    //     $columns = [];
    //     for ($i = 0; $i < 9; $i++) {
    //         $start = $i * 10 + 1;
    //         $end = ($i == 0) ? 9 : (($i == 8) ? 90 : $i * 10 + 10 - 1);
    //         $range = range($start, $end);
    //         shuffle($range);
    //         $columns[$i] = array_slice($range, 0, 3); // প্রতিটি কলাম থেকে ৩টি সংখ্যা নিব
    //     }

    //     // Step 2: এখন 15টি সংখ্যা বেছে নেওয়া হবে (3x5)
    //     $filled = 0;
    //     while ($filled < 15) {
    //         for ($row = 0; $row < 3; $row++) {
    //             $filledInRow = array_filter($ticket[$row], fn($v) => !is_null($v));
    //             if (count($filledInRow) >= 5) continue;

    //             $col = rand(0, 8);
    //             if (is_null($ticket[$row][$col]) && !empty($columns[$col])) {
    //                 $ticket[$row][$col] = array_pop($columns[$col]);
    //                 $filled++;
    //                 if ($filled >= 15) break;
    //             }
    //         }
    //     }

    //     // Step 3: কলাম অনুযায়ী sort করে দিন
    //     for ($row = 0; $row < 3; $row++) {
    //         ksort($ticket[$row]);
    //     }

    //     return $ticket;
    // }



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

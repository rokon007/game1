<?php

namespace App\Livewire\Frontend;

use App\Models\Transaction;
use App\Models\Game;
use App\Models\User;
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
     public $agreements = [];

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

    public function validateAgreement($gameId)
    {
        // ভ্যালিডেশন চেক
        if (empty($this->agreements[$gameId])) {
            $this->addError('agreements.'.$gameId, 'You must agree to the terms and conditions');
            return;
        }

        // যদি চেকবক্স টিক করা থাকে তাহলে টিকেট কিনুন
        $this->buySheet();
    }

    // public function buySheet()
    // {
    //     $user = Auth::user();
    //     $systemUser = User::where('role','admin')->first();
    //     $game = Game::where('id', $this->selectedGameId)
    //                 ->where('is_active', true)
    //                 ->first();

    //     if (!$game) {
    //         session()->flash('error', 'Selected game not found or inactive.');
    //         return;
    //     }

    //     // চেকবক্স ভ্যালিডেশন (অতিরিক্ত সুরক্ষা)
    //     if (empty($this->agreements[$this->selectedGameId])) {
    //         $this->addError('agreements.'.$this->selectedGameId, 'You must agree to the terms and conditions');
    //         return;
    //     }

    //     if ($user->credit < $game->ticket_price) {
    //         session()->flash('error', 'Insufficient balance!');
    //         return;
    //     }

    //     // ✅ Check if user already bought a ticket for this game
    //     $alreadyBought = Ticket::where('user_id', $user->id)
    //                             ->where('game_id', $game->id)
    //                             ->exists();

    //     if ($alreadyBought) {
    //         session()->flash('error', 'You have already purchased a ticket sheet for this game.');
    //         return;
    //     }

    //     // ক্রেডিট কর্তন
    //     $user->decrement('credit', $game->ticket_price);
    //     $systemUser->increment('credit', $game->ticket_price);

    //     // ট্রান্সাকশন লগ
    //     Transaction::create([
    //         'user_id' => $user->id,
    //         'type' => 'debit',
    //         'amount' => $game->ticket_price,
    //         'details' => 'Ticket Sheet purchase for game ID: ' . $game->id,
    //     ]);

    //     Transaction::create([
    //         'user_id' => $systemUser->id,
    //         'type' => 'credit',
    //         'amount' => $game->ticket_price,
    //         'details' => 'Ticket Sheet purchase for game ID: ' . $game->id . ' by ' . $user->name,
    //     ]);

    //     // ইউনিক শীট নাম্বার
    //     // $sheetUid = 'SHEET-' . strtoupper(Str::random(8));
    //     $sheetUid = strtoupper(Str::random(8));

    //     // Reset static variables before generating tickets
    //     $this->resetTicketGenerator();

    //     // ৬টি টিকিট তৈরি
    //     for ($i = 0; $i < 6; $i++) {
    //         Ticket::create([
    //             'user_id' => $user->id,
    //             'game_id' => $game->id,
    //             'ticket_number' => $sheetUid . '-' . ($i + 1),
    //             'numbers' => json_encode($this->generateHousieTicket()),
    //         ]);
    //     }

    //     session()->flash('success', 'Ticket Sheet Purchased Successfully!');
    //     $this->sheetShow($sheetUid);
    //     $this->selectedGameId = null;
    //     $this->getCredit();
    // }

    public function buySheet()
    {
        $user = Auth::user();
        $systemUser = User::where('role','admin')->first();
        $game = Game::where('id', $this->selectedGameId)
                    ->where('is_active', true)
                    ->first();

        if (!$game) {
            session()->flash('error', 'Selected game not found or inactive.');
            return;
        }

        // চেকবক্স ভ্যালিডেশন (অতিরিক্ত সুরক্ষা)
        if (empty($this->agreements[$this->selectedGameId])) {
            $this->addError('agreements.'.$this->selectedGameId, 'You must agree to the terms and conditions');
            return;
        }

        // ✅ Check if user already bought a ticket for this game
        $alreadyBought = Ticket::where('user_id', $user->id)
                                ->where('game_id', $game->id)
                                ->exists();

        if ($alreadyBought) {
            session()->flash('error', 'You have already purchased a ticket sheet for this game.');
            return;
        }

        try {
            // ইউজারের ব্যালেন্স থেকে কাটবে (bonus → তারপর credit)
            $user->spendBalance($game->ticket_price, 'Ticket Sheet purchase for game ID: ' . $game->id);

            // অ্যাডমিনকে ক্রেডিট যোগ করা
            $systemUser->increment('credit', $game->ticket_price);

            // ট্রান্সাকশন লগ
            Transaction::create([
                'user_id' => $systemUser->id,
                'type' => 'credit',
                'amount' => $game->ticket_price,
                'details' => 'Ticket Sheet purchase for game ID: ' . $game->id . ' by ' . $user->name,
            ]);

            // ইউনিক শীট নাম্বার
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
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }



    private function resetTicketGenerator()
    {
        static $usedNumbers = [];
        static $initialized = false;

        $usedNumbers = [];
        $initialized = false;
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

        // Create empty ticket (3 rows x 9 columns)
        $ticket = array_fill(0, 3, array_fill(0, 9, null));

        // Define which columns will have numbers in each row
        // Each row must have exactly 5 numbers
        $columnDistribution = [
            [1, 1, 1, 1, 1, 0, 0, 0, 0], // Row 1 columns with numbers
            [1, 1, 1, 1, 1, 0, 0, 0, 0], // Row 2 columns with numbers
            [1, 1, 1, 1, 1, 0, 0, 0, 0]  // Row 3 columns with numbers
        ];

        // Shuffle each row's distribution to randomize which columns have numbers
        for ($row = 0; $row < 3; $row++) {
            shuffle($columnDistribution[$row]);
        }

        // Fill the ticket according to the distribution
        for ($col = 0; $col < 9; $col++) {
            // Determine range of numbers for this column
            $min = $col * 10 + 1;
            $max = ($col == 8) ? 90 : ($col + 1) * 10;

            // Get available numbers for this column (not used in previous tickets)
            $availableNumbers = array_diff(range($min, $max), $usedNumbers);

            // Count how many numbers we need in this column
            $numbersNeeded = array_sum(array_column($columnDistribution, $col));

            // If we need numbers in this column
            if ($numbersNeeded > 0) {
                // Shuffle available numbers
                shuffle($availableNumbers);

                // Assign numbers to rows that need them in this column
                $numberIndex = 0;
                for ($row = 0; $row < 3; $row++) {
                    if ($columnDistribution[$row][$col] == 1) {
                        if (isset($availableNumbers[$numberIndex])) {
                            $ticket[$row][$col] = $availableNumbers[$numberIndex];
                            $usedNumbers[] = $availableNumbers[$numberIndex];
                            $numberIndex++;
                        } else {
                            // Handle case where we run out of available numbers
                            // Find a number from another column that hasn't been used yet
                            for ($altCol = 0; $altCol < 9; $altCol++) {
                                if ($altCol == $col) continue;

                                $altMin = $altCol * 10 + 1;
                                $altMax = ($altCol == 8) ? 90 : ($altCol + 1) * 10;
                                $altAvailable = array_diff(range($altMin, $altMax), $usedNumbers);

                                if (!empty($altAvailable)) {
                                    $number = reset($altAvailable);
                                    $ticket[$row][$col] = $number;
                                    $usedNumbers[] = $number;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Verify each row has exactly 5 numbers
        for ($row = 0; $row < 3; $row++) {
            $filledCount = count(array_filter($ticket[$row], function($cell) {
                return $cell !== null;
            }));

            // If we don't have exactly 5 numbers in this row, adjust
            if ($filledCount != 5) {
                if ($filledCount < 5) {
                    // Add more numbers if less than 5
                    $emptyCols = array_keys(array_filter($ticket[$row], function($cell) {
                        return $cell === null;
                    }));

                    shuffle($emptyCols);
                    $toFill = 5 - $filledCount;

                    for ($i = 0; $i < $toFill && $i < count($emptyCols); $i++) {
                        $col = $emptyCols[$i];
                        $min = $col * 10 + 1;
                        $max = ($col == 8) ? 90 : ($col + 1) * 10;
                        $available = array_diff(range($min, $max), $usedNumbers);

                        if (!empty($available)) {
                            $number = reset($available);
                            $ticket[$row][$col] = $number;
                            $usedNumbers[] = $number;
                        }
                    }
                } else {
                    // Remove numbers if more than 5
                    $filledCols = array_keys(array_filter($ticket[$row], function($cell) {
                        return $cell !== null;
                    }));

                    shuffle($filledCols);
                    $toRemove = $filledCount - 5;

                    for ($i = 0; $i < $toRemove && $i < count($filledCols); $i++) {
                        $ticket[$row][$filledCols[$i]] = null;
                    }
                }
            }
        }

        // Sort numbers within each column
        for ($col = 0; $col < 9; $col++) {
            $colNumbers = [];
            for ($row = 0; $row < 3; $row++) {
                if ($ticket[$row][$col] !== null) {
                    $colNumbers[] = $ticket[$row][$col];
                }
            }

            if (!empty($colNumbers)) {
                sort($colNumbers);
                $index = 0;
                for ($row = 0; $row < 3; $row++) {
                    if ($ticket[$row][$col] !== null) {
                        $ticket[$row][$col] = $colNumbers[$index++];
                    }
                }
            }
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

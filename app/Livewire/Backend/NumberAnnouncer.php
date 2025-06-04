<?php

namespace App\Livewire\Backend;

use App\Models\Game;
use App\Models\Ticket;
use App\Models\Prize;
use App\Models\Winner;
use App\Models\Announcement;
use App\Events\NumberAnnounced;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Events\GameRedirectEvent;

class NumberAnnouncer extends Component
{
    public $gameId;
    public $calledNumbers = [];
    public $nextNumber;
    public $selectedNumber;
    public $game;

    // Statistics
    public $totalParticipants;
    public $totalSheetsSold;
    public $totalSalesAmount;
    public $totalPrizeAmount;
    public $participants;
    public $winners;
    public $sheetsId;

    public $showNumberModal = false;
    public $currentAnnouncedNumber = null;
    public $gameOver=false;

    public $textNote;
    public $gameOverAllart=false;
    public $winnerAllart=false;
    public $redirectUrl;

     protected $listeners = [
        'echo:game.*,game.winner' => 'handleWinnerAnnounced',
        'echo:game.*,game.over' => 'handleGameOver',
        'updateProgress' => 'updateTransferProgress',
        'transfer-completed' => 'onTransferCompleted'
    ];

    public function redirectAllPlayers()
    {
        $tickets = Ticket::where('game_id', $this->gameId)
                    ->selectRaw("user_id, SUBSTRING_INDEX(ticket_number, '-', 1) as sheet_id")
                    ->pluck('sheet_id', 'user_id')
                    ->toArray();

        event(new GameRedirectEvent($this->gameId, $tickets));

        //return back()->with('success', 'সকল প্লেয়ারকে রিডাইরেক্ট করা হচ্ছে');
    }

    public function handleWinnerAnnounced($payload = null)
    {
        // ডিবাগ লগ যোগ করুন
        Log::info('handleWinnerAnnounced called', [
            'payload' => $payload,
            'game_id' => $this->games_Id,
            'method' => 'handleWinnerAnnounced'
        ]);

        // গেমের সকল উইনার লোড করুন
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        Log::info('Winners loaded', ['winners_count' => $this->winners->count()]);

        $this->winnerAllart = true;

        // UI আপডেট করুন
        $this->dispatch('winnerAnnounced', ['winners' => $this->winners]);
        $this->dispatch('winnerAllartMakeFalse');
    }

    public function handleGameOver($data)
    {
         Log::info('Winner announced event received', ['payload' => $data]);
        // গেমের সকল উইনার লোড করুন
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        $this->dispatch('openGameoverModal');
    }

    public function updateTransferProgress($progress)
    {
        $this->dispatch('progressUpdated', progress: $progress);
    }

    public function onTransferCompleted()
    {
        $this->dispatch('transfer-completed');
    }

    public function manageNotification()
    {
        $this->dispatch('notificationText', text: $this->textNote);
        $this->dispatch('notificationRefresh');
    }

    public function oprenGameoverModalAfterdelay()
    {
        $this->gameOverAllart=true;
        $this->gameOver=true;
    }

    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->game = Game::findOrFail($gameId);
        $this->calledNumbers = Announcement::where('game_id', $gameId)->pluck('number')->toArray();
        $this->loadStatistics();
        $this->checkGameOver();
    }

    private function checkGameOver()
    {
        // Count how many patterns have been claimed in this game
        $claimedPatternsCount = Winner::where('game_id', $this->gameId)
            ->distinct('pattern')
            ->count('pattern');

        // If all 5 patterns are claimed, the game is over
        $this->gameOver = ($claimedPatternsCount >= 5);

        return $this->gameOver;
    }

    protected function loadStatistics()
    {
        // Participants count (unique users who bought tickets)
        $this->totalParticipants = Ticket::where('game_id', $this->gameId)
                                    ->distinct('user_id')
                                    ->count('user_id');

        $this->sheetsId = Ticket::where('game_id', $this->gameId)
                            ->selectRaw("SUBSTRING_INDEX(ticket_number, '-', 1) as sheet_id")
                            ->groupBy('sheet_id')
                            ->pluck('sheet_id');

        // Tickets sold
        // $this->totalTicketsSold = Ticket::where('game_id', $this->gameId)->count();
        $this->totalSheetsSold = Ticket::where('game_id', $this->gameId)
            ->selectRaw("
                SUBSTRING_INDEX(ticket_number, '-', 1) as sheet_id
            ")
            ->groupBy('sheet_id')
            ->get()
            ->count();

        // Total sales amount
        $this->totalSalesAmount = $this->totalSheetsSold * $this->game->ticket_price;

        // Calculate total prize amount from active prizes
       $game = Game::find($this->gameId);

        if ($game) {
            $this->totalPrizeAmount =
                ($game->corner_prize ?? 0) +
                ($game->top_line_prize ?? 0) +
                ($game->middle_line_prize ?? 0) +
                ($game->bottom_line_prize ?? 0) +
                ($game->full_house_prize ?? 0);
        }else{
            $this->totalPrizeAmount =0;
        }

        // Load participants with their tickets
        $this->participants = Ticket::with(['user', 'game'])
                                ->where('game_id', $this->gameId)
                                ->select('user_id', 'game_id')
                                ->selectRaw('count(*) as ticket_count')
                                ->groupBy('user_id', 'game_id')
                                ->get();

        // Load winners with their patterns
        $this->winners = Winner::with(['user', 'ticket'])
                          ->where('game_id', $this->gameId)
                          ->orderByDesc('won_at')
                          ->get();
    }

    public function announceNumber()
    {
        $number = $this->selectedNumber;
        $this->checkGameOver();

        if($this->gameOver){
            session()->flash('error', 'Gamr Over.');
            return;
        }

        if (!$number) {
            session()->flash('error', 'Please select a number first.');
            return;
        }

        // Check if number already announced
        if (in_array($number, $this->calledNumbers)) {
            session()->flash('error', 'This number has already been announced.');
            return;
        }

        try {
            // Create announcement
            Announcement::create([
                'game_id' => $this->gameId,
                'number' => $number,
            ]);



            $this->currentAnnouncedNumber = $number;
            $this->showNumberModal = true;

            // Broadcast event
            broadcast(new NumberAnnounced($this->gameId, $number))->toOthers();
            // $this->dispatch('numberAnnounced', number: $number);

            // Close modal after 9 seconds (6 for spin + 3 for display)
            $this->dispatch('closeNumberModalAfterDelay');

            // Add to called numbers
            $this->calledNumbers[] = $number;
            $this->selectedNumber = null;

            // Check for winners after each announcement
            $this->checkWinningPatterns();

            session()->flash('success', "Number $number announced successfully!");
        } catch (\Exception $e) {
            Log::error("Error announcing number: " . $e->getMessage());
            // session()->flash('error', "Error announcing number: " . $e->getMessage());
        }
    }


    // Add this method to your NumberAnnouncer.php file
    // public function announceNumber()
    // {
    //     $number = $this->selectedNumber;

    //     if (!$number) {
    //         session()->flash('error', 'Please select a number first.');
    //         return;
    //     }

    //     // Check if number already announced
    //     if (in_array($number, $this->calledNumbers)) {
    //         session()->flash('error', 'This number has already been announced.');
    //         return;
    //     }

    //     try {
    //         // Create announcement
    //         Announcement::create([
    //             'game_id' => $this->gameId,
    //             'number' => $number,
    //         ]);

    //         // Add to called numbers
    //         $this->calledNumbers[] = $number;
    //         $this->selectedNumber = null;

    //         // Broadcast event
    //         broadcast(new NumberAnnounced($this->gameId, $number))->toOthers();

    //         // Dispatch events for UI updates - this will trigger the modal
    //         $this->dispatch('numberAnnounced', number: $number);

    //         // Check for winners after each announcement
    //         $this->checkWinningPatterns();

    //         session()->flash('success', "Number $number announced successfully!");
    //     } catch (\Exception $e) {
    //         Log::error("Error announcing number: " . $e->getMessage());
    //     }
    // }



    protected function checkWinningPatterns()
    {
        $tickets = Ticket::with('user')
                    ->where('game_id', $this->gameId)
                    ->whereDoesntHave('winnings', function($q) {
                        $q->where('game_id', $this->gameId);
                    })
                    ->get();

        foreach ($tickets as $ticket) {
            $ticketNumbers = json_decode($ticket->numbers, true);
            $matchedNumbers = array_intersect($ticketNumbers, $this->calledNumbers);

            // Check for each pattern
            $patterns = [
                'corner' => $this->checkCorners($ticketNumbers, $matchedNumbers),
                'top_line' => $this->checkTopLine($ticketNumbers, $matchedNumbers),
                'middle_line' => $this->checkMiddleLine($ticketNumbers, $matchedNumbers),
                'bottom_line' => $this->checkBottomLine($ticketNumbers, $matchedNumbers),
                'full_house' => $this->checkFullHouse($ticketNumbers, $matchedNumbers)
            ];

            foreach ($patterns as $pattern => $isWinner) {
                if ($isWinner) {
                    Winner::create([
                        'user_id' => $ticket->user_id,
                        'game_id' => $this->gameId,
                        'ticket_id' => $ticket->id,
                        'pattern' => $pattern,
                        'won_at' => now()
                    ]);
                }
            }
        }
    }

    protected function checkCorners($ticketNumbers, $matchedNumbers)
    {
        // Assuming ticket numbers are arranged in a 3x9 grid (27 numbers)
        $corners = [
            $ticketNumbers[0],    // First number (top-left)
            $ticketNumbers[8],    // Last number of first row (top-right)
            $ticketNumbers[18],   // First number of last row (bottom-left)
            $ticketNumbers[26]    // Last number (bottom-right)
        ];

        return count(array_intersect($corners, $matchedNumbers)) === 4;
    }

    protected function checkTopLine($ticketNumbers, $matchedNumbers)
    {
        // First 9 numbers (assuming first row)
        $topLine = array_slice($ticketNumbers, 0, 9);
        return count(array_intersect($topLine, $matchedNumbers)) === count($topLine);
    }

    protected function checkMiddleLine($ticketNumbers, $matchedNumbers)
    {
        // Middle 9 numbers (assuming second row)
        $middleLine = array_slice($ticketNumbers, 9, 9);
        return count(array_intersect($middleLine, $matchedNumbers)) === count($middleLine);
    }

    protected function checkBottomLine($ticketNumbers, $matchedNumbers)
    {
        // Last 9 numbers (assuming third row)
        $bottomLine = array_slice($ticketNumbers, 18, 9);
        return count(array_intersect($bottomLine, $matchedNumbers)) === count($bottomLine);
    }

    protected function checkFullHouse($ticketNumbers, $matchedNumbers)
    {
        return count($matchedNumbers) === count($ticketNumbers);
    }

    public function callNextNumber()
    {
        $available = collect(range(1, 90))->diff($this->calledNumbers)->values();

        if($this->gameOver){
            session()->flash('error', 'Gamr Over.');
            return;
        }

        if ($available->isEmpty()) {
            session()->flash('error', 'All numbers have been announced.');
            return;
        }

        $this->selectedNumber = $available->random();
        $this->announceNumber();
    }

    protected function getPatternColor($pattern)
    {
        $colors = [
            'corner' => 'info',
            'top_line' => 'primary',
            'middle_line' => 'success',
            'bottom_line' => 'warning',
            'full_house' => 'danger'
        ];

        return $colors[$pattern] ?? 'secondary';
    }

    public function render()
    {
        return view('livewire.backend.number-announcer')->layout('livewire.backend.base');
    }
}

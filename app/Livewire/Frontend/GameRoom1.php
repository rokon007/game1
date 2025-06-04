<?php

namespace App\Livewire\Frontend;

use App\Models\Announcement;
use App\Models\Ticket;
use App\Models\Winner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Game;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreditTransferred;
use App\Events\NotificationRefresh;
use App\Events\WinnerAnnouncedEvent;
use App\Events\GameOverEvent;
use Livewire\Component;

class GameRoom extends Component
{
    public $games_Id;
    public $sheet_Id;
    public $announcedNumbers = [];
    public $userTickets = [];
    public $sheetTickets = [];
    public $winningPatterns = [];
    public $gameOver = false;
    public $textNote;
    public $gameOverAllart=false;
    public $winners;
    public $winnerAllart=false;

    protected $listeners = [
        'echo:game.*,number.announced' => 'handleNumberAnnounced',
        'echo:game.*,game.winner' => 'handleWinnerAnnounced',
        'echo:game.*,game.over' => 'handleGameOver',
        'numberAnnounced' => 'onNumberReceived',
        'updateProgress' => 'updateTransferProgress',
        'transfer-completed' => 'onTransferCompleted'
    ];

    public function onTransferCompleted()
    {
        $this->dispatchBrowserEvent('transfer-completed');
    }

    public function updateTransferProgress($progress)
    {
        //dd('updateProgress');
        $this->dispatch('progressUpdated', progress: $progress);
    }

    public function mount($gameId, $sheetId = null)
    {
        $this->sheet_Id = $sheetId;
        $this->games_Id = $gameId;
        $this->loadNumbers();
        $this->initWinningPatterns();
        $this->checkGameOver();
    }

    public function handleWinnerAnnounced($payload = null)
    {
        Log::info('Winner announced event received', ['payload' => $payload]);
        // গেমের সকল উইনার লোড করুন
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        // ফ্রন্টএন্ডে ইভেন্ট পাঠান
        // $this->dispatch('showWinnerModal', [
        //     'winners' => $this->winners,
        //     'title' => 'নতুন উইনার ঘোষণা!'
        // ]);
        $this->winnerAllart=true;
    }

    public function pushEvent()
    {
        try {
            broadcast(new WinnerAnnouncedEvent($this->games_Id))->toOthers();
        } catch (\Exception $e) {
            Log::error("WinnerBroadcasting failed: ".$e->getMessage());
        }
    }

    public function winnerSelfAnnounced()
    {
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();
        $this->winnerAllart=true;

        // ফ্রন্টএন্ডে ইভেন্ট পাঠান
        // $this->dispatch('showWinnerModal', [
        //     'winners' => $this->winners,
        //     'title' => 'You উইনার ঘোষণা!'
        // ]);
    }

    public function handleGameOver($data)
    {
         Log::info('Winner announced event received', ['payload' => $data]);
        // গেমের সকল উইনার লোড করুন
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        // ফ্রন্টএন্ডে ইভেন্ট পাঠান
        // $this->dispatch('showGameOverModal', [
        //     'winners' => $this->winners,
        //     'message' => 'গেম শেষ! সকল প্যাটার্ন ক্লেইম করা হয়েছে।',
        //     'title' => 'গেম সমাপ্ত'
        // ]);
        $this->gameOverAllart=true;
    }

    public function gameOverSelfAnnounced()
    {
        // গেমের সকল উইনার লোড করুন
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        // ফ্রন্টএন্ডে ইভেন্ট পাঠান
        $this->gameOverAllart=true;
    }
//--------------------------------------------------------------
    // public function handleGameOver($data)
    // {
    //     if ($this->checkGameOver()) {
    //         $this->gameOver = true;
    //         $this->dispatch('gameOver', [
    //             'message' => 'Game Over! All patterns have been claimed.',
    //             'score' => 1250,
    //             'level' => $this->games_Id,
    //             'titel'=>'Game Over'
    //         ]);
    //     }

    // }

    // public function handleWinnerAnnounced($data)
    // {

    //         $this->dispatch('gameOver', [
    //         'message' => 'New Winner have been claimed.',
    //         'score' => 1250,
    //         'level' => 5,
    //         'title'=>'New Winner'
    //     ]);

    // }

//--------------------------------------------------------------
//----------------------------------------------
    // public function updateWinnersList($winners)
    // {
    //     $this->winners = $winners;
    // }
    // public function handleWinnerAnnounced($payload)
    // {
    //     // শুধুমাত্র উইনার ইউজারকে শুভেচ্ছা জানান
    //     if ($payload['winnerId'] == auth()->id()) {
    //         $this->dispatch('showWinnerNotification', [
    //             'title' => 'Congratulations!',
    //             'message' => "You won {$payload['pattern']} and earned {$payload['prizeAmount']} credits!",
    //             'pattern' => $payload['pattern']
    //         ]);
    //     }

    //     // সকল ইউজারকে উইনার ইনফো আপডেট করতে বলুন
    //     $this->loadWinners();
    // }

    // private function loadWinners()
    // {
    //     $this->winners = Winner::where('game_id', $this->games_Id)
    //         ->with('user') // User relation লোড করুন
    //         ->get()
    //         ->groupBy('user_id') // ইউজার আইডি অনুযায়ী গ্রুপ করুন
    //         ->map(function($wins) {
    //             return [
    //                 'user' => $wins->first()->user,
    //                 'patterns' => $wins->pluck('pattern'),
    //                 'total_prize' => $wins->sum('prize_amount')
    //             ];
    //         })
    //         ->values()
    //         ->toArray();

    //     // UI আপডেট করার জন্য ইভেন্ট ডিসপ্যাচ করুন
    //     $this->dispatch('winnersUpdated', winners: $this->winners);
    // }

    // public function handleGameFinished($payload)
    // {
    //     $this->gameOver = true;
    //     $this->winners = $payload['winners'];

    //     $this->dispatch('showGameOver', [
    //         'message' => 'Game Over! All patterns have been claimed.',
    //         'winners' => $this->winners
    //     ]);
    // }

    // private function checkGameOver()
    // {
    //     $claimedPatternsCount = Winner::where('game_id', $this->games_Id)
    //         ->distinct('pattern')
    //         ->count('pattern');

    //     $this->gameOver = ($claimedPatternsCount >= 5);

    //     if ($this->gameOver) {
    //         $winners = Winner::where('game_id', $this->games_Id)
    //             ->with('user')
    //             ->get()
    //             ->groupBy('user_id')
    //             ->map(function($wins) {
    //                 return [
    //                     'user' => $wins->first()->user,
    //                     'patterns' => $wins->pluck('pattern'),
    //                     'total_prize' => $wins->sum('prize_amount')
    //                 ];
    //             })
    //             ->values()
    //             ->toArray();

    //         event(new GameFinishedEvent($this->games_Id, $winners));
    //     }

    //     return $this->gameOver;
    // }

    // private function updateTicketWinningStatus($ticketId, $winningPatterns)
    // {
    //     $ticket = Ticket::find($ticketId);
    //     $game = Game::find($this->games_Id);

    //     if ($ticket && $game) {
    //         try {
    //             DB::transaction(function () use ($ticket, $winningPatterns, $game) {
    //                 $ticket->is_winner = true;
    //                 if (Schema::hasColumn('tickets', 'winning_patterns')) {
    //                     $ticket->winning_patterns = $winningPatterns;
    //                 }
    //                 $ticket->save();

    //                 foreach ($winningPatterns as $pattern) {
    //                     $patternAlreadyWon = Winner::where('game_id', $this->games_Id)
    //                         ->where('pattern', $pattern)
    //                         ->exists();


    //                     if (!$patternAlreadyWon) {
    //                         $prizeAmount = $this->getPrizeAmountForPattern($game, $pattern);

    //                         if ($prizeAmount > 0) {
    //                             $this->processPrize($ticket, $game, $pattern, $prizeAmount);
    //                         }

    //                         Winner::create([
    //                             'user_id' => $ticket->user_id,
    //                             'game_id' => $this->games_Id,
    //                             'ticket_id' => $ticket->id,
    //                             'pattern' => $pattern,
    //                             'won_at' => now(),
    //                             'prize_amount' => $prizeAmount
    //                         ]);

    //                         // প্রতিটি উইনার জন্য ইভেন্ট ব্রডকাস্ট করুন
    //                         event(new WinnerAnnouncedEvent(
    //                             $this->games_Id,
    //                             $ticket->user_id,
    //                             $pattern,
    //                             $prizeAmount
    //                         ));
    //                     }
    //                 }
    //             });
    //         } catch (\Exception $e) {
    //             Log::error('Error in updateTicketWinningStatus: '.$e->getMessage());
    //             $ticket->is_winner = true;
    //             $ticket->save();
    //         }
    //     }
    // }

//---------------------------------------------------------



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

    private function initWinningPatterns()
    {
        $this->winningPatterns = [
            'corner' => [
                'name' => 'Corner Numbers',
                'claimed' => $this->isPatternClaimedInGame('corner'),
                'description' => 'All 4 corners of the ticket'
            ],
            'top_line' => [
                'name' => 'Top Line',
                'claimed' => $this->isPatternClaimedInGame('top_line'),
                'description' => 'Complete top row'
            ],
            'middle_line' => [
                'name' => 'Middle Line',
                'claimed' => $this->isPatternClaimedInGame('middle_line'),
                'description' => 'Complete middle row'
            ],
            'bottom_line' => [
                'name' => 'Bottom Line',
                'claimed' => $this->isPatternClaimedInGame('bottom_line'),
                'description' => 'Complete bottom row'
            ],
            'full_house' => [
                'name' => 'Full House',
                'claimed' => $this->isPatternClaimedInGame('full_house'),
                'description' => 'All numbers on the ticket'
            ]
        ];
    }

    private function isPatternClaimedInGame($pattern)
    {
        return Winner::where('game_id', $this->games_Id)
            ->where('pattern', $pattern)
            ->exists();
    }

    private function checkGameOver()
    {
        // Count how many patterns have been claimed in this game
        $claimedPatternsCount = Winner::where('game_id', $this->games_Id)
            ->distinct('pattern')
            ->count('pattern');

        // If all 5 patterns are claimed, the game is over
        $this->gameOver = ($claimedPatternsCount >= 5);

        // Dispatch game over event to all users if game is over
        if ($this->gameOver) {
            $this->dispatchGlobalGameOverEvent();
        }

        return $this->gameOver;
    }


    private function dispatchGlobalGameOverEvent()
    {
        // Broadcast a global event that all users can listen to
        //event(new \App\Events\GameOverEvent($this->games_Id))->toOthers();
        broadcast(new GameOverEvent($this->games_Id))->toOthers();

        // গেমের সকল উইনার লোড করুন
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        // ফ্রন্টএন্ডে ইভেন্ট পাঠান
        $this->dispatch('showGameOverModal', [
            'winners' => $this->winners,
            'message' => 'গেম শেষ! সকল প্যাটার্ন ক্লেইম করা হয়েছে।',
            'title' => 'গেম সমাপ্ত'
        ]);
    }

    private function dispatchGlobalWinerEvent()
    {
        // Broadcast a global event that all users can listen to
        //event(new WinnerAnnouncedEvent($this->games_Id));
        // শুধুমাত্র অন্যান্য ইউজারদের কাছে ব্রডকাস্ট করবে
        try {
            broadcast(new WinnerAnnouncedEvent($this->games_Id))->toOthers();
        } catch (\Exception $e) {
            Log::error("WinnerBroadcasting failed: ".$e->getMessage());
        }

        // বর্তমান ইউজারকে আপডেট
        $this->winnerSelfAnnounced();
    }


    // Updated method signature for Livewire 3
    public function handleNumberAnnounced($payload = null)
    {
        // If game is over, don't process any more numbers
        if ($this->gameOver) {
            return;
        }

        // Log for debugging
        Log::info('Number announced event received', ['payload' => $payload]);

        // Extract number from payload
        $number = null;
        if (is_array($payload) && isset($payload['number'])) {
            $number = $payload['number'];
        } elseif (is_object($payload) && isset($payload->number)) {
            $number = $payload->number;
        }

        // Add the new number to announced numbers if valid
        if ($number && !in_array($number, $this->announcedNumbers)) {
            $this->announcedNumbers[] = $number;

            // Dispatch event to play number audio
            $this->dispatch('play-number-audio', number: $number);
        }

        // Reload all numbers from database to ensure consistency
        $this->loadNumbers();

        // Check for winners
        $this->checkWinners();

        // Dispatch browser event for UI updates - Using dispatch for Livewire 3
        if ($number) {
            $this->dispatch('numberAnnounced', number: $number);
        }
    }
    // Updated method signature for Livewire 3
    public function onNumberReceived($number = null)
    {
        // If game is over, don't process any more numbers
        if ($this->gameOver) {
            return;
        }

        // Log for debugging
        Log::info('Number received via Livewire event', ['number' => $number]);

        // Handle both array and direct value formats
        if (is_array($number) && isset($number['number'])) {
            $number = $number['number'];
        }

        if ($number && !in_array($number, $this->announcedNumbers)) {
            $this->announcedNumbers[] = $number;

            // Dispatch event to play number audio
            $this->dispatch('play-number-audio', number: $number);

            $this->loadNumbers();
            $this->checkWinners();

            // Dispatch browser event for UI updates - Using dispatch for Livewire 3
            $this->dispatch('numberAnnounced', number: $number);
        }
    }

    // ... existing code ...

    public function checkWinners()
    {
        // If game is over, don't check for more winners
        if ($this->checkGameOver()) {
            return;
        }

        foreach ($this->sheetTickets as $index => $ticket) {
            $ticketId = $ticket['id'];
            $numbers = $ticket['numbers'];
            $winningPatterns = [];

            // Check for corner numbers
            if ($this->checkCornerNumbers($numbers)) {
                // Only add if not already claimed by anyone in the game
                if (!$this->isPatternClaimedInGame('corner')) {
                    $winningPatterns[] = 'corner';
                    $this->winningPatterns['corner']['claimed'] = true;
                }
            }

            // Check for top line
            if ($this->checkTopLine($numbers)) {
                // Only add if not already claimed by anyone in the game
                if (!$this->isPatternClaimedInGame('top_line')) {
                    $winningPatterns[] = 'top_line';
                    $this->winningPatterns['top_line']['claimed'] = true;
                }
            }

            // Check for middle line
            if ($this->checkMiddleLine($numbers)) {
                // Only add if not already claimed by anyone in the game
                if (!$this->isPatternClaimedInGame('middle_line')) {
                    $winningPatterns[] = 'middle_line';
                    $this->winningPatterns['middle_line']['claimed'] = true;
                }
            }

            // Check for bottom line
            if ($this->checkBottomLine($numbers)) {
                // Only add if not already claimed by anyone in the game
                if (!$this->isPatternClaimedInGame('bottom_line')) {
                    $winningPatterns[] = 'bottom_line';
                    $this->winningPatterns['bottom_line']['claimed'] = true;
                }
            }

            // Check for full house
            if ($this->checkFullHouse($numbers)) {
                // Only add if not already claimed by anyone in the game
                if (!$this->isPatternClaimedInGame('full_house')) {
                    $winningPatterns[] = 'full_house';
                    $this->winningPatterns['full_house']['claimed'] = true;
                }
            }

            // Update ticket if it's a winner
            if (!empty($winningPatterns)) {
                $this->updateTicketWinningStatus($ticketId, $winningPatterns);
                $this->sheetTickets[$index]['is_winner'] = true;
                $this->sheetTickets[$index]['winning_patterns'] = $winningPatterns;

                // Show notification for each winning pattern
                foreach ($winningPatterns as $pattern) {
                    // Dispatch event to play winner audio
                    $this->dispatch('play-winner-audio', pattern: $pattern);

                    // Using dispatch for Livewire 3 instead of dispatchBrowserEvent
                    $this->dispatch('winner-alert', title: 'Congratulations!',
                        message: 'You won ' . $this->winningPatterns[$pattern]['name'] . '!',
                        pattern: $pattern);
                }

                // Check if game is over after this win
                if ($this->checkGameOver()) {

                    // $this->dispatch('gameOver', [
                    //         'message' => 'Game Over! All patterns have been claimed.',
                    //         'score' => 1250,
                    //         'level' => 5
                    //     ]);
                    break; // Exit the loop as game is over
                }else{
                    // $this->dispatchGlobalWinerEvent();
                }
            }
        }
    }

    public function loadNumbers()
    {
        $this->announcedNumbers = Announcement::where('game_id', $this->games_Id)
            ->pluck('number')
            ->toArray();

        $this->sheetTickets = Ticket::where('user_id', Auth::id())
            ->where('ticket_number', 'LIKE', $this->sheet_Id . '-%')
            ->orderBy('ticket_number')
            ->get()
            ->map(function($ticket) {
                $winningPatterns = [];
                if (Schema::hasColumn('tickets', 'winning_patterns') && $ticket->winning_patterns) {
                    $winningPatterns = is_string($ticket->winning_patterns)
                        ? json_decode($ticket->winning_patterns, true)
                        : $ticket->winning_patterns;
                }

                return [
                    'id' => $ticket->id,
                    'number' => $ticket->ticket_number,
                    'numbers' => is_string($ticket->numbers)
                                ? json_decode($ticket->numbers, true)
                                : $ticket->numbers,
                    'is_winner' => $ticket->is_winner,
                    'winning_patterns' => $winningPatterns,
                    'created_at' => $ticket->created_at->format('d M Y h:i A'),
                    'game' => $ticket->game,
                ];
            })
            ->toArray();
    }




    private function checkCornerNumbers($numbers)
    {
        // Find actual corners by getting first and last non-null values
        $topRow = $numbers[0];
        $bottomRow = $numbers[2];

        // Find top-left (first non-null value in top row)
        $topLeft = null;
        for ($i = 0; $i < 9; $i++) {
            if ($topRow[$i] !== null) {
                $topLeft = $topRow[$i];
                break;
            }
        }

        // Find top-right (last non-null value in top row)
        $topRight = null;
        for ($i = 8; $i >= 0; $i--) {
            if ($topRow[$i] !== null) {
                $topRight = $topRow[$i];
                break;
            }
        }

        // Find bottom-left (first non-null value in bottom row)
        $bottomLeft = null;
        for ($i = 0; $i < 9; $i++) {
            if ($bottomRow[$i] !== null) {
                $bottomLeft = $bottomRow[$i];
                break;
            }
        }

        // Find bottom-right (last non-null value in bottom row)
        $bottomRight = null;
        for ($i = 8; $i >= 0; $i--) {
            if ($bottomRow[$i] !== null) {
                $bottomRight = $bottomRow[$i];
                break;
            }
        }

        // Collect all corner numbers (excluding nulls)
        $corners = array_filter([$topLeft, $topRight, $bottomLeft, $bottomRight], function($value) {
            return $value !== null;
        });

        // Check if all corners are in announced numbers
        foreach ($corners as $corner) {
            if (!in_array($corner, $this->announcedNumbers)) {
                return false;
            }
        }

        return true;
    }

    private function checkTopLine($numbers)
    {
        return $this->checkLine($numbers[0]);
    }

    private function checkMiddleLine($numbers)
    {
        return $this->checkLine($numbers[1]);
    }

    private function checkBottomLine($numbers)
    {
        return $this->checkLine($numbers[2]);
    }

    private function checkLine($line)
    {
        // Filter out null values (empty cells)
        $lineNumbers = array_filter($line, function($value) {
            return $value !== null;
        });

        // Check if all numbers in the line are in announced numbers
        foreach ($lineNumbers as $number) {
            if (!in_array($number, $this->announcedNumbers)) {
                return false;
            }
        }

        return true;
    }

    private function checkFullHouse($numbers)
    {
        // Check all rows
        for ($i = 0; $i < 3; $i++) {
            if (!$this->checkLine($numbers[$i])) {
                return false;
            }
        }

        return true;
    }

    // private function updateTicketWinningStatus($ticketId, $winningPatterns)
    // {
    //     $ticket = Ticket::find($ticketId);

    //     if ($ticket) {
    //         try {
    //             // Check if the winning_patterns column exists
    //             if (Schema::hasColumn('tickets', 'winning_patterns')) {
    //                 $ticket->is_winner = true;
    //                 $ticket->winning_patterns = $winningPatterns; // This will be automatically JSON encoded
    //                 $ticket->save();
    //             } else {
    //                 // If column doesn't exist, just update is_winner
    //                 $ticket->is_winner = true;
    //                 $ticket->save();

    //                 // Log the issue
    //                 Log::warning('winning_patterns column does not exist in tickets table. Please run the migration.');
    //             }

    //             // Record the win in the winners tablegames_Id
    //             foreach ($winningPatterns as $pattern) {
    //                 // First check if this pattern is already claimed in the game
    //                 $patternClaimed = Winner::where('game_id', $this->games_Id)
    //                     ->where('pattern', $pattern)
    //                     ->exists();

    //                 if (!$patternClaimed) {
    //                     Winner::create([
    //                         'user_id' => Auth::id(),
    //                         'game_id' => $this->games_Id,
    //                         'ticket_id' => $ticketId,
    //                         'pattern' => $pattern,
    //                         'won_at' => now()
    //                     ]);
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             // Log the error
    //             Log::error('Error updating ticket winning status: ' . $e->getMessage());

    //             // Try a simpler update
    //             DB::table('tickets')
    //                 ->where('id', $ticketId)
    //                 ->update(['is_winner' => true]);
    //         }
    //     }
    // }

    private function updateTicketWinningStatus($ticketId, $winningPatterns)
    {
        $ticket = Ticket::find($ticketId);
        $game = Game::find($this->games_Id);

        if ($ticket && $game) {
            try {
                DB::transaction(function () use ($ticket, $winningPatterns, $game) {
                    // Update ticket status
                    $ticket->is_winner = true;
                    if (Schema::hasColumn('tickets', 'winning_patterns')) {
                        $ticket->winning_patterns = $winningPatterns;
                    }
                    $ticket->save();

                    foreach ($winningPatterns as $pattern) {
                        // Check if this specific pattern is already claimed in the game
                        $patternAlreadyWon = Winner::where('game_id', $this->games_Id)
                            ->where('pattern', $pattern)
                            ->exists();

                        if (!$patternAlreadyWon) {
                            $prizeAmount = $this->getPrizeAmountForPattern($game, $pattern);

                            // Only process if prize amount is valid
                            if ($prizeAmount > 0) {
                                $this->processPrize($ticket, $game, $pattern, $prizeAmount);
                            }

                            // Always create winner record even if prize is 0
                            Winner::create([
                                'user_id' => $ticket->user_id,
                                'game_id' => $this->games_Id,
                                'ticket_id' => $ticket->id,
                                'pattern' => $pattern,
                                'won_at' => now(),
                                'prize_amount' => $prizeAmount
                            ]);

                            $this->dispatchGlobalWinerEvent();
                        }
                    }
                });
            } catch (\Exception $e) {
                Log::error('Error in updateTicketWinningStatus: '.$e->getMessage());
                // Fallback to minimal update
                $ticket->is_winner = true;
                $ticket->save();
            }
        }
    }

    private function processPrize($ticket, $game, $pattern, $prizeAmount)
    {
        $systemUser = User::where('role','admin')->first();
        $winnerUser = User::find($ticket->user_id);

        if (!$systemUser || !$winnerUser) {
            throw new \Exception('System or winner user not found');
        }

        // Transfer credits
        $systemUser->decrement('credit', $prizeAmount);
        $winnerUser->increment('credit', $prizeAmount);

        // Create transactions
        Transaction::create([
            'user_id' => $systemUser->id,
            'type' => 'debit',
            'amount' => $prizeAmount,
            'details' => 'Prize for '.$pattern.' in game: '.$game->title,
        ]);

        Transaction::create([
            'user_id' => $winnerUser->id,
            'type' => 'credit',
            'amount' => $prizeAmount,
            'details' => 'Won '.$pattern.' in game: '.$game->title,
        ]);



        // Send notifications
        Notification::send($systemUser, new CreditTransferred(
            'Prize of '.$prizeAmount.' credits awarded to '.$winnerUser->name.' for '.$pattern
        ));

        Notification::send($winnerUser, new CreditTransferred(
            'You won '.$prizeAmount.' credits for '.$pattern.' in game: '.$game->title
        ));
        $this->textNote='You won '.$prizeAmount.' credits for '.$pattern.' in game: '.$game->title;
        // broadcast(new NotificationRefresh(auth()->user(), $this->textNote));
        $this->dispatch('notificationText', text: $this->textNote);
        $this->dispatch('notificationRefresh');
        //broadcast(new WinnerAnnouncedEvent($this->gameId))->toOthers();
    }

    private function getPrizeAmountForPattern($game, $pattern)
    {
        return match($pattern) {
            'corner' => $game->corner_prize,
            'top_line' => $game->top_line_prize,
            'middle_line' => $game->middle_line_prize,
            'bottom_line' => $game->bottom_line_prize,
            'full_house' => $game->full_house_prize,
            default => 0,
        };
    }

    /**
     * Check if any ticket has won a specific pattern
     *
     * @param string $pattern
     * @return bool
     */
    public function hasWonPattern($pattern)
    {
        foreach ($this->sheetTickets as $ticket) {
            $winningPatterns = $ticket['winning_patterns'] ?? [];
            if (is_string($winningPatterns)) {
                $winningPatterns = json_decode($winningPatterns, true);
            }

            if (in_array($pattern, $winningPatterns)) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.frontend.game-room')->layout('livewire.layout.frontend.base');
    }
}

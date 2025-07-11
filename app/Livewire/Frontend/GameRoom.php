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
use Carbon\Carbon;
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
    public $gameOverAllart = false;
    public $winners;
    public $winnerAllart = false;
    public $sentNotification = false;
    public $remainingTime = '00:00:00';
    public $gameScheduledAt;
    public $totalParticipants;
    public $winnerPattarns;
    public $simultaneousWinners = [];

    // নতুন property যোগ করা হয়েছে
    public $processingPatterns = [];

    protected $listeners = [
        'echo:game.*,number.announced' => 'handleNumberAnnounced',
        'echo:game.*,game.winner' => 'handleWinnerAnnounced',
        'echo:game.*,game.over' => 'handleGameOver',
        'numberAnnounced' => 'onNumberReceived',
        'updateProgress' => 'updateTransferProgress',
        'transfer-completed' => 'onTransferCompleted',
        'tick' => 'updateTimer',
        'process-delayed-prizes' => 'processDelayedPrizes'
    ];

    public function mount($gameId, $sheetId = null)
    {
        $this->sheet_Id = $sheetId;
        $this->games_Id = $gameId;
        $this->totalParticipants = Ticket::where('game_id', $gameId)
            ->distinct('user_id')
            ->count('user_id');
        $this->loadNumbers();
        $this->initWinningPatterns();
        $this->checkGameOver();
        $this->gameScheduledAt = $this->sheetTickets[0]['game']['scheduled_at'] ?? null;
        $this->updateTimer();
        $this->getWinnerPattarns();

        Log::info('GameRoom mounted for game: ' . $gameId);
    }

    public function updateTimer()
    {
        if (!$this->gameScheduledAt) {
            return;
        }

        $gameTime = Carbon::parse($this->gameScheduledAt);
        $diff = $gameTime->diffInSeconds(now(), false);

        if ($diff >= 0) {
            $this->remainingTime = 'Game Started!';
            return;
        }

        $this->remainingTime = gmdate('H:i:s', abs($diff));
        $this->dispatch('tick', delay: 1000);
    }

    public function handleWinnerAnnounced($payload = null)
    {
        Log::info('handleWinnerAnnounced called', [
            'payload' => $payload,
            'game_id' => $this->games_Id,
            'method' => 'handleWinnerAnnounced'
        ]);

        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        Log::info('Winners loaded', ['winners_count' => $this->winners->count()]);

        $this->winnerAllart = true;
        $this->dispatch('winnerAnnounced', ['winners' => $this->winners]);
        $this->dispatch('winnerAllartMakeFalse');
        $this->getWinnerPattarns();
    }

    public function handleNumberAnnounced($payload = null)
    {
        Log::info('handleNumberAnnounced called', [
            'payload' => $payload,
            'game_id' => $this->games_Id,
            'method' => 'handleNumberAnnounced'
        ]);

        if ($this->gameOver) {
            return;
        }

        $number = null;
        if (is_array($payload) && isset($payload['number'])) {
            $number = $payload['number'];
        } elseif (is_object($payload) && isset($payload->number)) {
            $number = $payload->number;
        }

        if ($number && !in_array($number, $this->announcedNumbers)) {
            $this->announcedNumbers[] = $number;
            $this->dispatch('play-number-audio', number: $number);
        }

        $this->loadNumbers();
        $this->checkWinners();

        if ($number) {
            $this->dispatch('numberAnnounced', number: $number);
        }
    }

    public function testWinnerHandler()
    {
        Log::info('Test winner handler called manually');
        $this->handleWinnerAnnounced(['test' => 'data']);
    }

    public function pushEvent()
    {
        try {
            broadcast(new WinnerAnnouncedEvent($this->games_Id))->toOthers();
            Log::info('WinnerAnnouncedEvent broadcasted successfully');
        } catch (\Exception $e) {
            Log::error("WinnerBroadcasting failed: " . $e->getMessage());
        }
    }

    public function winnerSelfAnnounced()
    {
        Log::info('winnerSelfAnnounced called');
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();
        $this->winnerAllart = true;
        $this->dispatch('winnerAllartMakeFalse');
        $this->getWinnerPattarns();
    }

    public function getWinnerPattarns()
    {
        $this->winnerPattarns = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();
    }

    public function winnerAllartMakeFalseMethod()
    {
        $this->winnerAllart = false;
    }

    public function handleGameOver($data)
    {
        Log::info('Winner announced event received', ['payload' => $data]);
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        $this->dispatch('openGameoverModal');
    }

    public function oprenGameoverModalAfterdelay()
    {
        $this->gameOverAllart = true;
        $this->gameOver = true;
    }

    public function gameOverSelfAnnounced()
    {
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        $this->dispatch('openGameoverModal');
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
        $claimedPatternsCount = Winner::where('game_id', $this->games_Id)
            ->distinct('pattern')
            ->count('pattern');

        $this->gameOver = ($claimedPatternsCount >= 5);

        if ($this->gameOver) {
            $this->dispatchGlobalGameOverEvent();
            $this->gameOverSelfAnnounced();
        }

        return $this->gameOver;
    }

    private function dispatchGlobalGameOverEvent()
    {
        broadcast(new GameOverEvent($this->games_Id))->toOthers();

        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        $this->dispatch('showGameOverModal', [
            'winners' => $this->winners,
            'message' => 'গেম শেষ! সকল প্যাটার্ন ক্লেইম করা হয়েছে।',
            'title' => 'গেম সমাপ্ত'
        ]);
    }

    private function dispatchGlobalWinerEvent()
    {
        try {
            broadcast(new WinnerAnnouncedEvent($this->games_Id))->toOthers();
            Log::info('WinnerAnnouncedEvent dispatched successfully');
        } catch (\Exception $e) {
            Log::error("WinnerBroadcasting failed: " . $e->getMessage());
        }

        $this->winnerSelfAnnounced();
    }

    // উন্নত checkWinners method
    public function checkWinners()
    {
        if ($this->checkGameOver()) {
            return;
        }

        $detectedWinners = [];

        foreach ($this->sheetTickets as $index => $ticket) {
            $ticketId = $ticket['id'];
            $numbers = $ticket['numbers'];
            $winningPatterns = [];

            // সব patterns চেক করা
            $patterns = [
                'corner' => $this->checkCornerNumbers($numbers),
                'top_line' => $this->checkTopLine($numbers),
                'middle_line' => $this->checkMiddleLine($numbers),
                'bottom_line' => $this->checkBottomLine($numbers),
                'full_house' => $this->checkFullHouse($numbers)
            ];

            foreach ($patterns as $pattern => $isWon) {
                if ($isWon && !$this->isPatternClaimedInGame($pattern)) {
                    $winningPatterns[] = $pattern;
                }
            }

            if (!empty($winningPatterns)) {
                $detectedWinners[] = [
                    'ticket_id' => $ticketId,
                    'ticket_index' => $index,
                    'patterns' => $winningPatterns
                ];
            }
        }

        // সব winners একসাথে process করা
        if (!empty($detectedWinners)) {
            $this->processMultipleWinners($detectedWinners);
        }
    }

    // FIXED: একাধিক winners একসাথে process করার জন্য
    private function processMultipleWinners($detectedWinners)
    {
        try {
            DB::transaction(function () use ($detectedWinners) {
                $allPatternsToProcess = [];
                $processedPatterns = []; // ইতিমধ্যে process করা patterns track করার জন্য

                foreach ($detectedWinners as $winnerData) {
                    $ticketId = $winnerData['ticket_id'];
                    $ticketIndex = $winnerData['ticket_index'];
                    $winningPatterns = $winnerData['patterns'];

                    $ticket = Ticket::find($ticketId);
                    if (!$ticket) continue;

                    // Ticket কে winner হিসেবে mark করা
                    $ticket->is_winner = true;
                    if (Schema::hasColumn('tickets', 'winning_patterns')) {
                        $ticket->winning_patterns = $winningPatterns;
                    }
                    $ticket->save();

                    // UI update করা
                    $this->sheetTickets[$ticketIndex]['is_winner'] = true;
                    $this->sheetTickets[$ticketIndex]['winning_patterns'] = $winningPatterns;

                    foreach ($winningPatterns as $pattern) {
                        // Pattern এর জন্য winner record তৈরি করা
                        Winner::create([
                            'user_id' => $ticket->user_id,
                            'game_id' => $this->games_Id,
                            'ticket_id' => $ticket->id,
                            'pattern' => $pattern,
                            'won_at' => now(),
                            'prize_amount' => 0, // এখানে 0 রাখা হয়েছে, পরে update হবে
                            'prize_processed' => false
                        ]);

                        // Pattern list এ add করা (শুধুমাত্র একবার)
                        if (!in_array($pattern, $allPatternsToProcess)) {
                            $allPatternsToProcess[] = $pattern;
                        }

                        // UI effects
                        $this->dispatch('play-winner-audio', pattern: $pattern);
                        $this->dispatch('winner-alert',
                            title: 'Congratulations!',
                            message: 'You won ' . $this->winningPatterns[$pattern]['name'] . '!',
                            pattern: $pattern
                        );
                    }

                    Log::info("Winner record created for user {$ticket->user_id}, patterns: " .
                             implode(', ', $winningPatterns) . ", game: {$this->games_Id}");
                }

                // সব patterns একসাথে process করা (শুধুমাত্র একবার প্রতিটি pattern এর জন্য)
                foreach ($allPatternsToProcess as $pattern) {
                    $this->winningPatterns[$pattern]['claimed'] = true;

                    // শুধুমাত্র একবার prize process করা
                    if (!in_array($pattern, $processedPatterns)) {
                        $this->processPrizesForPatternImmediate($pattern);
                        $processedPatterns[] = $pattern;
                    }
                }

                $this->dispatchGlobalWinerEvent();

                if ($this->checkGameOver()) {
                    return;
                }
            }, 5);

        } catch (\Exception $e) {
            Log::error('Error in processMultipleWinners: ' . $e->getMessage());
        }
    }

    // FIXED: তৎক্ষণাৎ prize process করার জন্য
    private function processPrizesForPatternImmediate($pattern)
    {
        try {
            $game = Game::find($this->games_Id);
            if (!$game) {
                Log::error("Game not found: {$this->games_Id}");
                return;
            }

            // এই pattern এর সব unprocessed winners পাওয়া
            $winners = Winner::where('game_id', $this->games_Id)
                ->where('pattern', $pattern)
                ->where('prize_processed', false)
                ->with('user', 'ticket')
                ->get();

            $numberOfWinners = $winners->count();

            if ($numberOfWinners == 0) {
                Log::info("No unprocessed winners found for pattern: $pattern");
                return;
            }

            // Prize calculation
            $totalPrizeAmount = $this->getPrizeAmountForPattern($game, $pattern);
            $prizePerWinner = $totalPrizeAmount / $numberOfWinners;

            Log::info("Processing immediate prizes for pattern $pattern", [
                'total_prize' => $totalPrizeAmount,
                'number_of_winners' => $numberOfWinners,
                'prize_per_winner' => $prizePerWinner
            ]);

            // System user
            $systemUser = User::where('role', 'admin')->first();
            if (!$systemUser) {
                throw new \Exception('System user not found');
            }

            // Check if system has enough credit
            if ($systemUser->credit < $totalPrizeAmount) {
                Log::error("System user doesn't have enough credit. Required: $totalPrizeAmount, Available: {$systemUser->credit}");
                return;
            }

            // System থেকে ONLY একবার total amount deduct করা
            $systemUser->decrement('credit', $totalPrizeAmount);

            // System transaction (একবার)
            Transaction::create([
                'user_id' => $systemUser->id,
                'type' => 'debit',
                'amount' => $totalPrizeAmount,
                'details' => "Prize for $pattern in game: {$game->title} (shared among $numberOfWinners winners)",
            ]);

            Log::info("System credit deducted: $totalPrizeAmount for pattern: $pattern");

            // প্রতিটি winner এর জন্য prize distribute করা
            foreach ($winners as $winner) {
                $winnerUser = $winner->user;
                if (!$winnerUser) {
                    Log::error("Winner user not found for winner ID: {$winner->id}");
                    continue;
                }

                // Winner এর credit বাড়ানো
                $winnerUser->increment('credit', $prizePerWinner);

                // Winner record update করা
                $winner->prize_amount = $prizePerWinner;
                $winner->prize_processed = true;
                $winner->save();

                // Winner transaction
                Transaction::create([
                    'user_id' => $winnerUser->id,
                    'type' => 'credit',
                    'amount' => $prizePerWinner,
                    'details' => "Won $pattern in game: {$game->title}" .
                               ($numberOfWinners > 1 ? " (shared with " . ($numberOfWinners - 1) . " other winners)" : ''),
                ]);

                // Notification
                $notificationMessage = $numberOfWinners > 1
                    ? "You won $prizePerWinner credits for $pattern in game: {$game->title} (shared with " . ($numberOfWinners - 1) . " other winners)"
                    : "You won $prizePerWinner credits for $pattern in game: {$game->title}";

                Notification::send($winnerUser, new CreditTransferred($notificationMessage));

                // Current user এর জন্য notification set করা
                if ($winner->user_id == Auth::id()) {
                    $this->textNote = $notificationMessage;
                    $this->sentNotification = true;
                }

                Log::info("Prize distributed to user {$winnerUser->id}: $prizePerWinner credits");
            }

            // System notification
            $systemNotificationMessage = "Prize of $totalPrizeAmount credits awarded for $pattern (shared among $numberOfWinners winners)";
            Notification::send($systemUser, new CreditTransferred($systemNotificationMessage));

            Log::info("All prizes processed successfully for pattern $pattern. Total winners: $numberOfWinners, Total amount: $totalPrizeAmount");

        } catch (\Exception $e) {
            Log::error("Error processing immediate prizes for pattern $pattern: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
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
            ->map(function ($ticket) {
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

    // বাকি সব methods একই রকম থাকবে...

    private function checkCornerNumbers($numbers)
    {
        $topRow = $numbers[0];
        $bottomRow = $numbers[2];

        $topLeft = null;
        for ($i = 0; $i < 9; $i++) {
            if ($topRow[$i] !== null) {
                $topLeft = $topRow[$i];
                break;
            }
        }

        $topRight = null;
        for ($i = 8; $i >= 0; $i--) {
            if ($topRow[$i] !== null) {
                $topRight = $topRow[$i];
                break;
            }
        }

        $bottomLeft = null;
        for ($i = 0; $i < 9; $i++) {
            if ($bottomRow[$i] !== null) {
                $bottomLeft = $bottomRow[$i];
                break;
            }
        }

        $bottomRight = null;
        for ($i = 8; $i >= 0; $i--) {
            if ($bottomRow[$i] !== null) {
                $bottomRight = $bottomRow[$i];
                break;
            }
        }

        $corners = array_filter([$topLeft, $topRight, $bottomLeft, $bottomRight], function ($value) {
            return $value !== null;
        });

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
        $lineNumbers = array_filter($line, function ($value) {
            return $value !== null;
        });

        foreach ($lineNumbers as $number) {
            if (!in_array($number, $this->announcedNumbers)) {
                return false;
            }
        }

        return true;
    }

    private function checkFullHouse($numbers)
    {
        for ($i = 0; $i < 3; $i++) {
            if (!$this->checkLine($numbers[$i])) {
                return false;
            }
        }

        return true;
    }

    // Legacy method - এখন আর ব্যবহার হবে না
    private function updateTicketWinningStatus($ticketId, $winningPatterns)
    {
        // This method is now deprecated in favor of processMultipleWinners
    }

    // Legacy method - backward compatibility এর জন্য রাখা হয়েছে
    public function processDelayedPrizes($data)
    {
        Log::info("processDelayedPrizes called but using immediate processing instead");
        // এখন আর এই method ব্যবহার হবে না
    }

    private function getPrizeAmountForPattern($game, $pattern)
    {
        return match ($pattern) {
            'corner' => $game->corner_prize,
            'top_line' => $game->top_line_prize,
            'middle_line' => $game->middle_line_prize,
            'bottom_line' => $game->bottom_line_prize,
            'full_house' => $game->full_house_prize,
            default => 0,
        };
    }

    public function manageNotification()
    {
        if ($this->sentNotification) {
            $this->dispatch('notificationText', text: $this->textNote);
            $this->dispatch('notificationRefresh');
        }
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

    public function onTransferCompleted()
    {
        $this->dispatch('transfer-completed');
    }

    public function progressCompleted()
    {
        //dd('transfer-completed');
    }

    public function updateTransferProgress($progress)
    {
        $this->dispatch('progressUpdated', progress: $progress);
    }

    public function onNumberReceived($number = null)
    {
        if ($this->gameOver) {
            return;
        }

        Log::info('Number received via Livewire event', ['number' => $number]);

        if (is_array($number) && isset($number['number'])) {
            $number = $number['number'];
        }

        if ($number && !in_array($number, $this->announcedNumbers)) {
            $this->announcedNumbers[] = $number;
            $this->dispatch('play-number-audio', number: $number);
            $this->loadNumbers();
            $this->checkWinners();
            $this->dispatch('numberAnnounced', number: $number);
        }
    }

    public function render()
    {
        return view('livewire.frontend.game-room')->layout('livewire.layout.frontend.base');
    }
}

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

        // Create game_locks table if not exists
        $this->ensureGameLocksTableExists();

        Log::info('GameRoom mounted for game: ' . $gameId);
    }

    // Ensure game_locks table exists
    private function ensureGameLocksTableExists()
    {
        try {
            if (!Schema::hasTable('game_locks')) {
                Schema::create('game_locks', function ($table) {
                    $table->id();
                    $table->string('lock_key')->unique();
                    $table->timestamp('created_at');
                    $table->timestamp('expires_at');
                    $table->index(['lock_key', 'expires_at']);
                });
                Log::info('game_locks table created successfully');
            }
        } catch (\Exception $e) {
            Log::error('Error creating game_locks table: ' . $e->getMessage());
        }
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
        $this->dispatch('openGameoverModal');
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

    public function checkWinners()
    {
        if ($this->checkGameOver()) {
            return;
        }

        $newWinners = [];

        foreach ($this->sheetTickets as $index => $ticket) {
            $ticketId = $ticket['id'];
            $numbers = $ticket['numbers'];
            $winningPatterns = [];

            // Check all patterns
            if ($this->checkCornerNumbers($numbers)) {
                if (!$this->isPatternClaimedInGame('corner')) {
                    $winningPatterns[] = 'corner';
                }
            }
            if ($this->checkTopLine($numbers)) {
                if (!$this->isPatternClaimedInGame('top_line')) {
                    $winningPatterns[] = 'top_line';
                }
            }
            if ($this->checkMiddleLine($numbers)) {
                if (!$this->isPatternClaimedInGame('middle_line')) {
                    $winningPatterns[] = 'middle_line';
                }
            }
            if ($this->checkBottomLine($numbers)) {
                if (!$this->isPatternClaimedInGame('bottom_line')) {
                    $winningPatterns[] = 'bottom_line';
                }
            }
            if ($this->checkFullHouse($numbers)) {
                if (!$this->isPatternClaimedInGame('full_house')) {
                    $winningPatterns[] = 'full_house';
                }
            }

            if (!empty($winningPatterns)) {
                $newWinners[] = [
                    'ticket_id' => $ticketId,
                    'patterns' => $winningPatterns,
                    'index' => $index,
                    'user_id' => $ticket['user_id'] ?? Auth::id() // Fixed: Added fallback
                ];
            }
        }

        // Process all winners at once to avoid race conditions
        if (!empty($newWinners)) {
            $this->processMultipleWinners($newWinners);
        }
    }

    private function processMultipleWinners($newWinners)
    {
        try {
            DB::transaction(function () use ($newWinners) {
                $patternsToProcess = [];

                foreach ($newWinners as $winnerData) {
                    $ticketId = $winnerData['ticket_id'];
                    $patterns = $winnerData['patterns'];
                    $index = $winnerData['index'];
                    $userId = $winnerData['user_id'];

                    $ticket = Ticket::find($ticketId);
                    if (!$ticket) continue;

                    // Mark ticket as winner
                    $ticket->is_winner = true;
                    if (Schema::hasColumn('tickets', 'winning_patterns')) {
                        $ticket->winning_patterns = $patterns;
                    }
                    $ticket->save();

                    // Update UI
                    $this->sheetTickets[$index]['is_winner'] = true;
                    $this->sheetTickets[$index]['winning_patterns'] = $patterns;

                    foreach ($patterns as $pattern) {
                        // Check if pattern is already being processed or won
                        $patternAlreadyWon = Winner::where('game_id', $this->games_Id)
                            ->where('pattern', $pattern)
                            ->exists();

                        if (!$patternAlreadyWon) {
                            // Add pattern to list for prize processing if not already won
                            if (!in_array($pattern, $patternsToProcess)) {
                                $patternsToProcess[] = $pattern;
                            }

                            // Create winner record immediately with prize_amount = 0
                            Winner::create([
                                'user_id' => $userId,
                                'game_id' => $this->games_Id,
                                'ticket_id' => $ticket->id,
                                'pattern' => $pattern,
                                'won_at' => now(),
                                'prize_amount' => 0,
                                'prize_processed' => false
                            ]);

                            Log::info("বিজয়ী রেকর্ড তৈরি হয়েছে ব্যবহারকারী {$userId}, প্যাটার্ন: $pattern, গেম: {$this->games_Id}");

                            // Update pattern status
                            $this->winningPatterns[$pattern]['claimed'] = true;

                            // Dispatch UI events for immediate feedback
                            $this->dispatch('play-winner-audio', pattern: $pattern);
                            $this->dispatch('winner-alert',
                                title: 'Congratulations!',
                                message: 'You won ' . $this->winningPatterns[$pattern]['name'] . '!',
                                pattern: $pattern
                            );
                        }
                    }
                }

                // Process prizes for each unique pattern immediately
                foreach ($patternsToProcess as $pattern) {
                    // Use dispatch with delay to ensure all winner records are created
                    $this->dispatch('process-delayed-prizes', [
                        'pattern' => $pattern,
                        'game_id' => $this->games_Id
                    ], delay: 1000); // 1 second delay
                }

                // Dispatch winner event once for all patterns that were newly claimed
                if (!empty($patternsToProcess)) {
                    $this->dispatchGlobalWinerEvent();
                }

            }, 5); // 5 retry attempts for deadlock

        } catch (\Exception $e) {
            Log::error('Error in processMultipleWinners: ' . $e->getMessage());

            // Fallback to individual processing
            foreach ($newWinners as $winnerData) {
                try {
                    $this->updateTicketWinningStatus($winnerData['ticket_id'], $winnerData['patterns']);
                } catch (\Exception $fallbackError) {
                    Log::error('Fallback processing failed for ticket ' . $winnerData['ticket_id'] . ': ' . $fallbackError->getMessage());
                }
            }
        }
    }

    private function processPrizesForPatternImmediate($pattern)
    {
        $maxAttempts = 3; // Reduced attempts
        $delayBetweenAttempts = 500; // 0.5 seconds

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $lockKey = "prize_processing_{$this->games_Id}_{$pattern}";

                // Clean expired locks first
                DB::table('game_locks')
                    ->where('expires_at', '<', now())
                    ->delete();

                // Try to acquire lock
                $lockAcquired = DB::table('game_locks')->insertOrIgnore([
                    'lock_key' => $lockKey,
                    'created_at' => now(),
                    'expires_at' => now()->addMinutes(2) // Reduced lock time
                ]);

                if (!$lockAcquired) {
                    Log::info("Pattern $pattern is already being processed (attempt $attempt/$maxAttempts)");
                    if ($attempt < $maxAttempts) {
                        usleep($delayBetweenAttempts * 1000);
                        continue;
                    }
                    return;
                }

                try {
                    DB::transaction(function () use ($pattern, $lockKey) {
                        $game = Game::find($this->games_Id);
                        if (!$game) {
                            Log::error("গেম আইডি {$this->games_Id} পাওয়া যায়নি।");
                            return;
                        }

                        // Check if already processed
                        $alreadyProcessed = Winner::where('game_id', $this->games_Id)
                            ->where('pattern', $pattern)
                            ->where('prize_processed', true)
                            ->exists();

                        if ($alreadyProcessed) {
                            Log::info("প্যাটার্ন $pattern এর জন্য পুরস্কার ইতিমধ্যে প্রক্রিয়া করা হয়েছে।");
                            return;
                        }

                        // Get all unprocessed winners for this pattern
                        $winners = Winner::where('game_id', $this->games_Id)
                            ->where('pattern', $pattern)
                            ->where('prize_processed', false)
                            ->lockForUpdate()
                            ->with('user', 'ticket')
                            ->get();

                        $numberOfWinners = $winners->count();
                        if ($numberOfWinners == 0) {
                            Log::info("প্যাটার্ন $pattern এর জন্য কোনো অপ্রক্রিয়াজাত বিজয়ী পাওয়া যায়নি।");
                            return;
                        }

                        // Calculate prize distribution
                        $totalPrizeAmount = $this->getPrizeAmountForPattern($game, $pattern);
                        $prizePerWinner = round($totalPrizeAmount / $numberOfWinners, 2);

                        Log::info("Processing prizes for pattern $pattern. Total prize: $totalPrizeAmount, Winners: $numberOfWinners, Prize per winner: $prizePerWinner");

                        // Get system user
                        $systemUser = User::where('role', 'admin')->first();
                        if (!$systemUser) {
                            throw new \Exception('সিস্টেম ব্যবহারকারী পাওয়া যায়নি');
                        }

                        // Check if system user has enough credit
                        if ($systemUser->credit < $totalPrizeAmount) {
                            Log::error("System user doesn't have enough credit. Required: $totalPrizeAmount, Available: {$systemUser->credit}");
                            throw new \Exception('সিস্টেমে পর্যাপ্ত ক্রেডিট নেই');
                        }

                        // Deduct from system user
                        $systemUser->decrement('credit', $totalPrizeAmount);

                        // System transaction
                        Transaction::create([
                            'user_id' => $systemUser->id,
                            'type' => 'debit',
                            'amount' => $totalPrizeAmount,
                            'details' => "Prize for $pattern in game: {$game->title} (shared among $numberOfWinners winners)",
                        ]);

                        $simultaneousWinnersData = [];

                        // Process each winner
                        foreach ($winners as $winner) {
                            $winnerUser = $winner->user;
                            if (!$winnerUser) {
                                Log::error("Winner user not found for winner ID: {$winner->id}");
                                continue;
                            }

                            // Add prize to winner
                            $winnerUser->increment('credit', $prizePerWinner);

                            // Update winner record
                            $winner->update([
                                'prize_amount' => $prizePerWinner,
                                'prize_processed' => true
                            ]);

                            // Winner transaction
                            Transaction::create([
                                'user_id' => $winnerUser->id,
                                'type' => 'credit',
                                'amount' => $prizePerWinner,
                                'details' => "Won $pattern in game: {$game->title}" .
                                            ($numberOfWinners > 1 ? " (shared with " . ($numberOfWinners - 1) . " other winners)" : ''),
                            ]);

                            // Send notification
                            $notificationMessage = $numberOfWinners > 1
                                ? "You won $prizePerWinner credits for $pattern in game: {$game->title} (shared with " . ($numberOfWinners - 1) . " other winners)"
                                : "You won $prizePerWinner credits for $pattern in game: {$game->title}";

                            Notification::send($winnerUser, new CreditTransferred($notificationMessage));

                            // Set notification for current user
                            if ($winner->user_id == Auth::id()) {
                                $this->textNote = $notificationMessage;
                                $this->sentNotification = true;
                            } else {
                                $this->textNote = '';
                                $this->sentNotification = false;
                            }

                            // Collect simultaneous winners data
                            $simultaneousWinnersData[] = [
                                'user_id' => $winnerUser->id,
                                'user_name' => $winnerUser->name,
                                'pattern' => $winner->pattern,
                                'prize_amount' => $winner->prize_amount,
                                'ticket_number' => $winner->ticket->ticket_number,
                            ];
                        }

                        // System notification
                        Notification::send($systemUser, new CreditTransferred(
                            "Prize of $totalPrizeAmount credits awarded for $pattern (shared among $numberOfWinners winners)"
                        ));

                        Log::info("প্যাটার্ন $pattern এর জন্য সমস্ত পুরস্কার সফলভাবে প্রক্রিয়া করা হয়েছে। মোট বিজয়ী: $numberOfWinners");

                        // Update simultaneous winners
                        $this->simultaneousWinners = $simultaneousWinnersData;

                        // Dispatch event for frontend
                        $this->dispatch('simultaneousWinnersAnnounced', [
                            'pattern' => $pattern,
                            'winners' => $this->simultaneousWinners
                        ]);

                    }, 3); // Reduced retry attempts

                    break; // Success, exit retry loop

                } finally {
                    // Always release lock
                    DB::table('game_locks')->where('lock_key', $lockKey)->delete();
                }

            } catch (\Exception $e) {
                Log::error("প্যাটার্ন $pattern এর জন্য পুরস্কার প্রক্রিয়াকরণে ত্রুটি: " . $e->getMessage() . " (চেষ্টা: $attempt/$maxAttempts)");

                if ($attempt < $maxAttempts) {
                    usleep($delayBetweenAttempts * 1000);
                } else {
                    // Final attempt failed, log and continue
                    Log::error("Final attempt failed for pattern $pattern: " . $e->getMessage());
                }
            }
        }
    }

    // Handle simultaneous winners announced event
    public function handleSimultaneousWinnersAnnounced($payload)
    {
        Log::info('Simultaneous winners announced event received', ['payload' => $payload]);

        if (isset($payload['winners']) && is_array($payload['winners'])) {
            $this->simultaneousWinners = $payload['winners'];

            // Dispatch to frontend for UI updates
            $this->dispatch('showSimultaneousWinnersModal', [
                'pattern' => $payload['pattern'] ?? 'Unknown',
                'winners' => $this->simultaneousWinners
            ]);
        }
    }

    // Improved delayed prize processing
    public function processDelayedPrizes($data)
    {
        $pattern = $data['pattern'];
        $gameId = $data['game_id'];

        Log::info("Processing delayed prizes for pattern: $pattern, game: $gameId");

        // Small delay to ensure all winner records are created
        usleep(500000); // 0.5 seconds

        $game = Game::find($gameId);
        if ($game) {
            $this->processPrizesForPatternImmediate($pattern);
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
                    'user_id' => $ticket->user_id, // Fixed: Added user_id
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

    // ... Rest of the methods remain the same ...

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

    private function updateTicketWinningStatus($ticketId, $winningPatterns)
    {
        $ticket = Ticket::find($ticketId);
        $game = Game::find($this->games_Id);

        if ($ticket && $game) {
            try {
                DB::transaction(function () use ($ticket, $winningPatterns, $game) {
                    $ticket->is_winner = true;
                    if (Schema::hasColumn('tickets', 'winning_patterns')) {
                        $ticket->winning_patterns = $winningPatterns;
                    }
                    $ticket->save();

                    foreach ($winningPatterns as $pattern) {
                        $patternAlreadyWon = Winner::where('game_id', $this->games_Id)
                            ->where('pattern', $pattern)
                            ->exists();

                        if (!$patternAlreadyWon) {
                            Winner::create([
                                'user_id' => $ticket->user_id,
                                'game_id' => $this->games_Id,
                                'ticket_id' => $ticket->id,
                                'pattern' => $pattern,
                                'won_at' => now(),
                                'prize_amount' => 0,
                                'prize_processed' => false
                            ]);

                            Log::info("Winner record created for user {$ticket->user_id}, pattern: $pattern, game: {$this->games_Id}");

                            $this->dispatchGlobalWinerEvent();

                            $this->dispatch('process-delayed-prizes', [
                                'pattern' => $pattern,
                                'game_id' => $this->games_Id
                            ], delay: 2000);
                        }
                    }
                });
            } catch (\Exception $e) {
                Log::error('Error in updateTicketWinningStatus: ' . $e->getMessage());
                $ticket->is_winner = true;
                $ticket->save();
            }
        }
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

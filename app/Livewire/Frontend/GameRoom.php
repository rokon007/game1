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
    public $simultaneousWinners = []; // এই প্রপার্টিটি এখন একটি নির্দিষ্ট প্যাটার্নের জন্য একাধিক বিজয়ী ধারণ করবে

    protected $listeners = [
        'echo:game.*,number.announced' => 'handleNumberAnnounced',
        'echo:game.*,game.winner' => 'handleWinnerAnnounced',
        'echo:game.*,game.over' => 'handleGameOver',
        'numberAnnounced' => 'onNumberReceived',
        'updateProgress' => 'updateTransferProgress',
        'transfer-completed' => 'onTransferCompleted',
        'tick' => 'updateTimer',
        'process-delayed-prizes' => 'processDelayedPrizes',
        'simultaneousWinnersAnnounced' => 'handleSimultaneousWinnersAnnounced', // নতুন ইভেন্ট লিসেনার
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

        // ডিবাগ লগ যোগ করুন
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

        // Livewire v3 স্টাইলে ডেলাইড ডিসপ্যাচ
        $this->dispatch('tick', delay: 1000);
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
        $this->getWinnerPattarns();
    }

    public function handleNumberAnnounced($payload = null)
    {
        // ডিবাগ লগ যোগ করুন
        Log::info('handleNumberAnnounced called', [
            'payload' => $payload,
            'game_id' => $this->games_Id,
            'method' => 'handleNumberAnnounced'
        ]);

        // If game is over, don't process any more numbers
        if ($this->gameOver) {
            return;
        }

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
            $this->dispatch('play-number-audio', number: $number);
        }

        $this->loadNumbers();
        $this->checkWinners();

        if ($number) {
            $this->dispatch('numberAnnounced', number: $number);
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

    // টেস্ট মেথড যোগ করুন
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
        // গেমের সকল উইনার লোড করুন
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
        // গেমের সকল উইনার লোড করুন
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
                    'user_id' => $ticket['user_id'] // user_id যোগ করা হয়েছে
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
                $allSimultaneousWinners = []; // সকল প্যাটার্নের জন্য সকল যৌথ বিজয়ী

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

                    // Update UI (Livewire will re-render)
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

                // Process prizes for each unique pattern
                foreach ($patternsToProcess as $pattern) {
                    $this->processPrizesForPatternImmediate($pattern);
                }

                // Dispatch winner event once for all patterns that were newly claimed
                if (!empty($patternsToProcess)) {
                    $this->dispatchGlobalWinerEvent();
                }

            }, 5); // 5 retry attempts for deadlock

        } catch (\Exception $e) {
            Log::error('Error in processMultipleWinners: ' . $e->getMessage());
            // Fallback to individual processing if transaction fails
            foreach ($newWinners as $winnerData) {
                // This fallback might still have race condition issues if not handled carefully
                // Consider re-evaluating the transaction or using queues for prize processing
                // For now, we'll just log the error.
                Log::error('Fallback processing for winner ' . $winnerData['ticket_id'] . ' failed: ' . $e->getMessage());
            }
        }
    }

    private function processPrizesForPatternImmediate($pattern)
    {
        // পুরস্কার প্রক্রিয়াকরণের জন্য সর্বোচ্চ চেষ্টার সংখ্যা
        $maxAttempts = 5;
        // প্রতিটি চেষ্টার মধ্যে অপেক্ষা করার সময় (মিলিসেকেন্ড)
        $delayBetweenAttempts = 1000; // 1 সেকেন্ড

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                DB::transaction(function () use ($pattern, $attempt, $maxAttempts) {
                    $game = Game::find($this->games_Id);
                    if (!$game) {
                        Log::error("গেম আইডি {$this->games_Id} পাওয়া যায়নি।");
                        return;
                    }

                    // Use Redis lock or database lock to prevent concurrent processing
                    $lockKey = "prize_processing_{$this->games_Id}_{$pattern}";

                    // Simple database-based locking mechanism
                    $lockResult = DB::table('game_locks')->insertOrIgnore([
                        'lock_key' => $lockKey,
                        'created_at' => now(),
                        'expires_at' => now()->addMinutes(5)
                    ]);

                    if (!$lockResult) {
                        Log::info("Pattern $pattern is already being processed by another instance");
                        // যদি লক না পাওয়া যায়, এবং এটি শেষ চেষ্টা না হয়, তাহলে একটি ব্যতিক্রম ছুঁড়ুন যাতে আবার চেষ্টা করা যায়।
                        if ($attempt < $maxAttempts) {
                            throw new \Exception("লক পাওয়া যায়নি, আবার চেষ্টা করা হচ্ছে...");
                        }
                        return; // যদি শেষ চেষ্টা হয় এবং লক না পাওয়া যায়, তাহলে বেরিয়ে যান।
                    }

                    try {
                        // এই প্যাটার্নের জন্য পুরস্কার ইতিমধ্যে প্রক্রিয়া করা হয়েছে কিনা তা পরীক্ষা করুন
                        $alreadyProcessed = Winner::where('game_id', $this->games_Id)
                            ->where('pattern', $pattern)
                            ->where('prize_processed', true)
                            ->exists();

                        if ($alreadyProcessed) {
                            Log::info("প্যাটার্ন $pattern এর জন্য পুরস্কার ইতিমধ্যে প্রক্রিয়া করা হয়েছে।");
                            return; // ট্রানজেকশন থেকে বেরিয়ে যান
                        }

                        // Get all unprocessed winners for this pattern
                        // রেস কন্ডিশন প্রতিরোধ করতে lockForUpdate ব্যবহার করুন
                        $winners = Winner::where('game_id', $this->games_Id)
                            ->where('pattern', $pattern)
                            ->where('prize_processed', false)
                            ->lockForUpdate()
                            ->with('user', 'ticket')
                            ->get();

                        $numberOfWinners = $winners->count();
                        if ($numberOfWinners == 0) {
                            Log::info("প্যাটার্ন $pattern এর জন্য কোনো অপ্রক্রিয়াজাত বিজয়ী পাওয়া যায়নি।");
                            // যদি কোনো বিজয়ী না থাকে, এবং এটি শেষ চেষ্টা না হয়, তাহলে একটি ব্যতিক্রম ছুঁড়ুন যাতে আবার চেষ্টা করা যায়।
                            if ($attempt < $maxAttempts) {
                                throw new \Exception("কোনো বিজয়ী পাওয়া যায়নি, আবার চেষ্টা করা হচ্ছে...");
                            }
                            return; // যদি শেষ চেষ্টা হয় এবং কোনো বিজয়ী না থাকে, তাহলে বেরিয়ে যান।
                        }

                        // Calculate prize distribution
                        $totalPrizeAmount = $this->getPrizeAmountForPattern($game, $pattern);
                        $prizePerWinner = $totalPrizeAmount / $numberOfWinners;

                        Log::info("Processing prizes for pattern $pattern. Total: $totalPrizeAmount, Winners: $numberOfWinners, Each: $prizePerWinner");

                        // Get system user
                        $systemUser = User::where('role', 'admin')->first();
                        if (!$systemUser) {
                            throw new \Exception('সিস্টেম ব্যবহারকারী পাওয়া যায়নি');
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

                        $simultaneousWinnersData = []; // এই প্যাটার্নের জন্য যৌথ বিজয়ীদের ডেটা

                        // Process each winner
                        foreach ($winners as $winner) {
                            $winnerUser = $winner->user;
                            if (!$winnerUser) continue;

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
                            }

                            // যৌথ বিজয়ীদের ডেটা সংগ্রহ করুন
                            $simultaneousWinnersData[] = [
                                'user_id' => $winnerUser->id,
                                'user_name' => $winnerUser->name,
                                'pattern' => $winner->pattern,
                                'prize_amount' => $winner->prize_amount,
                                'ticket_number' => $winner->ticket->ticket_number,
                            ];
                        }

                        // সিস্টেম ব্যবহারকারীকে নোটিফিকেশন পাঠান
                        Notification::send($systemUser, new CreditTransferred(
                            "Prize of $totalPrizeAmount credits awarded for $pattern (shared among $numberOfWinners winners)"
                        ));

                        Log::info("প্যাটার্ন $pattern এর জন্য সমস্ত পুরস্কার সফলভাবে প্রক্রিয়া করা হয়েছে। মোট বিজয়ী: $numberOfWinners");

                        // simultaneousWinners প্রপার্টি আপডেট করুন
                        $this->simultaneousWinners = $simultaneousWinnersData;

                        // ফ্রন্টএন্ডে একটি ইভেন্ট ডিসপ্যাচ করুন যাতে একাধিক বিজয়ী দেখানো যায়
                        $this->dispatch('simultaneousWinnersAnnounced', [
                            'pattern' => $pattern,
                            'winners' => $this->simultaneousWinners
                        ]);

                    } finally {
                        // Release lock
                        DB::table('game_locks')->where('lock_key', $lockKey)->delete();
                    }
                }, 5); // ডেডলক রিট্রাইয়ের জন্য 5 বার চেষ্টা
                break; // সফল হলে লুপ থেকে বেরিয়ে যান
            } catch (\Exception $e) {
                Log::error("প্যাটার্ন $pattern এর জন্য পুরস্কার প্রক্রিয়াকরণে ত্রুটি: " . $e->getMessage() . " (চেষ্টা: $attempt/$maxAttempts)");
                if ($attempt < $maxAttempts) {
                    usleep($delayBetweenAttempts * 1000); // মিলিসেকেন্ড থেকে মাইক্রোসেকেন্ডে রূপান্তর
                } else {
                    // শেষ চেষ্টাতেও ব্যর্থ হলে, ত্রুটিটি পুনরায় ছুঁড়ুন
                    throw $e;
                }
            }
        }
    }

    // নতুন মেথড: simultaneousWinnersAnnounced ইভেন্ট হ্যান্ডেল করার জন্য
    public function handleSimultaneousWinnersAnnounced($payload)
    {
        Log::info('Simultaneous winners announced event received', ['payload' => $payload]);
        // এই মেথডটি ফ্রন্টএন্ড থেকে ডিসপ্যাচ করা ইভেন্ট রিসিভ করবে।
        // আপনি চাইলে এই ডেটা একটি পাবলিক প্রপার্টিতে সংরক্ষণ করতে পারেন
        // এবং ফ্রন্টএন্ডে একটি মডেল বা অ্যালার্ট দেখাতে ব্যবহার করতে পারেন।
        // উদাহরণস্বরূপ:
        // $this->simultaneousWinners = $payload['winners'];
        // $this->dispatch('showSimultaneousWinnersModal');
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

        Log::info('Livewire ইভেন্টের মাধ্যমে নম্বর প্রাপ্ত হয়েছে', ['number' => $number]);

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

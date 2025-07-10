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

        foreach ($this->sheetTickets as $index => $ticket) {
            $ticketId = $ticket['id'];
            $numbers = $ticket['numbers'];
            $winningPatterns = [];

            if ($this->checkCornerNumbers($numbers)) {
                if (!$this->isPatternClaimedInGame('corner')) {
                    $winningPatterns[] = 'corner';
                    $this->winningPatterns['corner']['claimed'] = true;
                }
            }

            if ($this->checkTopLine($numbers)) {
                if (!$this->isPatternClaimedInGame('top_line')) {
                    $winningPatterns[] = 'top_line';
                    $this->winningPatterns['top_line']['claimed'] = true;
                }
            }

            if ($this->checkMiddleLine($numbers)) {
                if (!$this->isPatternClaimedInGame('middle_line')) {
                    $winningPatterns[] = 'middle_line';
                    $this->winningPatterns['middle_line']['claimed'] = true;
                }
            }

            if ($this->checkBottomLine($numbers)) {
                if (!$this->isPatternClaimedInGame('bottom_line')) {
                    $winningPatterns[] = 'bottom_line';
                    $this->winningPatterns['bottom_line']['claimed'] = true;
                }
            }

            if ($this->checkFullHouse($numbers)) {
                if (!$this->isPatternClaimedInGame('full_house')) {
                    $winningPatterns[] = 'full_house';
                    $this->winningPatterns['full_house']['claimed'] = true;
                }
            }

            if (!empty($winningPatterns)) {
                $this->updateTicketWinningStatus($ticketId, $winningPatterns);
                $this->sheetTickets[$index]['is_winner'] = true;
                $this->sheetTickets[$index]['winning_patterns'] = $winningPatterns;

                foreach ($winningPatterns as $pattern) {
                    $this->dispatch('play-winner-audio', pattern: $pattern);
                    $this->dispatch('winner-alert', title: 'Congratulations!',
                        message: 'You won ' . $this->winningPatterns[$pattern]['name'] . '!',
                        pattern: $pattern);
                }

                if ($this->checkGameOver()) {
                    break;
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
                    // টিকিটকে বিজয়ী হিসেবে চিহ্নিত করুন
                    $ticket->is_winner = true;
                    if (Schema::hasColumn('tickets', 'winning_patterns')) {
                        $ticket->winning_patterns = $winningPatterns;
                    }
                    $ticket->save();

                    foreach ($winningPatterns as $pattern) {
                        // এই প্যাটার্নটি ইতিমধ্যে জেতা হয়েছে কিনা তা পরীক্ষা করুন
                        $patternAlreadyWon = Winner::where('game_id', $this->games_Id)
                            ->where('pattern', $pattern)
                            ->exists();

                        if (!$patternAlreadyWon) {
                            // একটি অস্থায়ী বিজয়ী রেকর্ড তৈরি করুন (prize_amount = 0 সহ)
                            Winner::create([
                                'user_id' => $ticket->user_id,
                                'game_id' => $this->games_Id,
                                'ticket_id' => $ticket->id,
                                'pattern' => $pattern,
                                'won_at' => now(),
                                'prize_amount' => 0, // অস্থায়ী মান, পরে আপডেট করা হবে
                                'prize_processed' => false
                            ]);

                            Log::info("বিজয়ী রেকর্ড তৈরি হয়েছে ব্যবহারকারী {$ticket->user_id}, প্যাটার্ন: $pattern, গেম: {$this->games_Id}");

                            // গ্লোবাল উইনার ইভেন্ট ডিসপ্যাচ করুন
                            $this->dispatchGlobalWinerEvent();

                            // বিলম্বিত পুরস্কার প্রক্রিয়াকরণের জন্য ইভেন্ট ডিসপ্যাচ করুন
                            // এখানে আর ডিলে ব্যবহার করা হচ্ছে না, কারণ processDelayedPrizes নিজেই অপেক্ষা করবে
                            $this->dispatch('process-delayed-prizes', [
                                'pattern' => $pattern,
                                'game_id' => $this->games_Id
                            ]);
                        }
                    }
                });

            } catch (\Exception $e) {
                Log::error('updateTicketWinningStatus এ ত্রুটি: ' . $e->getMessage());
                $ticket->is_winner = true;
                $ticket->save();
            }
        }
    }

    // বিলম্বিত পুরস্কার প্রক্রিয়াকরণের জন্য নতুন মেথড
    public function processDelayedPrizes($data)
    {
        $pattern = $data['pattern'];
        $gameId = $data['game_id'];

        Log::info("প্যাটার্ন $pattern, গেম $gameId এর জন্য বিলম্বিত পুরস্কার প্রক্রিয়া করা হচ্ছে");

        $game = Game::find($gameId);
        if (!$game) {
            Log::error("গেম আইডি $gameId পাওয়া যায়নি।");
            return;
        }

        // পুরস্কার প্রক্রিয়াকরণের জন্য সর্বোচ্চ চেষ্টার সংখ্যা
        $maxAttempts = 5;
        // প্রতিটি চেষ্টার মধ্যে অপেক্ষা করার সময় (মিলিসেকেন্ড)
        $delayBetweenAttempts = 1000; // 1 সেকেন্ড

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                DB::transaction(function () use ($pattern, $game, $attempt, $maxAttempts) {
                    // এই প্যাটার্নের জন্য পুরস্কার ইতিমধ্যে প্রক্রিয়া করা হয়েছে কিনা তা পরীক্ষা করুন
                    $alreadyProcessed = Winner::where('game_id', $this->games_Id)
                        ->where('pattern', $pattern)
                        ->where('prize_processed', true)
                        ->exists();

                    if ($alreadyProcessed) {
                        Log::info("প্যাটার্ন $pattern এর জন্য পুরস্কার ইতিমধ্যে প্রক্রিয়া করা হয়েছে।");
                        return; // ট্রানজেকশন থেকে বেরিয়ে যান
                    }

                    // এই প্যাটার্নের জন্য সমস্ত বিজয়ী যারা তাদের পুরস্কার প্রক্রিয়া করেননি
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

                    // পুরস্কারের পরিমাণ গণনা করুন
                    $totalPrizeAmount = $this->getPrizeAmountForPattern($game, $pattern);
                    $prizePerWinner = $totalPrizeAmount / $numberOfWinners;

                    Log::info("প্যাটার্ন $pattern এর জন্য পুরস্কার প্রক্রিয়া করা হচ্ছে। মোট পুরস্কার: $totalPrizeAmount, বিজয়ী: $numberOfWinners, প্রতি বিজয়ীর পুরস্কার: $prizePerWinner");

                    // সিস্টেম ব্যবহারকারীকে পান
                    $systemUser = User::where('role', 'admin')->first();

                    if (!$systemUser) {
                        throw new \Exception('সিস্টেম ব্যবহারকারী পাওয়া যায়নি');
                    }

                    // সিস্টেম ব্যবহারকারীর কাছ থেকে মোট পুরস্কারের পরিমাণ একবার কেটে নিন
                    $systemUser->decrement('credit', $totalPrizeAmount);

                    // সিস্টেম ডেবিট লেনদেন তৈরি করুন
                    Transaction::create([
                        'user_id' => $systemUser->id,
                        'type' => 'debit',
                        'amount' => $totalPrizeAmount,
                        'details' => 'গেম: ' . $game->title . ' এ ' . $pattern . ' এর জন্য পুরস্কার (' . $numberOfWinners . ' বিজয়ীর মধ্যে ভাগ করা হয়েছে)',
                    ]);

                    // প্রতিটি বিজয়ীকে প্রক্রিয়া করুন
                    foreach ($winners as $winner) {
                        $winnerUser = $winner->user;

                        if (!$winnerUser) {
                            Log::error('বিজয়ী রেকর্ডের জন্য বিজয়ী ব্যবহারকারী পাওয়া যায়নি: ' . $winner->id);
                            continue;
                        }

                        // বিজয়ীকে ভাগ করা পুরস্কারের পরিমাণ যোগ করুন
                        $winnerUser->increment('credit', $prizePerWinner);

                        // প্রকৃত পুরস্কারের পরিমাণ সহ বিজয়ী রেকর্ড আপডেট করুন
                        $winner->prize_amount = $prizePerWinner;
                        $winner->prize_processed = true;
                        $winner->save();

                        Log::info("ব্যবহারকারী {$winnerUser->id} এর জন্য পুরস্কার প্রক্রিয়া করা হয়েছে: $prizePerWinner ক্রেডিট প্যাটার্ন $pattern এর জন্য");

                        // বিজয়ী ক্রেডিট লেনদেন তৈরি করুন
                        Transaction::create([
                            'user_id' => $winnerUser->id,
                            'type' => 'credit',
                            'amount' => $prizePerWinner,
                            'details' => 'গেম: ' . $game->title . ' এ ' . $pattern . ' জিতেছে' . ($numberOfWinners > 1 ? ' (' . ($numberOfWinners - 1) . ' অন্যান্য বিজয়ীর সাথে ভাগ করা হয়েছে)' : ''),
                        ]);

                        // বিজয়ীকে নোটিফিকেশন পাঠান
                        $notificationMessage = $numberOfWinners > 1
                            ? 'আপনি গেম: ' . $game->title . ' এ ' . $pattern . ' এর জন্য ' . $prizePerWinner . ' ক্রেডিট জিতেছেন (' . ($numberOfWinners - 1) . ' অন্যান্য বিজয়ীর সাথে ভাগ করা হয়েছে)'
                            : 'আপনি গেম: ' . $game->title . ' এ ' . $pattern . ' এর জন্য ' . $prizePerWinner . ' ক্রেডিট জিতেছেন';

                        Notification::send($winnerUser, new CreditTransferred($notificationMessage));

                        if ($winner->user_id == Auth::id()) {
                            $this->textNote = $notificationMessage;
                             $this->sentNotification = true;
                        }else{
                             $this->textNote = '';
                             $this->sentNotification = false;
                        }
                    }

                    // সিস্টেম ব্যবহারকারীকে নোটিফিকেশন পাঠান
                    $systemNotificationMessage = 'প্যাটার্ন ' . $pattern . ' এর জন্য ' . $totalPrizeAmount . ' ক্রেডিট পুরস্কার দেওয়া হয়েছে (' . $numberOfWinners . ' বিজয়ীর মধ্যে ভাগ করা হয়েছে)';
                    Notification::send($systemUser, new CreditTransferred($systemNotificationMessage));

                    Log::info("প্যাটার্ন $pattern এর জন্য সমস্ত পুরস্কার সফলভাবে প্রক্রিয়া করা হয়েছে। মোট বিজয়ী: $numberOfWinners");

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

<?php

namespace App\Livewire\Backend;

use App\Models\Game;
use App\Models\Ticket;
use App\Models\Prize;
use App\Models\User;
use App\Models\Winner;
use App\Models\Announcement;
use App\Models\Transaction;
use App\Events\NumberAnnounced;
use App\Events\WinnerAnnouncedEvent;
use App\Events\GameOverEvent;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Events\GameRedirectEvent;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreditTransferred;

class NumberAnnouncer extends Component
{
    use WithPagination;

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
    public $gameOver = false;

    public $textNote;
    public $gameOverAllart = false;
    public $winnerAllart = false;
    public $redirectUrl;
    public $ridirectAllart = false;

    public $sheetTickets = [];
    public $participantsUsers;
    public $newParticipants;
    public $selectedUser;
    public $search = '';
    public $users_id;
    public $unique_id = '';
    public $announcedNumbers = [];
    public $sheet_Id;
    public $games_Id;

    protected $listeners = [
        'echo:game.*,game.winner' => 'handleWinnerAnnounced',
        'echo:game.*,game.over' => 'handleGameOver',
        'updateProgress' => 'updateTransferProgress',
        'transfer-completed' => 'onTransferCompleted',
        'process-delayed-prizes' => 'processDelayedPrizes'
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function redirectAllPlayers()
    {
        $tickets = Ticket::where('game_id', $this->gameId)
                    ->selectRaw("user_id, SUBSTRING_INDEX(ticket_number, '-', 1) as sheet_id")
                    ->pluck('sheet_id', 'user_id')
                    ->toArray();

        Log::info('Redirecting all players', [
            'game_id' => $this->gameId,
            'tickets' => $tickets,
        ]);

        broadcast(new GameRedirectEvent($this->gameId))->toOthers();
        $this->ridirectAllart = true;
    }

    public function handleWinnerAnnounced($payload = null)
    {
        Log::info('handleWinnerAnnounced called', [
            'payload' => $payload,
            'game_id' => $this->gameId,
            'method' => 'handleWinnerAnnounced'
        ]);

        $this->winners = Winner::where('game_id', $this->gameId)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        Log::info('Winners loaded', ['winners_count' => $this->winners->count()]);

        $this->winnerAllart = true;
        $this->dispatch('winnerAnnounced', ['winners' => $this->winners]);
        $this->dispatch('winnerAllartMakeFalse');
    }

    public function handleGameOver($data)
    {
        Log::info('Game over event received', ['payload' => $data]);
        $this->winners = Winner::where('game_id', $this->gameId)
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
        $this->gameOverAllart = true;
        $this->gameOver = true;
    }

    public function winnerAllartMakeFalseMethod()
    {
        $this->winnerAllart = false;
    }

    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->games_Id = $gameId;
        $this->game = Game::findOrFail($gameId);
        $this->calledNumbers = Announcement::where('game_id', $gameId)->pluck('number')->toArray();
        $this->loadStatistics();
        $this->checkGameOver();
    }

    public function updated($property)
    {
        if ($property === 'newParticipants') {
            $this->dispatch('updateSelect2');
        }
    }

    public function setUserSheet($userId)
    {
        $this->users_id = $userId;
        $user = User::find($userId);
        $this->unique_id = $user->unique_id . "'s Sheet";
        $this->showSheet();
    }

    public function showSheet()
    {
        $this->announcedNumbers = Announcement::where('game_id', $this->gameId)
            ->pluck('number')
            ->toArray();

        $this->sheetTickets = Ticket::selectRaw('
            id,
            user_id,
            ticket_number,
            numbers,
            is_winner,
            created_at,
            game_id,
            winning_patterns,
            SUBSTRING_INDEX(ticket_number, "-", 1) as sheet_id
        ')
        ->where('user_id', $this->users_id)
        ->where('game_id', $this->gameId)
        ->orderBy('ticket_number')
        ->with('game')
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
                'sheet_id' => $ticket->sheet_id,
                'number' => $ticket->ticket_number,
                'numbers' => is_string($ticket->numbers)
                    ? json_decode($ticket->numbers, true)
                    : $ticket->numbers,
                'is_winner' => $ticket->is_winner,
                'winning_patterns' => $winningPatterns,
                'created_at' => $ticket->created_at
                    ? $ticket->created_at->format('d M Y h:i A')
                    : null,
                'game' => $ticket->game,
            ];
        })
        ->toArray();
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

    private function checkGameOver()
    {
        $claimedPatternsCount = Winner::where('game_id', $this->gameId)
            ->distinct('pattern')
            ->count('pattern');

        $this->gameOver = ($claimedPatternsCount >= 5);

        if ($this->gameOver) {
            $this->dispatchGlobalGameOverEvent();
        }

        return $this->gameOver;
    }

    private function dispatchGlobalGameOverEvent()
    {
        broadcast(new GameOverEvent($this->gameId))->toOthers();

        $this->winners = Winner::where('game_id', $this->gameId)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        $this->dispatch('showGameOverModal', [
            'winners' => $this->winners,
            'message' => 'গেম শেষ! সকল প্যাটার্ন ক্লেইম করা হয়েছে।',
            'title' => 'গেম সমাপ্ত'
        ]);
    }

    protected function loadStatistics()
    {
        $this->participantsUsers = Ticket::with('user')
                    ->where('game_id', $this->gameId)
                    ->get()
                    ->unique('user_id');

        $this->totalParticipants = Ticket::where('game_id', $this->gameId)
                                    ->distinct('user_id')
                                    ->count('user_id');

        $this->sheetsId = Ticket::where('game_id', $this->gameId)
                            ->selectRaw("SUBSTRING_INDEX(ticket_number, '-', 1) as sheet_id")
                            ->groupBy('sheet_id')
                            ->pluck('sheet_id');

        $this->totalSheetsSold = Ticket::where('game_id', $this->gameId)
            ->selectRaw("SUBSTRING_INDEX(ticket_number, '-', 1) as sheet_id")
            ->groupBy('sheet_id')
            ->get()
            ->count();

        $this->totalSalesAmount = $this->totalSheetsSold * $this->game->ticket_price;

        $game = Game::find($this->gameId);

        if ($game) {
            $this->totalPrizeAmount =
                ($game->corner_prize ?? 0) +
                ($game->top_line_prize ?? 0) +
                ($game->middle_line_prize ?? 0) +
                ($game->bottom_line_prize ?? 0) +
                ($game->full_house_prize ?? 0);
        } else {
            $this->totalPrizeAmount = 0;
        }

        $this->participants = Ticket::with(['user', 'game'])
                                ->where('game_id', $this->gameId)
                                ->select('user_id', 'game_id')
                                ->selectRaw('count(*) as ticket_count')
                                ->groupBy('user_id', 'game_id')
                                ->get();

        $this->winners = Winner::with(['user', 'ticket'])
                          ->where('game_id', $this->gameId)
                          ->orderByDesc('won_at')
                          ->get();
    }

    public function announceNumber()
    {
        $number = $this->selectedNumber;
        $this->checkGameOver();

        if ($this->gameOver) {
            session()->flash('error', 'Game Over.');
            return;
        }

        if (!$number) {
            session()->flash('error', 'Please select a number first.');
            return;
        }

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

            // Broadcast number announced event
            broadcast(new NumberAnnounced($this->gameId, $number))->toOthers();

            $this->dispatch('closeNumberModalAfterDelay');

            // Add to called numbers
            $this->calledNumbers[] = $number;
            $this->selectedNumber = null;

            // Check for winners immediately after announcing number
            $this->checkWinnersForAllTickets();

            session()->flash('success', "Number $number announced successfully!");
        } catch (\Exception $e) {
            Log::error("Error announcing number: " . $e->getMessage());
            session()->flash('error', "Error announcing number: " . $e->getMessage());
        }

        $this->showSheet();
    }

    // IMPROVED METHOD: Check winners for all tickets in the game - allowing multiple pattern wins per ticket
    private function checkWinnersForAllTickets()
    {
        if ($this->checkGameOver()) {
            return;
        }

        Log::info("Starting winner check for game: {$this->gameId}");

        // Get all tickets for this game
        $allTickets = Ticket::where('game_id', $this->gameId)
            ->with('user')
            ->get();

        Log::info("Checking {$allTickets->count()} tickets for winners");

        // Check each pattern separately
        $patterns = ['corner', 'top_line', 'middle_line', 'bottom_line', 'full_house'];

        foreach ($patterns as $pattern) {
            // Skip if pattern already claimed globally
            if ($this->isPatternClaimedInGame($pattern)) {
                Log::info("Pattern $pattern already claimed globally, skipping");
                continue;
            }

            $patternWinners = [];

            // Check each ticket for this pattern
            foreach ($allTickets as $ticket) {
                // Check if this specific ticket has already won this specific pattern
                if ($this->hasTicketWonPattern($ticket, $pattern)) {
                    Log::info("Ticket {$ticket->id} already won pattern $pattern, skipping");
                    continue;
                }

                $ticketNumbers = is_string($ticket->numbers)
                    ? json_decode($ticket->numbers, true)
                    : $ticket->numbers;

                $isWinner = false;

                switch ($pattern) {
                    case 'corner':
                        $isWinner = $this->checkCornerNumbers($ticketNumbers);
                        break;
                    case 'top_line':
                        $isWinner = $this->checkTopLine($ticketNumbers);
                        break;
                    case 'middle_line':
                        $isWinner = $this->checkMiddleLine($ticketNumbers);
                        break;
                    case 'bottom_line':
                        $isWinner = $this->checkBottomLine($ticketNumbers);
                        break;
                    case 'full_house':
                        $isWinner = $this->checkFullHouse($ticketNumbers);
                        break;
                }

                if ($isWinner) {
                    $patternWinners[] = $ticket;
                    Log::info("Found NEW winner for pattern $pattern: User {$ticket->user_id}, Ticket {$ticket->id}");
                }
            }

            // Process winners for this pattern
            if (!empty($patternWinners)) {
                Log::info("Processing " . count($patternWinners) . " NEW winners for pattern: $pattern");
                $this->processWinnersForPattern($pattern, $patternWinners);
            }
        }
    }

    // NEW METHOD: Check if a specific ticket has already won a specific pattern
    private function hasTicketWonPattern($ticket, $pattern)
    {
        // Check in Winner table if this ticket has already won this pattern
        $hasWonInWinnerTable = Winner::where('game_id', $this->gameId)
            ->where('ticket_id', $ticket->id)
            ->where('pattern', $pattern)
            ->exists();

        if ($hasWonInWinnerTable) {
            return true;
        }

        // Also check in ticket's winning_patterns field if it exists
        if (Schema::hasColumn('tickets', 'winning_patterns') && $ticket->winning_patterns) {
            $winningPatterns = is_string($ticket->winning_patterns)
                ? json_decode($ticket->winning_patterns, true)
                : $ticket->winning_patterns;

            if (is_array($winningPatterns) && in_array($pattern, $winningPatterns)) {
                return true;
            }
        }

        return false;
    }

    // Check if pattern is already claimed globally in the game
    private function isPatternClaimedInGame($pattern)
    {
        return Winner::where('game_id', $this->gameId)
            ->where('pattern', $pattern)
            ->exists();
    }

    // Check corner numbers
    private function checkCornerNumbers($numbers)
    {
        if (!is_array($numbers) || count($numbers) < 3) {
            return false;
        }

        $topRow = $numbers[0] ?? [];
        $bottomRow = $numbers[2] ?? [];

        // Find corners
        $topLeft = null;
        $topRight = null;
        $bottomLeft = null;
        $bottomRight = null;

        // Find top left corner
        for ($i = 0; $i < 9; $i++) {
            if (isset($topRow[$i]) && $topRow[$i] !== null) {
                $topLeft = $topRow[$i];
                break;
            }
        }

        // Find top right corner
        for ($i = 8; $i >= 0; $i--) {
            if (isset($topRow[$i]) && $topRow[$i] !== null) {
                $topRight = $topRow[$i];
                break;
            }
        }

        // Find bottom left corner
        for ($i = 0; $i < 9; $i++) {
            if (isset($bottomRow[$i]) && $bottomRow[$i] !== null) {
                $bottomLeft = $bottomRow[$i];
                break;
            }
        }

        // Find bottom right corner
        for ($i = 8; $i >= 0; $i--) {
            if (isset($bottomRow[$i]) && $bottomRow[$i] !== null) {
                $bottomRight = $bottomRow[$i];
                break;
            }
        }

        $corners = array_filter([$topLeft, $topRight, $bottomLeft, $bottomRight]);

        // Check if all corners are called
        foreach ($corners as $corner) {
            if (!in_array($corner, $this->calledNumbers)) {
                return false;
            }
        }

        return count($corners) >= 4;
    }

    // Check top line
    private function checkTopLine($numbers)
    {
        if (!is_array($numbers) || !isset($numbers[0])) {
            return false;
        }
        return $this->checkLine($numbers[0]);
    }

    // Check middle line
    private function checkMiddleLine($numbers)
    {
        if (!is_array($numbers) || !isset($numbers[1])) {
            return false;
        }
        return $this->checkLine($numbers[1]);
    }

    // Check bottom line
    private function checkBottomLine($numbers)
    {
        if (!is_array($numbers) || !isset($numbers[2])) {
            return false;
        }
        return $this->checkLine($numbers[2]);
    }

    // Check a line
    private function checkLine($line)
    {
        if (!is_array($line)) {
            return false;
        }

        $lineNumbers = array_filter($line, function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($lineNumbers)) {
            return false;
        }

        foreach ($lineNumbers as $number) {
            if (!in_array($number, $this->calledNumbers)) {
                return false;
            }
        }

        return true;
    }

    // Check full house
    private function checkFullHouse($numbers)
    {
        if (!is_array($numbers) || count($numbers) < 3) {
            return false;
        }

        for ($i = 0; $i < 3; $i++) {
            if (!$this->checkLine($numbers[$i])) {
                return false;
            }
        }

        return true;
    }

    // IMPROVED METHOD: Process all winners for a specific pattern at once - allowing multiple wins per ticket
    private function processWinnersForPattern($pattern, $winnerTickets)
    {
        if (empty($winnerTickets)) {
            return;
        }

        $game = Game::find($this->gameId);
        if (!$game) {
            Log::error("Game not found: {$this->gameId}");
            return;
        }

        try {
            DB::transaction(function () use ($pattern, $winnerTickets, $game) {
                // Double-check that this pattern hasn't been claimed yet globally
                $existingWinners = Winner::where('game_id', $this->gameId)
                    ->where('pattern', $pattern)
                    ->count();

                if ($existingWinners > 0) {
                    Log::info("Pattern $pattern already has winners globally, skipping");
                    return;
                }

                $numberOfWinners = count($winnerTickets);
                $totalPrizeAmount = $this->getPrizeAmountForPattern($game, $pattern);
                $prizePerWinner = $numberOfWinners > 0 ? round($totalPrizeAmount / $numberOfWinners, 2) : 0;

                Log::info("Processing $numberOfWinners winners for pattern $pattern. Total prize: $totalPrizeAmount, Prize per winner: $prizePerWinner");

                // Get system user
                $systemUser = User::where('role', 'admin')->first();
                if (!$systemUser) {
                    Log::error('System admin user not found');
                    return;
                }

                // Create all winner records and update tickets
                foreach ($winnerTickets as $ticket) {
                    // Get existing winning patterns for this ticket
                    $existingPatterns = [];
                    if (Schema::hasColumn('tickets', 'winning_patterns') && $ticket->winning_patterns) {
                        $existingPatterns = is_string($ticket->winning_patterns)
                            ? json_decode($ticket->winning_patterns, true)
                            : $ticket->winning_patterns;
                    }

                    if (!is_array($existingPatterns)) {
                        $existingPatterns = [];
                    }

                    // Add new pattern to existing patterns (avoid duplicates)
                    if (!in_array($pattern, $existingPatterns)) {
                        $existingPatterns[] = $pattern;
                    }

                    // Update ticket with new winning pattern
                    $ticket->update([
                        'is_winner' => true, // Mark as winner if not already
                        'winning_patterns' => Schema::hasColumn('tickets', 'winning_patterns') ? $existingPatterns : null
                    ]);

                    // Create winner record for this specific pattern
                    Winner::create([
                        'user_id' => $ticket->user_id,
                        'game_id' => $this->gameId,
                        'ticket_id' => $ticket->id,
                        'pattern' => $pattern,
                        'won_at' => now(),
                        'prize_amount' => $prizePerWinner,
                        'prize_processed' => true
                    ]);

                    Log::info("Created winner record for user {$ticket->user_id}, ticket {$ticket->id}, pattern: $pattern, prize: $prizePerWinner. Total patterns won by this ticket: " . count($existingPatterns));
                }

                // Process prize distribution
                if ($prizePerWinner > 0) {
                    // Deduct total prize from system user
                    $systemUser->decrement('credit', $totalPrizeAmount);

                    // Create system debit transaction
                    Transaction::create([
                        'user_id' => $systemUser->id,
                        'type' => 'debit',
                        'amount' => $totalPrizeAmount,
                        'details' => "Prize distribution for $pattern in game: {$game->title} (shared among $numberOfWinners winners)",
                    ]);

                    // Add prize to each winner's account
                    foreach ($winnerTickets as $ticket) {
                        $winnerUser = User::find($ticket->user_id);

                        if ($winnerUser) {
                            // Add prize to winner's account
                            $winnerUser->increment('credit', $prizePerWinner);

                            // Create winner credit transaction
                            Transaction::create([
                                'user_id' => $winnerUser->id,
                                'type' => 'credit',
                                'amount' => $prizePerWinner,
                                'details' => $numberOfWinners > 1
                                    ? "Won $pattern in game: {$game->title} (shared with " . ($numberOfWinners - 1) . " other winners)"
                                    : "Won $pattern in game: {$game->title}",
                            ]);

                            // Send notification to winner
                            $notificationMessage = $numberOfWinners > 1
                                ? "Congratulations! You won $prizePerWinner credits for $pattern in game: {$game->title} (shared with " . ($numberOfWinners - 1) . " other winners)"
                                : "Congratulations! You won $prizePerWinner credits for $pattern in game: {$game->title}";

                            try {
                                Notification::send($winnerUser, new CreditTransferred($notificationMessage));
                            } catch (\Exception $e) {
                                Log::error("Failed to send notification to user {$winnerUser->id}: " . $e->getMessage());
                            }

                            Log::info("Added $prizePerWinner credits to user {$winnerUser->id} for pattern $pattern (may have multiple pattern wins)");
                        }
                    }

                    // Send notification to system user
                    $systemNotificationMessage = "Prize of $totalPrizeAmount credits distributed for $pattern pattern among $numberOfWinners winners in game: {$game->title}";
                    try {
                        Notification::send($systemUser, new CreditTransferred($systemNotificationMessage));
                    } catch (\Exception $e) {
                        Log::error("Failed to send notification to system user: " . $e->getMessage());
                    }
                }

                Log::info("Successfully processed all $numberOfWinners winners for pattern $pattern. Total prize distributed: $totalPrizeAmount");

            }, 5); // Retry attempts for deadlock

            // Broadcast winner event after successful processing
            $this->dispatchGlobalWinnerEvent();

        } catch (\Exception $e) {
            Log::error("Error processing winners for pattern $pattern: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }

    // Dispatch global winner event
    private function dispatchGlobalWinnerEvent()
    {
        try {
            broadcast(new WinnerAnnouncedEvent($this->gameId))->toOthers();
            Log::info('WinnerAnnouncedEvent dispatched successfully');
        } catch (\Exception $e) {
            Log::error("WinnerBroadcasting failed: " . $e->getMessage());
        }

        $this->winnerSelfAnnounced();
    }

    // Winner self announced
    public function winnerSelfAnnounced()
    {
        Log::info('winnerSelfAnnounced called');
        $this->winners = Winner::where('game_id', $this->gameId)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();
        $this->winnerAllart = true;
        $this->dispatch('winnerAllartMakeFalse');
    }

    // Process delayed prizes (kept for compatibility)
    public function processDelayedPrizes($data)
    {
        // This method is kept for compatibility but not used in the new flow
        Log::info("processDelayedPrizes called but not used in new flow");
    }

    // Get prize amount for pattern
    private function getPrizeAmountForPattern($game, $pattern)
    {
        return match ($pattern) {
            'corner' => $game->corner_prize ?? 0,
            'top_line' => $game->top_line_prize ?? 0,
            'middle_line' => $game->middle_line_prize ?? 0,
            'bottom_line' => $game->bottom_line_prize ?? 0,
            'full_house' => $game->full_house_prize ?? 0,
            default => 0,
        };
    }

    public function callNextNumber()
    {
        $available = collect(range(1, 90))->diff($this->calledNumbers)->values();

        if ($this->gameOver) {
            session()->flash('error', 'Game Over.');
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
        $newParticipantsUser = User::whereHas('tickets', function ($query) {
            $query->where('game_id', $this->gameId);
        })
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('unique_id', 'like', '%' . $this->search . '%')
                ->orWhere('name', 'like', '%' . $this->search . '%')
                ->orWhere('last_login_location', 'like', '%' . $this->search . '%');
            });
        })
        ->select('id', 'unique_id', 'name', 'avatar', 'last_login_location','is_online')
        ->paginate(10);

        return view('livewire.backend.number-announcer', [
                'newParticipantsUser' => $newParticipantsUser
            ])->layout('livewire.backend.base');
    }
}

<?php

namespace App\Livewire\Frontend;

use App\Models\Announcement;
use App\Models\Ticket;
use App\Models\Winner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Game;
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

    protected $listeners = [
        'echo:game.*,number.announced' => 'handleNumberAnnounced',
        'echo:game.*,game.winner' => 'handleWinnerAnnounced',
        'echo:game.*,game.over' => 'handleGameOver',
        'numberAnnounced' => 'onNumberReceived',
        'updateProgress' => 'updateTransferProgress',
        'transfer-completed' => 'onTransferCompleted',
        'tick' => 'updateTimer'
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

        // Reload winners from database
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();

        Log::info('Winners loaded', ['winners_count' => $this->winners->count()]);

        $this->winnerAllart = true;

        // Reload tickets to show updated winning status
        $this->loadNumbers();

        $this->dispatch('winnerAnnounced', ['winners' => $this->winners]);
        $this->dispatch('winnerAllartMakeFalse');
        $this->getWinnerPattarns();
        $this->checkWinners();
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

        // Reload the numbers and tickets from database
        $this->loadNumbers();

        if ($number) {
            $this->dispatch('numberAnnounced', number: $number);
        }
    }

    public function testWinnerHandler()
    {
        Log::info('Test winner handler called manually');
        $this->handleWinnerAnnounced(['test' => 'data']);
    }

    public function winnerSelfAnnounced()
    {
        Log::info('winnerSelfAnnounced called');
        $this->winners = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();
        //$this->winnerAllart = true;
        $this->dispatch('winnerAllartDispay');
        // $this->dispatch('winnerAllartMakeFalse');
        $this->getWinnerPattarns();
    }

    public function getWinnerPattarns()
    {
        $this->winnerPattarns = Winner::where('game_id', $this->games_Id)
            ->with('user')
            ->orderByDesc('won_at')
            ->get();
    }

     public function winnerAllartShowMethod()
    {
        $this->winnerAllart = true;
        $this->dispatch('winnerAllartMakeFalse');
    }

    public function winnerAllartMakeFalseMethod()
    {
        $this->winnerAllart = false;
    }

    public function handleGameOver($data)
    {
        Log::info('Game over event received', ['payload' => $data]);
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
            $this->gameOverSelfAnnounced();
        }

        return $this->gameOver;
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
            $this->dispatch('numberAnnounced', number: $number);
        }
    }

    public function render()
    {
        return view('livewire.frontend.game-room')->layout('livewire.layout.frontend.base');
    }
}

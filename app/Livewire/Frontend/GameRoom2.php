<?php

namespace App\Livewire\Frontend;

use App\Models\Announcement;
use App\Models\Ticket;
use App\Models\Winner;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class GameRoom extends Component
{
    public $games_Id;
    public $sheet_Id;
    public $announcedNumbers = [];
    public $userTickets = [];
    public $sheetTickets = [];
    public $winningPatterns = [];

    protected $listeners = [
        'numberReceived' => 'onNumberReceived',
        'echo:game.*,number.announced' => 'handleNumberAnnounced'
    ];

    public function mount($gameId, $sheetId = null)
    {
        $this->sheet_Id = $sheetId;
        $this->games_Id = $gameId;
        $this->loadNumbers();
        $this->initWinningPatterns();
    }

    private function initWinningPatterns()
    {
        $this->winningPatterns = [
            'corner' => [
                'name' => 'Corner Numbers',
                'claimed' => false,
                'description' => 'All 4 corners of the ticket'
            ],
            'top_line' => [
                'name' => 'Top Line',
                'claimed' => false,
                'description' => 'Complete top row'
            ],
            'middle_line' => [
                'name' => 'Middle Line',
                'claimed' => false,
                'description' => 'Complete middle row'
            ],
            'bottom_line' => [
                'name' => 'Bottom Line',
                'claimed' => false,
                'description' => 'Complete bottom row'
            ],
            'full_house' => [
                'name' => 'Full House',
                'claimed' => false,
                'description' => 'All numbers on the ticket'
            ]
        ];
    }

    public function handleNumberAnnounced($event)
    {
        $this->loadNumbers();
        $this->checkWinners();
    }

    public function onNumberReceived($number)
    {
        $this->announcedNumbers[] = $number;
        $this->checkWinners();
    }

    public function loadNumbers()
    {
        $this->announcedNumbers = Announcement::where('game_id', $this->games_Id)->pluck('number')->toArray();
        $this->sheetTickets = Ticket::where('user_id', Auth::id())
            ->where('ticket_number', 'LIKE', $this->sheet_Id . '-%')
            ->orderBy('ticket_number')
            ->get()
            ->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'number' => $ticket->ticket_number,
                    'numbers' => is_string($ticket->numbers)
                                ? json_decode($ticket->numbers, true)
                                : $ticket->numbers,
                    'is_winner' => $ticket->is_winner,
                    'winning_patterns' => $ticket->winning_patterns ?? [],
                    'created_at' => $ticket->created_at->format('d M Y h:i A'),
                    'game' => $ticket->game,
                ];
            })
            ->toArray();
    }

    public function checkWinners()
    {
        foreach ($this->sheetTickets as $index => $ticket) {
            $ticketId = $ticket['id'];
            $numbers = $ticket['numbers'];
            $winningPatterns = [];

            // Check for corner numbers
            if ($this->checkCornerNumbers($numbers)) {
                $winningPatterns[] = 'corner';
            }

            // Check for top line
            if ($this->checkTopLine($numbers)) {
                $winningPatterns[] = 'top_line';
            }

            // Check for middle line
            if ($this->checkMiddleLine($numbers)) {
                $winningPatterns[] = 'middle_line';
            }

            // Check for bottom line
            if ($this->checkBottomLine($numbers)) {
                $winningPatterns[] = 'bottom_line';
            }

            // Check for full house
            if ($this->checkFullHouse($numbers)) {
                $winningPatterns[] = 'full_house';
            }

            // Update ticket if it's a winner
            if (!empty($winningPatterns)) {
                $this->updateTicketWinningStatus($ticketId, $winningPatterns);
                $this->sheetTickets[$index]['is_winner'] = true;
                $this->sheetTickets[$index]['winning_patterns'] = $winningPatterns;

                // Show notification for each winning pattern
                foreach ($winningPatterns as $pattern) {
                    if (isset($this->winningPatterns[$pattern]) && !$this->winningPatterns[$pattern]['claimed']) {
                        $this->winningPatterns[$pattern]['claimed'] = true;
                        $this->dispatchBrowserEvent('winner-alert', [
                            'title' => 'Congratulations!',
                            'message' => 'You won ' . $this->winningPatterns[$pattern]['name'] . '!',
                            'pattern' => $pattern
                        ]);
                    }
                }
            }
        }
    }

    private function checkCornerNumbers($numbers)
    {
        // Get the 4 corners of the ticket
        $corners = [
            $numbers[0][0],  // Top-left
            $numbers[0][8],  // Top-right
            $numbers[2][0],  // Bottom-left
            $numbers[2][8]   // Bottom-right
        ];

        // Filter out null values (empty cells)
        $corners = array_filter($corners, function($value) {
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

    private function updateTicketWinningStatus($ticketId, $winningPatterns)
    {
        $ticket = Ticket::find($ticketId);

        if ($ticket) {
            $ticket->is_winner = true;
            $ticket->winning_patterns = json_encode($winningPatterns);
            $ticket->save();

            // Optionally record the win in a winners table
            foreach ($winningPatterns as $pattern) {
                // Check if this win is already recorded
                $existingWin = Winner::where('ticket_id', $ticketId)
                    ->where('pattern', $pattern)
                    ->first();

                if (!$existingWin) {
                    Winner::create([
                        'user_id' => Auth::id(),
                        'game_id' => $this->games_Id,
                        'ticket_id' => $ticketId,
                        'pattern' => $pattern,
                        'won_at' => now()
                    ]);
                }
            }
        }
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

<?php

namespace App\Livewire\Frontend\Hajari;

use App\Models\HajariGame;
use App\Models\HajariGameParticipant;
use App\Models\HajariGameMove;
use App\Models\Transaction;
use App\Events\GameUpdated;
use App\Events\CardPlayed;
use App\Events\ScoreUpdated;
use App\Events\GameWinner;
use App\Events\RoundWinner;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HajariGameRoom extends Component
{
    public HajariGame $game;
    public $player;
    public $selectedCards = [];
    public $gameState = [];
    public $currentTurn = 1;
    public $round = 1;
    public $playedCards = [];
    public $currentPlayer = null;
    public $gameLog = [];
    public $showScoreModal = false;
    public $showWinnerModal = false;
    public $scoreData = [];
    public $winnerData = [];

    protected $listeners = [
        'refreshGame' => '$refresh',
        'echo-presence:game.{game.id},GameUpdated' => 'handleGameUpdate',
        'echo-presence:game.{game.id},CardPlayed' => 'handleCardPlayed',
        'echo-presence:game.{game.id},ScoreUpdated' => 'handleScoreUpdate',
        'echo-presence:game.{game.id},GameWinner' => 'handleGameWinner',
        'echo-presence:game.{game.id},RoundWinner' => 'handleRoundWinner',
    ];

    public function mount(HajariGame $game)
    {
        $this->game = $game;
        $this->player = $game->participants()->where('user_id', Auth::id())->first();

        if (!$this->player) {
            abort(403, 'You are not a participant in this game.');
        }

        $this->loadGameState();

        if ($game->status === HajariGame::STATUS_PENDING && $game->canStart()) {
            $this->startGame();
        }
    }

    public function loadGameState()
    {
        $currentRound = $this->game->moves()->max('round') ?? 1;
        $currentTurn = $this->getCurrentTurn($currentRound);

        $this->gameState = [
            'current_round' => $currentRound,
            'current_turn' => $currentTurn,
            'played_cards' => $this->getPlayedCardsForCurrentRound($currentRound),
        ];

        // Refresh player data
        $this->player->refresh();

        // Refresh game data
        $this->game->refresh();
        $this->game->load(['participants.user']);
    }

    public function getCurrentTurn($round)
    {
        $movesInRound = $this->game->moves()
            ->where('round', $round)
            ->count();

        // If 4 moves completed, start new round
        if ($movesInRound >= 4) {
            return 1; // Start new round
        }

        return $movesInRound + 1;
    }

    public function getPlayedCardsForCurrentRound($round)
    {
        return $this->game->moves()
            ->where('round', $round)
            ->with('player')
            ->orderBy('turn_order')
            ->get()
            ->map(function ($move) {
                return [
                    'player' => $move->player->name,
                    'player_id' => $move->player->id,
                    'cards' => $move->cards_played,
                    'turn' => $move->turn_order,
                    'points' => $move->points_earned
                ];
            })
            ->toArray();
    }

    public function getPlayerPosition($playerId)
    {
        $participant = $this->game->participants()->where('user_id', $playerId)->first();
        return $participant ? $participant->position : 1;
    }

    // Card arrangement methods
    public function reorderCards($fromIndex, $toIndex)
    {
        if (!$this->player || !$this->player->cards) return;

        $cards = $this->player->cards;

        // Remove the card from original position
        $cardToMove = array_splice($cards, $fromIndex, 1)[0];

        // Insert at new position
        array_splice($cards, $toIndex, 0, [$cardToMove]);

        // Update player's cards
        $this->player->update(['cards' => $cards]);
        $this->player->refresh();

        // Update selected cards indices
        $this->updateSelectedCardsAfterReorder($fromIndex, $toIndex);
    }

    private function updateSelectedCardsAfterReorder($fromIndex, $toIndex)
    {
        $newSelectedCards = [];

        foreach ($this->selectedCards as $selectedIndex) {
            if ($selectedIndex == $fromIndex) {
                // The moved card goes to new position
                $newSelectedCards[] = $toIndex;
            } elseif ($fromIndex < $toIndex) {
                // Moving right
                if ($selectedIndex > $fromIndex && $selectedIndex <= $toIndex) {
                    $newSelectedCards[] = $selectedIndex - 1;
                } else {
                    $newSelectedCards[] = $selectedIndex;
                }
            } else {
                // Moving left
                if ($selectedIndex >= $toIndex && $selectedIndex < $fromIndex) {
                    $newSelectedCards[] = $selectedIndex + 1;
                } else {
                    $newSelectedCards[] = $selectedIndex;
                }
            }
        }

        $this->selectedCards = $newSelectedCards;
    }

    public function sortCardsBySuit()
    {
        if (!$this->player || !$this->player->cards) return;

        $cards = collect($this->player->cards)->sortBy(function ($card) {
            $suitOrder = ['spades' => 4, 'hearts' => 3, 'diamonds' => 2, 'clubs' => 1];
            return ($suitOrder[$card['suit']] * 100) + $card['value'];
        })->values()->toArray();

        $this->player->update(['cards' => $cards]);
        $this->player->refresh();

        // Clear selection after sorting
        $this->selectedCards = [];
    }

    public function sortCardsByRank()
    {
        if (!$this->player || !$this->player->cards) return;

        $cards = collect($this->player->cards)->sortBy(function ($card) {
            return $card['value'];
        })->values()->toArray();

        $this->player->update(['cards' => $cards]);
        $this->player->refresh();

        // Clear selection after sorting
        $this->selectedCards = [];
    }

    public function startGame()
    {
        if (!$this->game->canStart()) {
            session()->flash('error', 'Cannot start game. Not enough players or game already started.');
            return;
        }

        try {
            DB::transaction(function () {
                // Generate and distribute cards
                $deck = $this->game->generateDeck();
                $participants = $this->game->participants()
                    ->whereIn('status', [
                        HajariGameParticipant::STATUS_JOINED,
                        HajariGameParticipant::STATUS_ACCEPTED
                    ])
                    ->get();

                if ($participants->count() !== $this->game->max_players) {
                    throw new \Exception('Not enough players to start the game.');
                }

                $cardsPerPlayer = 13;
                $cardIndex = 0;

                foreach ($participants as $participant) {
                    $playerCards = array_slice($deck, $cardIndex, $cardsPerPlayer);

                    // Update participant with proper status
                    $participant->update([
                        'cards' => $playerCards,
                        'status' => HajariGameParticipant::STATUS_PLAYING,
                        'total_points' => 0,
                        'rounds_won' => 0,
                        'round_scores' => [],
                        'hazari_count' => 0
                    ]);

                    $participant->sortCards();
                    $cardIndex += $cardsPerPlayer;
                }

                $this->game->update(['status' => HajariGame::STATUS_PLAYING]);
            });

            // Broadcast game start
            broadcast(new GameUpdated($this->game, 'game_started', [
                'message' => 'Game has started! Cards have been distributed.'
            ]));

            $this->loadGameState();
            session()->flash('success', 'Game started successfully!');

        } catch (\Exception $e) {
            Log::error('Error starting game: ' . $e->getMessage());
            session()->flash('error', 'Failed to start game: ' . $e->getMessage());
        }
    }

    public function toggleCardSelection($cardIndex)
    {
        if (in_array($cardIndex, $this->selectedCards)) {
            $this->selectedCards = array_filter($this->selectedCards, fn($i) => $i !== $cardIndex);
        } else {
            $this->selectedCards[] = $cardIndex;
        }
    }

    public function playCards()
    {
        if (empty($this->selectedCards)) {
            session()->flash('error', 'Please select cards to play.');
            return;
        }

        if (!$this->isPlayerTurn()) {
            session()->flash('error', 'It\'s not your turn.');
            return;
        }

        if (!$this->isValidMove()) {
            session()->flash('error', 'Invalid move according to Hazari rules.');
            return;
        }

        try {
            $playedCards = $this->processMove();

            broadcast(new CardPlayed(
                $this->game,
                Auth::user(),
                $playedCards,
                $this->gameState['current_round'],
                $this->gameState['current_turn']
            ));

            $this->selectedCards = [];

            // Check if round is complete (all 4 players played)
            if ($this->isRoundComplete()) {
                $this->calculateRoundWinner();
            }

            $this->checkGameProgress();
            $this->loadGameState(); // Refresh state after move

        } catch (\Exception $e) {
            Log::error('Error playing cards: ' . $e->getMessage());
            session()->flash('error', 'Failed to play cards: ' . $e->getMessage());
        }
    }

    private function isPlayerTurn(): bool
    {
        $currentTurnPlayer = $this->game->participants()
            ->where('position', $this->gameState['current_turn'])
            ->first();

        return $currentTurnPlayer && $currentTurnPlayer->user_id === Auth::id();
    }

    private function isValidMove(): bool
    {
        if (empty($this->selectedCards)) {
            return false;
        }

        if ($this->gameState['current_turn'] === 1) {
            return true;
        }

        $firstMove = collect($this->gameState['played_cards'])->first();
        if (!$firstMove) {
            return true;
        }

        return $this->validateAgainstFirstMove($firstMove['cards']);
    }

    private function validateAgainstFirstMove(array $firstCards): bool
    {
        $selectedCardObjects = [];
        foreach ($this->selectedCards as $index) {
            $selectedCardObjects[] = $this->player->cards[$index];
        }

        if (count($selectedCardObjects) !== count($firstCards)) {
            return false;
        }

        return true;
    }

    private function processMove(): array
    {
        $playedCards = [];
        foreach ($this->selectedCards as $cardIndex) {
            $playedCards[] = $this->player->cards[$cardIndex];
        }

        $points = $this->calculateMovePoints($playedCards);
        $scoreType = $this->getScoreType($playedCards);

        // Create the move record
        HajariGameMove::create([
            'hajari_game_id' => $this->game->id,
            'player_id' => Auth::id(),
            'round' => $this->gameState['current_round'],
            'turn_order' => $this->gameState['current_turn'],
            'cards_played' => $playedCards,
            'points_earned' => $points
        ]);

        // Remove played cards from player's hand
        $remainingCards = [];
        foreach ($this->player->cards as $index => $card) {
            if (!in_array($index, $this->selectedCards)) {
                $remainingCards[] = $card;
            }
        }

        $this->player->update(['cards' => $remainingCards]);

        return $playedCards;
    }

    private function calculateMovePoints(array $cards): int
    {
        $points = 0;
        foreach ($cards as $card) {
            $points += match($card['rank']) {
                'A' => 14,
                'K' => 13,
                'Q' => 12,
                'J' => 11,
                default => (int) $card['rank']
            };
        }

        // Bonus points for special combinations
        if ($this->isHazariCombination($cards)) {
            $points += 50; // Hazari bonus
        }

        return $points;
    }

    private function getScoreType(array $cards): string
    {
        if ($this->isHazariCombination($cards)) {
            return 'hazari';
        }
        return 'normal';
    }

    private function isHazariCombination(array $cards): bool
    {
        // Check for Hazari combinations (sequence, same suit, etc.)
        if (count($cards) < 3) return false;

        // Check for sequence
        $ranks = array_map(fn($card) => $card['value'], $cards);
        sort($ranks);

        $isSequence = true;
        for ($i = 1; $i < count($ranks); $i++) {
            if ($ranks[$i] !== $ranks[$i-1] + 1) {
                $isSequence = false;
                break;
            }
        }

        // Check for same suit
        $suits = array_unique(array_map(fn($card) => $card['suit'], $cards));
        $isSameSuit = count($suits) === 1;

        return $isSequence && $isSameSuit;
    }

    private function isRoundComplete(): bool
    {
        $movesInCurrentRound = $this->game->moves()
            ->where('round', $this->gameState['current_round'])
            ->count();

        return $movesInCurrentRound >= 4;
    }

    private function calculateRoundWinner()
    {
        $roundMoves = $this->game->moves()
            ->where('round', $this->gameState['current_round'])
            ->with('player')
            ->get();

        $winner = $roundMoves->sortByDesc('points_earned')->first();

        if ($winner) {
            $participant = $this->game->participants()
                ->where('user_id', $winner->player_id)
                ->first();

            if ($participant) {
                // Update winner's stats
                $participant->increment('rounds_won');
                $participant->increment('total_points', $winner->points_earned);

                if ($this->getScoreType($winner->cards_played) === 'hazari') {
                    $participant->increment('hazari_count');
                }

                // Update round scores
                $roundScores = $participant->round_scores ?? [];
                $roundScores[] = [
                    'round' => $this->gameState['current_round'],
                    'points' => $winner->points_earned,
                    'type' => $this->getScoreType($winner->cards_played)
                ];
                $participant->update(['round_scores' => $roundScores]);

                // Broadcast round winner for animation
                broadcast(new RoundWinner(
                    $this->game,
                    $participant,
                    $this->gameState['current_round']
                ));

                // Broadcast score update
                broadcast(new ScoreUpdated(
                    $this->game,
                    $participant,
                    $winner->points_earned,
                    $this->gameState['current_round'],
                    $this->getScoreType($winner->cards_played)
                ));
            }
        }
    }

    private function checkGameProgress()
    {
        $playersWithCards = $this->game->participants()
            ->where('status', HajariGameParticipant::STATUS_PLAYING)
            ->get()
            ->filter(function ($participant) {
                return is_array($participant->cards) && count($participant->cards) > 0;
            })
            ->count();

        if ($playersWithCards === 0) {
            $this->endGame();
        }
    }

    private function endGame()
    {
        $winner = $this->calculateWinner();

        $this->game->update([
            'status' => HajariGame::STATUS_COMPLETED,
            'winner_id' => $winner->user_id
        ]);

        // Update all participants status
        $this->game->participants()->update([
            'status' => HajariGameParticipant::STATUS_FINISHED
        ]);

        // Get final scores
        $finalScores = $this->game->participants()
            ->with('user')
            ->get()
            ->map(function ($participant) {
                return [
                    'user_id' => $participant->user_id,
                    'name' => $participant->user->name,
                    'total_points' => $participant->total_points,
                    'rounds_won' => $participant->rounds_won,
                    'hazari_count' => $participant->hazari_count,
                    'position' => $participant->position
                ];
            })
            ->sortByDesc('total_points')
            ->values()
            ->toArray();

        $this->processGamePayments($winner);

        // Broadcast game winner
        broadcast(new GameWinner($this->game, $winner, $finalScores));
    }

    private function calculateWinner()
    {
        return $this->game->participants()
            ->orderByDesc('total_points')
            ->orderByDesc('rounds_won')
            ->orderByDesc('hazari_count')
            ->first();
    }

    private function processGamePayments($winner)
    {
        $bidAmount = $this->game->bid_amount;
        $participants = $this->game->participants()->get();

        DB::transaction(function () use ($winner, $bidAmount, $participants) {
            $winnerAmount = $bidAmount * 4;

            Transaction::create([
                'user_id' => $winner->user_id,
                'type' => 'credit',
                'amount' => $winnerAmount,
                'details' => 'Game win: ' . $this->game->title,
            ]);

            $winner->user->increment('credit', $winnerAmount);

            foreach ($participants as $participant) {
                if ($participant->user_id !== $winner->user_id) {
                    Transaction::create([
                        'user_id' => $participant->user_id,
                        'type' => 'debit',
                        'amount' => $bidAmount,
                        'details' => 'Game loss: ' . $this->game->title,
                    ]);

                    $participant->user->decrement('credit', $bidAmount);
                }
            }
        });
    }

    // Real-time event handlers
    public function handleGameUpdate($event)
    {
        $this->loadGameState();
        $this->dispatch('gameUpdated', $event);
    }

    public function handleCardPlayed($event)
    {
        $this->loadGameState();
        $this->gameLog[] = [
            'type' => 'card_played',
            'player' => $event['player_name'],
            'cards' => $event['cards'],
            'timestamp' => $event['timestamp']
        ];
        $this->dispatch('cardPlayed', $event);
    }

    public function handleScoreUpdate($event)
    {
        $this->scoreData = $event;
        $this->showScoreModal = true;
        $this->loadGameState(); // Refresh to show updated scores

        // Auto hide after 3 seconds
        $this->dispatch('hideScoreModal');
    }

    public function handleRoundWinner($event)
    {
        $this->dispatch('roundWinner', [
            'winner_position' => $event['winner_position'],
            'winner_name' => $event['winner_name'],
            'round' => $event['round']
        ]);
    }

    public function handleGameWinner($event)
    {
        $this->winnerData = $event;
        $this->showWinnerModal = true;
    }

    public function closeScoreModal()
    {
        $this->showScoreModal = false;
    }

    public function closeWinnerModal()
    {
        $this->showWinnerModal = false;
    }

    public function render()
    {
        return view('livewire.frontend.hajari.hajari-game-room')
            ->layout('livewire.layout.frontend.game-room', [
                'title' => $this->game->title . ' - Game Room'
            ]);
    }
}

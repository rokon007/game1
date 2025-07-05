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
    public $roundWinner = null;

    protected $listeners = [
        'refreshGame' => '$refresh',
        'echo-presence:game.{game.id},GameUpdated' => 'handleGameUpdate',
        'echo-presence:game.{game.id},CardPlayed' => 'handleCardPlayed',
        'echo-presence:game.{game.id},ScoreUpdated' => 'handleScoreUpdate',
        'echo-presence:game.{game.id},GameWinner' => 'handleGameWinner',
        'echo-presence:game.{game.id},RoundWinner' => 'handleRoundWinner',
    ];

    // fixing problems ------------------------------
     public function loadGameState()
    {
        $currentRound = $this->game->moves()->max('round') ?? 1;
        $movesInCurrentRound = $this->game->moves()->where('round', $currentRound)->count();

        // টার্ন নির্ধারণ
        if ($movesInCurrentRound >= 4) {
            // রাউন্ড সম্পন্ন, পরবর্তী রাউন্ডের জন্য টার্ন নির্ধারণ
            $nextRoundStarter = $this->getRoundWinnerPosition($currentRound);
            $currentRound++;
            $currentTurn = $this->getPlayerTurnOrder($nextRoundStarter ?? 1);
            $playedCards = []; // নতুন রাউন্ডের জন্য কার্ড ক্লিয়ার
        } else {
            // বর্তমান রাউন্ডে টার্ন নির্ধারণ
            $currentTurn = $this->getCurrentTurnInRound($currentRound);
            $playedCards = $this->getPlayedCardsForCurrentRound($currentRound);
        }

        $this->gameState = [
            'current_round' => $currentRound,
            'current_turn' => $currentTurn,
            'played_cards' => $playedCards,
        ];

        // প্লেয়ার এবং গেম ডেটা রিফ্রেশ
        $this->player->refresh();
        $this->game->refresh();
        $this->game->load(['participants.user']);

        // নতুন কার্ড ডিল চেক
        $this->checkForNewCardDeal();
    }

    private function getCurrentTurnInRound($round)
    {
        $movesInRound = $this->game->moves()->where('round', $round)->count();

        if ($movesInRound === 0) {
            // রাউন্ডের প্রথম মুভ
            if ($round > 1) {
                // আগের রাউন্ডের বিজয়ী শুরু করবে
                $previousRoundWinner = $this->getRoundWinnerPosition($round - 1);
                return $this->getPlayerTurnOrder($previousRoundWinner ?? 1);
            } else {
                // প্রথম রাউন্ড, ক্রিয়েটর শুরু করবে
                $creator = $this->game->participants()->where('user_id', $this->game->creator_id)->first();
                return $this->getPlayerTurnOrder($creator ? $creator->position : 1);
            }
        }

        // শেষ মুভ থেকে পরবর্তী প্লেয়ার নির্ধারণ
        $lastMove = $this->game->moves()
            ->where('round', $round)
            ->orderBy('turn_order', 'desc')
            ->first();

        if ($lastMove) {
            $lastPlayerPosition = $this->getPlayerPosition($lastMove->player_id);
            return $this->getNextPlayerTurnOrder($lastPlayerPosition);
        }

        return 1; // ফলব্যাক
    }

    private function getNextPlayerTurnOrder($currentPosition)
    {
        $nextPosition = $currentPosition + 1;
        if ($nextPosition > 4) {
            $nextPosition = 1; // পজিশন ১ এ ফিরে যায়
        }

        // পরবর্তী পজিশনে প্লেয়ার আছে কিনা চেক
        $participant = $this->game->participants()->where('position', $nextPosition)->first();
        if ($participant && $participant->status === HajariGameParticipant::STATUS_PLAYING) {
            return $nextPosition;
        }

        // যদি পজিশন খালি থাকে, পরবর্তী সক্রিয় প্লেয়ার খুঁজে বের করা
        for ($i = 1; $i <= 4; $i++) {
            $participant = $this->game->participants()->where('position', $i)->first();
            if ($participant && $participant->status === HajariGameParticipant::STATUS_PLAYING) {
                return $i;
            }
        }

        return 1; // ফলব্যাক
    }

    private function processMove(): array
    {
        $playedCards = [];
        foreach ($this->selectedCards as $cardIndex) {
            if (isset($this->player->cards[$cardIndex])) {
                $playedCards[] = $this->player->cards[$cardIndex];
            }
        }

        if (empty($playedCards)) {
            throw new \Exception('No valid cards to play');
        }

        $points = $this->calculateMovePoints($playedCards);
        $scoreType = $this->getScoreType($playedCards);

        // মুভ রেকর্ড করা
        HajariGameMove::create([
            'hajari_game_id' => $this->game->id,
            'player_id' => Auth::id(),
            'round' => $this->gameState['current_round'],
            'turn_order' => $this->gameState['current_turn'],
            'cards_played' => $playedCards,
            'points_earned' => $points
        ]);

        // প্লেয়ারের হাত থেকে কার্ড সরানো
        $remainingCards = [];
        foreach ($this->player->cards as $index => $card) {
            if (!in_array($index, $this->selectedCards)) {
                $remainingCards[] = $card;
            }
        }

        $this->player->update(['cards' => array_values($remainingCards)]);

        // টার্ন আপডেট
        $this->gameState['current_turn'] = $this->getNextPlayerTurnOrder($this->gameState['current_turn']);

        // গেম রিফ্রেশ
        $this->game->refresh();

        return $playedCards;
    }

    private function checkForNewCardDeal()
    {
        // সব প্লেয়ারের কার্ড শেষ কিনা চেক
        $playersWithCards = $this->game->participants()
            ->where('status', HajariGameParticipant::STATUS_PLAYING)
            ->get()
            ->filter(function ($participant) {
                return is_array($participant->cards) && count($participant->cards) > 0;
            })
            ->count();

        if ($playersWithCards === 0 && $this->game->status === HajariGame::STATUS_PLAYING) {
            $this->dealNewCards();
            // নতুন কার্ড ডিলের পর টার্ন রিসেট
            $lastRound = $this->game->moves()->max('round') ?? 1;
            $lastRoundWinner = $this->getRoundWinnerPosition($lastRound);
            $this->gameState['current_turn'] = $this->getPlayerTurnOrder($lastRoundWinner ?? 1);
            $this->gameState['current_round']++;
        }
    }

    private function getRoundWinnerPosition($round)
    {
        $roundMoves = $this->game->moves()
            ->where('round', $round)
            ->with('player')
            ->get();

        if ($roundMoves->isEmpty()) return null;

        // প্রথম পয়েন্ট পাওয়া প্লেয়ার নির্ধারণ
        $winner = $roundMoves->filter(function ($move) {
            return $move->points_earned > 0;
        })->sortBy('turn_order')->first();

        if ($winner) {
            $participant = $this->game->participants()
                ->where('user_id', $winner->player_id)
                ->first();
            return $participant ? $participant->position : null;
        }

        return null;
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

        // প্রথম পয়েন্ট পাওয়া প্লেয়ার
        $winner = $roundMoves->filter(function ($move) {
            return $move->points_earned > 0;
        })->sortBy('turn_order')->first();

        if ($winner) {
            $participant = $this->game->participants()
                ->where('user_id', $winner->player_id)
                ->first();

            if ($participant) {
                $participant->increment('rounds_won');
                $participant->increment('total_points', $winner->points_earned);

                if ($this->getScoreType($winner->cards_played) === 'hazari') {
                    $participant->increment('hazari_count');
                }

                $roundScores = $participant->round_scores ?? [];
                $roundScores[] = [
                    'round' => $this->gameState['current_round'],
                    'points' => $winner->points_earned,
                    'type' => $this->getScoreType($winner->cards_played)
                ];
                $participant->update(['round_scores' => $roundScores]);

                // রাউন্ড বিজয়ী ব্রডকাস্ট
                broadcast(new RoundWinner(
                    $this->game,
                    $participant,
                    $this->gameState['current_round']
                ));

                // স্কোর আপডেট ব্রডকাস্ট
                broadcast(new ScoreUpdated(
                    $this->game,
                    $participant,
                    $winner->points_earned,
                    $this->gameState['current_round'],
                    $this->getScoreType($winner->cards_played)
                ));

                // পরবর্তী রাউন্ডের জন্য টার্ন সেট
                $this->gameState['current_turn'] = $this->getPlayerTurnOrder($participant->position);
            }
        }
    }

    public function toggleCardSelection($cardIndex)
    {
        if (in_array($cardIndex, $this->selectedCards)) {
            // কার্ড ডিসিলেক্ট
            $this->selectedCards = array_filter($this->selectedCards, fn($i) => $i !== $cardIndex);
        } else {
            // কার্ড সিলেকশন লিমিট চেক
            $currentCardCount = count($this->player->cards ?? []);
            $maxSelection = $this->getMaxSelectionLimit($currentCardCount);

            if (count($this->selectedCards) < $maxSelection) {
                $this->selectedCards[] = $cardIndex;
            } else {
                session()->flash('error', "You can only select maximum {$maxSelection} cards at a time.");
                return;
            }
        }
        $this->selectedCards = array_values($this->selectedCards);
    }

    private function getMaxSelectionLimit($cardCount)
    {
        // যদি ৪টি বা তার কম কার্ড থাকে, তবে সব কার্ড সিলেক্ট করা যাবে
        if ($cardCount <= 4) {
            return $cardCount;
        }
        // অন্যথায়, সর্বোচ্চ ৩টি কার্ড
        return 3;
    }

    private function isValidMove(): bool
    {
        if (empty($this->selectedCards)) {
            return false;
        }

        $currentCardCount = count($this->player->cards ?? []);

        // যদি হাতে ৪টি বা তার কম কার্ড থাকে, তবে সব কার্ড সাবমিট করতে হবে
        if ($currentCardCount <= 4) {
            if (count($this->selectedCards) !== $currentCardCount) {
                return false;
            }
        } else {
            // অন্যথায়, ঠিক ৩টি কার্ড সাবমিট করতে হবে
            if (count($this->selectedCards) !== 3) {
                return false;
            }
        }

        // কার্ডগুলো বৈধ কিনা চেক
        foreach ($this->selectedCards as $index) {
            if (!isset($this->player->cards[$index])) {
                return false;
            }
        }

        // প্রথম মুভ হলে যেকোনো কার্ড খেলা যাবে
        if (empty($this->gameState['played_cards'])) {
            return true;
        }

        $firstMove = collect($this->gameState['played_cards'])->first();
        if (!$firstMove) {
            return true;
        }

        // প্রথম মুভের সাথে কার্ড সংখ্যা মিলানো
        if (count($this->selectedCards) !== count($firstMove['cards'])) {
            return false;
        }

        return $this->validateAgainstFirstMove($firstMove['cards']);
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

        // কার্ড সাবমিশন বৈধ কিনা চেক
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

            // রাউন্ড সম্পন্ন কিনা চেক
            if ($this->isRoundComplete()) {
                $this->calculateRoundWinner();
            }

            $this->checkGameProgress();
            $this->loadGameState(); // রিফ্রেশ স্টেট

        } catch (\Exception $e) {
            Log::error('Error playing cards: ' . $e->getMessage());
            session()->flash('error', 'Failed to play cards: ' . $e->getMessage());
        }
    }



    // fixing problems ------------------------------

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

    // public function loadGameState()
    // {
    //     $currentRound = $this->game->moves()->max('round') ?? 1;
    //     $movesInCurrentRound = $this->game->moves()->where('round', $currentRound)->count();

    //     // Enhanced turn management logic with proper completion check
    //     if ($movesInCurrentRound >= 4) {
    //         // Round is complete, determine next round starter
    //         $nextRoundStarter = $this->getRoundWinnerPosition($currentRound);
    //         $currentRound++;
    //         $currentTurn = $this->getPlayerTurnOrder($nextRoundStarter ?? 1);
    //         $playedCards = []; // Clear for new round
    //     } else {
    //         // Continue current round - get next player in sequence
    //         $currentTurn = $this->getCurrentTurnInRound($currentRound);
    //         $playedCards = $this->getPlayedCardsForCurrentRound($currentRound);
    //     }

    //     $this->gameState = [
    //         'current_round' => $currentRound,
    //         'current_turn' => $currentTurn,
    //         'played_cards' => $playedCards,
    //     ];

    //     // Refresh player data
    //     $this->player->refresh();

    //     // Refresh game data
    //     $this->game->refresh();
    //     $this->game->load(['participants.user']);

    //     // Check if we need to deal new cards
    //     $this->checkForNewCardDeal();
    // }

    // Enhanced turn management - get current turn in round
    // private function getCurrentTurnInRound($round)
    // {
    //     $movesInRound = $this->game->moves()->where('round', $round)->count();

    //     if ($movesInRound === 0) {
    //         // First move of the round
    //         if ($round > 1) {
    //             // Get previous round winner to start this round
    //             $previousRoundWinner = $this->getRoundWinnerPosition($round - 1);
    //             return $this->getPlayerTurnOrder($previousRoundWinner ?? 1);
    //         } else {
    //             // Very first round - creator starts
    //             $creator = $this->game->participants()->where('user_id', $this->game->creator_id)->first();
    //             return $this->getPlayerTurnOrder($creator ? $creator->position : 1);
    //         }
    //     }

    //     // Get the last move and determine next player
    //     $lastMove = $this->game->moves()
    //         ->where('round', $round)
    //         ->orderBy('turn_order', 'desc')
    //         ->first();

    //     if ($lastMove) {
    //         $lastPlayerPosition = $this->getPlayerPosition($lastMove->player_id);
    //         return $this->getNextPlayerTurnOrder($lastPlayerPosition);
    //     }

    //     return 1; // Fallback
    // }

    // Get player turn order (1-4) based on position
    private function getPlayerTurnOrder($position)
    {
        // Ensure position is within valid range
        return max(1, min(4, $position));
    }

    // Get next player's turn order
    // private function getNextPlayerTurnOrder($currentPosition)
    // {
    //     $nextPosition = $currentPosition + 1;
    //     if ($nextPosition > 4) {
    //         $nextPosition = 1;
    //     }

    //     // Verify this position exists in the game
    //     $participant = $this->game->participants()->where('position', $nextPosition)->first();
    //     if ($participant) {
    //         return $nextPosition;
    //     }

    //     // If position doesn't exist, find the next available position
    //     for ($i = 1; $i <= 4; $i++) {
    //         $participant = $this->game->participants()->where('position', $i)->first();
    //         if ($participant) {
    //             return $i;
    //         }
    //     }

    //     return 1; // Fallback
    // }

    // private function getRoundWinnerPosition($round)
    // {
    //     $roundMoves = $this->game->moves()
    //         ->where('round', $round)
    //         ->with('player')
    //         ->get();

    //     if ($roundMoves->isEmpty()) return null;

    //     $winner = $roundMoves->sortByDesc('points_earned')->first();
    //     if ($winner) {
    //         $participant = $this->game->participants()
    //             ->where('user_id', $winner->player_id)
    //             ->first();
    //         return $participant ? $participant->position : null;
    //     }

    //     return null;
    // }

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
                    'points' => $move->points_earned,
                    'position' => $this->getPlayerPosition($move->player->id)
                ];
            })
            ->toArray();
    }

    public function getPlayerPosition($playerId)
    {
        $participant = $this->game->participants()->where('user_id', $playerId)->first();
        return $participant ? $participant->position : 1;
    }

    public function getCurrentTurnPlayer()
    {
        $currentTurn = $this->gameState['current_turn'] ?? 1;

        return $this->game->participants()
            ->where('position', $currentTurn)
            ->first();
    }

    // Enhanced card deal checking
    // private function checkForNewCardDeal()
    // {
    //     // Check if all players have no cards
    //     $playersWithCards = $this->game->participants()
    //         ->where('status', HajariGameParticipant::STATUS_PLAYING)
    //         ->get()
    //         ->filter(function ($participant) {
    //             return is_array($participant->cards) && count($participant->cards) > 0;
    //         })
    //         ->count();

    //     // If no players have cards and game is still playing, deal new cards
    //     if ($playersWithCards === 0 && $this->game->status === HajariGame::STATUS_PLAYING) {
    //         $this->dealNewCards();
    //     }
    // }

    private function dealNewCards()
    {
        try {
            DB::transaction(function () {
                // Generate new deck
                $deck = $this->game->generateDeck();
                $participants = $this->game->participants()
                    ->where('status', HajariGameParticipant::STATUS_PLAYING)
                    ->get();

                if ($participants->count() === 0) {
                    return; // No players to deal to
                }

                $cardsPerPlayer = 13;
                $cardIndex = 0;

                foreach ($participants as $participant) {
                    $playerCards = array_slice($deck, $cardIndex, $cardsPerPlayer);

                    $participant->update(['cards' => $playerCards]);
                    $participant->sortCards();
                    $cardIndex += $cardsPerPlayer;
                }
            });

            // Broadcast new card deal
            broadcast(new GameUpdated($this->game, 'new_cards_dealt', [
                'message' => 'New cards have been dealt!'
            ]));

            Log::info('New cards dealt for game: ' . $this->game->id);

        } catch (\Exception $e) {
            Log::error('Error dealing new cards: ' . $e->getMessage());
        }
    }

    // Card arrangement methods
    public function reorderCards($fromIndex, $toIndex)
    {
        if (!$this->player || !$this->player->cards) return;

        $cards = $this->player->cards;

        // Validate indices
        if ($fromIndex < 0 || $fromIndex >= count($cards) ||
            $toIndex < 0 || $toIndex >= count($cards)) {
            return;
        }

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

        $this->selectedCards = array_values(array_unique($newSelectedCards));
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

    // Enhanced card selection with limits and better validation
    // public function toggleCardSelection($cardIndex)
    // {
    //     if (in_array($cardIndex, $this->selectedCards)) {
    //         // Remove from selection
    //         $this->selectedCards = array_filter($this->selectedCards, fn($i) => $i !== $cardIndex);
    //     } else {
    //         // Check selection limits
    //         $currentCardCount = count($this->player->cards ?? []);
    //         $maxSelection = $this->getMaxSelectionLimit($currentCardCount);

    //         if (count($this->selectedCards) < $maxSelection) {
    //             $this->selectedCards[] = $cardIndex;
    //         } else {
    //             session()->flash('error', "You can only select maximum {$maxSelection} cards at a time.");
    //             return;
    //         }
    //     }
    //     $this->selectedCards = array_values($this->selectedCards);
    // }

    // Enhanced selection limit logic
    // private function getMaxSelectionLimit($cardCount)
    // {
    //     // If 4 or fewer cards remain, allow selecting all
    //     if ($cardCount <= 4) {
    //         return $cardCount;
    //     }
    //     // Otherwise, maximum 3 cards
    //     return 3;
    // }

    // Enhanced play cards with better validation
    // public function playCards()
    // {
    //     if (empty($this->selectedCards)) {
    //         session()->flash('error', 'Please select cards to play.');
    //         return;
    //     }

    //     if (!$this->isPlayerTurn()) {
    //         session()->flash('error', 'It\'s not your turn.');
    //         return;
    //     }

    //     // Enhanced validation for card submission
    //     if (!$this->isValidMove()) {
    //         session()->flash('error', 'Invalid move according to Hazari rules.');
    //         return;
    //     }

    //     // Additional validation for final cards
    //     $remainingCards = count($this->player->cards ?? []);
    //     if ($remainingCards <= 4 && count($this->selectedCards) !== $remainingCards) {
    //         session()->flash('error', 'You must play all remaining cards when you have 4 or fewer cards.');
    //         return;
    //     }

    //     try {
    //         $playedCards = $this->processMove();

    //         broadcast(new CardPlayed(
    //             $this->game,
    //             Auth::user(),
    //             $playedCards,
    //             $this->gameState['current_round'],
    //             $this->gameState['current_turn']
    //         ));

    //         $this->selectedCards = [];

    //         // Check if round is complete (all 4 players played)
    //         if ($this->isRoundComplete()) {
    //             $this->calculateRoundWinner();
    //         }

    //         $this->checkGameProgress();
    //         $this->loadGameState(); // Refresh state after move

    //     } catch (\Exception $e) {
    //         Log::error('Error playing cards: ' . $e->getMessage());
    //         session()->flash('error', 'Failed to play cards: ' . $e->getMessage());
    //     }
    // }

    private function isPlayerTurn(): bool
    {
        $currentTurnPlayer = $this->getCurrentTurnPlayer();
        return $currentTurnPlayer && $currentTurnPlayer->user_id === Auth::id();
    }

    // Enhanced move validation
    // private function isValidMove(): bool
    // {
    //     if (empty($this->selectedCards)) {
    //         return false;
    //     }

    //     // Validate selected cards exist
    //     foreach ($this->selectedCards as $index) {
    //         if (!isset($this->player->cards[$index])) {
    //             return false;
    //         }
    //     }

    //     // First move of the round can be anything
    //     if (empty($this->gameState['played_cards'])) {
    //         return true;
    //     }

    //     $firstMove = collect($this->gameState['played_cards'])->first();
    //     if (!$firstMove) {
    //         return true;
    //     }

    //     return $this->validateAgainstFirstMove($firstMove['cards']);
    // }

    private function validateAgainstFirstMove(array $firstCards): bool
    {
        $selectedCardObjects = [];
        foreach ($this->selectedCards as $index) {
            if (isset($this->player->cards[$index])) {
                $selectedCardObjects[] = $this->player->cards[$index];
            }
        }

        // Must play same number of cards as first player
        if (count($selectedCardObjects) !== count($firstCards)) {
            return false;
        }

        return true;
    }

    // Enhanced move processing
    // private function processMove(): array
    // {
    //     $playedCards = [];
    //     foreach ($this->selectedCards as $cardIndex) {
    //         if (isset($this->player->cards[$cardIndex])) {
    //             $playedCards[] = $this->player->cards[$cardIndex];
    //         }
    //     }

    //     if (empty($playedCards)) {
    //         throw new \Exception('No valid cards to play');
    //     }

    //     $points = $this->calculateMovePoints($playedCards);
    //     $scoreType = $this->getScoreType($playedCards);

    //     // Create the move record with proper turn order
    //     HajariGameMove::create([
    //         'hajari_game_id' => $this->game->id,
    //         'player_id' => Auth::id(),
    //         'round' => $this->gameState['current_round'],
    //         'turn_order' => $this->gameState['current_turn'],
    //         'cards_played' => $playedCards,
    //         'points_earned' => $points
    //     ]);

    //     // Remove played cards from player's hand
    //     $remainingCards = [];
    //     foreach ($this->player->cards as $index => $card) {
    //         if (!in_array($index, $this->selectedCards)) {
    //             $remainingCards[] = $card;
    //         }
    //     }

    //     $this->player->update(['cards' => array_values($remainingCards)]);

    //     // Force refresh game state to update turn immediately
    //     $this->game->refresh();

    //     return $playedCards;
    // }

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

    // private function isRoundComplete(): bool
    // {
    //     $movesInCurrentRound = $this->game->moves()
    //         ->where('round', $this->gameState['current_round'])
    //         ->count();

    //     return $movesInCurrentRound >= 4;
    // }

    // private function calculateRoundWinner()
    // {
    //     $roundMoves = $this->game->moves()
    //         ->where('round', $this->gameState['current_round'])
    //         ->with('player')
    //         ->get();

    //     $winner = $roundMoves->sortByDesc('points_earned')->first();

    //     if ($winner) {
    //         $participant = $this->game->participants()
    //             ->where('user_id', $winner->player_id)
    //             ->first();

    //         if ($participant) {
    //             // Update winner's stats
    //             $participant->increment('rounds_won');
    //             $participant->increment('total_points', $winner->points_earned);

    //             if ($this->getScoreType($winner->cards_played) === 'hazari') {
    //                 $participant->increment('hazari_count');
    //             }

    //             // Update round scores
    //             $roundScores = $participant->round_scores ?? [];
    //             $roundScores[] = [
    //                 'round' => $this->gameState['current_round'],
    //                 'points' => $winner->points_earned,
    //                 'type' => $this->getScoreType($winner->cards_played)
    //             ];
    //             $participant->update(['round_scores' => $roundScores]);

    //             // Broadcast round winner for animation
    //             broadcast(new RoundWinner(
    //                 $this->game,
    //                 $participant,
    //                 $this->gameState['current_round']
    //             ));

    //             // Broadcast score update
    //             broadcast(new ScoreUpdated(
    //                 $this->game,
    //                 $participant,
    //                 $winner->points_earned,
    //                 $this->gameState['current_round'],
    //                 $this->getScoreType($winner->cards_played)
    //             ));
    //         }
    //     }
    // }

    private function checkGameProgress()
    {
        $playersWithCards = $this->game->participants()
            ->where('status', HajariGameParticipant::STATUS_PLAYING)
            ->get()
            ->filter(function ($participant) {
                return is_array($participant->cards) && count($participant->cards) > 0;
            })
            ->count();

        // If no players have cards, check if we should end game or deal new cards
        if ($playersWithCards === 0) {
            // Check if we've played enough rounds to end the game
            $totalRounds = $this->game->moves()->max('round') ?? 0;

            // End game after 13 rounds (all cards played) or based on your game rules
            if ($totalRounds >= 13) {
                $this->endGame();
            } else {
                // Deal new cards for next set of rounds
                $this->dealNewCards();
            }
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

        // Clear center cards after animation
        $this->dispatch('clearCenterCards');
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

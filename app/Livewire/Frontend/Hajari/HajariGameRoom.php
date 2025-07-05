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
                // আগের রাউন্ডের সর্বোচ্চ পয়েন্ট প্রাপ্ত প্লেয়ার শুরু করবে
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
            ->orderBy('id', 'desc')
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

    private function getRoundWinnerPosition($round)
    {
        $roundMoves = $this->game->moves()
            ->where('round', $round)
            ->with('player')
            ->get();

        if ($roundMoves->isEmpty()) return null;

        // প্রথম প্লেয়ারের স্যুট
        $firstMove = $roundMoves->sortBy('id')->first();
        $firstSuit = $firstMove->cards_played[0]['suit'];

        // সর্বোচ্চ পয়েন্টের মুভ খুঁজুন
        $maxPoints = $roundMoves->max('points_earned');
        $candidates = $roundMoves->where('points_earned', $maxPoints);

        if ($candidates->count() > 1) {
            // টাই হলে সিনিয়রিটি চেক
            $winner = $candidates->sortByDesc(function ($move) use ($firstSuit) {
                $highestRank = 0;
                foreach ($move->cards_played as $card) {
                    if ($card['suit'] === $firstSuit) {
                        $rankValue = $this->getRankValue($card['rank']);
                        $highestRank = max($highestRank, $rankValue);
                    }
                }
                // সিনিয়রিটি সমান হলে টার্ন অর্ডার বিবেচনা
                return [$highestRank, $move->turn_order];
            })->first();
        } else {
            $winner = $candidates->first();
        }

        if ($winner) {
            $participant = $this->game->participants()
                ->where('user_id', $winner->player_id)
                ->first();
            return $participant ? $participant->position : null;
        }

        return null;
    }

    private function calculateRoundWinner()
    {
        $roundMoves = $this->game->moves()
            ->where('round', $this->gameState['current_round'])
            ->with('player')
            ->get();

        if ($roundMoves->isEmpty()) return;

        // সকল প্লেয়ারের কার্ডের পয়েন্টের যোগফল
        $totalPoints = $roundMoves->sum('points_earned');

        // প্রথম প্লেয়ারের স্যুট
        $firstMove = $roundMoves->sortBy('id')->first();
        $firstSuit = $firstMove->cards_played[0]['suit'];

        // সর্বোচ্চ পয়েন্টের মুভ খুঁজুন
        $maxPoints = $roundMoves->max('points_earned');
        $candidates = $roundMoves->where('points_earned', $maxPoints);

        if ($candidates->count() > 1) {
            // টাই হলে সিনিয়রিটি চেক
            $winner = $candidates->sortByDesc(function ($move) use ($firstSuit) {
                $highestRank = 0;
                foreach ($move->cards_played as $card) {
                    if ($card['suit'] === $firstSuit) {
                        $rankValue = $this->getRankValue($card['rank']);
                        $highestRank = max($highestRank, $rankValue);
                    }
                }
                // সিনিয়রিটি সমান হলে টার্ন অর্ডার বিবেচনা
                return [$highestRank, $move->turn_order];
            })->first();
        } else {
            $winner = $candidates->first();
        }

        if ($winner) {
            $participant = $this->game->participants()
                ->where('user_id', $winner->player_id)
                ->first();

            if ($participant) {
                $participant->increment('rounds_won');
                $participant->increment('total_points', $totalPoints); // সকল কার্ডের পয়েন্ট যোগ

                $roundScores = $participant->round_scores ?? [];
                $roundScores[] = [
                    'round' => $this->gameState['current_round'],
                    'points' => $totalPoints,
                    'type' => 'normal'
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
                    $totalPoints,
                    $this->gameState['current_round'],
                    'normal'
                ));

                // পরবর্তী রাউন্ডের জন্য টার্ন সেট
                //$this->gameState['current_turn'] = $this->getPlayerTurnOrder($participant->position);

                // লগিং যোগ করুন ডিবাগিংয়ের জন্য
                Log::info('Round Winner', [
                    'round' => $this->gameState['current_round'],
                    'winner_id' => $winner->player_id,
                    'points' => $totalPoints,
                    'position' => $participant->position
                ]);
            }
        }
    }

    private function calculateMovePoints(array $cards): int
    {
        $points = 0;

        // প্রতিটি কার্ডের জন্য পয়েন্ট গণনা
        foreach ($cards as $card) {
            $points += match ($card['rank']) {
                '2', '3', '4', '5', '6', '7', '8', '9' => 5,
                '10', 'J', 'Q', 'K', 'A' => 10,
                default => 0 // অজানা কার্ডের জন্য ডিফল্ট
            };
        }

        return $points;
    }

    private function getRankValue(string $rank): int
    {
        return match ($rank) {
            'A' => 14,
            'K' => 13,
            'Q' => 12,
            'J' => 11,
            '10' => 10,
            '9' => 9,
            '8' => 8,
            '7' => 7,
            '6' => 6,
            '5' => 5,
            '4' => 4,
            '3' => 3,
            '2' => 2,
            default => 0
        };
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
        //$this->gameState['current_turn'] = $this->getNextPlayerTurnOrder($this->gameState['current_turn']);

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

    private function getPlayerTurnOrder($position)
    {
        return max(1, min(4, $position));
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

    private function isRoundComplete(): bool
    {
        $movesInCurrentRound = $this->game->moves()
            ->where('round', $this->gameState['current_round'])
            ->count();

        return $movesInCurrentRound >= 4;
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
        if ($cardCount <= 4) {
            return $cardCount;
        }
        return 3;
    }

    private function isValidMove(): bool
    {
        if (empty($this->selectedCards)) {
            return false;
        }

        $currentCardCount = count($this->player->cards ?? []);

        // কার্ড সিলেকশন লিমিট চেক
        if ($currentCardCount <= 4) {
            if (count($this->selectedCards) !== $currentCardCount) {
                return false;
            }
        } else {
            if (count($this->selectedCards) !== 3) {
                return false;
            }
        }

        // নির্বাচিত কার্ডগুলো প্লেয়ারের হাতে আছে কিনা চেক
        foreach ($this->selectedCards as $index) {
            if (!isset($this->player->cards[$index])) {
                return false;
            }
        }

        return true;
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

            if ($this->isRoundComplete()) {
                $this->calculateRoundWinner();
                $this->checkGameProgress(); // গেম প্রোগ্রেস চেক রাউন্ড শেষে
            }

            $this->loadGameState(); // রিফ্রেশ স্টেট

        } catch (\Exception $e) {
            Log::error('Error playing cards: ' . $e->getMessage());
            session()->flash('error', 'Failed to play cards: ' . $e->getMessage());
        }
    }

    private function isPlayerTurn(): bool
    {
        $currentTurnPlayer = $this->getCurrentTurnPlayer();
        return $currentTurnPlayer && $currentTurnPlayer->user_id === Auth::id();
    }

    public function reorderCards($fromIndex, $toIndex)
    {
        if (!$this->player || !$this->player->cards) return;

        $cards = $this->player->cards;

        if ($fromIndex < 0 || $fromIndex >= count($cards) ||
            $toIndex < 0 || $toIndex >= count($cards)) {
            return;
        }

        $cardToMove = array_splice($cards, $fromIndex, 1)[0];
        array_splice($cards, $toIndex, 0, [$cardToMove]);

        $this->player->update(['cards' => $cards]);
        $this->player->refresh();

        $this->updateSelectedCardsAfterReorder($fromIndex, $toIndex);
    }

    private function updateSelectedCardsAfterReorder($fromIndex, $toIndex)
    {
        $newSelectedCards = [];

        foreach ($this->selectedCards as $selectedIndex) {
            if ($selectedIndex == $fromIndex) {
                $newSelectedCards[] = $toIndex;
            } elseif ($fromIndex < $toIndex) {
                if ($selectedIndex > $fromIndex && $selectedIndex <= $toIndex) {
                    $newSelectedCards[] = $selectedIndex - 1;
                } else {
                    $newSelectedCards[] = $selectedIndex;
                }
            } else {
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

    private function checkGameProgress()
    {
        // রাউন্ড সম্পন্ন হলে পয়েন্ট চেক
        if ($this->isRoundComplete()) {
            $participants = $this->game->participants()
                ->where('status', HajariGameParticipant::STATUS_PLAYING)
                ->get();

            // কোনো প্লেয়ারের পয়েন্ট ১০০০ বা তার বেশি কিনা চেক
            $winner = $participants->firstWhere('total_points', '>=', 1000);

            if ($winner) {
                $this->endGame();
                return;
            }
        }

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
        }
    }

    private function endGame()
    {
        $winner = $this->calculateWinner();

        $this->game->update([
            'status' => HajariGame::STATUS_COMPLETED,
            'winner_id' => $winner->user_id
        ]);

        $this->game->participants()->update([
            'status' => HajariGameParticipant::STATUS_FINISHED
        ]);

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

        // ট্রানজাকশন প্রক্রিয়া এবং নোটিফিকেশন
        $transactions = $this->processGamePayments($winner);

        // গেম উইনার ইভেন্টে ট্রানজাকশনের তথ্য যোগ
        broadcast(new GameWinner($this->game, $winner, $finalScores, $transactions));

        Log::info('Game Ended', [
            'game_id' => $this->game->id,
            'winner_id' => $winner->user_id,
            'winner_name' => $winner->user->name,
            'final_scores' => $finalScores,
            'transactions' => $transactions
        ]);
    }

    private function calculateWinner()
    {
        return $this->game->participants()
            ->where('total_points', '>=', 1000)
            ->orderByDesc('total_points')
            ->first() ?? $this->game->participants()
                ->orderByDesc('total_points')
                ->orderByDesc('rounds_won')
                ->first();
    }

    private function processGamePayments($winner)
    {
        $bidAmount = $this->game->bid_amount;
        $participants = $this->game->participants()->get();
        $transactions = [];

        DB::transaction(function () use ($winner, $bidAmount, $participants, &$transactions) {
            // বিজয়ীর জন্য ক্রেডিট ট্রানজাকশন
            $winnerAmount = $bidAmount * 4;
            Transaction::create([
                'user_id' => $winner->user_id,
                'type' => 'credit',
                'amount' => $winnerAmount,
                'details' => "Game win: {$this->game->title} (Winner: {$winner->user->name})",
            ]);
            $winner->user->increment('credit', $winnerAmount);
            $transactions[] = [
                'user_id' => $winner->user_id,
                'type' => 'credit',
                'amount' => $winnerAmount,
                'details' => "Game win: {$this->game->title}"
            ];

            // অন্য প্লেয়ারদের জন্য ডেবিট ট্রানজাকশন
            foreach ($participants as $participant) {
                if ($participant->user_id !== $winner->user_id) {
                    Transaction::create([
                        'user_id' => $participant->user_id,
                        'type' => 'debit',
                        'amount' => $bidAmount,
                        'details' => "Game loss: {$this->game->title} (Winner: {$winner->user->name})",
                    ]);
                    $participant->user->decrement('credit', $bidAmount);
                    $transactions[] = [
                        'user_id' => $participant->user_id,
                        'type' => 'debit',
                        'amount' => $bidAmount,
                        'details' => "Game loss: {$this->game->title}"
                    ];
                }
            }
        });

        return $transactions;
    }

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
        $this->loadGameState();

        $this->dispatch('hideScoreModal');
    }

    public function handleRoundWinner($event)
    {
        $this->dispatch('roundWinner', [
            'winner_position' => $event['winner_position'],
            'winner_name' => $event['winner_name'],
            'round' => $event['round']
        ]);

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

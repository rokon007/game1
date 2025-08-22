<?php

namespace App\Livewire\Frontend\Hajari;

use App\Models\HajariGame;
use App\Models\HajariGameParticipant;
use App\Models\HajariGameMove;
use App\Models\Transaction;
use App\Models\GameSetting;
use App\Events\GameUpdated;
use App\Events\CardPlayed;
use App\Events\ScoreUpdated;
use App\Events\GameWinner;
use App\Events\RoundWinner;
use App\Events\VoiceChatUpdate;
use App\Events\WrongMove;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
    public $arrangementTimeLeft = 0;
    public $isArrangementPhase = false;
    public $isCardsLocked = false;
    public $canStartGame = false;
    public $isMicEnabled = false;
    public $isPushToTalkMode = true;
    public $speakingPlayers = [];

    // Wrong এর প্রোপার্টি
    public $wrongPlayers = [];
    public $playerPreviousCombinations = [];
    public $showAllWrongModal = false; // নতুন মোডাল কন্ট্রোল করার জন্য

    protected $listeners = [
        'refreshGame' => '$refresh',
        'echo-presence:game.{game.id},GameUpdated' => 'handleGameUpdate',
        'echo-presence:game.{game.id},CardPlayed' => 'handleCardPlayed',
        'echo-presence:game.{game.id},ScoreUpdated' => 'handleScoreUpdate',
        'echo-presence:game.{game.id},GameWinner' => 'handleGameWinner',
        'echo-presence:game.{game.id},RoundWinner' => 'handleRoundWinner',
        'echo-presence:game.{game.id},VoiceChatUpdate' => 'handleVoiceChatUpdate',
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

        // Store previous turn for comparison
        $previousTurn = $this->gameState['current_turn'] ?? null;

        // টার্ন নির্ধারণ
        if ($movesInCurrentRound >= 4) {
            $roundCompletedAt = $this->getRoundCompletionTime($currentRound);
            $shouldClearCards = $roundCompletedAt && now()->diffInSeconds($roundCompletedAt) >= 7;

            if ($shouldClearCards) {
                // রাউন্ড সম্পন্ন এবং ৭ সেকেন্ড পার হয়েছে, পরবর্তী রাউন্ডের জন্য টার্ন নির্ধারণ
                $nextRoundStarter = $this->getRoundWinnerPosition($currentRound);
                $currentRound++;
                $currentTurn = $this->getPlayerTurnOrder($nextRoundStarter ?? 1);
                $playedCards = []; // নতুন রাউন্ডের জন্য কার্ড ক্লিয়ার
            } else {
                // রাউন্ড সম্পন্ন কিন্তু এখনো ৭ সেকেন্ড হয়নি, কার্ড দেখানো চালিয়ে যাও
                $currentTurn = null; // কোনো টার্ন নেই কারণ রাউন্ড শেষ
                $playedCards = $this->getPlayedCardsForCurrentRound($currentRound);

                $remainingTime = 7 - now()->diffInSeconds($roundCompletedAt);
                if ($remainingTime > 0) {
                    $this->dispatch('refresh-after-delay', ['seconds' => $remainingTime]);
                }
            }
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

        if ($previousTurn !== null && $previousTurn !== $currentTurn) {
            $this->dispatch('playerTurn');
        }

        // প্লেয়ার এবং গেম ডেটা রিফ্রেশ
        $this->player->refresh();
        $this->game->refresh();
        $this->game->load(['participants.user']);

        // নতুন কার্ড ডিল চেক
        $this->checkForNewCardDeal();

        // Arrangement phase check
        $this->checkArrangementPhase();

        // Auto select cards for current turn player (only if game is playing and not in arrangement phase)
        if ($this->game->status === HajariGame::STATUS_PLAYING && !$this->isArrangementPhase) {
            $this->autoSelectCardsForCurrentPlayer();
        }
    }

    private function checkArrangementPhase()
    {
        if ($this->game->status === HajariGame::STATUS_PLAYING) {
            // Check if player has cards locked status
            $this->isCardsLocked = $this->player->cards_locked ?? false;

            // Get dynamic arrangement time from database
            $arrangementTimeMinutes = GameSetting::getArrangementTime() / 60;
            $arrangementEndTime = $this->game->updated_at->addMinutes($arrangementTimeMinutes);
            $now = Carbon::now();

            if ($now->lt($arrangementEndTime) && !$this->isCardsLocked) {
                $this->isArrangementPhase = true;
                $this->arrangementTimeLeft = $arrangementEndTime->diffInSeconds($now);
            } else {
                $this->isArrangementPhase = false;
                $this->arrangementTimeLeft = 0;

                // Check if all players have locked their cards
                $allPlayersLocked = $this->game->participants()
                    ->where('status', HajariGameParticipant::STATUS_PLAYING)
                    ->get()
                    ->every(function ($participant) {
                        return $participant->cards_locked ?? false;
                    });

                $this->canStartGame = $allPlayersLocked && $this->game->creator_id === Auth::id();
            }
        }
    }

    private function autoSelectCardsForCurrentPlayer()
    {
        $currentTurnPlayer = $this->getCurrentTurnPlayer();

        // Only auto-select if it's current player's turn and they have cards
        if ($currentTurnPlayer &&
            $currentTurnPlayer->user_id === Auth::id() &&
            $this->player->cards &&
            is_array($this->player->cards) &&
            count($this->player->cards) > 0) {

            $cardCount = count($this->player->cards);

            if ($cardCount <= 4) {
                // Select all remaining cards
                $this->selectedCards = array_keys($this->player->cards);
            } else {
                // Select first 3 cards from left
                $this->selectedCards = [0, 1, 2];
            }
        }
    }

    public function lockCards()
    {
        if (!$this->player->cards || !is_array($this->player->cards)) {
            session()->flash('error', 'No cards to lock.');
            return;
        }

        $this->player->update(['cards_locked' => true]);
        $this->isCardsLocked = true;

        broadcast(new GameUpdated($this->game, 'cards_locked', [
            'player_id' => Auth::id(),
            'player_name' => Auth::user()->name,
            'message' => Auth::user()->name . ' has locked their cards'
        ]));

        $this->checkArrangementPhase();
    }

    public function startGameAfterArrangement()
    {
        if (!$this->canStartGame) {
            session()->flash('error', 'Cannot start game yet.');
            return;
        }

        $this->game->update(['arrangement_completed' => true]);

        broadcast(new GameUpdated($this->game, 'game_ready', [
            'message' => 'Game is ready to start!'
        ]));

        $this->isArrangementPhase = false;
        $this->loadGameState();
    }

    private function getCurrentTurnInRound($round)
    {
        $movesInRound = $this->game->moves()->where('round', $round)->count();

        if ($movesInRound === 0) {
            // রাউন্ডের প্রথম মুভ
            if ($round > 1) {
                // আগের রাউন্ডের সর্বোচ্চ পয়েন্ট প্রাপ্ত প্লেয়ার শুরু করবে
                $previousRoundWinner = $this->getRoundWinnerPosition($round - 1);
                return $this->getPlayerTurnOrder($previousRoundWinner ?? 1);
            } else {
                // প্রথম রাউন্ড, ক্রিয়েটর শুরু করবে
                $creator = $this->game->participants()->where('user_id', $this->game->creator_id)->first();
                return $this->getPlayerTurnOrder($creator ? $creator->position : 1);
            }
        }

        // শেষ মুভ থেকে পরবর্তী প্লেয়ার নির্ধারণ
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
            $nextPosition = 1; // পজিশন ১ এ ফিরে যায়
        }

        // পরবর্তী পজিশনে প্লেয়ার আছে কিনা চেক
        $participant = $this->game->participants()->where('position', $nextPosition)->first();
        if ($participant && $participant->status === HajariGameParticipant::STATUS_PLAYING) {
            return $nextPosition;
        }

        // যদি পজিশন খালি থাকে, পরবর্তী সক্রিয় প্লেয়ার খুঁজে বের করা
        for ($i = 1; $i <= 4; $i++) {
            $participant = $this->game->participants()->where('position', $i)->first();
            if ($participant && $participant->status === HajariGameParticipant::STATUS_PLAYING) {
                return $i;
            }
        }

        return 1; // ফলব্যাক
    }

    // getRoundWinnerPosition() মেথডে Wrong প্লেয়ারদের বাদ দেওয়ার লজিক যোগ করুন
    private function getRoundWinnerPosition($round)
    {
        $roundMoves = $this->game->moves()
            ->where('round', $round)
            ->with('player')
            ->get();

        if ($roundMoves->isEmpty()) return null;

        // Wrong প্লেয়ারদের বাদ দিন
        $validMoves = $roundMoves->reject(function ($move) {
            $playerPosition = $this->getPlayerPosition($move->player_id);
            return in_array($playerPosition, $this->wrongPlayers);
        });

        if ($validMoves->isEmpty()) return null;

        // Convert moves to the format expected by the winner determination function
        $hands = $validMoves->map(function ($move) {
            return [
                'cards' => $this->convertCardsToHajariFormat($move->cards_played),
                'submitted_at' => $move->created_at->toISOString(),
                'player_id' => $move->player_id,
                'position' => $this->getPlayerPosition($move->player_id)
            ];
        })->toArray();

        $winnerIndex = $this->determineHajariWinner($hands);

        if ($winnerIndex !== null && isset($hands[$winnerIndex])) {
            return $hands[$winnerIndex]['position'];
        }

        return null;
    }

    private function convertCardsToHajariFormat($cards)
    {
        $suitMap = [
            'spades' => '♠',
            'hearts' => '♥',
            'diamonds' => '♦',
            'clubs' => '♣'
        ];

        return array_map(function ($card) use ($suitMap) {
            return $card['rank'] . ($suitMap[$card['suit']] ?? '♠');
        }, $cards);
    }

    private function determineHajariWinner(array $hands)
    {
        if (empty($hands)) return null;

        $evaluatedHands = [];

        foreach ($hands as $index => $hand) {
            $cards = $hand['cards'];
            $cardCount = count($cards);
            $bestEvaluation = null;

            if ($cardCount === 3) {
                $bestEvaluation = $this->evaluateHajariHand($cards);
            } elseif ($cardCount === 4) {
                $combinations = $this->getThreeCardCombinations($cards);
                foreach ($combinations as $combo) {
                    $evaluation = $this->evaluateHajariHand($combo);
                    if ($bestEvaluation === null ||
                        $evaluation['priority'] < $bestEvaluation['priority'] ||
                        ($evaluation['priority'] === $bestEvaluation['priority'] &&
                        $evaluation['highest_card'] > $bestEvaluation['highest_card'])
                    ) {
                        $bestEvaluation = $evaluation;
                    }
                }
            } else {
                // অন্য সংখ্যক কার্ড হলে সরাসরি ইভ্যালুয়েটেশন
                $bestEvaluation = $this->evaluateHajariHand($cards);
            }

            $evaluatedHands[] = [
                'index' => $index,
                'evaluation' => $bestEvaluation,
                'submitted_at' => $hand['submitted_at'],
                'player_id' => $hand['player_id'],
            ];
        }

        usort($evaluatedHands, function ($a, $b) {
            if ($a['evaluation']['priority'] !== $b['evaluation']['priority']) {
                return $a['evaluation']['priority'] - $b['evaluation']['priority'];
            }
            if ($a['evaluation']['highest_card'] !== $b['evaluation']['highest_card']) {
                return $b['evaluation']['highest_card'] - $a['evaluation']['highest_card'];
            }
            // যারা পরে জমা দিয়েছে তারা জিতবে (টাই-ব্রেকার)
            return strcmp($b['submitted_at'], $a['submitted_at']);
        });

        return $evaluatedHands[0]['index'] ?? null;
    }

    private function evaluateHajariHand($cards)
    {
        $cardValues = $this->getCardValues($cards);
        $suits = $this->getCardSuits($cards);
        $cardCount = count($cards);

        // Check for Tie (same rank cards - 3 or 4 of same rank)
        if ($this->isTie($cardValues)) {
            return [
                'type' => 'tie',
                'priority' => 1,
                'highest_card' => max($cardValues)
            ];
        }

        // Check for Running (Straight Flush - sequential cards of same suit)
        if ($this->isRunning($cardValues, $suits)) {
            return [
                'type' => 'running',
                'priority' => 2,
                'highest_card' => max($cardValues)
            ];
        }

        // Check for Run (Straight - sequential cards of different suits)
        if ($this->isRun($cardValues)) {
            return [
                'type' => 'run',
                'priority' => 3,
                'highest_card' => max($cardValues)
            ];
        }

        // Check for Color (Flush - same suit but not sequential)
        if ($this->isColor($suits)) {
            return [
                'type' => 'color',
                'priority' => 4,
                'highest_card' => max($cardValues)
            ];
        }

        // Check for Pair (two cards of same rank)
        if ($this->isPair($cardValues)) {
            return [
                'type' => 'pair',
                'priority' => 5,
                'highest_card' => max($cardValues)
            ];
        }

        // Mixed (no special combination)
        return [
            'type' => 'mixed',
            'priority' => 6,
            'highest_card' => max($cardValues)
        ];
    }

    private function getCardValues(array $cards): array
    {
        $values = [];
        foreach ($cards as $card) {
            // ইউনিকোড লেন্থ হিসেবে পরীক্ষা করুন '10♠' এর জন্য
            if (mb_strlen($card) === 3) {
                $rank = mb_substr($card, 0, 2);
            } else {
                $rank = mb_substr($card, 0, 1);
            }

            // লগ করুন
            Log::debug('Card rank extraction', [
                'card' => $card,
                'rank' => $rank,
            ]);

            $values[] = $this->getHajariCardValue($rank);
        }
        return $values;
    }

    private function getCardSuits($cards)
    {
        $suits = [];
        foreach ($cards as $card) {
            $suits[] = substr($card, -1); // Get suit symbol
        }
        return $suits;
    }

    private function getHajariCardValue($rank)
    {
        return match($rank) {
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

    private function isTie($cardValues)
    {
        $valueCounts = array_count_values($cardValues);
        $maxCount = max($valueCounts);
        return $maxCount >= 3; // 3 or 4 cards of same rank
    }

    private function isRunning($cardValues, $suits)
    {
        return $this->isSequential($cardValues) && $this->isColor($suits);
    }

    private function isRun($cardValues)
    {
        return $this->isSequential($cardValues);
    }

    private function isColor($suits)
    {
        $uniqueSuits = array_unique($suits);
        return count($uniqueSuits) === 1; // All cards same suit
    }

    private function isPair($cardValues)
    {
        $valueCounts = array_count_values($cardValues);
        return in_array(2, $valueCounts); // Exactly 2 cards of same rank
    }

    private function isSequential($cardValues)
    {
        if (count($cardValues) < 3) return false;

        sort($cardValues);

        // Check for normal sequence
        for ($i = 1; $i < count($cardValues); $i++) {
            if ($cardValues[$i] - $cardValues[$i-1] !== 1) {
                // Check for A-2-3 sequence (Ace low)
                if (!($cardValues[0] === 2 && $cardValues[1] === 3 && $cardValues[count($cardValues)-1] === 14)) {
                    return false;
                }
            }
        }

        return true;
    }


    // Wrong এর জন্য আপডেট করা হয়েছে
    private function calculateRoundWinner()
    {
        $roundMoves = $this->game->moves()
            ->where('round', $this->gameState['current_round'])
            ->with('player')
            ->get();

        if ($roundMoves->isEmpty()) return;

        // Wrong প্লেয়ারদের বাদ দিন
        $validMoves = $roundMoves->reject(function ($move) {
            $playerPosition = $this->getPlayerPosition($move->player_id);
            return in_array($playerPosition, $this->wrongPlayers);
        });

        if ($validMoves->isEmpty()) {
            // সব প্লেয়ার Wrong হলে রাউন্ড বাতিল করুন
            Log::info('All players wrong in round: ' . $this->gameState['current_round']);
            return;
        }

        // Convert moves to the format expected by the winner determination function
        $hands = $validMoves->map(function ($move) {
            return [
                'cards' => $this->convertCardsToHajariFormat($move->cards_played),
                'submitted_at' => $move->created_at->toISOString(),
                'player_id' => $move->player_id,
                'move' => $move
            ];
        })->toArray();

        // ... বাকি কোড winner determination এর ...
    }

    private function calculateMovePoints(array $cards): int
    {
        $points = 0;
        // প্রতিটি কার্ডের জন্য পয়েন্ট গণনা
        foreach ($cards as $card) {
            $points += match ($card['rank']) {
                '2', '3', '4', '5', '6', '7', '8', '9' => 5,
                '10', 'J', 'Q', 'K', 'A' => 10,
                default => 0 // অজানা কার্ডের জন্য ডিফল্ট
            };
        }

        return $points;
    }


    // Wrong  এর জন্য আপডেট করা হয়েছে
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

        // Wrong Rule চেক করুন
        $isWrongMove = false;
        $playerPosition = $this->player->position;

        if (isset($this->playerPreviousCombinations[$playerPosition])) {
            $previousEvaluation = $this->playerPreviousCombinations[$playerPosition];
            $currentEvaluation = $this->evaluateBestCombination($playedCards);

            // যদি বর্তমান কম্বিনেশন আগেরটির চেয়ে শক্তিশালী হয়
            if ($currentEvaluation['priority'] < $previousEvaluation['priority'] ||
                ($currentEvaluation['priority'] === $previousEvaluation['priority'] &&
                 $currentEvaluation['highest_card'] > $previousEvaluation['highest_card'])) {

                $isWrongMove = true;
                if (!in_array($playerPosition, $this->wrongPlayers)) {
                    $this->wrongPlayers[] = $playerPosition;

                    // WrongMove ইভেন্ট ব্রডকাস্ট করুন
                    broadcast(new WrongMove(
                        $this->game,
                        Auth::user(),
                        $currentEvaluation,
                        $previousEvaluation
                    ));

                    Log::info('Wrong combination detected', [
                        'player' => $this->player->user->name,
                        'position' => $playerPosition,
                        'current' => $currentEvaluation,
                        'previous' => $previousEvaluation
                    ]);
                }
            }

            // বর্তমান কম্বিনেশন সংরক্ষণ করুন
            $this->playerPreviousCombinations[$playerPosition] = $currentEvaluation;
        } else {
            // প্রথম মুভের জন্য শুধু কম্বিনেশন সংরক্ষণ করুন
            $this->playerPreviousCombinations[$playerPosition] = $this->evaluateBestCombination($playedCards);
        }

        // মুভ রেকর্ড করা
        HajariGameMove::create([
            'hajari_game_id' => $this->game->id,
            'player_id' => Auth::id(),
            'round' => $this->gameState['current_round'],
            'turn_order' => $this->gameState['current_turn'],
            'cards_played' => $playedCards,
            'points_earned' => $points,
            'is_wrong' => $isWrongMove // Wrong মুভ চিহ্নিত করুন
        ]);

        // প্লেয়ারের হাত থেকে কার্ড সরানো
        $remainingCards = [];
        foreach ($this->player->cards as $index => $card) {
            if (!in_array($index, $this->selectedCards)) {
                $remainingCards[] = $card;
            }
        }

        $this->player->update(['cards' => array_values($remainingCards)]);

        // 4 জন প্লেয়ার Wrong হলে নতুন কার্ড বিতরণ করুন
        if (count($this->wrongPlayers) >= 4) {
            $this->showAllWrongModal = true;
            $this->dispatch('allPlayersWrong');

            // ৩ সেকেন্ড পর স্বয়ংক্রিয়ভাবে নতুন কার্ড বিতরণ করুন
            $this->dispatch('refresh-after-delay', ['seconds' => 3]);
        }

        return $playedCards;
    }

    // Wrong  এর জন্য নতুন মেথড
    private function dealNewCards()
    {
        // Wrong Rule ট্র্যাকিং রিসেট করুন
        $this->wrongPlayers = [];
        $this->playerPreviousCombinations = [];

        Log::info('Reset wrong rule tracking for new card deal');

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

                    $participant->update([
                        'cards' => $playerCards,
                        'cards_locked' => false // Reset lock status
                    ]);
                    $participant->sortCards();
                    $cardIndex += $cardsPerPlayer;
                }

                // Start new arrangement phase
                $this->game->touch(); // Update timestamp for arrangement timer
            });

            $this->dispatch('cardDealt');

            // Broadcast new card deal
            broadcast(new GameUpdated($this->game, 'new_cards_dealt', [
                'message' => 'New cards have been dealt! Arrange your cards.'
            ]));

            Log::info('New cards dealt for game: ' . $this->game->id);

        } catch (\Exception $e) {
            Log::error('Error dealing new cards: ' . $e->getMessage());
        }
    }

    private function checkForNewCardDeal()
    {
        // সব প্লেয়ারের কার্ড শেষ কিনা চেক
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
        // Don't allow selection during arrangement phase or if cards are locked
        if ($this->isArrangementPhase || $this->isCardsLocked) {
            return;
        }

        // Check if player has cards
        if (!$this->player->cards || !is_array($this->player->cards)) {
            return;
        }

        if (in_array($cardIndex, $this->selectedCards)) {
            // কার্ড ডিসিলেক্ট
            $this->selectedCards = array_filter($this->selectedCards, fn($i) => $i !== $cardIndex);
        } else {
            // কার্ড সিলেকশন লিমিট চেক
            $currentCardCount = count($this->player->cards);
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

        // Check if player has cards
        if (!$this->player->cards || !is_array($this->player->cards)) {
            return false;
        }

        $currentCardCount = count($this->player->cards);

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

        // নির্বাচিত কার্ডগুলো প্লেয়ারের হাতে আছে কিনা চেক
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
            session()->flash('error', 'Invalid move according to Hajari rules.');
            return;
        }

        try {
            $playedCards = $this->processMove();

            $this->dispatch('cardPlayed');

            broadcast(new CardPlayed(
                $this->game,
                Auth::user(),
                $playedCards,
                $this->gameState['current_round'],
                $this->gameState['current_turn']
            ));

            $this->selectedCards = [];

            // 4 জন প্লেয়ার Wrong হলে নতুন কার্ড বিতরণ করুন
            if (count($this->wrongPlayers) >= 4) {
                $this->showAllWrongModal = true;
                $this->dispatch('allPlayersWrong');

                // ৩ সেকেন্ড পর স্বয়ংক্রিয়ভাবে নতুন কার্ড বিতরণ করুন
                $this->dispatch('refresh-after-delay', ['seconds' => 3]);
            }

            if ($this->isRoundComplete()) {
                $this->calculateRoundWinner();
                $this->checkGameProgress(); // গেম প্রোগ্রেস চেক রাউন্ড শেষে
            }

            $this->loadGameState(); // রিফ্রেশ স্টেট

        } catch (\Exception $e) {
            Log::error('Error playing cards: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while playing cards.');
        }
    }

    private function isPlayerTurn(): bool
    {
        $currentTurnPlayer = $this->getCurrentTurnPlayer();
        return $currentTurnPlayer && $currentTurnPlayer->user_id === Auth::id();
    }

    public function reorderCards($fromIndex, $toIndex)
    {
        if (!$this->player || !$this->player->cards || $this->isCardsLocked || !is_array($this->player->cards)) return;

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
        if (!$this->player || !$this->player->cards || $this->isCardsLocked || !is_array($this->player->cards)) return;

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
        if (!$this->player || !$this->player->cards || $this->isCardsLocked || !is_array($this->player->cards)) return;

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
                        'hazari_count' => 0,
                        'cards_locked' => false
                    ]);

                    $participant->sortCards();
                    $cardIndex += $cardsPerPlayer;
                }

                $this->game->update(['status' => HajariGame::STATUS_PLAYING]);
            });

            $this->dispatch('cardDealt');

            broadcast(new GameUpdated($this->game, 'game_started', [
                'message' => 'Game has started! Cards have been distributed. You have ' . (GameSetting::getArrangementTime() / 60) . ' minutes to arrange your cards.'
            ]));

            $this->loadGameState();
            session()->flash('success', 'Game started successfully!');

        } catch (\Exception $e) {
            Log::error('Error starting game: ' . $e->getMessage());
            session()->flash('error', 'Failed to start game: ' . $e->getMessage());
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

        // ট্রানজাকশন প্রক্রিয়া এবং নোটিফিকেশন
        $transactions = $this->processGamePayments($winner);

        $this->dispatch('gameOver');
        $this->winnerData = [
                'winner_name'   => $winner->user->name,
                'final_scores'  => $finalScores,
            ];
        $this->showWinnerModal=true;

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

    private function getRoundCompletionTime($round)
    {
        $lastMoveInRound = $this->game->moves()
            ->where('round', $round)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastMoveInRound) {
            $movesCount = $this->game->moves()->where('round', $round)->count();
            if ($movesCount >= 4) {
                return $lastMoveInRound->created_at;
            }
        }

        return null;
    }

    private function getThreeCardCombinations(array $cards)
    {
        $results = [];
        $count = count($cards);

        for ($i = 0; $i < $count - 2; $i++) {
            for ($j = $i + 1; $j < $count - 1; $j++) {
                for ($k = $j + 1; $k < $count; $k++) {
                    $results[] = [$cards[$i], $cards[$j], $cards[$k]];
                }
            }
        }

        return $results;
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

        // কার্ড শেষ হলে নতুন শর্ত চেক
        if ($playersWithCards === 0) {
            // ১০০০+ পয়েন্ট আছে এমন প্লেয়ার খুঁজুন
            $hasWinner = $this->game->participants()
                ->where('status', HajariGameParticipant::STATUS_PLAYING)
                ->where('total_points', '>=', 1000)
                ->exists();

            if ($hasWinner) {
                $this->endGame(); // গেম শেষ করুন
            } else {
                $this->dealNewCards(); // নতুন কার্ড বিতরণ করুন
            }
        }
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


    private function evaluateBestCombination($cards)
    {
        $cardValues = $this->getCardValues($cards);
        $suits = $this->getCardSuits($cards);
        $cardCount = count($cards);

        // Check for Tie (same rank cards - 3 or 4 of same rank)
        if ($this->isTie($cardValues)) {
            return [
                'type' => 'tie',
                'priority' => 1,
                'highest_card' => max($cardValues)
            ];
        }

        // Check for Running (Straight Flush - sequential cards of same suit)
        if ($this->isRunning($cardValues, $suits)) {
            return [
                'type' => 'running',
                'priority' => 2,
                'highest_card' => max($cardValues)
            ];
        }

        // Check for Run (Straight - sequential cards of different suits)
        if ($this->isRun($cardValues)) {
            return [
                'type' => 'run',
                'priority' => 3,
                'highest_card' => max($cardValues)
            ];
        }

        // Check for Color (Flush - same suit but not sequential)
        if ($this->isColor($suits)) {
            return [
                'type' => 'color',
                'priority' => 4,
                'highest_card' => max($cardValues)
            ];
        }

        // Check for Pair (two cards of same rank)
        if ($this->isPair($cardValues)) {
            return [
                'type' => 'pair',
                'priority' => 5,
                'highest_card' => max($cardValues)
            ];
        }

        // Mixed (no special combination)
        return [
            'type' => 'mixed',
            'priority' => 6,
            'highest_card' => max($cardValues)
        ];
    }

    // dealNewCardsAfterAllWrong() মেথড যোগ করুন
    public function dealNewCardsAfterAllWrong()
    {
        $this->showAllWrongModal = false;
        $this->dealNewCards();
    }

    // render() মেথড আপডেট করুন
    public function render()
    {
        return view('livewire.frontend.hajari.hajari-game-room', [
            'wrongPlayersCount' => count($this->wrongPlayers)
        ])->layout('livewire.layout.frontend.game-room', [
            'title' => $this->game->title . ' - Game Room'
        ]);
    }
}

<div class="game-container">
    <!-- Include card CSS -->
    <link rel="stylesheet" href="{{ asset('css/cards-unicode.css') }}">

    <!-- Audio elements for sound effects -->
    <audio id="dealSound" preload="auto">
        <source src="{{ asset('sounds/card-deal.mp3') }}" type="audio/mpeg">
        <source src="{{ asset('sounds/card-deal.wav') }}" type="audio/wav">
        <source src="{{ asset('sounds/card-deal.ogg') }}" type="audio/ogg">
    </audio>
    <audio id="turnSound" preload="auto">
        <source src="{{ asset('sounds/turn-notification.mp3') }}" type="audio/mpeg">
        <source src="{{ asset('sounds/turn-notification.wav') }}" type="audio/wav">
        <source src="{{ asset('sounds/turn-notification.ogg') }}" type="audio/ogg">
    </audio>
    <audio id="winRoundSound" preload="auto">
        <source src="{{ asset('sounds/round-win.mp3') }}" type="audio/mpeg">
        <source src="{{ asset('sounds/round-win.wav') }}" type="audio/wav">
        <source src="{{ asset('sounds/round-win.ogg') }}" type="audio/ogg">
    </audio>
    <audio id="playCardSound" preload="auto">
        <source src="{{ asset('sounds/card-play.mp3') }}" type="audio/mpeg">
        <source src="{{ asset('sounds/card-play.wav') }}" type="audio/wav">
        <source src="{{ asset('sounds/card-play.ogg') }}" type="audio/ogg">
    </audio>
    <audio id="gameOverSound" preload="auto">
        <source src="{{ asset('sounds/game-over.mp3') }}" type="audio/mpeg">
        <source src="{{ asset('sounds/game-over.wav') }}" type="audio/wav">
        <source src="{{ asset('sounds/game-over.ogg') }}" type="audio/ogg">
    </audio>

    <!-- Game notifications container -->
    <div id="game-notifications" class="notifications-container"></div>

    <!-- Ultra Compact Game Header -->
    <div class="game-header">
        <div class="header-content">
            <div class="game-info">
                <h1 class="game-title">{{ Str::limit($game->title, 12) }}</h1>
                <div class="game-stats">
                    <span>৳{{ number_format($game->bid_amount, 0) }}</span>
                    <span>R{{ $gameState['current_round'] ?? 1 }}</span>
                    <span>T{{ $gameState['current_turn'] ?? 1 }}</span>
                    @if($isArrangementPhase && $arrangementTimeLeft > 0)
                        <span class="timer-badge" id="arrangement-timer">{{ gmdate('i:s', $arrangementTimeLeft) }}</span>
                    @endif
                </div>
            </div>
            <div class="scoreboard">
                @foreach($game->participants as $participant)
                    <div class="player-score {{ $participant->user_id === Auth::id() ? 'current-player' : '' }}
                                {{ ($gameState['current_turn'] ?? 1) === $participant->position ? 'active-turn' : '' }}
                                {{ ($participant->cards_locked ?? false) ? 'cards-locked' : '' }}"
                         id="player-score-{{ $participant->position }}">
                        <div class="player-name">{{ Str::limit($participant->user->name, 4) }}</div>
                        <div class="player-points">{{ $participant->total_points ?? 0 }}</div>
                        @if($participant->cards_locked ?? false)
                            <div class="lock-indicator">🔒</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Arrangement Phase Controls -->
    @if($isArrangementPhase)
        <div class="arrangement-controls">
            <div class="arrangement-info">
                <span class="arrangement-text">Arrange your cards</span>
                <div class="arrangement-timer" id="live-timer">{{ gmdate('i:s', $arrangementTimeLeft) }}</div>
            </div>
            <div class="arrangement-buttons">
                <button wire:click="sortCardsBySuit" class="sort-btn" {{ $isCardsLocked ? 'disabled' : '' }}>
                    <i class="fas fa-sort-alpha-down"></i> Suit
                </button>
                <button wire:click="sortCardsByRank" class="sort-btn" {{ $isCardsLocked ? 'disabled' : '' }}>
                    <i class="fas fa-sort-numeric-down"></i> Rank
                </button>
                <button wire:click="lockCards" class="lock-btn" {{ $isCardsLocked ? 'disabled' : '' }}>
                    <i class="fas fa-lock"></i> {{ $isCardsLocked ? 'Locked' : 'Lock Cards' }}
                </button>
            </div>
        </div>
    @endif

    <!-- Creator Start Game Button -->
    @if($canStartGame)
        <div class="start-game-controls">
            <button wire:click="startGameAfterArrangement" class="start-game-btn">
                <i class="fas fa-play"></i> Start Game
            </button>
        </div>
    @endif

    <!-- Game Table - Fan Style Stacked Cards -->
    <div class="game-table-container">
        <div class="game-table">
            <!-- Center area for played cards - Fan Style Stacks -->
            <div class="center-cards" id="center-cards">
                @if(!empty($gameState['played_cards']))
                    <div class="fan-stacks-container">
                        @foreach($gameState['played_cards'] as $index => $move)
                            @php
                                // Calculate positions in a circle around center
                                $positions = [
                                    1 => ['bottom: 20px', 'left: 50%', 'transform: translateX(-50%)'], // Bottom
                                    2 => ['left: 20px', 'top: 50%', 'transform: translateY(-50%)'],   // Left
                                    3 => ['top: 20px', 'left: 50%', 'transform: translateX(-50%)'],   // Top
                                    4 => ['right: 20px', 'top: 50%', 'transform: translateY(-50%)']  // Right
                                ];
                                $position = $positions[$move['position']] ?? $positions[1];
                            @endphp
                            <div class="fan-player-stack"
                                 data-player-position="{{ $move['position'] }}"
                                 data-turn-order="{{ $move['turn'] }}"
                                 style="{{ implode('; ', $position) }}; z-index: {{ 20 + $move['turn'] }};">
                                <!-- Fan style stacked cards -->
                                <div class="fan-card-stack">
                                    @foreach($move['cards'] as $cardIndex => $card)
                                        <div class="fan-stacked-card"
                                             style="transform: translateX({{ $cardIndex * 15 }}px) rotate({{ $cardIndex * 3 - 3 }}deg); z-index: {{ $cardIndex + 1 }};">
                                            <x-playing-card
                                                :suit="$card['suit']"
                                                :rank="$card['rank']"
                                                :clickable="false"
                                                size="small"
                                                class="fan-center-card" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="center-placeholder">
                        <div class="placeholder-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Player positions - Moved outside center circle -->
            @foreach($game->participants as $participant)
                @php
                    $positions = [
                        1 => 'bottom-player',
                        2 => 'left-player',
                        3 => 'top-player',
                        4 => 'right-player'
                    ];
                    $positionClass = $positions[$participant->position] ?? 'bottom-player';
                    $isCurrentTurn = ($gameState['current_turn'] ?? 1) === $participant->position;
                @endphp
                <div class="player-position {{ $positionClass }} {{ $isCurrentTurn ? 'player-turn' : '' }}"
                     id="player-position-{{ $participant->position }}">
                    <div class="player-card">
                        <div class="player-info">
                            <div class="{{$isCurrentTurn ? 'player-name' : 'player-name-dark'}}">{{ Str::limit($participant->user->name, 8) }}</div>
                        </div>
                        @if($isCurrentTurn && !$isArrangementPhase)
                            <div class="turn-indicator">
                                <i class="fas fa-play"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Enhanced Player Cards Section -->
    @if($player && $player->cards && is_array($player->cards))
        <div class="player-cards-section">
            <div class="ultra-slim-header">
                <div class="header-left">
                    @php
                        $maxSelection = count($player->cards) <= 4 ? count($player->cards) : 3;
                        $isMyTurn = ($gameState['current_turn'] ?? 1) === $player->position;
                    @endphp
                    @if(!empty($selectedCards))
                        <div class="selected-indicator">
                            <span class="selected-count-badge">{{ count($selectedCards) }}</span>
                        </div>
                    @endif
                    @if($isMyTurn && !$isArrangementPhase)
                        <div class="turn-badge">YOUR TURN</div>
                    @endif
                    @if($isArrangementPhase)
                        <div class="arrangement-badge {{ $isCardsLocked ? 'locked' : 'arranging' }}">
                            {{ $isCardsLocked ? 'LOCKED' : 'ARRANGING' }}
                        </div>
                    @endif
                </div>
                <div class="header-controls">
                    @if($game->status === 'playing' && !$isArrangementPhase)
                        <button wire:click="playCards"
                                class="play-btn {{ !$isMyTurn || empty($selectedCards) ? 'disabled' : '' }}"
                                {{ !$isMyTurn || empty($selectedCards) ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Enhanced Cards Container with Stacking -->
            <div class="enhanced-cards-container" id="cards-container">
                <div class="scroll-indicator left-indicator" id="left-scroll">
                    <i class="fas fa-chevron-left"></i>
                </div>

                <div class="cards-scroll" id="cards-scroll">
                    @foreach($player->cards as $index => $card)
                        @php
                            $isSelected = in_array($index, $selectedCards);
                            $canSelect = !$isArrangementPhase && !$isCardsLocked && $isMyTurn && (count($selectedCards) < $maxSelection || $isSelected);
                            $remainingCards = count($player->cards);
                            $isLastFew = $remainingCards <= 4;
                        @endphp
                        <div class="enhanced-card-wrapper draggable-card
                                    {{ $isSelected ? 'selected' : '' }}
                                    {{ !$isMyTurn && !$isArrangementPhase ? 'not-my-turn' : '' }}
                                    {{ $isLastFew ? 'last-few' : '' }}
                                    {{ $isCardsLocked ? 'locked' : '' }}"
                             data-card-index="{{ $index }}"
                             wire:click="toggleCardSelection({{ $index }})"
                             draggable="{{ !$isCardsLocked && $isArrangementPhase ? 'true' : 'false' }}">

                            <x-playing-card
                                :suit="$card['suit']"
                                :rank="$card['rank']"
                                :selected="$isSelected"
                                :clickable="$canSelect"
                                size="normal" />

                            @if($isSelected)
                                <div class="enhanced-selection-indicator">
                                    <i class="fas fa-check"></i>
                                </div>
                            @endif

                            @if($isCardsLocked)
                                <div class="card-lock-overlay">
                                    <i class="fas fa-lock"></i>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="scroll-indicator right-indicator" id="right-scroll">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
    @endif

    <!-- Drop zones for drag and drop -->
    <div id="drop-zones" class="drop-zones" style="display: none;">
        <div class="drop-zone" data-zone="left">Drop Here</div>
        <div class="drop-zone" data-zone="right">Drop Here</div>
    </div>

    <!-- Score Modal -->
    @if($showScoreModal)
        <div class="modal-overlay" wire:click="closeScoreModal">
            <div class="score-modal">
                <h3>Round {{ $scoreData['round'] ?? '' }} Winner!</h3>
                <div class="winner-info">
                    <div class="winner-name">{{ $scoreData['winner_name'] ?? '' }}</div>
                    <div class="winner-points">{{ $scoreData['points'] ?? 0 }} Points</div>
                </div>
                <button wire:click="closeScoreModal" class="close-btn">Close</button>
            </div>
        </div>
    @endif

    <!-- Winner Modal -->
    @if($showWinnerModal)
        <div class="modal-overlay" wire:click="closeWinnerModal">
            <div class="winner-modal">
                <h2>🎉 Game Over! 🎉</h2>
                <div class="final-winner">
                    <div class="winner-name">{{ $winnerData['winner_name'] ?? '' }}</div>
                    <div class="winner-title">WINNER!</div>
                </div>
                <div class="final-scores">
                    @if(isset($winnerData['final_scores']))
                        @foreach($winnerData['final_scores'] as $score)
                            <div class="score-row">
                                <span>{{ $score['name'] }}</span>
                                <span>{{ $score['total_points'] }} pts</span>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button wire:click="closeWinnerModal" class="close-btn">Close</button>
            </div>
        </div>
    @endif

    <script src="{{ asset('js/hajari-room.js') }}" defer></script>
    <script>
    // Compute Livewire component ID safely from the DOM
    function setHajariLivewireId() {
      try {
        const root = document.querySelector('.game-container');
        const lwRoot = root ? root.closest('[wire\\:id]') : null;
        const anyRoot = lwRoot || document.querySelector('[wire\\:id]');
        if (anyRoot) {
          window.__HAJARI_LW_ID = anyRoot.getAttribute('wire:id');
        }
      } catch (e) {
        console.debug('Failed to detect Livewire ID', e);
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      setHajariLivewireId();
      if (window.HajariRoom) window.HajariRoom.init();
    });

    document.addEventListener('livewire:updated', () => {
      setHajariLivewireId();
      if (window.HajariRoom) window.HajariRoom.init();
    });

    document.addEventListener('livewire:navigated', () => {
      setHajariLivewireId();
      if (window.HajariRoom) window.HajariRoom.init();
    });
  </script>

    <style>
        /* Enhanced drag and drop styles */
        .draggable-card {
            cursor: grab;
            transition: all 0.3s ease;
        }

        .draggable-card:active {
            cursor: grabbing;
        }

        .draggable-card.dragging {
            opacity: 0.5 !important;
            transform: rotate(5deg) scale(1.05);
            z-index: 1000;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            transition: none;
        }

        .draggable-card.drag-over {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.4);
            border: 2px solid #3b82f6;
            border-radius: 8px;
            background: rgba(59, 130, 246, 0.1);
        }

        #drag-ghost {
            pointer-events: none;
            user-select: none;
        }

        /* Notifications container */
        .notifications-container {
            position: fixed;
            top: 50px;
            right: 10px;
            z-index: 1000;
            pointer-events: none;
        }

        .notification {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            margin-bottom: 5px;
            animation: slideIn 0.3s ease-out;
            pointer-events: auto;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Base container */
        .game-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            height: 100dvh;
            background: linear-gradient(135deg, #1e3a8a 0%, #059669 100%);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
            user-select: none;
        }

        /* Ultra compact header */
        .game-header {
            height: 45px;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            z-index: 20;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 0 10px;
        }

        .game-info {
            flex: 1;
        }

        .game-title {
            font-size: 12px;
            font-weight: bold;
            color: white;
            margin: 0;
            line-height: 1.1;
        }

        .game-stats {
            display: flex;
            gap: 8px;
            font-size: 9px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 2px;
        }

        .timer-badge {
            background: #f59e0b;
            color: white;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
            animation: pulse 1s infinite;
        }

        .timer-badge.urgent {
            background: #ef4444;
            animation: urgentPulse 0.5s infinite;
        }

        @keyframes urgentPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Scoreboard */
        .scoreboard {
            display: flex;
            gap: 4px;
        }

        .player-score {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            padding: 2px 4px;
            text-align: center;
            min-width: 30px;
            font-size: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .player-score.current-player {
            background: rgba(59, 130, 246, 0.3);
            border: 1px solid rgba(59, 130, 246, 0.5);
        }

        .player-score.active-turn {
            background: rgba(251, 191, 36, 0.3);
            border: 1px solid rgba(251, 191, 36, 0.5);
            animation: pulse 1s infinite;
        }

        .player-score.cards-locked {
            background: rgba(34, 197, 94, 0.3);
            border: 1px solid rgba(34, 197, 94, 0.5);
        }

        .player-name {
            color: white;
            font-weight: bold;
            line-height: 1;
        }

        .player-name-dark {
            color: #6b7280;
            font-weight: bold;
            line-height: 1;
        }

        .player-points {
            color: #fbbf24;
            font-weight: bold;
            font-size: 10px;
        }

        .lock-indicator {
            position: absolute;
            top: -2px;
            right: -2px;
            font-size: 8px;
            background: #10b981;
            border-radius: 50%;
            width: 12px;
            height: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Arrangement controls */
        .arrangement-controls {
            height: 35px;
            background: rgba(251, 191, 36, 0.2);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(251, 191, 36, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 10px;
            flex-shrink: 0;
        }

        .arrangement-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .arrangement-text {
            color: white;
            font-size: 11px;
            font-weight: bold;
        }

        .arrangement-timer {
            background: rgba(0, 0, 0, 0.3);
            color: #fbbf24;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            font-family: monospace;
        }

        .arrangement-timer.urgent {
            background: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            animation: urgentPulse 0.5s infinite;
        }

        .arrangement-buttons {
            display: flex;
            gap: 5px;
        }

        .sort-btn, .lock-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 9px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sort-btn:hover, .lock-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .sort-btn:disabled, .lock-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .lock-btn {
            background: rgba(34, 197, 94, 0.3);
        }

        .lock-btn:disabled {
            background: rgba(107, 114, 128, 0.3);
        }

        /* Start game controls */
        .start-game-controls {
            height: 40px;
            background: rgba(34, 197, 94, 0.2);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(34, 197, 94, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
        }

        .start-game-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: pulse 1.5s infinite;
        }

        .start-game-btn:hover {
            background: #059669;
            transform: scale(1.05);
        }

        /* Game table */
        .game-table-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            min-height: 0;
        }

        .game-table {
            position: relative;
            width: 100%;
            max-width: 500px;
            height: 160px;
            background: rgba(34, 197, 94, 0.8);
            border-radius: 50%;
            border: 3px solid rgba(34, 197, 94, 0.6);
        }

        /* Center cards */
        .center-cards {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fan-stacks-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .fan-player-stack {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }

        .fan-card-stack {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 70px;
        }

        .fan-stacked-card {
            position: absolute;
            transition: all 0.3s ease;
            transform-origin: bottom center;
        }

        .fan-center-card {
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.4), 3px 0 6px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .center-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            background: rgba(21, 128, 61, 0.6);
            border-radius: 12px;
            padding: 15px;
            border: 2px dashed rgba(255, 255, 255, 0.3);
        }

        .placeholder-icon {
            font-size: 20px;
            opacity: 0.7;
        }

        /* Player positions */
        .player-position {
            position: absolute;
            z-index: 15;
        }

        .bottom-player {
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .left-player {
            left: -25px;
            top: 50%;
            transform: translateY(-50%);
        }

        .top-player {
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .right-player {
            right: -25px;
            top: 50%;
            transform: translateY(-50%);
        }

        .player-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 6px 8px;
            text-align: center;
            min-width: 60px;
            font-size: 9px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .player-turn .player-card {
            background: rgba(251, 191, 36, 0.95);
            animation: pulse 1.5s infinite;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
        }

        .turn-indicator {
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #ef4444;
            color: white;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            border: 1px solid white;
        }

        /* Enhanced player cards section */
        .player-cards-section {
            height: calc(100vh - 45px - 160px - 35px);
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(15px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        .ultra-slim-header {
            height: 35px;
            padding: 0 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .ultra-slim-header .header-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .selected-indicator {
            position: relative;
        }

        .selected-count-badge {
            background: #3b82f6;
            color: white;
            font-size: 9px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid white;
        }

        .turn-badge {
            background: #f59e0b;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }

        .arrangement-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .arrangement-badge.arranging {
            background: #f59e0b;
            color: white;
            animation: pulse 1.5s infinite;
        }

        .arrangement-badge.locked {
            background: #10b981;
            color: white;
        }

        .play-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .play-btn:hover {
            background: #059669;
            transform: scale(1.1);
        }

        .play-btn.disabled {
            background: #6b7280;
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Enhanced cards container with stacking */
        .enhanced-cards-container {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .scroll-indicator {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 30px;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.4), transparent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .left-indicator {
            left: 0;
        }

        .right-indicator {
            right: 0;
            background: linear-gradient(to left, rgba(0, 0, 0, 0.4), transparent);
        }

        .scroll-indicator:hover {
            background: rgba(0, 0, 0, 0.6);
            color: white;
        }

        .cards-scroll {
            display: flex;
            gap: 3px;
            overflow-x: auto;
            overflow-y: hidden;
            height: 100%;
            align-items: center;
            padding: 8px 35px;
            scroll-behavior: smooth;
        }

        .cards-scroll::-webkit-scrollbar {
            height: 2px;
        }

        .cards-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .cards-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 1px;
        }

        /* Enhanced card styling with stacking */
        .enhanced-card-wrapper {
            position: relative;
            flex-shrink: 0;
            transition: all 0.3s ease;
            user-select: none;
            touch-action: manipulation;
            margin-right: -8px; /* Create stacking effect */
            z-index: 1;
        }

        .enhanced-card-wrapper:hover {
            z-index: 10;
            transform: translateY(-5px) scale(1.02);
        }

        .enhanced-card-wrapper.selected {
            transform: translateY(-12px) scale(1.08);
            filter: brightness(1.2);
            z-index: 15;
            margin-left: 3px;
            margin-right: -5px;
        }

        .enhanced-card-wrapper.not-my-turn {
            opacity: 0.6;
            filter: grayscale(0.3);
        }

        .enhanced-card-wrapper.last-few {
            border: 2px solid rgba(239, 68, 68, 0.6);
            border-radius: 8px;
            animation: lastFewPulse 2s infinite;
        }

        .enhanced-card-wrapper.locked {
            opacity: 0.8;
            filter: grayscale(0.5);
            cursor: not-allowed;
        }

        @keyframes lastFewPulse {
            0%, 100% { border-color: rgba(239, 68, 68, 0.6); }
            50% { border-color: rgba(239, 68, 68, 1); }
        }

        .enhanced-selection-indicator {
            position: absolute;
            top: -8px;
            left: -8px;
            background: #10b981;
            color: white;
            font-size: 10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            z-index: 5;
        }

        .card-lock-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            z-index: 5;
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .score-modal, .winner-modal {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            max-width: 300px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .score-modal h3, .winner-modal h2 {
            margin: 0 0 15px 0;
            color: #1f2937;
        }

        .winner-info, .final-winner {
            margin: 15px 0;
        }

        .winner-name {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }

        .winner-points, .winner-title {
            font-size: 14px;
            color: #6b7280;
        }

        .winner-title {
            color: #f59e0b;
            font-weight: bold;
        }

        .final-scores {
            margin: 15px 0;
            text-align: left;
        }

        .score-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .close-btn {
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 15px;
        }

        .close-btn:hover {
            background: #2563eb;
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Mobile landscape optimizations */
        @media screen and (orientation: landscape) and (max-width: 768px) {
            .game-header {
                height: 40px;
            }

            .arrangement-controls {
                height: 30px;
            }

            .start-game-controls {
                height: 35px;
            }

            .game-table {
                height: 120px;
            }

            .player-cards-section {
                height: calc(100vh - 40px - 120px - 30px);
            }

            .ultra-slim-header {
                height: 30px;
            }

            .enhanced-card-wrapper {
                margin-right: -12px; /* More stacking on mobile landscape */
            }

            .cards-scroll {
                gap: 2px;
                padding: 5px 30px;
            }
        }

        /* Very small screens */
        @media screen and (max-width: 400px) {
            .game-title {
                font-size: 11px;
            }

            .game-stats {
                font-size: 8px;
                gap: 6px;
            }

            .player-score {
                min-width: 28px;
                font-size: 7px;
            }

            .enhanced-card-wrapper {
                margin-right: -10px;
            }

            .cards-scroll {
                gap: 1px;
                padding: 8px 25px;
            }

            .arrangement-buttons {
                gap: 3px;
            }

            .sort-btn, .lock-btn {
                padding: 3px 6px;
                font-size: 8px;
            }
        }

        /* Disable text selection and improve touch */
        .enhanced-card-wrapper * {
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        /* Better mobile touch handling */
        .enhanced-card-wrapper {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</div>

<div class="game-container">
    <!-- Include card CSS -->
    <link rel="stylesheet" href="{{ asset('css/cards-unicode.css') }}">

    <!-- Compact Game Header for Mobile Landscape -->
    <div class="game-header">
        <div class="header-content">
            <!-- Game Info - Ultra Compact -->
            <div class="game-info">
                <h1 class="game-title">{{ Str::limit($game->title, 15) }}</h1>
                <div class="game-stats">
                    <span>৳{{ number_format($game->bid_amount, 0) }}</span>
                    <span>R{{ $gameState['current_round'] ?? 1 }}</span>
                    <span>T{{ $gameState['current_turn'] ?? 1 }}</span>
                </div>
            </div>

            <!-- Ultra Compact Scoreboard -->
            <div class="scoreboard">
                @foreach($game->participants as $participant)
                    <div class="player-score {{ $participant->user_id === Auth::id() ? 'current-player' : '' }}
                                {{ ($gameState['current_turn'] ?? 1) === $participant->position ? 'active-turn' : '' }}"
                         id="player-score-{{ $participant->position }}">
                        <div class="player-name">{{ Str::limit($participant->user->name, 6) }}</div>
                        <div class="player-points">{{ $participant->total_points ?? 0 }}</div>
                        <div class="player-wins">{{ $participant->rounds_won ?? 0 }}W</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Game Table - Optimized Center Layout -->
    <div class="game-table-container">
        <div class="game-table">
            <!-- Center area for played cards - Grid Layout -->
            <div class="center-cards" id="center-cards">
                @if(!empty($gameState['played_cards']))
                    <div class="played-cards-grid">
                        @foreach($gameState['played_cards'] as $index => $move)
                            @php
                                $playerPosition = $this->getPlayerPosition($move['player_id']);
                                $gridPositions = [
                                    1 => 'grid-bottom',    // Bottom player
                                    2 => 'grid-left',     // Left player
                                    3 => 'grid-top',      // Top player
                                    4 => 'grid-right'     // Right player
                                ];
                                $gridClass = $gridPositions[$playerPosition] ?? 'grid-bottom';
                            @endphp

                            <div class="played-card-section {{ $gridClass }}"
                                 data-player-position="{{ $playerPosition }}">

                                <!-- Player info -->
                                <div class="player-info">
                                    <span class="player-name-badge">{{ Str::limit($move['player'], 5) }}</span>
                                    @if($move['points'] > 0)
                                        <span class="points-badge">{{ $move['points'] }}pts</span>
                                    @endif
                                </div>

                                <!-- Cards display -->
                                <div class="cards-display">
                                    @foreach($move['cards'] as $card)
                                        <div class="center-card">
                                            <x-playing-card
                                                :suit="$card['suit']"
                                                :rank="$card['rank']"
                                                :clickable="false"
                                                size="small"
                                                class="played-card" />
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
                        <span class="placeholder-text">Played Cards</span>
                    </div>
                @endif
            </div>

            <!-- Player positions -->
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
                        <div class="player-name">{{ Str::limit($participant->user->name, 6) }}</div>
                        <div class="card-count">{{ is_array($participant->cards) ? count($participant->cards) : 0 }}</div>
                        @if($participant->user_id === Auth::id())
                            <div class="current-indicator"></div>
                        @endif
                        @if($isCurrentTurn)
                            <div class="turn-indicator">TURN</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Enhanced Full-Width Player Cards Section -->
    @if($player && $player->cards)
        <div class="player-cards-section">
            <div class="cards-header">
                <div class="cards-info">
                    <div class="cards-title">Your Cards</div>
                    <div class="cards-count">({{ count($player->cards) }} cards)</div>
                </div>
                <div class="cards-controls">
                    <button wire:click="sortCardsBySuit" class="sort-btn" title="Sort by Suit">
                        <i class="fas fa-sort-alpha-down"></i>
                    </button>
                    <button wire:click="sortCardsByRank" class="sort-btn" title="Sort by Rank">
                        <i class="fas fa-sort-numeric-down"></i>
                    </button>
                    @if($game->status === 'playing')
                        <button wire:click="playCards"
                                class="play-btn {{ empty($selectedCards) ? 'disabled' : '' }}"
                                {{ empty($selectedCards) ? 'disabled' : '' }}>
                            <i class="fas fa-play"></i>
                            <span class="btn-text">Play</span>
                            <span class="selected-count">({{ count($selectedCards) }})</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Full-Width Enhanced Cards Container -->
            <div class="cards-container">
                <!-- Left scroll indicator -->
                <div class="scroll-indicator left-indicator" id="left-scroll">
                    <i class="fas fa-chevron-left"></i>
                </div>

                <!-- Cards scroll area -->
                <div class="cards-scroll" id="cards-scroll">
                    @foreach($player->cards as $index => $card)
                        <div class="card-wrapper draggable-card {{ in_array($index, $selectedCards) ? 'selected' : '' }}"
                             data-card-index="{{ $index }}"
                             draggable="true"
                             wire:click="toggleCardSelection({{ $index }})">

                            <!-- Card component -->
                            <x-playing-card
                                :suit="$card['suit']"
                                :rank="$card['rank']"
                                :selected="in_array($index, $selectedCards)"
                                :clickable="true"
                                size="normal" />

                            <!-- Card position indicator -->
                            <div class="card-position">{{ $index + 1 }}</div>

                            <!-- Selection indicator -->
                            @if(in_array($index, $selectedCards))
                                <div class="selection-indicator">
                                    <i class="fas fa-check"></i>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Right scroll indicator -->
                <div class="scroll-indicator right-indicator" id="right-scroll">
                    <i class="fas fa-chevron-right"></i>
                </div>

                <!-- Drop zones for reordering -->
                <div class="drop-zone-overlay" id="drop-zones" style="display: none;">
                    <div class="drop-zone left-drop-zone">
                        <i class="fas fa-arrow-left"></i>
                        <span>Move Left</span>
                    </div>
                    <div class="drop-zone right-drop-zone">
                        <i class="fas fa-arrow-right"></i>
                        <span>Move Right</span>
                    </div>
                </div>
            </div>

            <!-- Selected cards summary -->
            @if(!empty($selectedCards))
                <div class="selected-summary">
                    <div class="summary-header">
                        <i class="fas fa-hand-paper"></i>
                        <span>Selected Cards:</span>
                    </div>
                    <div class="selected-cards-list">
                        @foreach($selectedCards as $index)
                            <span class="selected-card-item">
                                <span class="card-symbol">{{ $player->cards[$index]['rank'] }}</span>
                                <span class="card-suit">{{ substr($player->cards[$index]['suit'], 0, 1) }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Game Controls -->
    @if($game->status === 'pending' && $game->creator_id === Auth::id())
        <div class="game-controls">
            @if($game->canStart())
                <button wire:click="startGame" class="start-game-btn">
                    <i class="fas fa-play"></i> Start Game
                </button>
            @else
                <p class="waiting-text">Waiting for {{ 4 - $game->participants()->count() }} more players...</p>
            @endif
        </div>
    @endif

    <!-- Modals (Score, Winner) -->
    @if($showScoreModal)
        <div class="modal-overlay">
            <div class="score-modal">
                <div class="modal-content">
                    <div class="score-icon">
                        @if($scoreData['score_type'] === 'hazari')
                            🎉 HAZARI! 🎉
                        @else
                            ⭐ Points! ⭐
                        @endif
                    </div>
                    <div class="score-text">
                        <span class="player-name">{{ $scoreData['player_name'] ?? '' }}</span>
                        earned <span class="points-earned">{{ $scoreData['points_earned'] ?? 0 }}</span> points!
                    </div>

                    @if($scoreData['score_type'] === 'hazari')
                        <div class="hazari-bonus">🔥 Hazari Bonus! +50 Points! 🔥</div>
                    @endif

                    <div class="total-points">Total: {{ $scoreData['total_points'] ?? 0 }}</div>

                    <button wire:click="closeScoreModal" class="continue-btn">Continue</button>
                </div>
            </div>
        </div>
    @endif

    @if($showWinnerModal)
        <div class="modal-overlay">
            <div class="winner-modal">
                <div class="modal-content">
                    <div class="winner-icon">🏆</div>
                    <div class="winner-title">WINNER!</div>
                    <div class="winner-name">🎉 {{ $winnerData['winner']['name'] ?? '' }} 🎉</div>

                    <div class="prize-info">
                        <div class="prize-label">Prize</div>
                        <div class="prize-amount">৳{{ number_format($winnerData['prize_amount'] ?? 0, 2) }}</div>
                    </div>

                    <div class="winner-actions">
                        <button wire:click="closeWinnerModal" class="close-btn">Close</button>
                        <a href="{{ route('games.index') }}" class="new-game-btn">New Game</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Real-time notifications -->
    <div id="game-notifications" class="notifications"></div>

    @push('scripts')
    <script>
        let draggedElement = null;
        let draggedIndex = null;
        let touchStartX = 0;
        let touchStartY = 0;
        let isDragging = false;
        let dragOffset = { x: 0, y: 0 };

        // Initialize enhanced drag and drop
        document.addEventListener('DOMContentLoaded', function() {
            initializeEnhancedDragAndDrop();
            initializeScrollIndicators();
            optimizeForLandscape();
        });

        function initializeEnhancedDragAndDrop() {
            const container = document.getElementById('player-cards-container');
            if (!container) return;

            // Mouse events
            container.addEventListener('dragstart', handleDragStart);
            container.addEventListener('dragover', handleDragOver);
            container.addEventListener('drop', handleDrop);
            container.addEventListener('dragend', handleDragEnd);

            // Touch events for mobile
            container.addEventListener('touchstart', handleTouchStart, { passive: false });
            container.addEventListener('touchmove', handleTouchMove, { passive: false });
            container.addEventListener('touchend', handleTouchEnd, { passive: false });
        }

        function initializeScrollIndicators() {
            const scrollContainer = document.getElementById('cards-scroll');
            const leftIndicator = document.getElementById('left-scroll');
            const rightIndicator = document.getElementById('right-scroll');

            if (!scrollContainer || !leftIndicator || !rightIndicator) return;

            function updateScrollIndicators() {
                const { scrollLeft, scrollWidth, clientWidth } = scrollContainer;

                leftIndicator.style.opacity = scrollLeft > 0 ? '1' : '0.3';
                rightIndicator.style.opacity = scrollLeft < (scrollWidth - clientWidth) ? '1' : '0.3';
            }

            scrollContainer.addEventListener('scroll', updateScrollIndicators);
            updateScrollIndicators();

            // Click handlers for scroll indicators
            leftIndicator.addEventListener('click', () => {
                scrollContainer.scrollBy({ left: -100, behavior: 'smooth' });
            });

            rightIndicator.addEventListener('click', () => {
                scrollContainer.scrollBy({ left: 100, behavior: 'smooth' });
            });
        }

        // Enhanced drag handlers
        function handleDragStart(e) {
            if (!e.target.closest('.draggable-card')) return;

            draggedElement = e.target.closest('.draggable-card');
            draggedIndex = parseInt(draggedElement.dataset.cardIndex);

            draggedElement.classList.add('dragging');
            document.getElementById('drop-zones').style.display = 'flex';

            e.dataTransfer.effectAllowed = 'move';
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }

        function handleDrop(e) {
            e.preventDefault();

            const dropTarget = e.target.closest('.draggable-card');
            if (!dropTarget || dropTarget === draggedElement) return;

            const dropIndex = parseInt(dropTarget.dataset.cardIndex);
            @this.call('reorderCards', draggedIndex, dropIndex);
        }

        function handleDragEnd(e) {
            if (draggedElement) {
                draggedElement.classList.remove('dragging');
                draggedElement = null;
                draggedIndex = null;
            }
            document.getElementById('drop-zones').style.display = 'none';
        }

        // Touch handlers for mobile
        function handleTouchStart(e) {
            if (!e.target.closest('.draggable-card')) return;

            const touch = e.touches[0];
            touchStartX = touch.clientX;
            touchStartY = touch.clientY;

            draggedElement = e.target.closest('.draggable-card');
            draggedIndex = parseInt(draggedElement.dataset.cardIndex);

            const rect = draggedElement.getBoundingClientRect();
            dragOffset.x = touch.clientX - rect.left;
            dragOffset.y = touch.clientY - rect.top;
        }

        function handleTouchMove(e) {
            if (!draggedElement) return;

            const touch = e.touches[0];
            const deltaX = Math.abs(touch.clientX - touchStartX);
            const deltaY = Math.abs(touch.clientY - touchStartY);

            if (!isDragging && (deltaX > 15 || deltaY > 15)) {
                isDragging = true;
                draggedElement.classList.add('dragging');
                document.getElementById('drop-zones').style.display = 'flex';
                createDragGhost(touch.clientX, touch.clientY);
                e.preventDefault();
            }

            if (isDragging) {
                updateDragGhost(touch.clientX, touch.clientY);
                e.preventDefault();
            }
        }

        function handleTouchEnd(e) {
            if (!draggedElement) return;

            if (isDragging) {
                const touch = e.changedTouches[0];
                const dropTarget = getDropTargetFromPoint(touch.clientX, touch.clientY);

                if (dropTarget && dropTarget !== draggedElement) {
                    const dropIndex = parseInt(dropTarget.dataset.cardIndex);
                    @this.call('reorderCards', draggedIndex, dropIndex);
                }

                removeDragGhost();
            }

            if (draggedElement) {
                draggedElement.classList.remove('dragging');
                draggedElement = null;
                draggedIndex = null;
            }

            document.getElementById('drop-zones').style.display = 'none';
            isDragging = false;
        }

        // Helper functions
        function createDragGhost(x, y) {
            const ghost = draggedElement.cloneNode(true);
            ghost.id = 'drag-ghost';
            ghost.style.position = 'fixed';
            ghost.style.left = (x - dragOffset.x) + 'px';
            ghost.style.top = (y - dragOffset.y) + 'px';
            ghost.style.zIndex = '9999';
            ghost.style.pointerEvents = 'none';
            ghost.style.transform = 'rotate(5deg) scale(1.1)';
            ghost.style.opacity = '0.8';
            document.body.appendChild(ghost);
        }

        function updateDragGhost(x, y) {
            const ghost = document.getElementById('drag-ghost');
            if (ghost) {
                ghost.style.left = (x - dragOffset.x) + 'px';
                ghost.style.top = (y - dragOffset.y) + 'px';
            }
        }

        function removeDragGhost() {
            const ghost = document.getElementById('drag-ghost');
            if (ghost) ghost.remove();
        }

        function getDropTargetFromPoint(x, y) {
            const elements = document.elementsFromPoint(x, y);
            return elements.find(el => el.closest('.draggable-card') && !el.closest('.dragging'));
        }

        // Animation for round winner with grid layout
        function animateCardsToWinner(winnerPosition) {
            const playedSections = document.querySelectorAll('.played-card-section');
            const winnerElement = document.getElementById(`player-position-${winnerPosition}`);

            if (!winnerElement) return;

            const winnerRect = winnerElement.getBoundingClientRect();

            playedSections.forEach((section, index) => {
                const cards = section.querySelectorAll('.center-card');

                cards.forEach((card, cardIndex) => {
                    setTimeout(() => {
                        card.style.transition = 'all 1.2s ease-in-out';
                        card.style.transform = `translate(${winnerRect.left - card.getBoundingClientRect().left}px, ${winnerRect.top - card.getBoundingClientRect().top}px) scale(0.3) rotate(${Math.random() * 20 - 10}deg)`;
                        card.style.opacity = '0.6';

                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 1200);
                    }, (index * 200) + (cardIndex * 100));
                });
            });

            // Clear center after animation
            setTimeout(() => {
                document.getElementById('center-cards').innerHTML = '<div class="center-placeholder"><div class="placeholder-icon"><i class="fas fa-layer-group"></i></div><span class="placeholder-text">Played Cards</span></div>';
            }, 2500);
        }

        // Landscape optimization
        function optimizeForLandscape() {
            function adjustLayout() {
                const isLandscape = window.innerWidth > window.innerHeight;
                const isMobile = window.innerWidth < 768;

                if (isLandscape && isMobile) {
                    document.body.classList.add('mobile-landscape');
                    // Force full screen
                    setTimeout(() => {
                        window.scrollTo(0, 0);
                        if (document.documentElement.requestFullscreen) {
                            document.documentElement.requestFullscreen().catch(() => {});
                        }
                    }, 100);
                } else {
                    document.body.classList.remove('mobile-landscape');
                }
            }

            adjustLayout();
            window.addEventListener('orientationchange', () => {
                setTimeout(adjustLayout, 200);
            });
            window.addEventListener('resize', adjustLayout);
        }

        // Real-time event listeners
        window.addEventListener('gameUpdated', event => {
            showNotification(event.detail.data.message || 'Game updated');
            @this.call('loadGameState');
        });

        window.addEventListener('cardPlayed', event => {
            showNotification(`${event.detail.player_name} played cards`);
            @this.call('loadGameState');
        });

        window.addEventListener('roundWinner', event => {
            const winnerPosition = event.detail.winner_position;
            const winnerName = event.detail.winner_name;

            showNotification(`${winnerName} wins the round!`);

            setTimeout(() => {
                animateCardsToWinner(winnerPosition);
            }, 1000);
        });

        window.addEventListener('hideScoreModal', () => {
            setTimeout(() => {
                @this.call('closeScoreModal');
            }, 5000);
        });

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;

            document.getElementById('game-notifications').appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Auto-refresh game state
        setInterval(() => {
            @this.call('loadGameState');
        }, 3000);

        // Reinitialize after Livewire updates
        document.addEventListener('livewire:updated', function() {
            initializeEnhancedDragAndDrop();
            initializeScrollIndicators();
        });
    </script>
    @endpush

    <!-- Enhanced Full-Screen Styles with Grid Center Layout -->
    <style>
        /* Base container - Absolute full screen */
        .game-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            height: 100dvh; /* Dynamic viewport height */
            background: linear-gradient(135deg, #1e3a8a 0%, #059669 100%);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
            user-select: none;
        }

        /* Compact header - Fixed height */
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
            padding: 0 12px;
        }

        .game-info {
            flex: 1;
        }

        .game-title {
            font-size: 13px;
            font-weight: bold;
            color: white;
            margin: 0;
            line-height: 1.1;
        }

        .game-stats {
            display: flex;
            gap: 8px;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 2px;
        }

        /* Ultra compact scoreboard */
        .scoreboard {
            display: flex;
            gap: 6px;
        }

        .player-score {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            padding: 3px 5px;
            text-align: center;
            min-width: 40px;
            font-size: 9px;
            transition: all 0.3s ease;
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

        .player-name {
            color: white;
            font-weight: bold;
            line-height: 1;
        }

        .player-points {
            color: #fbbf24;
            font-weight: bold;
            font-size: 11px;
        }

        .player-wins {
            color: rgba(255, 255, 255, 0.7);
            font-size: 8px;
        }

        /* Game table - Flexible height */
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
            height: 180px;
            background: rgba(34, 197, 94, 0.8);
            border-radius: 50%;
            border: 3px solid rgba(34, 197, 94, 0.6);
        }

        /* Enhanced center cards with grid layout */
        .center-cards {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .played-cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 8px;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-items: center;
        }

        .played-card-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(21, 128, 61, 0.8);
            border-radius: 8px;
            padding: 6px;
            min-width: 120px;
            min-height: 60px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .played-card-section:hover {
            background: rgba(21, 128, 61, 0.9);
            transform: scale(1.02);
        }

        /* Grid positioning */
        .grid-bottom {
            grid-column: 1 / 3;
            grid-row: 2;
        }

        .grid-left {
            grid-column: 1;
            grid-row: 1;
        }

        .grid-top {
            grid-column: 1 / 3;
            grid-row: 1;
        }

        .grid-right {
            grid-column: 2;
            grid-row: 1;
        }

        /* When only 2 players */
        .played-cards-grid:has(.played-card-section:nth-child(2):last-child) {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr;
        }

        .played-cards-grid:has(.played-card-section:nth-child(2):last-child) .grid-bottom {
            grid-column: 1;
            grid-row: 1;
        }

        .played-cards-grid:has(.played-card-section:nth-child(2):last-child) .grid-top {
            grid-column: 2;
            grid-row: 1;
        }

        .player-info {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 4px;
        }

        .player-name-badge {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
        }

        .points-badge {
            background: #fbbf24;
            color: #1f2937;
            padding: 1px 4px;
            border-radius: 6px;
            font-size: 8px;
            font-weight: bold;
        }

        .cards-display {
            display: flex;
            gap: 3px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .center-card {
            transition: all 0.3s ease;
        }

        .center-card:hover {
            transform: translateY(-2px);
        }

        .center-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            gap: 8px;
            background: rgba(21, 128, 61, 0.6);
            border-radius: 12px;
            padding: 20px;
            border: 2px dashed rgba(255, 255, 255, 0.3);
        }

        .placeholder-icon {
            font-size: 24px;
            opacity: 0.7;
        }

        .placeholder-text {
            font-weight: 500;
        }

        /* Player positions */
        .player-position {
            position: absolute;
            z-index: 15;
        }

        .bottom-player {
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
        }

        .left-player {
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
        }

        .top-player {
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
        }

        .right-player {
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
        }

        .player-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 5px 7px;
            text-align: center;
            min-width: 45px;
            font-size: 9px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .player-turn .player-card {
            background: rgba(251, 191, 36, 0.9);
            animation: pulse 1s infinite;
        }

        .card-count {
            font-size: 8px;
            color: #6b7280;
        }

        .current-indicator {
            width: 6px;
            height: 6px;
            background: #3b82f6;
            border-radius: 50%;
            margin: 2px auto 0;
        }

        .turn-indicator {
            font-size: 7px;
            color: #92400e;
            font-weight: bold;
            margin-top: 2px;
        }

        /* Enhanced full-width player cards section */
        .player-cards-section {
            height: calc(100vh - 45px - 180px - 16px); /* Full remaining height */
            min-height: 140px;
            max-height: 200px;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(15px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        .cards-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 15px;
            height: 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .cards-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cards-title {
            color: white;
            font-size: 13px;
            font-weight: bold;
        }

        .cards-count {
            color: rgba(255, 255, 255, 0.7);
            font-size: 11px;
        }

        .cards-controls {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .sort-btn {
            background: rgba(107, 114, 128, 0.8);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 6px;
            font-size: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sort-btn:hover {
            background: rgba(107, 114, 128, 1);
            transform: translateY(-1px);
        }

        .play-btn {
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .play-btn:hover:not(.disabled) {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .play-btn.disabled {
            background: rgba(107, 114, 128, 0.5);
            cursor: not-allowed;
        }

        .selected-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 9px;
        }

        /* Full-width cards container */
        .cards-container {
            flex: 1;
            position: relative;
            padding: 0;
            overflow: hidden;
        }

        .scroll-indicator {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 30px;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.3), transparent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .left-indicator {
            left: 0;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.4), transparent);
        }

        .right-indicator {
            right: 0;
            background: linear-gradient(to left, rgba(0, 0, 0, 0.4), transparent);
        }

        .scroll-indicator:hover {
            background: rgba(0, 0, 0, 0.5);
            color: white;
        }

        .cards-scroll {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            overflow-y: hidden;
            height: 100%;
            align-items: center;
            padding: 10px 40px; /* Space for scroll indicators */
            scroll-behavior: smooth;
        }

        .cards-scroll::-webkit-scrollbar {
            height: 3px;
        }

        .cards-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
        }

        .cards-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        /* Enhanced card wrapper */
        .card-wrapper {
            position: relative;
            flex-shrink: 0;
            cursor: grab;
            transition: all 0.3s ease;
            user-select: none;
            transform-origin: center bottom;
        }

        .card-wrapper:active {
            cursor: grabbing;
        }

        .card-wrapper.selected {
            transform: translateY(-8px) scale(1.05);
            filter: brightness(1.1);
        }

        .card-wrapper.dragging {
            opacity: 0.6;
            transform: rotate(8deg) scale(1.1);
            z-index: 1000;
        }

        .card-position {
            position: absolute;
            top: -10px;
            right: -10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            font-size: 8px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .selection-indicator {
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
            animation: bounce 0.5s ease;
        }

        /* Drop zones overlay */
        .drop-zone-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 100;
        }

        .drop-zone {
            background: rgba(59, 130, 246, 0.8);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            min-width: 80px;
            transition: all 0.3s ease;
        }

        .drop-zone:hover {
            background: rgba(59, 130, 246, 1);
            transform: scale(1.05);
        }

        .drop-zone i {
            display: block;
            font-size: 16px;
            margin-bottom: 4px;
        }

        /* Selected cards summary */
        .selected-summary {
            background: rgba(0, 0, 0, 0.3);
            padding: 8px 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .summary-header {
            display: flex;
            align-items: center;
            gap: 6px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 11px;
            margin-bottom: 4px;
        }

        .selected-cards-list {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .selected-card-item {
            background: rgba(59, 130, 246, 0.3);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .card-symbol {
            font-weight: bold;
        }

        .card-suit {
            opacity: 0.8;
            text-transform: uppercase;
        }

        /* Game controls */
        .game-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 25;
        }

        .start-game-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .waiting-text {
            color: white;
            font-size: 12px;
            text-align: center;
        }

        /* Modal styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            padding: 16px;
            backdrop-filter: blur(5px);
        }

        .score-modal, .winner-modal {
            background: white;
            border-radius: 12px;
            padding: 20px;
            max-width: 320px;
            width: 100%;
            text-align: center;
            animation: modalSlideIn 0.3s ease-out;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .score-icon, .winner-icon {
            font-size: 28px;
            margin-bottom: 12px;
        }

        .score-text, .winner-name {
            font-size: 14px;
            margin-bottom: 12px;
        }

        .points-earned {
            color: #3b82f6;
            font-weight: bold;
        }

        .hazari-bonus {
            font-size: 12px;
            color: #f59e0b;
            margin-bottom: 12px;
        }

        .total-points {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .continue-btn, .close-btn, .new-game-btn {
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 12px;
            cursor: pointer;
            margin: 0 4px;
            transition: all 0.2s ease;
        }

        .continue-btn:hover, .close-btn:hover {
            background: #2563eb;
        }

        .new-game-btn {
            background: #10b981;
        }

        .new-game-btn:hover {
            background: #059669;
        }

        /* Notifications */
        .notifications {
            position: fixed;
            top: 55px;
            right: 12px;
            z-index: 40;
            max-width: 200px;
        }

        .notification {
            background: #3b82f6;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 8px;
            font-size: 11px;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
            40%, 43% { transform: translate3d(0,-8px,0); }
            70% { transform: translate3d(0,-4px,0); }
            90% { transform: translate3d(0,-2px,0); }
        }

        @keyframes modalSlideIn {
            from {
                transform: scale(0.9) translateY(-20px);
                opacity: 0;
            }
            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Mobile landscape optimizations */
        @media screen and (orientation: landscape) and (max-height: 500px) {
            .game-header {
                height: 35px;
            }

            .game-table {
                height: 140px;
            }

            .center-cards {
                width: 240px;
                height: 120px;
            }

            .played-card-section {
                min-width: 100px;
                min-height: 50px;
                padding: 4px;
            }

            .player-cards-section {
                min-height: 120px;
                max-height: 160px;
            }

            .cards-header {
                height: 32px;
                padding: 4px 15px;
            }

            .cards-title {
                font-size: 12px;
            }

            .cards-count {
                font-size: 10px;
            }
        }

        /* Very small screens */
        @media screen and (max-width: 480px) {
            .scoreboard {
                gap: 4px;
            }

            .player-score {
                min-width: 35px;
                padding: 2px 4px;
            }

            .cards-scroll {
                gap: 4px;
                padding: 8px 35px;
            }

            .center-cards {
                width: 260px;
            }

            .played-card-section {
                min-width: 110px;
            }
        }

        /* Prevent text selection during drag */
        .dragging * {
            user-select: none !important;
            -webkit-user-select: none !important;
        }

        /* Touch improvements */
        .card-wrapper {
            touch-action: none;
        }

        /* Full screen body class */
        body.mobile-landscape {
            overflow: hidden;
            position: fixed;
            width: 100%;
            height: 100%;
        }

        body.mobile-landscape .game-container {
            height: 100vh;
            height: 100dvh;
        }

        /* Hide address bar on mobile */
        @media screen and (max-height: 500px) {
            body {
                height: 100vh;
                height: 100dvh;
            }
        }
    </style>
</div>

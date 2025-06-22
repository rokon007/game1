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

                                <!-- Player info badge -->
                                {{-- <div class="fan-stack-badge">
                                    <div class="badge-content">
                                        <span class="badge-name">{{ Str::limit($move['player'], 4) }}</span>
                                        <span class="badge-turn">T{{ $move['turn'] }}</span>
                                    </div>
                                    @if($move['points'] > 0)
                                        <div class="badge-points">{{ $move['points'] }}pts</div>
                                    @endif
                                </div> --}}

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
                        <span class="placeholder-text">Center Cards</span>
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
                        {{-- <div class="player-avatar">
                            {{ substr($participant->user->name, 0, 1) }}
                        </div> --}}
                        <div class="player-info">
                            <div class="{{$isCurrentTurn ? 'player-name' : 'player-name-dark'}}">{{ Str::limit($participant->user->name, 8) }}</div>
                            {{-- <div class="player-stats">
                                <span class="card-count">{{ is_array($participant->cards) ? count($participant->cards) : 0 }} cards</span>
                                <span class="player-points">{{ $participant->total_points ?? 0 }}pts</span>
                            </div> --}}
                        </div>
                        @if($participant->user_id === Auth::id())
                            {{-- <div class="current-indicator">
                                <i class="fas fa-user"></i>
                            </div> --}}
                        @endif
                        @if($isCurrentTurn)
                            <div class="turn-indicator">
                                <i class="fas fa-play"></i>
                                <span>TURN</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Enhanced Player Cards Section with Better UX -->
    @if($player && $player->cards)
        <div class="player-cards-section">
            <div class="enhanced-cards-header">
                <div class="header-left">
                    <div class="cards-title-section">
                        <div class="cards-title">Your Cards</div>
                        <div class="cards-count">({{ count($player->cards) }} cards)</div>

                        <!-- Enhanced selection limit indicator -->
                        @php
                            $maxSelection = count($player->cards) <= 4 ? count($player->cards) : 3;
                            $isMyTurn = ($gameState['current_turn'] ?? 1) === $player->position;
                        @endphp
                        <div class="selection-limit {{ $isMyTurn ? 'my-turn' : '' }}">
                            <i class="fas fa-hand-paper"></i>
                            <span>Max: {{ $maxSelection }}</span>
                            @if($isMyTurn)
                                <span class="turn-badge">YOUR TURN</span>
                            @endif
                        </div>
                    </div>

                    <!-- Selected cards moved to header -->
                    @if(!empty($selectedCards))
                        <div class="header-selected-summary">
                            <div class="selected-indicator">
                                <i class="fas fa-check-circle"></i>
                                <span class="selected-count-badge">{{ count($selectedCards) }}</span>
                            </div>
                            <div class="header-selected-cards">
                                @foreach($selectedCards as $index)
                                    @if(isset($player->cards[$index]))
                                        <span class="header-selected-card">
                                            <span class="card-rank">{{ $player->cards[$index]['rank'] }}</span>
                                            <span class="card-suit-icon" style="color: {{ in_array($player->cards[$index]['suit'], ['hearts', 'diamonds']) ? '#dc2626' : '#1f2937' }}">{{
                                                match($player->cards[$index]['suit']) {
                                                    'hearts' => '♥',
                                                    'diamonds' => '♦',
                                                    'clubs' => '♣',
                                                    'spades' => '♠',
                                                    default => '?'
                                                }
                                            }}</span>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="header-controls">
                    <button wire:click="sortCardsBySuit" class="sort-btn" title="Sort by Suit">
                        <i class="fas fa-sort-alpha-down"></i>
                    </button>
                    <button wire:click="sortCardsByRank" class="sort-btn" title="Sort by Rank">
                        <i class="fas fa-sort-numeric-down"></i>
                    </button>
                    @if($game->status === 'playing')
                        @php
                            $canPlay = !empty($selectedCards) && $isMyTurn;
                            $remainingCards = count($player->cards);
                            $mustPlayAll = $remainingCards <= 4 && count($selectedCards) !== $remainingCards;
                        @endphp
                        <button wire:click="playCards"
                                class="enhanced-play-btn {{ !$canPlay || $mustPlayAll ? 'disabled' : '' }}"
                                {{ !$canPlay || $mustPlayAll ? 'disabled' : '' }}>
                            <i class="fas fa-play"></i>
                            <span class="btn-text">Play</span>
                            @if(!empty($selectedCards))
                                <span class="play-count">({{ count($selectedCards) }})</span>
                            @endif
                            @if($mustPlayAll)
                                <span class="play-warning">!</span>
                            @endif
                        </button>

                        <!-- Enhanced play button feedback -->
                        @if($mustPlayAll)
                            <div class="play-feedback warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Must play all {{ $remainingCards }} cards</span>
                            </div>
                        @elseif(!$isMyTurn)
                            <div class="play-feedback info">
                                <i class="fas fa-clock"></i>
                                <span>Wait for your turn</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Enhanced Cards Container with Better UX -->
            <div class="enhanced-cards-container" id="cards-container">
                <!-- Left scroll indicator -->
                <div class="scroll-indicator left-indicator" id="left-scroll">
                    <i class="fas fa-chevron-left"></i>
                </div>

                <!-- Cards scroll area -->
                <div class="cards-scroll" id="cards-scroll">
                    @foreach($player->cards as $index => $card)
                        @php
                            $maxSelection = count($player->cards) <= 4 ? count($player->cards) : 3;
                            $isSelected = in_array($index, $selectedCards);
                            $canSelect = $isMyTurn && (count($selectedCards) < $maxSelection || $isSelected);
                            $remainingCards = count($player->cards);
                            $isLastFew = $remainingCards <= 4;
                        @endphp

                        <div class="enhanced-card-wrapper draggable-card
                                    {{ $isSelected ? 'selected' : '' }}
                                    {{ !$isMyTurn ? 'not-my-turn' : '' }}
                                    {{ $isLastFew ? 'last-few' : '' }}"
                             data-card-index="{{ $index }}"
                             draggable="{{ $canSelect ? 'true' : 'false' }}"
                             wire:click="toggleCardSelection({{ $index }})">

                            <!-- Card component -->
                            <x-playing-card
                                :suit="$card['suit']"
                                :rank="$card['rank']"
                                :selected="$isSelected"
                                :clickable="$canSelect"
                                size="normal" />

                            <!-- Enhanced card position indicator -->
                            {{-- <div class="enhanced-card-position">{{ $index + 1 }}</div> --}}

                            <!-- Enhanced selection indicator -->
                            @if($isSelected)
                                <div class="enhanced-selection-indicator">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            @endif

                            <!-- Last few cards indicator -->
                            @if($isLastFew)
                                {{-- <div class="last-few-indicator">
                                    <i class="fas fa-exclamation"></i>
                                </div> --}}
                            @endif

                            <!-- Turn status overlay - only show when not selectable due to selection limit -->
                            @if(!$canSelect && $isMyTurn && !$isSelected)
                                {{-- <div class="selection-limit-overlay">
                                    <i class="fas fa-ban"></i>
                                    <span class="limit-text">Limit</span>
                                </div> --}}
                            @endif

                            <!-- Not my turn indicator -->
                            @if(!$isMyTurn)
                                {{-- <div class="turn-wait-indicator">
                                    <i class="fas fa-clock"></i>
                                </div> --}}
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Right scroll indicator -->
                <div class="scroll-indicator right-indicator" id="right-scroll">
                    <i class="fas fa-chevron-right"></i>
                </div>

                <!-- Enhanced drop zones -->
                <div class="enhanced-drop-zone-overlay" id="drop-zones" style="display: none;">
                    <div class="enhanced-drop-zone left-drop-zone">
                        <i class="fas fa-arrow-left"></i>
                        <span>Move Left</span>
                    </div>
                    <div class="enhanced-drop-zone right-drop-zone">
                        <i class="fas fa-arrow-right"></i>
                        <span>Move Right</span>
                    </div>
                </div>
            </div>
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
        let dragStartTime = 0;

        // Initialize enhanced drag and drop
        document.addEventListener('DOMContentLoaded', function() {
            initializeEnhancedDragAndDrop();
            initializeScrollIndicators();
            optimizeForLandscape();
        });

        function initializeEnhancedDragAndDrop() {
            const container = document.getElementById('cards-container');
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
            if (!e.target.closest('.draggable-card') || e.target.closest('.disabled') || e.target.closest('.wait-overlay')) return;

            draggedElement = e.target.closest('.draggable-card');
            draggedIndex = parseInt(draggedElement.dataset.cardIndex);

            draggedElement.classList.add('dragging');
            document.getElementById('drop-zones').style.display = 'flex';

            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', '');
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const dropTarget = e.target.closest('.draggable-card');
            if (dropTarget && dropTarget !== draggedElement && !dropTarget.classList.contains('disabled')) {
                dropTarget.classList.add('drag-over');
            }
        }

        function handleDrop(e) {
            e.preventDefault();

            // Remove drag-over class from all cards
            document.querySelectorAll('.drag-over').forEach(el => {
                el.classList.remove('drag-over');
            });

            const dropTarget = e.target.closest('.draggable-card');
            if (!dropTarget || dropTarget === draggedElement || dropTarget.classList.contains('disabled')) return;

            const dropIndex = parseInt(dropTarget.dataset.cardIndex);
            if (draggedIndex !== null && dropIndex !== null) {
                @this.call('reorderCards', draggedIndex, dropIndex);
            }
        }

        function handleDragEnd(e) {
            // Remove drag-over class from all cards
            document.querySelectorAll('.drag-over').forEach(el => {
                el.classList.remove('drag-over');
            });

            if (draggedElement) {
                draggedElement.classList.remove('dragging');
                draggedElement = null;
                draggedIndex = null;
            }
            document.getElementById('drop-zones').style.display = 'none';
        }

        // Enhanced touch handlers for mobile
        function handleTouchStart(e) {
            if (!e.target.closest('.draggable-card') || e.target.closest('.disabled') || e.target.closest('.wait-overlay')) return;

            const touch = e.touches[0];
            touchStartX = touch.clientX;
            touchStartY = touch.clientY;
            dragStartTime = Date.now();

            draggedElement = e.target.closest('.draggable-card');
            draggedIndex = parseInt(draggedElement.dataset.cardIndex);

            const rect = draggedElement.getBoundingClientRect();
            dragOffset.x = touch.clientX - rect.left;
            dragOffset.y = touch.clientY - rect.top;

            // Prevent default to avoid scrolling
            e.preventDefault();
        }

        function handleTouchMove(e) {
            if (!draggedElement) return;

            const touch = e.touches[0];
            const deltaX = Math.abs(touch.clientX - touchStartX);
            const deltaY = Math.abs(touch.clientY - touchStartY);
            const timeDelta = Date.now() - dragStartTime;

            // Start dragging if moved enough distance or held long enough
            if (!isDragging && (deltaX > 10 || deltaY > 10 || timeDelta > 500)) {
                isDragging = true;
                draggedElement.classList.add('dragging');
                document.getElementById('drop-zones').style.display = 'flex';
                createDragGhost(touch.clientX, touch.clientY);

                // Add haptic feedback if available
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
            }

            if (isDragging) {
                updateDragGhost(touch.clientX, touch.clientY);

                // Check for drop target
                const elementBelow = document.elementFromPoint(touch.clientX, touch.clientY);
                const dropTarget = elementBelow?.closest('.draggable-card');

                // Remove previous drag-over classes
                document.querySelectorAll('.drag-over').forEach(el => {
                    el.classList.remove('drag-over');
                });

                // Add drag-over class to current target
                if (dropTarget && dropTarget !== draggedElement && !dropTarget.classList.contains('disabled')) {
                    dropTarget.classList.add('drag-over');
                }
            }

            e.preventDefault();
        }

        function handleTouchEnd(e) {
            if (!draggedElement) return;

            if (isDragging) {
                const touch = e.changedTouches[0];
                const elementBelow = document.elementFromPoint(touch.clientX, touch.clientY);
                const dropTarget = elementBelow?.closest('.draggable-card');

                if (dropTarget && dropTarget !== draggedElement && !dropTarget.classList.contains('disabled')) {
                    const dropIndex = parseInt(dropTarget.dataset.cardIndex);
                    if (draggedIndex !== null && dropIndex !== null) {
                        @this.call('reorderCards', draggedIndex, dropIndex);
                    }
                }

                removeDragGhost();
            }

            // Remove drag-over class from all cards
            document.querySelectorAll('.drag-over').forEach(el => {
                el.classList.remove('drag-over');
            });

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
            ghost.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.3)';
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

        // Enhanced animation for round winner with fan stacks
        function animateCardsToWinner(winnerPosition) {
            const playerStacks = document.querySelectorAll('.fan-player-stack');
            const winnerElement = document.getElementById(`player-position-${winnerPosition}`);

            if (!winnerElement) return;

            const winnerRect = winnerElement.getBoundingClientRect();

            playerStacks.forEach((stack, index) => {
                const cards = stack.querySelectorAll('.fan-stacked-card');

                cards.forEach((card, cardIndex) => {
                    setTimeout(() => {
                        card.style.transition = 'all 1.8s ease-in-out';
                        card.style.transform = `translate(${winnerRect.left - card.getBoundingClientRect().left}px, ${winnerRect.top - card.getBoundingClientRect().top}px) scale(0.15) rotate(${Math.random() * 60 - 30}deg)`;
                        card.style.opacity = '0.3';

                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 1800);
                    }, (index * 400) + (cardIndex * 200));
                });
            });

            // Clear center after animation
            setTimeout(() => {
                clearCenterCards();
            }, 3500);
        }

        function clearCenterCards() {
            const centerCards = document.getElementById('center-cards');
            if (centerCards) {
                centerCards.innerHTML = '<div class="center-placeholder"><div class="placeholder-icon"><i class="fas fa-layer-group"></i></div><span class="placeholder-text">Center Cards</span></div>';
            }
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

        window.addEventListener('clearCenterCards', () => {
            clearCenterCards();
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

    <!-- Enhanced Styles with Turn Management and Final Cards -->
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
            height: 100dvh;
            background: linear-gradient(135deg, #1e3a8a 0%, #059669 100%);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            font-family: 'Arial', sans-serif;
            user-select: none;
        }

        /* Compact header */
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

        /* Scoreboard */
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

        .player-name-dark {
            color: black;
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
            height: 180px;
            background: rgba(34, 197, 94, 0.8);
            border-radius: 50%;
            border: 3px solid rgba(34, 197, 94, 0.6);
        }

        /* Fan style center cards */
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

        .fan-stack-badge {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(31, 41, 55, 0.9));
            color: white;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 8px;
            margin-bottom: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
            z-index: 100;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .badge-content {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge-name {
            font-weight: bold;
            color: #e5e7eb;
        }

        .badge-turn {
            background: rgba(59, 130, 246, 0.8);
            color: white;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 7px;
        }

        .badge-points {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #1f2937;
            padding: 2px 6px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 7px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Fan style card stack - Cards overlap with visible edges */
        .fan-card-stack {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
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

        /* Enhanced player cards section with turn management */
        .player-cards-section {
            height: calc(100vh - 45px - 180px - 16px);
            min-height: 140px;
            max-height: 200px;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(15px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        /* Enhanced cards header with turn indicators */
        .enhanced-cards-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 15px;
            min-height: 50px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
            gap: 8px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            flex-wrap: wrap;
        }

        .cards-title-section {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
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

        /* Enhanced selection limit indicator with turn status */
        .selection-limit {
            display: flex;
            align-items: center;
            gap: 4px;
            background: rgba(168, 85, 247, 0.3);
            color: white;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid rgba(168, 85, 247, 0.4);
            transition: all 0.3s ease;
        }

        .selection-limit.my-turn {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.4), rgba(245, 158, 11, 0.4));
            border-color: rgba(251, 191, 36, 0.6);
            animation: pulse 1s infinite;
        }

        .turn-badge {
            background: rgba(251, 191, 36, 0.9);
            color: #1f2937;
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: bold;
            margin-left: 4px;
        }

        /* Header selected summary */
        .header-selected-summary {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(37, 99, 235, 0.3));
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid rgba(59, 130, 246, 0.4);
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }

        .selected-indicator {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .selected-count-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            font-size: 8px;
            font-weight: bold;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            animation: bounce 0.5s ease;
        }

        .header-selected-cards {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            max-width: 200px;
            overflow-x: auto;
        }

        .header-selected-card {
            background: rgba(255, 255, 255, 0.9);
            color: #1f2937;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 2px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .card-rank {
            font-weight: bold;
            color: #1f2937;
        }

        .card-suit-icon {
            font-size: 12px;
        }

        .header-controls {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-shrink: 0;
            flex-wrap: wrap;
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

        /* Enhanced play button with better feedback */
        .enhanced-play-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
            position: relative;
        }

        .enhanced-play-btn:hover:not(.disabled) {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
        }

        .enhanced-play-btn.disabled {
            background: rgba(107, 114, 128, 0.5);
            cursor: not-allowed;
            box-shadow: none;
        }

        .play-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 9px;
        }

        .play-warning {
            background: #ef4444;
            color: white;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 9px;
            animation: pulse 1s infinite;
        }

        /* Play feedback messages */
        .play-feedback {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: bold;
            margin-left: 8px;
        }

        .play-feedback.warning {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .play-feedback.info {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* Enhanced cards container */
        .enhanced-cards-container {
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
            padding: 10px 40px;
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

        /* Enhanced card wrapper with turn and final card states */
        .enhanced-card-wrapper {
            position: relative;
            flex-shrink: 0;
            cursor: grab;
            transition: all 0.3s ease;
            user-select: none;
            transform-origin: center bottom;
            touch-action: none;
        }

        .enhanced-card-wrapper:active {
            cursor: grabbing;
        }

        .enhanced-card-wrapper.selected {
            transform: translateY(-10px) scale(1.08);
            filter: brightness(1.15);
        }

        .enhanced-card-wrapper.dragging {
            opacity: 0.6;
            transform: rotate(8deg) scale(1.1);
            z-index: 1000;
        }

        .enhanced-card-wrapper.drag-over {
            transform: translateY(-4px) scale(1.02);
            filter: brightness(1.2);
        }

        /* Last few cards styling */
        .enhanced-card-wrapper.last-few {
            border: 2px solid rgba(239, 68, 68, 0.4);
            border-radius: 10px;
            background: rgba(239, 68, 68, 0.1);
        }

        /* Disabled card styling */
        .enhanced-card-wrapper.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(0.5);
        }

        .disabled-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            font-size: 16px;
            border-radius: 8px;
            z-index: 10;
        }

        .wait-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 14px;
            border-radius: 8px;
            z-index: 10;
        }

        .enhanced-card-position {
            position: absolute;
            top: -12px;
            right: -12px;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(31, 41, 55, 0.9));
            color: white;
            font-size: 8px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .enhanced-selection-indicator {
            position: absolute;
            top: -10px;
            left: -10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-size: 12px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounce 0.5s ease;
            box-shadow: 0 3px 6px rgba(16, 185, 129, 0.4);
            border: 2px solid white;
        }

        /* Last few cards indicator */
        .last-few-indicator {
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #ef4444;
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 1s infinite;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
            border: 2px solid white;
        }

        /* Enhanced drop zones */
        .enhanced-drop-zone-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 100;
            backdrop-filter: blur(2px);
        }

        .enhanced-drop-zone {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(37, 99, 235, 0.9));
            color: white;
            padding: 24px;
            border-radius: 16px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            min-width: 90px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .enhanced-drop-zone:hover {
            background: linear-gradient(135deg, rgba(37, 99, 235, 1), rgba(29, 78, 216, 1));
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.6);
        }

        .enhanced-drop-zone i {
            display: block;
            font-size: 18px;
            margin-bottom: 6px;
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
            background: linear-gradient(135deg, #10b981, #059669);
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
                width: 260px;
                height: 120px;
            }

            .player-cards-section {
                min-height: 120px;
                max-height: 160px;
            }

            .enhanced-cards-header {
                min-height: 40px;
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

            .enhanced-cards-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                min-height: 60px;
            }

            .header-left {
                width: 100%;
                justify-content: space-between;
            }

            .header-controls {
                width: 100%;
                justify-content: flex-end;
            }

            .header-selected-summary {
                order: 3;
                width: 100%;
            }
        }

        /* Prevent text selection during drag */
        .dragging * {
            user-select: none !important;
            -webkit-user-select: none !important;
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
{{--</existing_code>

Now, look at the updates to be made:
<updates>
In the CSS section, replace the player positions and card wrapper styles with:

\`\`\`css
/* Player positions - Outside center circle for better visibility */
.player-position {
    position: absolute;
    z-index: 15;
}

.bottom-player {
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
}

.left-player {
    left: -35px;
    top: 50%;
    transform: translateY(-50%);
}

.top-player {
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
}

.right-player {
    right: -35px;
    top: 50%;
    transform: translateY(-50%);
}

.player-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.95));
    border-radius: 12px;
    padding: 8px 10px;
    text-align: center;
    min-width: 70px;
    font-size: 9px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    position: relative;
    transition: all 0.3s ease;
}

.player-turn .player-card {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.95), rgba(245, 158, 11, 0.95));
    border-color: rgba(251, 191, 36, 0.8);
    animation: pulse 1.5s infinite;
    box-shadow: 0 4px 16px rgba(251, 191, 36, 0.4);
}

.player-avatar {
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 10px;
    margin: 0 auto 4px;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.player-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.player-name {
    font-weight: bold;
    color: #1f2937;
    font-size: 10px;
    line-height: 1.1;
}

.player-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 4px;
    font-size: 8px;
}

.card-count {
    color: #6b7280;
    background: rgba(107, 114, 128, 0.1);
    padding: 1px 4px;
    border-radius: 4px;
}

.player-points {
    color: #059669;
    font-weight: bold;
    background: rgba(5, 150, 105, 0.1);
    padding: 1px 4px;
    border-radius: 4px;
}

.current-indicator {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 18px;
    height: 18px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

.turn-indicator {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 7px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 2px;
    border: 2px solid white;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
    animation: bounce 1s infinite;
}

/* Enhanced card wrapper with better UX */
.enhanced-card-wrapper {
    position: relative;
    flex-shrink: 0;
    cursor: grab;
    transition: all 0.3s ease;
    user-select: none;
    transform-origin: center bottom;
    touch-action: none;
}

.enhanced-card-wrapper:active {
    cursor: grabbing;
}

.enhanced-card-wrapper.selected {
    transform: translateY(-10px) scale(1.08);
    filter: brightness(1.15);
    z-index: 10;
}

.enhanced-card-wrapper.dragging {
    opacity: 0.6;
    transform: rotate(8deg) scale(1.1);
    z-index: 1000;
}

.enhanced-card-wrapper.drag-over {
    transform: translateY(-4px) scale(1.02);
    filter: brightness(1.2);
}

/* Not my turn styling - subtle but not disabled */
.enhanced-card-wrapper.not-my-turn {
    opacity: 0.7;
    cursor: default;
}

.enhanced-card-wrapper.not-my-turn:hover {
    opacity: 0.8;
    transform: translateY(-2px);
}

/* Last few cards styling */
.enhanced-card-wrapper.last-few {
    border: 2px solid rgba(239, 68, 68, 0.4);
    border-radius: 10px;
    background: rgba(239, 68, 68, 0.05);
}

/* Selection limit overlay - only for selection limit, not turn */
.selection-limit-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(168, 85, 247, 0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #a855f7;
    font-size: 12px;
    border-radius: 8px;
    z-index: 5;
    backdrop-filter: blur(1px);
}

.limit-text {
    font-size: 8px;
    font-weight: bold;
    margin-top: 2px;
}

/* Turn wait indicator - subtle and informative */
.turn-wait-indicator {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    background: rgba(107, 114, 128, 0.8);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    z-index: 5;
    opacity: 0.8;
}

.enhanced-card-position {
    position: absolute;
    top: -12px;
    right: -12px;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(31, 41, 55, 0.9));
    color: white;
    font-size: 8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border: 2px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.enhanced-selection-indicator {
    position: absolute;
    top: -10px;
    left: -10px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    font-size: 12px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: bounce 0.5s ease;
    box-shadow: 0 3px 6px rgba(16, 185, 129, 0.4);
    border: 2px solid white;
    z-index: 10;
}

/* Last few cards indicator */
.last-few-indicator {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    background: #ef4444;
    color: white;
    font-size: 10px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 1s infinite;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
    border: 2px solid white;
    z-index: 8;
}

/* Mobile landscape optimizations for player positions */
@media screen and (orientation: landscape) and (max-height: 500px) {
    .bottom-player {
        bottom: -20px;
    }

    .top-player {
        top: -20px;
    }

    .left-player {
        left: -30px;
    }

    .right-player {
        right: -30px;
    }

    .player-card {
        min-width: 60px;
        padding: 6px 8px;
    }

    .player-avatar {
        width: 20px;
        height: 20px;
        font-size: 9px;
    }

    .player-name {
        font-size: 9px;
    }

    .player-stats {
        font-size: 7px;
    }
}

/* Very small screens */
@media screen and (max-width: 480px) {
    .bottom-player {
        bottom: -18px;
    }

    .top-player {
        top: -18px;
    }

    .left-player {
        left: -25px;
    }

    .right-player {
        right: -25px;
    }

    .player-card {
        min-width: 55px;
        padding: 5px 6px;
    }

    .player-avatar {
        width: 18px;
        height: 18px;
        font-size: 8px;
    }

    .player-name {
        font-size: 8px;
    }

    .player-stats {
        font-size: 6px;
    }
}
\`\`\`--}}


    <!-- Enhanced Styles with Turn Management and Final Cards -->
    {{-- <style>
        /* Base container - Absolute full screen */
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

        /* Compact header */
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

        /* Scoreboard */
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
            height: 180px;
            background: rgba(34, 197, 94, 0.8);
            border-radius: 50%;
            border: 3px solid rgba(34, 197, 94, 0.6);
        }

        /* Fan style center cards */
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

        .fan-stack-badge {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(31, 41, 55, 0.9));
            color: white;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 8px;
            margin-bottom: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
            z-index: 100;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .badge-content {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge-name {
            font-weight: bold;
            color: #e5e7eb;
        }

        .badge-turn {
            background: rgba(59, 130, 246, 0.8);
            color: white;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 7px;
        }

        .badge-points {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #1f2937;
            padding: 2px 6px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 7px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Fan style card stack - Cards overlap with visible edges */
        .fan-card-stack {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
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

        /* Enhanced player cards section with turn management */
        .player-cards-section {
            height: calc(100vh - 45px - 180px - 16px);
            min-height: 140px;
            max-height: 200px;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(15px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        /* Enhanced cards header with turn indicators */
        .enhanced-cards-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 15px;
            min-height: 50px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
            gap: 8px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            flex-wrap: wrap;
        }

        .cards-title-section {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
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

        /* Enhanced selection limit indicator with turn status */
        .selection-limit {
            display: flex;
            align-items: center;
            gap: 4px;
            background: rgba(168, 85, 247, 0.3);
            color: white;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid rgba(168, 85, 247, 0.4);
            transition: all 0.3s ease;
        }

        .selection-limit.my-turn {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.4), rgba(245, 158, 11, 0.4));
            border-color: rgba(251, 191, 36, 0.6);
            animation: pulse 1s infinite;
        }

        .turn-badge {
            background: rgba(251, 191, 36, 0.9);
            color: #1f2937;
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: bold;
            margin-left: 4px;
        }

        /* Header selected summary */
        .header-selected-summary {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(37, 99, 235, 0.3));
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid rgba(59, 130, 246, 0.4);
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }

        .selected-indicator {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .selected-count-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            font-size: 8px;
            font-weight: bold;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            animation: bounce 0.5s ease;
        }

        .header-selected-cards {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            max-width: 200px;
            overflow-x: auto;
        }

        .header-selected-card {
            background: rgba(255, 255, 255, 0.9);
            color: #1f2937;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 2px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .card-rank {
            font-weight: bold;
            color: #1f2937;
        }

        .card-suit-icon {
            font-size: 12px;
        }

        .header-controls {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-shrink: 0;
            flex-wrap: wrap;
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

        /* Enhanced play button with better feedback */
        .enhanced-play-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
            position: relative;
        }

        .enhanced-play-btn:hover:not(.disabled) {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
        }

        .enhanced-play-btn.disabled {
            background: rgba(107, 114, 128, 0.5);
            cursor: not-allowed;
            box-shadow: none;
        }

        .play-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 9px;
        }

        .play-warning {
            background: #ef4444;
            color: white;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 9px;
            animation: pulse 1s infinite;
        }

        /* Play feedback messages */
        .play-feedback {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: bold;
            margin-left: 8px;
        }

        .play-feedback.warning {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .play-feedback.info {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* Enhanced cards container */
        .enhanced-cards-container {
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
            padding: 10px 40px;
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

        /* Enhanced card wrapper with turn and final card states */
        .enhanced-card-wrapper {
            position: relative;
            flex-shrink: 0;
            cursor: grab;
            transition: all 0.3s ease;
            user-select: none;
            transform-origin: center bottom;
            touch-action: none;
        }

        .enhanced-card-wrapper:active {
            cursor: grabbing;
        }

        .enhanced-card-wrapper.selected {
            transform: translateY(-10px) scale(1.08);
            filter: brightness(1.15);
        }

        .enhanced-card-wrapper.dragging {
            opacity: 0.6;
            transform: rotate(8deg) scale(1.1);
            z-index: 1000;
        }

        .enhanced-card-wrapper.drag-over {
            transform: translateY(-4px) scale(1.02);
            filter: brightness(1.2);
        }

        /* Last few cards styling */
        .enhanced-card-wrapper.last-few {
            border: 2px solid rgba(239, 68, 68, 0.4);
            border-radius: 10px;
            background: rgba(239, 68, 68, 0.1);
        }

        /* Disabled card styling */
        .enhanced-card-wrapper.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(0.5);
        }

        .disabled-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            font-size: 16px;
            border-radius: 8px;
            z-index: 10;
        }

        .wait-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 14px;
            border-radius: 8px;
            z-index: 10;
        }

        .enhanced-card-position {
            position: absolute;
            top: -12px;
            right: -12px;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(31, 41, 55, 0.9));
            color: white;
            font-size: 8px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .enhanced-selection-indicator {
            position: absolute;
            top: -10px;
            left: -10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-size: 12px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounce 0.5s ease;
            box-shadow: 0 3px 6px rgba(16, 185, 129, 0.4);
            border: 2px solid white;
        }

        /* Last few cards indicator */
        .last-few-indicator {
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #ef4444;
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 1s infinite;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.4);
            border: 2px solid white;
        }

        /* Enhanced drop zones */
        .enhanced-drop-zone-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 100;
            backdrop-filter: blur(2px);
        }

        .enhanced-drop-zone {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(37, 99, 235, 0.9));
            color: white;
            padding: 24px;
            border-radius: 16px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            min-width: 90px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .enhanced-drop-zone:hover {
            background: linear-gradient(135deg, rgba(37, 99, 235, 1), rgba(29, 78, 216, 1));
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.6);
        }

        .enhanced-drop-zone i {
            display: block;
            font-size: 18px;
            margin-bottom: 6px;
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
            background: linear-gradient(135deg, #10b981, #059669);
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
                width: 260px;
                height: 120px;
            }

            .player-cards-section {
                min-height: 120px;
                max-height: 160px;
            }

            .enhanced-cards-header {
                min-height: 40px;
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

            .enhanced-cards-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                min-height: 60px;
            }

            .header-left {
                width: 100%;
                justify-content: space-between;
            }

            .header-controls {
                width: 100%;
                justify-content: flex-end;
            }

            .header-selected-summary {
                order: 3;
                width: 100%;
            }
        }

        /* Prevent text selection during drag */
        .dragging * {
            user-select: none !important;
            -webkit-user-select: none !important;
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
</div> --}}

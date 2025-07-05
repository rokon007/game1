<div class="game-container">
    <!-- Include card CSS -->
    <link rel="stylesheet" href="{{ asset('css/cards-unicode.css') }}">

    <!-- Audio elements for sound effects -->
    <audio id="dealSound" src="{{ asset('sounds/card-deal.mp3') }}" preload="auto"></audio>
    <audio id="turnSound" src="{{ asset('sounds/turn-notification.mp3') }}" preload="auto"></audio>
    <audio id="winRoundSound" src="{{ asset('sounds/round-win.mp3') }}" preload="auto"></audio>
    <audio id="playCardSound" src="{{ asset('sounds/card-play.mp3') }}" preload="auto"></audio>
    <audio id="gameOverSound" src="{{ asset('sounds/game-over.mp3') }}" preload="auto"></audio>

    <!-- Ultra Compact Game Header -->
    <div class="game-header">
        <div class="header-content">
            <div class="game-info">
                <h1 class="game-title">{{ Str::limit($game->title, 12) }}</h1>
                <div class="game-stats">
                    <span>৳{{ number_format($game->bid_amount, 0) }}</span>
                    <span>R{{ $gameState['current_round'] ?? 1 }}</span>
                    <span>T{{ $gameState['current_turn'] ?? 1 }}</span>
                </div>
            </div>

            <div class="scoreboard">
                @foreach($game->participants as $participant)
                    <div class="player-score {{ $participant->user_id === Auth::id() ? 'current-player' : '' }}
                                {{ ($gameState['current_turn'] ?? 1) === $participant->position ? 'active-turn' : '' }}"
                         id="player-score-{{ $participant->position }}">
                        <div class="player-name">{{ Str::limit($participant->user->name, 4) }}</div>
                        <div class="player-points">{{ $participant->total_points ?? 0 }}</div>
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
                        @if($isCurrentTurn)
                            <div class="turn-indicator">
                                <i class="fas fa-play"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Ultra Slim Player Cards Section -->
    @if($player && $player->cards)
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

                    @if($isMyTurn)
                        <div class="turn-badge">YOUR TURN</div>
                    @endif
                </div>

                <div class="header-controls">
                    @if($game->status === 'playing')
                        <button wire:click="playCards"
                                class="play-btn {{ !$isMyTurn || empty($selectedCards) ? 'disabled' : '' }}"
                                {{ !$isMyTurn || empty($selectedCards) ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    @endif
                </div>
            </div>

            <div class="enhanced-cards-container" id="cards-container">
                <div class="scroll-indicator left-indicator" id="left-scroll">
                    <i class="fas fa-chevron-left"></i>
                </div>

                <div class="cards-scroll" id="cards-scroll">
                    @foreach($player->cards as $index => $card)
                        @php
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
                             wire:click="toggleCardSelection({{ $index }})">

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
                        </div>
                    @endforeach
                </div>

                <div class="scroll-indicator right-indicator" id="right-scroll">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
    @endif

    {{-- @push('scripts')
    <script>
        let draggedElement = null;
        let draggedIndex = null;
        let touchStartX = 0;
        let touchStartY = 0;
        let isDragging = false;
        let dragOffset = { x: 0, y: 0 };
        let dragStartTime = 0;

        // Sound effects controller
        const SoundEffects = {
            playDealSound: () => document.getElementById('dealSound').play(),
            playTurnSound: () => document.getElementById('turnSound').play(),
            playWinRoundSound: () => document.getElementById('winRoundSound').play(),
            playCardSound: () => document.getElementById('playCardSound').play(),
            playGameOverSound: () => document.getElementById('gameOverSound').play()
        };

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
            SoundEffects.playCardSound();
        });

        window.addEventListener('roundWinner', event => {
            const winnerPosition = event.detail.winner_position;
            const winnerName = event.detail.winner_name;
            SoundEffects.playWinRoundSound();
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
    @endpush --}}

    @push('scripts')
<script>
    let draggedElement = null;
    let draggedIndex = null;
    let touchStartX = 0;
    let touchStartY = 0;
    let isDragging = false;
    let dragOffset = { x: 0, y: 0 };
    let dragStartTime = 0;

    // Sound effects controller
    const SoundEffects = {
        playDealSound: () => document.getElementById('dealSound').play(),
        playTurnSound: () => document.getElementById('turnSound').play(),
        playWinRoundSound: () => document.getElementById('winRoundSound').play(),
        playCardSound: () => document.getElementById('playCardSound').play(),
        playGameOverSound: () => document.getElementById('gameOverSound').play()
    };

    // Initialize enhanced drag and drop
    document.addEventListener('DOMContentLoaded', function() {
        initializeEnhancedDragAndDrop();
        initializeScrollIndicators();
        optimizeForLandscape();
        setupMobileCardSelection();
        initializePusherEvents();
    });

    function initializePusherEvents() {
        // Game updated event
        window.addEventListener('gameUpdated', event => {
            showNotification(event.detail.data.message || 'Game updated');
            @this.call('loadGameState');
        });

        // Card played event
        window.addEventListener('cardPlayed', event => {
            showNotification(`${event.detail.player_name} played cards`);
            @this.call('loadGameState');
            SoundEffects.playCardSound();
        });

        // Round winner event
        window.addEventListener('roundWinner', event => {
            const winnerPosition = event.detail.winner_position;
            const winnerName = event.detail.winner_name;

            SoundEffects.playWinRoundSound();
            showNotification(`${winnerName} wins the round!`);

            setTimeout(() => {
                animateCardsToWinner(winnerPosition);
            }, 1000);
        });

        // Clear center cards event
        window.addEventListener('clearCenterCards', () => {
            clearCenterCards();
        });

        // Hide score modal event
        window.addEventListener('hideScoreModal', () => {
            setTimeout(() => {
                @this.call('closeScoreModal');
            }, 5000);
        });

        // Auto-refresh game state
        setInterval(() => {
            @this.call('loadGameState');
        }, 3000);
    }

    function setupMobileCardSelection() {
        const cardsContainer = document.getElementById('cards-scroll');
        if (!cardsContainer) return;

        cardsContainer.addEventListener('touchstart', function(e) {
            const card = e.target.closest('.enhanced-card-wrapper');
            if (card && !card.classList.contains('not-my-turn')) {
                const cardIndex = parseInt(card.dataset.cardIndex);
                @this.toggleCardSelection(cardIndex);
                e.preventDefault();
            }
        }, { passive: false });
    }

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

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;

        document.getElementById('game-notifications').appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Reinitialize after Livewire updates
    document.addEventListener('livewire:updated', function() {
        initializeEnhancedDragAndDrop();
        initializeScrollIndicators();
    });
</script>
@endpush
     <style>
        /* Notification styling */
        .notification {
            position: fixed;
            top: 50px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Mobile touch fixes */
        .enhanced-card-wrapper {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        /* Card selection styling */
        .enhanced-card-wrapper.selected {
            transform: translateY(-10px) scale(1.05);
            filter: brightness(1.1);
            z-index: 10;
        }

        /* Disable text selection */
        .enhanced-card-wrapper * {
            user-select: none;
            -webkit-user-select: none;
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
            height: 40px;
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
            gap: 6px;
            font-size: 9px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 2px;
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
            font-size: 10px;
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

        .player-name {
            font-weight: bold;
            color: #1f2937;
            font-size: 9px;
        }

        .player-name-dark {
            color: #6b7280;
            font-weight: bold;
            font-size: 9px;
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

        /* Ultra slim player cards section */
        .player-cards-section {
            height: calc(100vh - 40px - 160px - 30px);
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(15px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }

        .ultra-slim-header {
            height: 30px;
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
            gap: 6px;
        }

        .selected-indicator {
            position: relative;
        }

        .selected-count-badge {
            background: #3b82f6;
            color: white;
            font-size: 9px;
            width: 16px;
            height: 16px;
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

        .play-btn {
            background: #10b981;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            cursor: pointer;
        }

        .play-btn.disabled {
            background: #6b7280;
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Cards container */
        .enhanced-cards-container {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .scroll-indicator {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 25px;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.3), transparent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .left-indicator {
            left: 0;
        }

        .right-indicator {
            right: 0;
            background: linear-gradient(to left, rgba(0, 0, 0, 0.3), transparent);
        }

        .scroll-indicator:hover {
            background: rgba(0, 0, 0, 0.5);
            color: white;
        }

        .cards-scroll {
            display: flex;
            gap: 5px;
            overflow-x: auto;
            overflow-y: hidden;
            height: 100%;
            align-items: center;
            padding: 8px 30px;
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
        }

        /* Card styling */
        .enhanced-card-wrapper {
            position: relative;
            flex-shrink: 0;
            transition: all 0.3s ease;
            user-select: none;
            touch-action: manipulation;
        }

        .enhanced-card-wrapper.selected {
            transform: translateY(-8px) scale(1.05);
            filter: brightness(1.1);
        }

        .enhanced-card-wrapper.not-my-turn {
            opacity: 0.7;
        }

        .enhanced-card-wrapper.last-few {
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 8px;
        }

        .enhanced-selection-indicator {
            position: absolute;
            top: -6px;
            left: -6px;
            background: #10b981;
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid white;
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Mobile landscape optimizations */
        @media screen and (orientation: landscape) {
            .game-header {
                height: 35px;
            }

            .game-table {
                height: 140px;
            }

            .player-cards-section {
                height: calc(100vh - 35px - 140px - 25px);
            }

            .ultra-slim-header {
                height: 25px;
            }

            .bottom-player {
                bottom: -15px;
            }

            .top-player {
                top: -15px;
            }

            .left-player {
                left: -20px;
            }

            .right-player {
                right: -20px;
            }
        }

        /* Very small screens */
        @media screen and (max-width: 400px) {
            .game-title {
                font-size: 11px;
            }

            .game-stats {
                font-size: 8px;
            }

            .player-score {
                min-width: 28px;
                font-size: 7px;
            }

            .player-points {
                font-size: 9px;
            }

            .cards-scroll {
                gap: 3px;
                padding: 8px 25px;
            }
        }
    </style>
</div>

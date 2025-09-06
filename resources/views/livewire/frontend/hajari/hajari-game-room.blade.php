<div class="game-container">
    <!-- Include card CSS -->
    <link rel="stylesheet" href="{{ asset('css/cards-unicode.css') }}">

    <!-- Fixed audio elements to use only MP3 files from public/sounds -->
    <audio id="dealSound" preload="auto">
        <source src="{{ asset('sounds/dealSound.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="turnSound" preload="auto">
        <source src="{{ asset('sounds/turnSound.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="winRoundSound" preload="auto">
        <source src="{{ asset('sounds/winRoundSound.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="rongSoundPlay" preload="auto">
        <source src="{{ asset('sounds/rongSoundPlay.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="playCardSound" preload="auto">
        <source src="{{ asset('sounds/playCardSound.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="gameOverSound" preload="auto">
        <source src="{{ asset('sounds/winner1.mp3') }}" type="audio/mpeg">
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
                                {{ ($participant->cards_locked ?? false) ? 'cards-locked' : '' }}
                                {{ in_array($participant->user_id, $speakingPlayers) ? 'speaking' : '' }}"
                         id="player-score-{{ $participant->position }}">
                        <div class="player-name">{{ Str::limit($participant->user->name, 4) }}</div>
                        <div class="player-points">{{ $participant->total_points ?? 0 }}</div>
                        @if($participant->cards_locked ?? false)
                            <div class="lock-indicator">🔒</div>
                        @endif
                        <!-- Added speaking indicator -->
                        @if(in_array($participant->user_id, $speakingPlayers))
                            <div class="speaking-indicator">🎤</div>
                        @endif
                    </div>
                @endforeach
            </div>
            <!-- Simplified voice chat controls -->
            {{-- <div class="voice-chat-controls fixed top-4 right-4 z-50 flex gap-2">
                <button id="micToggle" class="voice-btn bg-gray-700 hover:bg-gray-600 text-white p-2 rounded-full transition-colors">
                    <i class="fas fa-microphone-slash text-sm"></i>
                </button>
                <button id="pttButton" class="voice-btn bg-blue-600 hover:bg-blue-500 text-white p-2 rounded-full transition-colors">
                    <i class="fas fa-walkie-talkie text-sm"></i>
                </button>
                <div id="voiceStatus" class="text-xs text-gray-400 self-center hidden md:block">Voice chat ready</div>
            </div> --}}
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
                {{-- <button wire:click="lockCards" class="lock-btn" {{ $isCardsLocked ? 'disabled' : '' }}>
                    <i class="fas fa-lock"></i> {{ $isCardsLocked ? 'Locked' : 'Lock Cards' }}
                </button> --}}
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
                                                size="normal"
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

                        {{-- Rong মার্কার ডিভ --}}
                        @if(in_array($participant->user_id, $wrongPlayers))
                            <div class="rong-indicator" title="Rong">
                                <i class="fas fa-exclamation-triangle" style="color: red;"></i>Wrong
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
                    {{-- <button onclick="enterFullscreenAndLock()" title="Fullscreen" class="play-btn" style="margin-right: 6px;">
                      <i class="fas fa-expand"></i>
                    </button> --}}
                    <!-- Added push-to-talk button for mobile -->
                    @if($isPushToTalkMode && $isMicEnabled)
                        <button id="ptt-button"
                                class="play-btn ptt-btn"
                                title="Hold to Talk"
                                style="margin-right: 6px;">
                            <i class="fas fa-microphone"></i>
                        </button>
                    @endif
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
                {{-- <div class="scroll-indicator left-indicator" id="left-scroll">
                    <i class="fas fa-chevron-left"></i>
                </div> --}}

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

                {{-- <div class="scroll-indicator right-indicator" id="right-scroll">
                    <i class="fas fa-chevron-right"></i>
                </div> --}}
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
                    <div class="score-row">
                        <span>Final Score:</span>
                        <span>{{ $winnerData['final_scores'] ?? 0 }} pts</span>
                    </div>
                </div>
                <button wire:click="closeWinnerModal" class="close-btn">Close</button>
            </div>
        </div>
    @endif

    @if($showAllWrongModal)
        <div class="modal-overlay">
            <div class="winner-modal">
                <h2>All player wrong</h2>
                <p>All 4 players have played a Wrong combination. The cards will be redistributed.</p>
                <button wire:click="dealNewCardsAfterAllWrong" class="close-btn">Close</button>
            </div>
        </div>
    @endif

    <script src="{{ asset('js/hajari-room.js') }}" defer></script>
    <script>
        window.Echo.channel('game.{{ $game->id }}')
            .listen('WrongMove', (e) => {
                // Wrong move notification দেখান
                alert(e.user.name + ' has played a wrong combination!');
                playSound('rongSoundPlay');
                // UI আপডেট করুন
                Livewire.dispatch('refreshGameWrong',e.user.id);
            });

            document.addEventListener('livewire:init', () => {
                Livewire.on('refresh-after-delay', (data) => {
                    setTimeout(() => {
                        Livewire.dispatch('dealNewCardsAfterAllWrong');
                    }, data.seconds * 1000);
                });
            });
    </script>

    <script>
        // Additional Echo setup for notice events
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized, setting up additional Echo listeners');

            // Ensure Echo is available
            if (typeof Echo !== 'undefined') {
                // Get or create the channel
                const channel = Echo.channel('game.{{ $game->id }}');


                // Add specific listener for game over events
                channel.listen('.game.over', (event) => {
                    console.log('Direct Echo game over listener triggered:', event);

                    // Find the Livewire component and call the method
                    const component = Livewire.find('{{ $_instance->getId() }}');
                    if (component) {
                        component.call('handleGameOver', event);
                    } else {
                        console.error('Livewire component not found');
                    }
                });

                // Add specific listener for all Player Wrong events
                channel.listen('.game.allWrong', (event) => {
                    console.log('Direct Echo all Player Wrong listener triggered:', event);

                    // Find the Livewire component and call the method
                    const component = Livewire.find('{{ $_instance->getId() }}');
                    if (component) {
                        component.call('handleAllPlayerWrong', event);
                    } else {
                        console.error('Livewire component not found');
                    }
                });

                // Verify the listener is registered
                console.log('Game Over and all Player Wrong listener registered on channel:', channel);

            } else {
                console.error('Echo is not defined');
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the main game logic
            if (window.HajariRoom) {
                window.HajariRoom.init();
            }

            let localStream = null;
            let isMicEnabled = false;
            let isSpeaking = false;
            let micTimeout = null; // Added timeout variable to prevent auto-disable

            const micToggle = document.getElementById('micToggle');
            const pttButton = document.getElementById('pttButton');

            // Initialize microphone
            async function initMicrophone() {
                try {
                    localStream = await navigator.mediaDevices.getUserMedia({
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: true,
                            autoGainControl: true
                        }
                    });
                    // Mute by default
                    localStream.getAudioTracks().forEach(track => {
                        track.enabled = false;
                    });
                    console.log('Microphone initialized');
                    return true;
                } catch (error) {
                    console.log('Microphone access denied:', error);
                    return false;
                }
            }

            micToggle?.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Clear any existing timeout
                if (micTimeout) {
                    clearTimeout(micTimeout);
                    micTimeout = null;
                }

                if (!localStream) {
                    const initialized = await initMicrophone();
                    if (!initialized) {
                        alert('মাইক্রোফোন অ্যাক্সেস প্রয়োজন। ব্রাউজার সেটিংস চেক করুন।');
                        return;
                    }
                }

                isMicEnabled = !isMicEnabled;
                const icon = micToggle.querySelector('i');

                if (isMicEnabled) {
                    icon.classList.remove('fa-microphone-slash');
                    icon.classList.add('fa-microphone');
                    micToggle.classList.add('bg-green-600');
                    micToggle.classList.remove('bg-gray-700');
                    document.getElementById('voiceStatus').textContent = 'মাইক চালু - PTT দিয়ে কথা বলুন';

                    localStream.getAudioTracks().forEach(track => {
                        track.addEventListener('ended', () => {
                            console.log('Audio track ended, reinitializing...');
                            initMicrophone();
                        });
                    });
                } else {
                    icon.classList.remove('fa-microphone');
                    icon.classList.add('fa-microphone-slash');
                    micToggle.classList.remove('bg-green-600');
                    micToggle.classList.add('bg-gray-700');
                    document.getElementById('voiceStatus').textContent = 'মাইক বন্ধ';
                    stopSpeaking();
                }
            });

            function startSpeaking() {
                if (!localStream || !isMicEnabled || isSpeaking) return;

                isSpeaking = true;
                localStream.getAudioTracks().forEach(track => {
                    track.enabled = true;
                });

                pttButton?.classList.add('bg-red-600');
                pttButton?.classList.remove('bg-blue-600');
                document.getElementById('voiceStatus').textContent = 'কথা বলছেন...';

                if (micTimeout) {
                    clearTimeout(micTimeout);
                    micTimeout = null;
                }
            }

            function stopSpeaking() {
                if (!localStream || !isSpeaking) return;

                isSpeaking = false;
                localStream.getAudioTracks().forEach(track => {
                    track.enabled = false;
                });

                pttButton?.classList.remove('bg-red-600');
                pttButton?.classList.add('bg-blue-600');
                if (isMicEnabled) {
                    document.getElementById('voiceStatus').textContent = 'মাইক চালু - PTT দিয়ে কথা বলুন';
                }
            }

            // PTT button events - improved event handling
            pttButton?.addEventListener('mousedown', (e) => {
                e.preventDefault();
                startSpeaking();
            });
            pttButton?.addEventListener('mouseup', (e) => {
                e.preventDefault();
                stopSpeaking();
            });
            pttButton?.addEventListener('mouseleave', stopSpeaking);

            // Touch events for mobile - improved
            pttButton?.addEventListener('touchstart', (e) => {
                e.preventDefault();
                e.stopPropagation();
                startSpeaking();
            });
            pttButton?.addEventListener('touchend', (e) => {
                e.preventDefault();
                e.stopPropagation();
                stopSpeaking();
            });

            // Space key for PTT
            document.addEventListener('keydown', (e) => {
                if (e.code === 'Space' && !e.repeat) {
                    e.preventDefault();
                    startSpeaking();
                }
            });
            document.addEventListener('keyup', (e) => {
                if (e.code === 'Space') {
                    e.preventDefault();
                    stopSpeaking();
                }
            });

            function playSound(soundId) {
                try {
                    const audio = document.getElementById(soundId);
                    if (audio) {
                        audio.currentTime = 0;
                        const playPromise = audio.play();

                        if (playPromise !== undefined) {
                            playPromise.catch(error => {
                                console.log('Sound play failed (user interaction required):', error);
                            });
                        }
                    }
                } catch (error) {
                    console.error('Error playing sound:', error);
                }
            }

            window.addEventListener('livewire:init', () => {
                Livewire.on('playSound', (event) => {
                    playSound(event.soundId || event);
                });

                Livewire.on('cardDealt', () => {
                    playSound('dealSound');
                });

                Livewire.on('playerTurn', () => {
                    playSound('turnSound');
                });

                Livewire.on('cardPlayed', () => {
                    playSound('playCardSound');
                });

                Livewire.on('roundWon', () => {
                    playSound('winRoundSound');
                });

                Livewire.on('rongSound', () => {
                    playSound('rongSoundPlay');
                });

                Livewire.on('gameOver', () => {
                    playSound('gameOverSound');
                });

                Livewire.on('playCardsAfterDelay', (event) => {
                    const seconds = event.seconds || event;
                    setTimeout(() => {
                        // Use the correct method to call the component action
                        @this.call('playCards');
                    }, seconds * 1000);
                });

                Livewire.on('refresh-after-delay', (event) => {
                    const seconds = event.seconds || 7;
                    setTimeout(() => {
                        Livewire.dispatch('refreshGame');
                    }, seconds * 1000);
                });
            });

            // Initialize microphone on first user interaction
            document.addEventListener('click', initMicrophone, { once: true });
        });
    </script>

    <style>
        /* style for winner modal */
        .winner-modal {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            max-width: 300px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .winner-name {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }

        .winner-title {
            font-size: 14px;
            color: #f59e0b;
            font-weight: bold;
        }

        .final-scores {
            margin: 15px 0;
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

        /* winner modal style end */


        /* Added voice chat button styles */
        .voice-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .voice-btn:hover {
            transform: scale(1.05);
        }

        .voice-btn.speaking {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

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
            /* Fallbacks then JS-corrected height */
            height: 100svh;
            height: 100dvh;
            height: calc(var(--vh) * 100);
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

        .player-score.speaking {
            background: rgba(34, 197, 94, 0.4);
            animation: speakingPulse 1s infinite;
        }

        @keyframes speakingPulse {
            0%, 100% {
                background: rgba(34, 197, 94, 0.4);
                box-shadow: 0 0 5px rgba(34, 197, 94, 0.6);
            }
            50% {
                background: rgba(34, 197, 94, 0.6);
                box-shadow: 0 0 10px rgba(34, 197, 94, 0.8);
            }
        }

        .speaking-indicator {
            position: absolute;
            top: -2px;
            right: -2px;
            font-size: 8px;
            animation: bounce 1s infinite;
        }

        /* Added voice chat styling */
        .voice-chat-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 10px 0;
        }

        .voice-btn {
            background: #4a5568;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .voice-btn:hover {
            background: #2d3748;
            transform: scale(1.1);
        }

        .voice-btn.active {
            background: #48bb78;
        }

        .voice-btn.speaking {
            background: #f56565;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .voice-status {
            font-size: 12px;
            color: #718096;
            margin-left: 10px;
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
            top: 30px;
            /* top:-20px; */
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
            height: 15px;
            padding: 0 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); */
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
            width: 30px;
            height: 30px;
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

        /* Mobile responsive voice controls */
        @media (max-width: 768px) {
            .voice-chat-controls {
                justify-content: center;
                margin: 5px 0;
            }

            .voice-btn {
                width: 35px;
                height: 35px;
            }

            .voice-status {
                font-size: 10px;
            }
        }
    </style>
</div>

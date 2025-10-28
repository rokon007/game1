<div>
    @section('meta_description')
        <meta name="description" content="Housieblitz - Crash Game">
    @endsection

    @section('title')
        <title>Housieblitz | Crash Game</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            /* Base Mobile-First Styles */
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                padding: 0;
                overflow-x: hidden;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .mobile-game-container {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                padding: 3px;
            }

            /* Header Section - More Compact */
            .mobile-header {
                text-align: center;
                color: white;
                padding: 5px 0;
                flex-shrink: 0;
            }

            .mobile-header h1 {
                font-size: 16px;
                margin-bottom: 1px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }

            .mobile-header p {
                font-size: 10px;
                opacity: 0.9;
                margin: 0;
            }

            /* Main Content Area - Reduced Gap */
            .mobile-main-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            /* ✅ Game Display - INCREASED SIZE for Better View */
            .mobile-game-display-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                flex-shrink: 0;
            }

            .mobile-game-display {
                width: 350px;
                height: 420px; /* ✅ বড় করা হয়েছে */
                background: #1a1a1a;
                border: 3px solid #fff;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                color: white;
                position: relative;
                overflow: hidden;
                padding: 15px; /* ✅ বেশি padding */
            }

            .mobile-game-display::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(45deg, transparent 50%, rgba(255,255,255,0.1) 50%);
                background-size: 10px 10px;
            }

            /* Game Content Area - Optimized Spacing */
            .mobile-game-content {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                padding: 10px; /* ✅ বেশি padding */
                border: 1px solid rgba(255, 255, 255, 0.2);
                margin: 0 5px;
            }

            /* Bet Controls - ULTRA COMPACT VERSION */
            .bet-input-group {
                display: flex;
                align-items: center;
                gap: 4px;
                background: #f8f9fa;
                border: 2px solid #e9ecef;
                border-radius: 8px;
                padding: 3px;
                transition: all 0.3s ease;
                margin-bottom: 6px;
                height: 36px;
            }

            .bet-input-group:focus-within {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }

            .bet-input-group.disabled {
                background: #e9ecef;
                opacity: 0.7;
            }

            .bet-control-btn {
                width: 28px;
                height: 28px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 0.8rem;
                border: 2px solid #007bff;
                background: white;
                color: #007bff;
                transition: all 0.2s ease;
                flex-shrink: 0;
            }

            .bet-control-btn:not(:disabled):hover {
                background: #007bff;
                color: white;
                transform: translateY(-1px);
            }

            .bet-control-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
                border-color: #6c757d;
                color: #6c757d;
            }

            .bet-input {
                flex: 1;
                border: none;
                background: transparent;
                font-size: 0.85rem;
                font-weight: 600;
                text-align: center;
                padding: 0;
                min-width: 0;
                height: 100%;
            }

            .bet-input:focus {
                outline: none;
                box-shadow: none;
            }

            .bet-input:disabled {
                background: transparent;
            }

            /* Quick Bet Buttons - More Compact */
            .mobile-quick-bets {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 4px;
                margin-bottom: 8px;
            }

            .quick-bet-btn {
                padding: 4px 2px;
                background: rgba(255, 255, 255, 0.2);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 5px;
                color: white;
                font-size: 9px;
                font-weight: 600;
                transition: all 0.3s ease;
                height: 24px;
            }

            .quick-bet-btn:active {
                background: rgba(255, 255, 255, 0.4);
                transform: scale(0.95);
            }

            /* Main Action Button - More Compact */
            .mobile-action-btn {
                width: 100%;
                padding: 8px;
                border: none;
                border-radius: 8px;
                font-size: 13px;
                font-weight: bold;
                transition: all 0.3s ease;
                height: 36px;
            }

            .mobile-action-btn:active {
                transform: scale(0.98);
            }

            /* Game Status Display - Adjusted for bigger height */
            .multiplier-display {
                font-size: 2.8rem; /* ✅ আরও বড় */
                font-weight: bold;
                text-shadow: 0 0 20px rgba(255,255,255,0.5);
                margin: 5px 0;
            }

            .crashed-animation {
                animation: shake 0.5s;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }

            .pulse-button {
                animation: pulse 1.5s infinite;
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }

            /* Recent Games Mini - More Compact */
            .recent-games-mini {
                display: flex;
                justify-content: center;
                gap: 4px;
                margin-top: 6px;
                flex-wrap: wrap;
            }

            .recent-game-badge {
                font-size: 16px;
                padding: 4px 8px;
                border-radius: 8px;
            }

            /* Countdown Styles - More Compact */
            .countdown-container {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                padding: 0.8rem;
                border: 1px solid rgba(255, 255, 255, 0.2);
                width: 100%;
            }

            .countdown-timer {
                font-size: 2rem;
                font-weight: 800;
                background: linear-gradient(45deg, #FFD700, #FFA500);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
                line-height: 1;
                margin-bottom: 0.3rem;
            }

            .progress-bar-custom {
                height: 5px;
                border-radius: 5px;
                overflow: hidden;
                background: rgba(255, 255, 255, 0.2);
                margin: 0.5rem 0;
            }

            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #00b09b, #96c93d);
                border-radius: 5px;
                width: 100%;
                transition: width 1s linear;
            }

            /* User Bet Info - More Compact */
            .mobile-user-bet {
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(10px);
                border-radius: 6px;
                padding: 4px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                margin-top: 4px;
            }

            /* Game Images - Slightly Bigger */
            .game-image {
                max-width: 100%;
                height: auto;
            }

            /* Balance Info - More Compact */
            .balance-info {
                font-size: 9px;
                color: rgba(255, 255, 255, 0.8);
                text-align: center;
                margin-bottom: 4px;
            }

            /* Player Count Badge - More Compact */
            .player-count-badge {
                background: rgba(255, 193, 7, 0.2);
                border: 1px solid rgba(255, 193, 7, 0.5);
                border-radius: 12px;
                padding: 2px 6px;
                font-size: 9px;
                color: #ffc107;
            }

            /* Alert Styles - More Compact */
            .mobile-alert {
                padding: 4px 6px;
                font-size: 9px;
                margin-bottom: 4px;
            }

            /* Very Small Screens - Adjusted */
            @media (max-width: 380px) {
                .mobile-game-display {
                    width: 330px;
                    height: 400px; /* ✅ ছোট স্ক্রিনেও বড় */
                }

                .multiplier-display {
                    font-size: 2.2rem;
                }

                .countdown-timer {
                    font-size: 1.8rem;
                }

                .mobile-quick-bets {
                    grid-template-columns: repeat(3, 1fr);
                }

                .mobile-game-content {
                    padding: 8px;
                }

                .bet-input-group {
                    height: 32px;
                    padding: 2px;
                }

                .bet-control-btn {
                    width: 26px;
                    height: 26px;
                    font-size: 0.75rem;
                }

                .bet-input {
                    font-size: 0.8rem;
                }

                .quick-bet-btn {
                    height: 22px;
                    font-size: 8px;
                }

                .mobile-action-btn {
                    height: 32px;
                    padding: 6px;
                    font-size: 12px;
                }
            }

            /* Landscape Orientation - Adjusted */
            @media (max-height: 600px) and (orientation: landscape) {
                .mobile-main-content {
                    flex-direction: row;
                    align-items: center;
                    gap: 5px;
                }

                .mobile-header {
                    display: none;
                }

                .mobile-game-display-wrapper {
                    flex: 0 0 auto;
                    margin-right: 5px;
                }

                .mobile-game-display {
                    width: 300px;
                    height: 340px;
                }

                .mobile-game-content {
                    flex: 1;
                    margin: 0;
                }

                .bet-input-group {
                    height: 32px;
                }

                .bet-control-btn {
                    width: 26px;
                    height: 26px;
                }
            }

            /* Animation for Game Display */
            @keyframes glow {
                0% { box-shadow: 0 0 20px rgba(255,255,255,0.3); }
                50% { box-shadow: 0 0 30px rgba(255,255,255,0.5); }
                100% { box-shadow: 0 0 20px rgba(255,255,255,0.3); }
            }

            .mobile-game-display {
                animation: glow 3s ease-in-out infinite;
            }

            /* Game Content Center Alignment */
            .game-content-center {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                height: 100%;
                width: 100%;
            }

            /* Compact Styles for Mobile */
            .compact-text {
                font-size: 10px;
                margin: 1px 0;
            }

            .compact-text2 {
                font-size: 16px;
                margin: 1px 0;
            }

            .compact-badge {
                font-size: 7px;
                padding: 1px 4px;
            }

            /* Bet Amount Label */
            .bet-amount-label {
                font-size: 10px;
                color: white;
                margin-bottom: 3px;
                display: block;
            }
        </style>
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    <div style="margin-top: 30px;" class="mobile-game-container">
        <!-- Header - More Compact -->


        <!-- Main Content - Game Display and Controls Together -->
        <div class="mobile-main-content mt-4">
            <!-- Game Display -->
            <div wire:poll.100ms="refreshGameState" class="mobile-game-display-wrapper">
                <div class="mobile-game-display">
                    <div class="game-content-center">
                        @if($gameStatus === 'waiting')
                            <div class="countdown-container">
                                <img src="{{asset('assets/frontend/img/numbers.gif')}}" class="game-image" style="max-height: 50px;">
                                <p class="compact-text text-white mb-1">Next round starting in</p>

                                <!-- Progress Bar -->
                                <div class="progress-bar-custom mx-auto" style="max-width: 180px;">
                                    <div class="progress-fill" id="countdown-progress"></div>
                                </div>

                                <!-- Waiting player count -->
                                <div class="mt-1">
                                    <span class="player-count-badge">
                                        <i class="fas fa-users me-1"></i>
                                        <span id="waiting-player-count">{{ number_format($waitingPlayerCount) }}</span> waiting
                                    </span>
                                </div>
                            </div>
                        @elseif($gameStatus === 'running')
                            <div class="game-content-center">
                                <div class="multiplier-display pulse-button" id="multiplier-display">
                                    {{ number_format($currentMultiplier, 2) }}x
                                </div>
                                <img src="{{asset('assets/frontend/img/rocket.gif')}}" class="game-image" style="max-height: 240px;">

                                <div class="mt-1">
                                    <span class="player-count-badge">
                                        <i class="fas fa-users me-1"></i>
                                        <span id="running-player-count">{{ number_format($runningPlayerCount) }}</span> remaining
                                    </span>
                                </div>
                            </div>
                        @elseif($gameStatus === 'crashed')
                            <div class="game-content-center crashed-animation">
                                <img src="{{asset('assets/frontend/img/crashed.gif')}}" class="game-image" style="max-height: 80px;">
                                <h2 class="fw-bold text-danger mb-1" style="font-size: 1.3rem;">CRASHED!</h2>
                                <p class="text-white compact-text2">at {{ number_format($currentMultiplier, 2) }}x</p>
                            </div>
                        @endif

                        <!-- Recent Games Mini List -->
                        <div class="recent-games-mini">
                            @forelse($recentGames as $game)
                                <span class="badge recent-game-badge {{ $game['crash_point'] >= 2 ? 'bg-success' : 'bg-danger'}}">
                                    {{ number_format($game['crash_point'], 1) }}x
                                </span>
                            @empty
                                <span class="badge bg-warning text-dark recent-game-badge">
                                    No games
                                </span>
                            @endforelse
                        </div>
                    </div>

                    <!-- User Bet Info -->
                    @if($userBet)
                        <div class="mobile-user-bet">
                            <p class="mb-1 compact-text text-white">
                                <i class="fas fa-ticket-alt me-1"></i>Your Bet: ৳{{ number_format($userBet->bet_amount, 2) }}
                            </p>
                            @if($userBet->isWon())
                                <span class="badge bg-success compact-badge">
                                    <i class="fas fa-trophy me-1"></i>
                                    Won: ৳{{ number_format($userBet->profit + $userBet->bet_amount, 2) }}
                                </span>
                            @elseif($userBet->isLost())
                                <span class="badge bg-danger compact-badge">
                                    <i class="fas fa-times-circle me-1"></i>Lost
                                </span>
                            @elseif($userBet->isPlaying())
                                <span class="badge bg-warning text-dark pulse-button compact-badge">
                                    <i class="fas fa-spinner fa-spin me-1"></i>Playing...
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Mobile Game Controls - Ultra Compact -->
            <div class="mobile-game-content">
                @auth
                    <!-- Error/Success Messages -->
                    @if($errorMessage)
                        <div class="alert alert-danger alert-dismissible fade show mobile-alert" role="alert">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ $errorMessage }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 3px; font-size: 8px;"></button>
                        </div>
                    @endif

                    <!-- Balance Info -->
                    <div class="balance-info">
                        <i class="fas fa-wallet me-1"></i>Balance: ৳{{ number_format(auth()->user()->credit, 2) }}
                    </div>

                    <!-- Bet Amount Input -->
                    <label class="bet-amount-label fw-semibold">
                        <i class="fas fa-coins me-1"></i>Bet Amount
                    </label>

                    <div class="bet-input-group">
                        <button
                            class="bet-control-btn"
                            wire:click="decreaseBetAmount"
                            {{-- @if($userBet || $gameStatus === 'running') disabled @endif --}}
                        >
                            -
                        </button>

                        <input
                            type="number"
                            wire:model="betAmount"
                            step="0.01"
                            min="1"
                            class="form-control bet-input"
                            placeholder="Enter amount"

                        >

                        <button
                            class="bet-control-btn"
                            wire:click="increaseBetAmount"
                            {{-- @if($userBet || $gameStatus === 'running') disabled @endif --}}
                        >
                            +
                        </button>
                    </div>

                    <!-- Quick Bet Buttons -->
                    @if(!$userBet && $gameStatus !== 'running')
                        <div class="mobile-quick-bets">
                            <button wire:click="$set('betAmount', 10)" class="quick-bet-btn">৳10</button>
                            <button wire:click="$set('betAmount', 20)" class="quick-bet-btn">৳20</button>
                            <button wire:click="$set('betAmount', 50)" class="quick-bet-btn">৳50</button>
                            <button wire:click="$set('betAmount', 100)" class="quick-bet-btn">৳100</button>
                            <button wire:click="$set('betAmount', 500)" class="quick-bet-btn">৳500</button>
                            <button wire:click="$set('betAmount', 1000)" class="quick-bet-btn">৳1000</button>
                        </div>
                    @endif

                    <!-- Main Action Button -->
                    @if(!$userBet && $gameStatus !== 'running')
                        <button
                            wire:click="placeBet"
                            class="mobile-action-btn btn-success"
                        >
                            <i class="fas fa-play-circle me-2"></i>Place Bet
                        </button>
                    @elseif($userBet && $userBet->isPlaying() && $gameStatus === 'running')
                        <button
                            wire:click="cashout"
                            class="mobile-action-btn btn-warning pulse-button"
                        >
                            <i class="fas fa-hand-holding-usd me-2"></i>
                            Cashout ৳{{ number_format($userBet->bet_amount * $currentMultiplier, 2) }}
                        </button>
                    @else
                        <button
                            disabled
                            class="mobile-action-btn btn-secondary"
                        >
                            <i class="fas fa-hourglass-half me-2"></i>Please Wait
                        </button>
                    @endif
                @else
                    <!-- Login Prompt -->
                    <div class="text-center">
                        <i class="fas fa-lock fa-sm text-muted mb-1"></i>
                        <p class="text-muted mb-2 compact-text">Login to play the crash game</p>
                        <a href="{{ route('login') }}" class="mobile-action-btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Play
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    @section('footer')
        <livewire:layout.frontend.footer />
    @endsection

    {{-- @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded - initializing mobile game');

                // PlayerCountManager ক্লাস
                class PlayerCountManager {
                    constructor() {
                        this.waitingInterval = null;
                        this.runningInterval = null;
                    }

                    startWaitingIncrease() {
                        console.log('Starting waiting increase');
                        this.stopAll();
                        this.waitingInterval = setInterval(() => {
                            @this.call('increaseWaitingPlayers');
                        }, 800);
                    }

                    startRunningDecrease() {
                        console.log('Starting running decrease');
                        this.stopAll();
                        this.runningInterval = setInterval(() => {
                            @this.call('decreaseRunningPlayers');
                        }, 600);
                    }

                    stopAll() {
                        if (this.waitingInterval) {
                            clearInterval(this.waitingInterval);
                            this.waitingInterval = null;
                        }
                        if (this.runningInterval) {
                            clearInterval(this.runningInterval);
                            this.runningInterval = null;
                        }
                    }
                }

                window.playerManager = new PlayerCountManager();

                // Countdown Timer ক্লাস - EXACT TIMING
                class CountdownTimer {
                    constructor() {
                        this.interval = null;
                        this.endTime = null;
                        this.isRunning = false;
                        this.totalDuration = 10;
                    }

                    start(duration = 10, totalDuration = 10) {
                        this.stop();

                        console.log(`Starting countdown: ${duration}s remaining of ${totalDuration}s total`);

                        const progressElement = document.getElementById('countdown-progress');
                        if (!progressElement) {
                            console.error('Progress element not found');
                            return;
                        }

                        this.totalDuration = totalDuration;
                        this.endTime = Date.now() + (duration * 1000);
                        this.isRunning = true;

                        // Immediate update
                        this.updateDisplay(progressElement, duration);

                        // Update every 50ms for smooth animation
                        this.interval = setInterval(() => {
                            const now = Date.now();
                            const timeLeft = Math.max(0, this.endTime - now);
                            const secondsLeft = timeLeft / 1000;

                            this.updateDisplay(progressElement, secondsLeft);

                            if (timeLeft <= 0) {
                                console.log('Countdown finished');
                                this.stop();
                                progressElement.style.width = '0%';
                            }
                        }, 50); // 50ms for smooth animation
                    }

                    updateDisplay(progressElement, secondsLeft) {
                        if (progressElement) {
                            // Calculate percentage based on total duration
                            const progressPercent = (secondsLeft / this.totalDuration) * 100;
                            progressElement.style.width = Math.max(0, Math.min(100, progressPercent)) + '%';
                        }
                    }

                    stop() {
                        if (this.interval) {
                            clearInterval(this.interval);
                            this.interval = null;
                        }
                        this.isRunning = false;
                        console.log('Countdown stopped');
                    }
                }

                window.countdownTimer = new CountdownTimer();

                // Multiplier animation functions
                window.gameInterval = null;
                window.currentMult = 1.00;

                window.startMultiplierAnimation = function(targetMult) {
                    if (window.gameInterval) clearInterval(window.gameInterval);

                    window.currentMult = 1.00;
                    window.gameInterval = setInterval(() => {
                        window.currentMult += 0.01;
                        const displayElement = document.getElementById('multiplier-display');
                        if (displayElement) {
                            displayElement.textContent = window.currentMult.toFixed(2) + 'x';
                        }

                        if (window.currentMult >= targetMult) {
                            window.stopMultiplierAnimation();
                        }
                    }, 50);
                }

                window.stopMultiplierAnimation = function() {
                    if (window.gameInterval) {
                        clearInterval(window.gameInterval);
                        window.gameInterval = null;
                    }
                }

                window.showCrashAlert = function(crashPoint) {
                    Swal.fire({
                        icon: 'error',
                        title: 'CRASHED!',
                        text: 'Crashed at ' + crashPoint.toFixed(2) + 'x',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }

                // Livewire event listeners
                // Livewire.on('startWaitingIncrease', () => {
                //     console.log('Livewire event: startWaitingIncrease');
                //     if (window.playerManager) {
                //         window.playerManager.startWaitingIncrease();
                //     }
                // });

                // Livewire.on('startRunningDecrease', () => {
                //     console.log('Livewire event: startRunningDecrease');
                //     if (window.playerManager) {
                //         window.playerManager.startRunningDecrease();
                //     }
                // });

                // Livewire.on('gameCrashed', (data) => {
                //     console.log('Livewire event: gameCrashed');
                //     if (window.playerManager) {
                //         window.playerManager.stopAll();
                //     }
                //     if (window.countdownTimer) {
                //         window.countdownTimer.stop();
                //     }
                //     setTimeout(() => {
                //         @this.call('resetPlayerCounts');
                //     }, 1000);
                //     window.showCrashAlert(data.crashPoint);
                // });

                // Livewire.on('countdownShouldStart', () => {
                //     console.log('Livewire event: countdownShouldStart');
                //     if (window.playerManager) {
                //         window.playerManager.stopAll();
                //     }
                //     @this.call('resetPlayerCounts');
                //     setTimeout(() => {
                //         if (window.playerManager) {
                //             window.playerManager.startWaitingIncrease();
                //         }
                //     }, 500);
                //     setTimeout(() => {
                //         if (window.countdownTimer) {
                //             window.countdownTimer.start(10);
                //         }
                //     }, 500);
                // });

                // Livewire.on('betPlaced', () => {
                //     console.log('Livewire event: betPlaced');
                //     Swal.fire({
                //         icon: 'success',
                //         title: 'Bet Successful!',
                //         text: 'Your bet has been recorded',
                //         timer: 2000,
                //         showConfirmButton: false
                //     });
                // });

                // Livewire.on('cashedOut', () => {
                //     console.log('Livewire event: cashedOut');
                //     Swal.fire({
                //         icon: 'success',
                //         title: 'Cashout Successful!',
                //         text: 'You won!',
                //         timer: 2000,
                //         showConfirmButton: false
                //     });
                // });

                // // Initialize based on current state
                // @if($gameStatus === 'waiting')
                //     console.log('Initializing waiting state');
                //     setTimeout(() => {
                //         if (window.playerManager) {
                //             window.playerManager.startWaitingIncrease();
                //         }
                //         if (window.countdownTimer) {
                //             window.countdownTimer.start(10);
                //         }
                //     }, 1000);
                // @endif

                // @if($gameStatus === 'running')
                //     console.log('Initializing running state');
                //     setTimeout(() => {
                //         if (window.playerManager) {
                //             window.playerManager.startRunningDecrease();
                //         }
                //     }, 1000);
                // @endif




                // Livewire event listeners
                Livewire.on('startWaitingIncrease', () => {
                    console.log('Livewire event: startWaitingIncrease');
                    if (window.playerManager) {
                        window.playerManager.startWaitingIncrease();
                    }
                });

                Livewire.on('startRunningDecrease', () => {
                    console.log('Livewire event: startRunningDecrease');
                    if (window.playerManager) {
                        window.playerManager.startRunningDecrease();
                    }
                });

                Livewire.on('gameCrashed', (data) => {
                    console.log('Livewire event: gameCrashed', data);

                    // Stop all intervals
                    if (window.playerManager) {
                        window.playerManager.stopAll();
                    }
                    if (window.countdownTimer) {
                        window.countdownTimer.stop();
                    }

                    // Show crash alert
                    window.showCrashAlert(data.crashPoint);

                    // Reset player counts after 1 second
                    setTimeout(() => {
                        @this.call('resetPlayerCounts');
                    }, 1000);
                });

                Livewire.on('countdownShouldStart', (data) => {
                    console.log('Countdown should start event received:', data);

                    // Stop all previous intervals
                    if (window.playerManager) {
                        window.playerManager.stopAll();
                    }
                    if (window.countdownTimer) {
                        window.countdownTimer.stop();
                    }

                    // Reset player counts
                    @this.call('resetPlayerCounts');

                    // Start new waiting period with EXACT timing
                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startWaitingIncrease();
                        }
                        if (window.countdownTimer && data) {
                            const duration = data.duration || 10;
                            const totalDuration = data.totalDuration || 10;
                            console.log(`Starting countdown with duration: ${duration}s, total: ${totalDuration}s`);
                            window.countdownTimer.start(duration, totalDuration);
                        } else if (window.countdownTimer) {
                            // Fallback
                            window.countdownTimer.start(10, 10);
                        }
                    }, 500);
                });

                Livewire.on('betPlaced', () => {
                    console.log('Livewire event: betPlaced');
                    Swal.fire({
                        icon: 'success',
                        title: 'Bet Successful!',
                        text: 'Your bet has been recorded',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });

                Livewire.on('cashedOut', () => {
                    console.log('Livewire event: cashedOut');
                    Swal.fire({
                        icon: 'success',
                        title: 'Cashout Successful!',
                        text: 'You won!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });

                // Initialize based on current state
                @if($gameStatus === 'waiting')
                    console.log('Initializing waiting state');
                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startWaitingIncrease();
                        }
                        if (window.countdownTimer) {
                            window.countdownTimer.start(10);
                        }
                    }, 1000);
                @elseif($gameStatus === 'running')
                    console.log('Initializing running state');
                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startRunningDecrease();
                        }
                    }, 1000);
                @elseif($gameStatus === 'crashed')
                    console.log('Initializing crashed state - will wait for next game');
                @endif

                // Mobile touch improvements
                const buttons = document.querySelectorAll('.quick-bet-btn, .mobile-action-btn');
                buttons.forEach(button => {
                    button.addEventListener('touchstart', function() {
                        this.style.transform = 'scale(0.95)';
                    });

                    button.addEventListener('touchend', function() {
                        this.style.transform = 'scale(1)';
                    });
                });

                // Prevent zoom on double tap
                let lastTouchEnd = 0;
                document.addEventListener('touchend', function (event) {
                    const now = (new Date()).getTime();
                    if (now - lastTouchEnd <= 300) {
                        event.preventDefault();
                    }
                    lastTouchEnd = now;
                }, false);
            });

            // Cleanup
            window.addEventListener('beforeunload', function() {
                if (window.countdownTimer) {
                    window.countdownTimer.stop();
                }
                if (window.playerManager) {
                    window.playerManager.stopAll();
                }
                if (window.gameInterval) {
                    clearInterval(window.gameInterval);
                }
            });
        </script>
    @endsection --}}


    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded - initializing mobile game');

                // ✅ Global state tracker
                window.crashGameState = {
                    currentGameId: null,
                    currentMultiplier: 1.00,
                    gameStatus: 'waiting'
                };

                // ✅ Multiplier reset function
                window.resetMultiplier = function() {
                    console.log('Resetting multiplier to 1.00');
                    window.crashGameState.currentMultiplier = 1.00;

                    const displayElement = document.getElementById('multiplier-display');
                    if (displayElement) {
                        displayElement.textContent = '1.00x';
                    }
                };

                // PlayerCountManager class
                class PlayerCountManager {
                    constructor() {
                        this.waitingInterval = null;
                        this.runningInterval = null;
                    }

                    startWaitingIncrease() {
                        console.log('Starting waiting increase');
                        this.stopAll();
                        this.waitingInterval = setInterval(() => {
                            @this.call('increaseWaitingPlayers');
                        }, 800);
                    }

                    startRunningDecrease() {
                        console.log('Starting running decrease');
                        this.stopAll();
                        this.runningInterval = setInterval(() => {
                            @this.call('decreaseRunningPlayers');
                        }, 600);
                    }

                    stopAll() {
                        if (this.waitingInterval) {
                            clearInterval(this.waitingInterval);
                            this.waitingInterval = null;
                        }
                        if (this.runningInterval) {
                            clearInterval(this.runningInterval);
                            this.runningInterval = null;
                        }
                    }
                }

                window.playerManager = new PlayerCountManager();

                // Countdown Timer class
                // class CountdownTimer {
                //     constructor() {
                //         this.interval = null;
                //         this.endTime = null;
                //         this.isRunning = false;
                //         this.totalDuration = 10;
                //     }

                //     start(duration = 10, totalDuration = 10) {
                //         this.stop();

                //         console.log(`Starting countdown: ${duration}s remaining of ${totalDuration}s total`);

                //         const progressElement = document.getElementById('countdown-progress');
                //         if (!progressElement) {
                //             console.error('Progress element not found');
                //             return;
                //         }

                //         this.totalDuration = totalDuration;
                //         this.endTime = Date.now() + (duration * 1000);
                //         this.isRunning = true;

                //         // Immediate update
                //         this.updateDisplay(progressElement, duration);

                //         // Update every 50ms for smooth animation
                //         this.interval = setInterval(() => {
                //             const now = Date.now();
                //             const timeLeft = Math.max(0, this.endTime - now);
                //             const secondsLeft = timeLeft / 1000;

                //             this.updateDisplay(progressElement, secondsLeft);

                //             if (timeLeft <= 0) {
                //                 console.log('Countdown finished');
                //                 this.stop();
                //                 progressElement.style.width = '0%';
                //             }
                //         }, 50);
                //     }

                //     updateDisplay(progressElement, secondsLeft) {
                //         if (progressElement) {
                //             const progressPercent = (secondsLeft / this.totalDuration) * 100;
                //             progressElement.style.width = Math.max(0, Math.min(100, progressPercent)) + '%';
                //         }
                //     }

                //     stop() {
                //         if (this.interval) {
                //             clearInterval(this.interval);
                //             this.interval = null;
                //         }
                //         this.isRunning = false;
                //         console.log('Countdown stopped');
                //     }
                // }

                // ✅ UPDATED CountdownTimer class - Exact 10 second timing
                    class CountdownTimer {
                        constructor() {
                            this.interval = null;
                            this.endTime = null;
                            this.isRunning = false;
                            this.totalDuration = 10.0;
                            this.startTime = null;
                        }

                        start(duration = 10.0, totalDuration = 10.0) {
                            this.stop();

                            // ✅ CRITICAL: Validate and cap duration
                            duration = Math.max(0, Math.min(10.0, parseFloat(duration)));
                            totalDuration = 10.0; // ✅ Always exactly 10

                            console.log(`⏱️  Starting countdown: ${duration.toFixed(3)}s remaining of ${totalDuration}s total`);

                            const progressElement = document.getElementById('countdown-progress');
                            if (!progressElement) {
                                console.error('❌ Progress element not found');
                                return;
                            }

                            this.totalDuration = totalDuration;
                            this.startTime = Date.now();
                            this.endTime = this.startTime + (duration * 1000);
                            this.isRunning = true;

                            // ✅ Log exact timing
                            console.log(`Start: ${new Date(this.startTime).toISOString()}`);
                            console.log(`End:   ${new Date(this.endTime).toISOString()}`);

                            // Immediate update
                            this.updateDisplay(progressElement, duration);

                            // ✅ High frequency updates for smoothness
                            this.interval = setInterval(() => {
                                const now = Date.now();
                                const timeLeft = Math.max(0, this.endTime - now);
                                const secondsLeft = timeLeft / 1000;

                                this.updateDisplay(progressElement, secondsLeft);

                                if (timeLeft <= 0) {
                                    const actualDuration = (now - this.startTime) / 1000;
                                    const deviation = Math.abs(actualDuration - this.totalDuration);

                                    console.log(`✅ Countdown finished!`);
                                    console.log(`   Expected: ${this.totalDuration.toFixed(3)}s`);
                                    console.log(`   Actual:   ${actualDuration.toFixed(3)}s`);
                                    console.log(`   Deviation: ${(deviation * 1000).toFixed(2)}ms`);

                                    if (deviation < 0.1) {
                                        console.log('🎯 PERFECT TIMING!');
                                    } else {
                                        console.warn('⚠️  Timing drift detected');
                                    }

                                    this.stop();
                                    progressElement.style.width = '0%';
                                }
                            }, 20); // ✅ Update every 20ms for smooth animation
                        }

                        updateDisplay(progressElement, secondsLeft) {
                            if (progressElement) {
                                const progressPercent = (secondsLeft / this.totalDuration) * 100;
                                const clampedPercent = Math.max(0, Math.min(100, progressPercent));
                                progressElement.style.width = clampedPercent + '%';
                            }
                        }

                        stop() {
                            if (this.interval) {
                                clearInterval(this.interval);
                                this.interval = null;
                            }
                            this.isRunning = false;
                        }
                    }

                window.countdownTimer = new CountdownTimer();

                // ✅ UPDATED: Multiplier animation functions
                window.gameInterval = null;
                window.currentMult = 1.00;

                window.startMultiplierAnimation = function(targetMult) {
                    if (window.gameInterval) clearInterval(window.gameInterval);

                    // ✅ সবসময় 1.00 থেকে শুরু করুন
                    window.currentMult = 1.00;
                    window.crashGameState.currentMultiplier = 1.00;

                    const displayElement = document.getElementById('multiplier-display');
                    if (displayElement) {
                        displayElement.textContent = '1.00x';
                    }

                    window.gameInterval = setInterval(() => {
                        window.currentMult += 0.01;
                        window.crashGameState.currentMultiplier = window.currentMult;

                        if (displayElement) {
                            displayElement.textContent = window.currentMult.toFixed(2) + 'x';
                        }

                        if (window.currentMult >= targetMult) {
                            window.stopMultiplierAnimation();
                        }
                    }, 50);
                }

                window.stopMultiplierAnimation = function() {
                    if (window.gameInterval) {
                        clearInterval(window.gameInterval);
                        window.gameInterval = null;
                    }
                }

                window.showCrashAlert = function(crashPoint) {
                    Swal.fire({
                        icon: 'error',
                        title: 'CRASHED!',
                        text: 'Crashed at ' + crashPoint.toFixed(2) + 'x',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }

                // ============================================
                // Livewire event listeners
                // ============================================

                Livewire.on('startWaitingIncrease', () => {
                    console.log('Livewire event: startWaitingIncrease');
                    if (window.playerManager) {
                        window.playerManager.startWaitingIncrease();
                    }
                });

                Livewire.on('startRunningDecrease', () => {
                    console.log('Livewire event: startRunningDecrease');
                    if (window.playerManager) {
                        window.playerManager.startRunningDecrease();
                    }
                });

                Livewire.on('gameCrashed', (data) => {
                    console.log('Livewire event: gameCrashed', data);

                    // Stop all intervals
                    if (window.playerManager) {
                        window.playerManager.stopAll();
                    }
                    if (window.countdownTimer) {
                        window.countdownTimer.stop();
                    }

                    // ✅ Stop multiplier animation
                    window.stopMultiplierAnimation();

                    // Show crash alert
                    window.showCrashAlert(data.crashPoint);

                    // Reset player counts after 1 second
                    setTimeout(() => {
                        @this.call('resetPlayerCounts');
                        // ✅ Reset multiplier display
                        window.resetMultiplier();
                    }, 1000);
                });

                // Livewire.on('countdownShouldStart', (data) => {
                //     console.log('Countdown should start event received:', data);

                //     // ✅ CRITICAL: Multiplier reset করুন waiting এ
                //     window.resetMultiplier();
                //     window.crashGameState.gameStatus = 'waiting';

                //     // Stop all previous intervals
                //     if (window.playerManager) {
                //         window.playerManager.stopAll();
                //     }
                //     if (window.countdownTimer) {
                //         window.countdownTimer.stop();
                //     }

                //     // Reset player counts
                //     @this.call('resetPlayerCounts');

                //     // Start new waiting period with EXACT timing
                //     setTimeout(() => {
                //         if (window.playerManager) {
                //             window.playerManager.startWaitingIncrease();
                //         }
                //         if (window.countdownTimer && data) {
                //             const duration = data.duration || 10;
                //             const totalDuration = data.totalDuration || 10;
                //             console.log(`Starting countdown with duration: ${duration}s, total: ${totalDuration}s`);
                //             window.countdownTimer.start(duration, totalDuration);
                //         } else if (window.countdownTimer) {
                //             window.countdownTimer.start(10, 10);
                //         }
                //     }, 500);
                // });

                // ✅ UPDATED Event Listener for countdown
                Livewire.on('countdownShouldStart', (data) => {
                    console.log('📨 Countdown event received:', data);

                    // ✅ Reset multiplier
                    window.resetMultiplier();
                    window.crashGameState.gameStatus = 'waiting';

                    // Stop all intervals
                    if (window.playerManager) {
                        window.playerManager.stopAll();
                    }
                    if (window.countdownTimer) {
                        window.countdownTimer.stop();
                    }

                    // Reset player counts
                    @this.call('resetPlayerCounts');

                    // ✅ Start countdown with exact timing
                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startWaitingIncrease();
                        }
                        if (window.countdownTimer && data) {
                            let duration = parseFloat(data.duration) || 10.0;
                            let totalDuration = parseFloat(data.totalDuration) || 10.0;

                            // ✅ Validate values
                            duration = Math.max(0, Math.min(10.0, duration));
                            totalDuration = 10.0;

                            console.log(`🎬 Starting countdown: ${duration.toFixed(3)}s of ${totalDuration}s`);
                            window.countdownTimer.start(duration, totalDuration);
                        } else if (window.countdownTimer) {
                            console.log('🎬 Starting countdown: fallback 10s');
                            window.countdownTimer.start(10.0, 10.0);
                        }
                    }, 200); // ✅ Reduced delay for quicker response
                });

                // ✅ VERIFICATION: Add this for testing
                window.testCountdownTiming = function() {
                    console.log('🧪 Testing countdown timing...');

                    let startTime = Date.now();
                    let countdownReceived = false;

                    const listener = Livewire.on('countdownShouldStart', () => {
                        if (!countdownReceived) {
                            countdownReceived = true;
                            startTime = Date.now();
                            console.log('⏱️  Countdown started at:', new Date(startTime).toISOString());

                            setTimeout(() => {
                                const endTime = Date.now();
                                const actualDuration = (endTime - startTime) / 1000;
                                const deviation = Math.abs(actualDuration - 10.0);

                                console.log('📊 Test Results:');
                                console.log('   Duration: ' + actualDuration.toFixed(3) + 's');
                                console.log('   Deviation: ' + (deviation * 1000).toFixed(2) + 'ms');
                                console.log(deviation < 0.2 ? '   ✅ PASS' : '   ❌ FAIL');
                            }, 10100);
                        }
                    });
                };

                // ✅ Initialize countdown timer
                window.countdownTimer = new CountdownTimer();
                //-------------------------

                Livewire.on('betPlaced', () => {
                    console.log('Livewire event: betPlaced');
                    Swal.fire({
                        icon: 'success',
                        title: 'Bet Successful!',
                        text: 'Your bet has been recorded',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });

                Livewire.on('cashedOut', () => {
                    console.log('Livewire event: cashedOut');
                    Swal.fire({
                        icon: 'success',
                        title: 'Cashout Successful!',
                        text: 'You won!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });

                // ============================================
                // Initialize based on current state
                // ============================================

                @if($gameStatus === 'waiting')
                    console.log('Initializing waiting state');
                    // ✅ Ensure multiplier is 1.00
                    window.resetMultiplier();
                    window.crashGameState.gameStatus = 'waiting';

                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startWaitingIncrease();
                        }
                        if (window.countdownTimer) {
                            window.countdownTimer.start(10);
                        }
                    }, 1000);
                @elseif($gameStatus === 'running')
                    console.log('Initializing running state');
                    // ✅ Running শুরু হলে current multiplier set করুন
                    window.crashGameState.gameStatus = 'running';
                    window.crashGameState.currentMultiplier = {{ $currentMultiplier }};

                    const displayElement = document.getElementById('multiplier-display');
                    if (displayElement) {
                        displayElement.textContent = '{{ number_format($currentMultiplier, 2) }}x';
                    }

                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startRunningDecrease();
                        }
                    }, 1000);
                @elseif($gameStatus === 'crashed')
                    console.log('Initializing crashed state - will wait for next game');
                    window.crashGameState.gameStatus = 'crashed';
                    window.crashGameState.currentMultiplier = {{ $currentMultiplier }};
                @endif

                // ============================================
                // Mobile touch improvements
                // ============================================

                const buttons = document.querySelectorAll('.quick-bet-btn, .mobile-action-btn');
                buttons.forEach(button => {
                    button.addEventListener('touchstart', function() {
                        this.style.transform = 'scale(0.95)';
                    });

                    button.addEventListener('touchend', function() {
                        this.style.transform = 'scale(1)';
                    });
                });

                // Prevent zoom on double tap
                let lastTouchEnd = 0;
                document.addEventListener('touchend', function (event) {
                    const now = (new Date()).getTime();
                    if (now - lastTouchEnd <= 300) {
                        event.preventDefault();
                    }
                    lastTouchEnd = now;
                }, false);
            });

            // ============================================
            // Cleanup
            // ============================================

            window.addEventListener('beforeunload', function() {
                if (window.countdownTimer) {
                    window.countdownTimer.stop();
                }
                if (window.playerManager) {
                    window.playerManager.stopAll();
                }
                if (window.gameInterval) {
                    clearInterval(window.gameInterval);
                }
            });
        </script>
    @endsection
</div>

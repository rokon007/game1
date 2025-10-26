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
            /* আপনার existing CSS স্টাইলগুলি এখানে রাখুন */
            .bet-input-group {
                display: flex;
                align-items: center;
                gap: 12px;
                background: #f8f9fa;
                border: 2px solid #e9ecef;
                border-radius: 12px;
                padding: 8px;
                transition: all 0.3s ease;
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
                width: 44px;
                height: 44px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 1.2rem;
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
                font-size: 1.1rem;
                font-weight: 600;
                text-align: center;
                padding: 0;
                min-width: 0;
            }

            .bet-input:focus {
                outline: none;
                box-shadow: none;
            }

            .bet-input:disabled {
                background: transparent;
            }

            .crash-game-display {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 400px;
                border-radius: 15px;
                position: relative;
                overflow: hidden;
            }

            .multiplier-display {
                font-size: 6rem;
                font-weight: bold;
                text-shadow: 0 0 20px rgba(255,255,255,0.5);
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

            .recent-game-card {
                transition: transform 0.2s;
            }

            .recent-game-card:hover {
                transform: translateY(-2px);
            }

            .bet-card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .countdown-container {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 2rem;
                border: 1px solid rgba(255, 255, 255, 0.2);
                width: 100%;
            }

            .countdown-timer {
                font-size: 4rem;
                font-weight: 800;
                background: linear-gradient(45deg, #FFD700, #FFA500);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
                line-height: 1;
                margin-bottom: 1rem;
            }

            .progress-bar-custom {
                height: 12px;
                border-radius: 10px;
                overflow: hidden;
                background: rgba(255, 255, 255, 0.2);
                margin: 1.5rem 0;
            }

            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #00b09b, #96c93d);
                border-radius: 10px;
                width: 100%;
                transition: width 1s linear;
            }

            @media (max-width: 768px) {
                .multiplier-display {
                    font-size: 4rem;
                }

                .countdown-timer {
                    font-size: 3rem;
                }

                .crash-game-display {
                    min-height: 300px;
                }

                .countdown-container {
                    padding: 1.5rem;
                }
            }

            .bet-input-group {
                    gap: 8px;
                    padding: 6px;
                }

                .bet-control-btn {
                    width: 40px;
                    height: 40px;
                    font-size: 1.1rem;
                }

            @media (max-width: 576px) {
                .multiplier-display {
                    font-size: 3rem;
                }

                .countdown-timer {
                    font-size: 2.5rem;
                }
                .bet-control-btn {
                    width: 36px;
                    height: 36px;
                    font-size: 1rem;
                }
            }
            .opacity-75 {
                color: black;
            }
        </style>
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    <div class="container my-4 my-md-5">
        <!-- Error/Success Messages -->
        @if($errorMessage)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ $errorMessage }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div wire:poll.100ms="refreshGameState" class="row g-3 g-md-4 mt-4">
            <!-- Main Game Area -->
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-3 p-md-4">
                        <!-- Game Display -->
                        <div class="crash-game-display d-flex align-items-center justify-content-center position-relative mb-4">
                            <div class="text-center text-white position-relative z-1 w-100 px-3">
                                @if($gameStatus === 'waiting')
                                    <div class="countdown-container">
                                        <img src="{{asset('assets/frontend/img/numbers.gif')}}">
                                        <p class="fs-5 opacity-75 mb-3">Next round starting in</p>

                                        <!-- Progress Bar -->
                                        <div class="progress-bar-custom mx-auto" style="max-width: 300px;">
                                            <div class="progress-fill" id="countdown-progress"></div>
                                        </div>

                                        <!-- Waiting player count -->
                                        <div class="mt-3">
                                            <span class="badge bg-warning text-dark fs-6">
                                                <i class="fas fa-users me-1"></i>
                                                <span id="waiting-player-count">{{ number_format($waitingPlayerCount) }}</span> players have bet
                                            </span>
                                        </div>
                                    </div>
                                @elseif($gameStatus === 'running')
                                    <div>
                                        <div class="multiplier-display pulse-button" id="multiplier-display">
                                            {{ number_format($currentMultiplier, 2) }}x
                                        </div>
                                        <img src="{{asset('assets/frontend/img/rocket.gif')}}">
                                        <p class="fs-4 mt-3 opacity-75">
                                            <i class="fas fa-rocket me-2"></i>
                                            <span id="running-player-count">{{ number_format($runningPlayerCount) }}</span> players remaining
                                        </p>
                                    </div>
                                @elseif($gameStatus === 'crashed')
                                    <div class="crashed-animation">
                                        <img src="{{asset('assets/frontend/img/crashed.gif')}}">
                                        <i class="fas fa-bomb fa-4x mb-3 text-danger"></i>
                                        <h2 class="display-4 fw-bold text-danger mb-3">CRASHED!</h2>
                                    </div>
                                @endif
                            </div>

                            <!-- User Bet Info -->
                            @if($userBet)
                                <div class="position-absolute top-0 end-0 m-2 m-md-3 bet-card rounded p-2 p-md-3 text-white">
                                    <p class="mb-1 small opacity-75">
                                        <i class="fas fa-ticket-alt me-1"></i>Your Bet
                                    </p>
                                    <h5 class="mb-2 fw-bold">৳{{ number_format($userBet->bet_amount, 2) }}</h5>
                                    @if($userBet->isWon())
                                        <span class="badge bg-success">
                                            <i class="fas fa-trophy me-1"></i>
                                            Won: ৳{{ number_format($userBet->profit + $userBet->bet_amount, 2) }}
                                        </span>
                                    @elseif($userBet->isLost())
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Lost
                                        </span>
                                    @elseif($userBet->isPlaying())
                                        <span class="badge bg-warning text-dark pulse-button">
                                            <i class="fas fa-spinner fa-spin me-1"></i>Playing...
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Betting Controls -->
                        @auth
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-coins me-1"></i>Bet Amount
                                        <span class="text-muted small ms-2">
                                            (Balance: ৳{{ number_format(auth()->user()->credit, 2) }})
                                        </span>
                                    </label>
                                    <!-- Professional Bet Amount Controls -->
                                    <div class="bet-input-group @if($userBet || $gameStatus === 'running') disabled @endif">
                                        <button
                                            class="bet-control-btn"
                                            wire:click="decreaseBetAmount"
                                            @if($userBet || $gameStatus === 'running') disabled @endif
                                        >
                                            -
                                        </button>

                                        <input
                                            type="number"
                                            wire:model="betAmount"
                                            step="0.01"
                                            min="1"
                                            class="form-control bet-input"
                                            placeholder="Enter bet amount"
                                            @if($userBet || $gameStatus === 'running') disabled @endif
                                        >

                                        <button
                                            class="bet-control-btn"
                                            wire:click="increaseBetAmount"
                                            @if($userBet || $gameStatus === 'running') disabled @endif
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    @if(!$userBet && $gameStatus !== 'running')
                                        <button
                                            wire:click="placeBet"
                                            class="btn btn-success btn-lg w-100 fw-bold py-3"
                                        >
                                            <i class="fas fa-play-circle me-2"></i>Place Bet
                                        </button>
                                    @elseif($userBet && $userBet->isPlaying() && $gameStatus === 'running')
                                        <button
                                            wire:click="cashout"
                                            class="btn btn-warning btn-lg w-100 fw-bold py-3 pulse-button"
                                        >
                                            <i class="fas fa-hand-holding-usd me-2"></i>
                                            Cashout ৳{{ number_format($userBet->bet_amount * $currentMultiplier, 2) }}
                                        </button>
                                    @else
                                        <button
                                            disabled
                                            class="btn btn-secondary btn-lg w-100 fw-bold py-3"
                                        >
                                            <i class="fas fa-hourglass-half me-2"></i>Please Wait
                                        </button>
                                    @endif
                                </div>

                                <!-- Quick Bet Buttons -->
                                @if(!$userBet && $gameStatus !== 'running')
                                    <div class="col-12">
                                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                                            <button wire:click="$set('betAmount', 10)" class="btn btn-outline-primary btn-sm">৳10</button>
                                            <button wire:click="$set('betAmount', 50)" class="btn btn-outline-primary btn-sm">৳50</button>
                                            <button wire:click="$set('betAmount', 100)" class="btn btn-outline-primary btn-sm">৳100</button>
                                            <button wire:click="$set('betAmount', 500)" class="btn btn-outline-primary btn-sm">৳500</button>
                                            <button wire:click="$set('betAmount', 1000)" class="btn btn-outline-primary btn-sm">৳1000</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-4 py-md-5">
                                <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-4 fs-5">Login to play</p>
                                <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4 px-md-5">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 mb-4">
                <!-- Recent Games -->
                <div class="card shadow-lg border-0 mb-3 mb-md-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0 text-white">
                            <i class="fas fa-history me-2"></i>Recent Results
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        @forelse($recentGames as $game)
                            <div class="recent-game-card p-2 mb-2 rounded {{ $game['crash_point'] >= 2 ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger' }} border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5 {{ $game['crash_point'] >= 2 ? 'text-white' : 'text-dark' }}">
                                        <i class="fas fa-chart-line me-1"></i>
                                        {{ number_format($game['crash_point'], 2) }}x
                                    </span>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>{{ $game['created_at'] }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">No results yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Game Rules -->
                <div class="card shadow-sm border-0 mt-3 mt-md-4 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0 text-white">
                            <i class="fas fa-info-circle me-2"></i>How to Play
                        </h6>
                    </div>
                    <div class="card-body">
                        <ol class="small mb-0 ps-3">
                            <li class="mb-2">Set your bet amount</li>
                            <li class="mb-2">Multiplier increases when game starts</li>
                            <li class="mb-2">Cashout before it crashes</li>
                            <li class="mb-0">Wait too long and you lose everything!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('footer')
        <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>

        <script>
            // সরাসরি Livewire event listeners যোগ করুন
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded - initializing game');

                // সরাসরি PlayerCountManager ক্লাস ডিফাইন করুন
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

                // Global instance তৈরি করুন
                window.playerManager = new PlayerCountManager();

                // Countdown Timer ক্লাস
                class CountdownTimer {
                    constructor() {
                        this.interval = null;
                        this.endTime = null;
                        this.isRunning = false;
                    }

                    start(duration = 10) {
                        this.stop();
                        const progressElement = document.getElementById('countdown-progress');
                        if (!progressElement) return;

                        this.endTime = Date.now() + (duration * 1000);
                        this.isRunning = true;

                        this.updateDisplay(progressElement, duration);
                        this.interval = setInterval(() => {
                            const now = Date.now();
                            const timeLeft = Math.max(0, this.endTime - now);
                            const secondsLeft = Math.ceil(timeLeft / 1000);
                            this.updateDisplay(progressElement, secondsLeft);

                            if (timeLeft <= 0) {
                                this.stop();
                                progressElement.style.width = '0%';
                            }
                        }, 100);
                    }

                    updateDisplay(progressElement, secondsLeft) {
                        if (progressElement) {
                            const progressPercent = (secondsLeft / 10) * 100;
                            progressElement.style.width = progressPercent + '%';
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

                // সরাসরি Livewire event listeners
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
                    console.log('Livewire event: gameCrashed');
                    if (window.playerManager) {
                        window.playerManager.stopAll();
                    }
                    if (window.countdownTimer) {
                        window.countdownTimer.stop();
                    }
                    // Reset player counts
                    setTimeout(() => {
                        @this.call('resetPlayerCounts');
                    }, 1000);
                    window.showCrashAlert(data.crashPoint);
                });

                Livewire.on('countdownShouldStart', () => {
                    console.log('Livewire event: countdownShouldStart');
                    if (window.playerManager) {
                        window.playerManager.stopAll();
                    }
                    // Reset player counts
                    @this.call('resetPlayerCounts');
                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startWaitingIncrease();
                        }
                    }, 500);
                    setTimeout(() => {
                        if (window.countdownTimer) {
                            window.countdownTimer.start(10);
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
                @endif

                @if($gameStatus === 'running')
                    console.log('Initializing running state');
                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startRunningDecrease();
                        }
                    }, 1000);
                @endif
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
    @endsection

    {{-- @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded - initializing game');

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
                        }, 1000); // 1 second interval
                    }

                    startRunningDecrease() {
                        console.log('Starting running decrease');
                        this.stopAll();
                        this.runningInterval = setInterval(() => {
                            @this.call('decreaseRunningPlayers');
                        }, 800); // 0.8 second interval
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
                window.countdownTimer = new CountdownTimer();

                // Countdown Timer ক্লাস
                class CountdownTimer {
                    constructor() {
                        this.interval = null;
                        this.endTime = null;
                        this.isRunning = false;
                    }

                    start(duration = 10) {
                        this.stop();
                        const progressElement = document.getElementById('countdown-progress');
                        if (!progressElement) return;

                        this.endTime = Date.now() + (duration * 1000);
                        this.isRunning = true;

                        this.updateDisplay(progressElement, duration);
                        this.interval = setInterval(() => {
                            const now = Date.now();
                            const timeLeft = Math.max(0, this.endTime - now);
                            const secondsLeft = Math.ceil(timeLeft / 1000);
                            this.updateDisplay(progressElement, secondsLeft);

                            if (timeLeft <= 0) {
                                this.stop();
                                progressElement.style.width = '0%';
                            }
                        }, 100);
                    }

                    updateDisplay(progressElement, secondsLeft) {
                        if (progressElement) {
                            const progressPercent = (secondsLeft / 10) * 100;
                            progressElement.style.width = progressPercent + '%';
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
                    console.log('Livewire event: gameCrashed');
                    if (window.playerManager) {
                        window.playerManager.stopAll();
                    }
                    if (window.countdownTimer) {
                        window.countdownTimer.stop();
                    }
                    // Game crashed হলে শুধু running player count reset করুন
                    setTimeout(() => {
                        @this.set('runningPlayerCount', 0);
                    }, 1000);

                    // SweetAlert show
                    Swal.fire({
                        icon: 'error',
                        title: 'CRASHED!',
                        text: 'Crashed at ' + data.crashPoint.toFixed(2) + 'x',
                        timer: 3000,
                        showConfirmButton: false
                    });
                });

                Livewire.on('countdownShouldStart', () => {
                    console.log('Livewire event: countdownShouldStart');
                    if (window.playerManager) {
                        window.playerManager.stopAll();
                    }
                    // শুধু running player count reset করুন
                    @this.set('runningPlayerCount', 0);

                    // Waiting increase শুরু করুন
                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startWaitingIncrease();
                        }
                    }, 500);

                    // Countdown শুরু করুন
                    setTimeout(() => {
                        if (window.countdownTimer) {
                            window.countdownTimer.start(10);
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
                @endif

                @if($gameStatus === 'running')
                    console.log('Initializing running state');
                    setTimeout(() => {
                        if (window.playerManager) {
                            window.playerManager.startRunningDecrease();
                        }
                    }, 1000);
                @endif
            });

            // Cleanup
            window.addEventListener('beforeunload', function() {
                if (window.countdownTimer) {
                    window.countdownTimer.stop();
                }
                if (window.playerManager) {
                    window.playerManager.stopAll();
                }
            });
        </script>
    @endsection --}}
</div>

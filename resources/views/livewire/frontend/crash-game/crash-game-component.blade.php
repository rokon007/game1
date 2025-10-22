<div wire:poll.100ms="refreshGameState">
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

            /* Countdown Styles */
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

            /* Mobile Responsive */
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

            @media (max-width: 576px) {
                .multiplier-display {
                    font-size: 3rem;
                }

                .countdown-timer {
                    font-size: 2.5rem;
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

        {{-- @if($successMessage)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ $successMessage }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif --}}

        <div class="row g-3 g-md-4 mt-4">
            <!-- Main Game Area -->
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-3 p-md-4">
                        <!-- Game Display -->
                        <div class="crash-game-display d-flex align-items-center justify-content-center position-relative mb-4">
                            <div class="text-center text-white position-relative z-1 w-100 px-3">
                                @if($gameStatus === 'waiting')
                                    <div class="countdown-container">
                                        {{-- <div class="countdown-timer" id="countdown-timer">10</div>
                                        <h4 class="fw-bold mb-2">GET READY!</h4> --}}
                                        <img src="{{asset('assets/frontend/img/numbers.gif')}}">
                                        <p class="fs-5 opacity-75 mb-3">Next round starting in</p>

                                        <!-- Progress Bar -->
                                        <div class="progress-bar-custom mx-auto" style="max-width: 300px;">
                                            <div class="progress-fill" id="countdown-progress"></div>
                                        </div>

                                        @if($currentGame && $currentGame->activeBets->count() > 0)
                                            <div class="mt-3">
                                                <span class="badge bg-warning text-dark fs-6">
                                                    <i class="fas fa-users me-1"></i>
                                                    {{ $currentGame->activeBets->count() }} players have bet
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($gameStatus === 'running')
                                    <div>
                                        <div class="multiplier-display pulse-button" id="multiplier-display">
                                            {{ number_format($currentMultiplier, 2) }}x
                                        </div>
                                        <img src="{{asset('assets/frontend/img/rocket.gif')}}">
                                        <p class="fs-4 mt-3 opacity-75">
                                            <i class="fas fa-rocket me-2"></i>Game in progress...
                                        </p>
                                    </div>
                                @elseif($gameStatus === 'crashed')
                                    <div class="crashed-animation">
                                        <img src="{{asset('assets/frontend/img/crashed.gif')}}">
                                        <i class="fas fa-bomb fa-4x mb-3 text-danger"></i>
                                        <h2 class="display-4 fw-bold text-danger mb-3">CRASHED!</h2>
                                        <p class="fs-2">
                                            Crashed at {{ number_format($currentGame->crash_point ?? 0, 2) }}x
                                        </p>
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
                                    <input
                                        type="number"
                                        wire:model="betAmount"
                                        step="0.01"
                                        min="1"
                                        class="form-control form-control-lg"
                                        placeholder="Enter bet amount"
                                        @if($userBet || $gameStatus === 'running') disabled @endif
                                    >
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
                                            Cashout ({{ number_format($currentMultiplier, 2) }}x)
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

                <!-- Game Statistics -->
                {{-- @if($currentGame)
                    <div class="card shadow-sm border-0 mt-3 mt-md-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-chart-line me-2"></i>Round Statistics
                            </h5>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <small class="text-muted d-block">Total Bet</small>
                                        <h5 class="mb-0">৳{{ number_format($currentGame->total_bet_amount, 2) }}</h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <small class="text-muted d-block">Players</small>
                                        <h5 class="mb-0">{{ $currentGame->bets->count() }}</h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Game ID</small>
                                    <h5 class="mb-0">#{{ $currentGame->id }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif --}}
            </div>

            <!-- Sidebar -->
            {{-- <div class="col-lg-4">
                <!-- Recent Games -->
                <div class="card shadow-lg border-0 mb-3 mb-md-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Results
                        </h5>
                    </div>
                    <div class="card-body p-2">
                        @forelse($recentGames as $game)
                            <div class="recent-game-card p-2 mb-2 rounded {{ $game['crash_point'] >= 2 ? 'bg-success bg-opacity-10 border-success' : 'bg-danger bg-opacity-10 border-danger' }} border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5 {{ $game['crash_point'] >= 2 ? 'text-success' : 'text-danger' }}">
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

                <!-- Active Players -->
                @if($currentGame && ($gameStatus === 'waiting' || $gameStatus === 'running'))
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Active Players
                            </h5>
                        </div>
                        <div class="card-body p-2" style="max-height: 300px; overflow-y: auto;">
                            @forelse($currentGame->activeBets()->with('user')->latest()->limit(20)->get() as $bet)
                                <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="fw-semibold small text-truncate" style="max-width: 120px;">{{ $bet->user->name }}</span>
                                    </div>
                                    <span class="badge bg-success">৳{{ number_format($bet->bet_amount, 2) }}</span>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted">
                                    <p class="mb-0 small">No active players</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <!-- Game Rules -->
                <div class="card shadow-sm border-0 mt-3 mt-md-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">
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
            </div> --}}
        </div>
    </div>

    @section('footer')
        <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>

        <script>
            // Simple and reliable countdown implementation
            class CountdownTimer {
                constructor() {
                    this.interval = null;
                    this.endTime = null;
                    this.isRunning = false;
                }

                start(duration = 10) {
                    this.stop();

                    const countdownElement = document.getElementById('countdown-timer');
                    const progressElement = document.getElementById('countdown-progress');

                    if (!countdownElement || !progressElement) {
                        console.error('Countdown elements not found');
                        return;
                    }

                    this.endTime = Date.now() + (duration * 1000);
                    this.isRunning = true;

                    // Initial update
                    this.updateDisplay(countdownElement, progressElement, duration);

                    this.interval = setInterval(() => {
                        const now = Date.now();
                        const timeLeft = Math.max(0, this.endTime - now);
                        const secondsLeft = Math.ceil(timeLeft / 1000);

                        this.updateDisplay(countdownElement, progressElement, secondsLeft);

                        if (timeLeft <= 0) {
                            this.stop();
                            countdownElement.textContent = '0';
                            progressElement.style.width = '0%';
                        }
                    }, 100);
                }

                updateDisplay(countdownElement, progressElement, secondsLeft) {
                    if (countdownElement) {
                        countdownElement.textContent = secondsLeft;
                    }
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

            // Create global countdown instance
            const countdownTimer = new CountdownTimer();

            // Initialize when page loads
            document.addEventListener('DOMContentLoaded', function() {
                // Start countdown immediately if in waiting state
                @if($gameStatus === 'waiting')
                    setTimeout(() => {
                        countdownTimer.start(10);
                    }, 500);
                @endif

                // Listen for Livewire events
                Livewire.on('countdownShouldStart', () => {
                    console.log('Countdown should start event received');
                    setTimeout(() => {
                        countdownTimer.start(10);
                    }, 500);
                });

                Livewire.on('gameCrashed', (data) => {
                    countdownTimer.stop();
                    showCrashAlert(data.crashPoint);
                });

                Livewire.on('betPlaced', () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Bet Successful!',
                        text: 'Your bet has been recorded',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });

                Livewire.on('cashedOut', () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cashout Successful!',
                        text: 'You won!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });

            // Multiplier animation functions
            let gameInterval = null;
            let currentMult = 1.00;

            function startMultiplierAnimation(targetMult) {
                if (gameInterval) clearInterval(gameInterval);

                currentMult = 1.00;
                gameInterval = setInterval(() => {
                    currentMult += 0.01;
                    const displayElement = document.getElementById('multiplier-display');
                    if (displayElement) {
                        displayElement.textContent = currentMult.toFixed(2) + 'x';
                    }

                    if (currentMult >= targetMult) {
                        stopMultiplierAnimation();
                    }
                }, 50);
            }

            function stopMultiplierAnimation() {
                if (gameInterval) {
                    clearInterval(gameInterval);
                    gameInterval = null;
                }
            }

            function showCrashAlert(crashPoint) {
                Swal.fire({
                    icon: 'error',
                    title: 'CRASHED!',
                    text: 'Crashed at ' + crashPoint.toFixed(2) + 'x',
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            // Cleanup
            window.addEventListener('beforeunload', function() {
                countdownTimer.stop();
                stopMultiplierAnimation();
            });
        </script>
    @endsection
</div>

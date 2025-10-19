<div>
    @section('meta_description')
      <meta name="description" content="Housieblitz - Lucky Spin">
    @endsection
    @section('title')
        <title>Housieblitz | Lucky Spin</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            .game-container {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px 0;
            }

            .game-card {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            /* Wheel Styles */
            .wheel-container {
                position: relative;
                width: 350px;
                height: 350px;
                margin: 0 auto;
            }

            .wheel {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                position: relative;
                overflow: hidden;
                border: 10px solid #2c3e50;
                box-shadow: 0 0 30px rgba(0,0,0,0.3);
                background: conic-gradient(
                    #e74c3c 0deg 60deg,
                    #2ecc71 60deg 120deg,
                    #e74c3c 120deg 180deg,
                    #f39c12 180deg 240deg,
                    #e74c3c 240deg 300deg,
                    #2ecc71 300deg 360deg
                );
                transition: transform 4s cubic-bezier(0.2, 0.8, 0.3, 1);
            }

            .wheel-pointer {
                position: absolute;
                top: -20px;
                left: 50%;
                transform: translateX(-50%);
                color: #e74c3c;
                font-size: 60px;
                z-index: 100;
                filter: drop-shadow(0 5px 10px rgba(0,0,0,0.3));
            }

            .wheel-center {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 60px;
                height: 60px;
                background: #2c3e50;
                border-radius: 50%;
                border: 5px solid #ecf0f1;
                z-index: 90;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
            }

            .segment-label {
                position: absolute;
                color: white;
                font-weight: bold;
                font-size: 14px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
                z-index: 10;
                transform-origin: center;
            }

            .label-1 { top: 10%; left: 70%; transform: rotate(30deg); }
            .label-2 { top: 10%; left: 30%; transform: rotate(-30deg); }
            .label-3 { top: 50%; left: 10%; transform: rotate(-90deg); }
            .label-4 { top: 50%; left: 80%; transform: rotate(90deg); }
            .label-5 { top: 85%; left: 70%; transform: rotate(150deg); }
            .label-6 { top: 85%; left: 30%; transform: rotate(-150deg); }

            /* Button Styles */
            .spin-btn {
                background: linear-gradient(135deg, #27ae60, #2ecc71);
                border: none;
                border-radius: 50px;
                padding: 15px 40px;
                font-size: 18px;
                font-weight: bold;
                color: white;
                box-shadow: 0 10px 25px rgba(39, 174, 96, 0.4);
                transition: all 0.3s ease;
            }

            .spin-btn:hover:not(:disabled) {
                transform: translateY(-2px);
                box-shadow: 0 15px 35px rgba(39, 174, 96, 0.6);
            }

            .spin-btn:disabled {
                background: #95a5a6;
                transform: scale(0.95);
            }

            .credit-display {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                border-radius: 15px;
                padding: 15px;
                box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            }

            .bet-controls .btn {
                width: 45px;
                height: 45px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .sound-toggle {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                background: rgba(255,255,255,0.9);
                border-radius: 50%;
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                cursor: pointer;
                border: none;
                font-size: 20px;
                color: #2c3e50;
            }

            /* Reward Preview Styles */
            .reward-preview-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.95);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.4s ease;
            }

            .reward-preview-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            .reward-preview-content {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 30px;
                padding: 50px 40px;
                text-align: center;
                color: white;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 30px 60px rgba(0,0,0,0.5);
                transform: scale(0.7) rotateX(20deg);
                transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
                position: relative;
                overflow: hidden;
            }

            .reward-preview-overlay.active .reward-preview-content {
                transform: scale(1) rotateX(0deg);
            }

            .reward-preview-content::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
                animation: shimmer 2s infinite;
            }

            @keyframes shimmer {
                0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
                100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            }

            .reward-icon {
                font-size: 80px;
                margin-bottom: 20px;
                animation: bounce 2s infinite;
                filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
            }

            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-30px); }
                60% { transform: translateY(-15px); }
            }

            .reward-title {
                font-size: 28px;
                font-weight: bold;
                margin-bottom: 15px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                letter-spacing: 2px;
            }

            .reward-multiplier {
                font-size: 72px;
                font-weight: bold;
                color: #f1c40f;
                margin: 20px 0;
                text-shadow: 0 0 30px rgba(241, 196, 15, 0.8), 0 0 60px rgba(241, 196, 15, 0.5);
                animation: pulse 1.5s infinite;
                position: relative;
                z-index: 1;
            }

            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                    text-shadow: 0 0 30px rgba(241, 196, 15, 0.8), 0 0 60px rgba(241, 196, 15, 0.5);
                }
                50% {
                    transform: scale(1.15);
                    text-shadow: 0 0 40px rgba(241, 196, 15, 1), 0 0 80px rgba(241, 196, 15, 0.7);
                }
            }

            .reward-amount {
                font-size: 32px;
                margin: 15px 0;
                font-weight: bold;
                color: #ffffff;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }

            .reward-subtitle {
                font-size: 18px;
                opacity: 0.9;
                margin-top: 20px;
                animation: fadeInOut 2s infinite;
            }

            @keyframes fadeInOut {
                0%, 100% { opacity: 0.6; }
                50% { opacity: 1; }
            }

            .countdown-timer {
                font-size: 48px;
                font-weight: bold;
                color: #f1c40f;
                margin-top: 30px;
                text-shadow: 0 0 20px rgba(241, 196, 15, 0.8);
                animation: countdownPulse 1s infinite;
            }

            @keyframes countdownPulse {
                0%, 100% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.2); opacity: 0.8; }
            }

            /* Sparkle Effects */
            .sparkle {
                position: absolute;
                width: 4px;
                height: 4px;
                background: white;
                border-radius: 50%;
                animation: sparkleFloat 3s infinite;
            }

            @keyframes sparkleFloat {
                0% { transform: translateY(0) scale(0); opacity: 0; }
                50% { opacity: 1; }
                100% { transform: translateY(-200px) scale(1); opacity: 0; }
            }

            .result-alert {
                animation: slideDown 0.5s ease;
            }

            @keyframes slideDown {
                from {
                    transform: translateY(-20px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            @media (max-width: 768px) {
                .wheel-container {
                    width: 280px;
                    height: 280px;
                }

                .wheel-pointer {
                    font-size: 50px;
                    top: -15px;
                }

                .segment-label {
                    font-size: 12px;
                }

                .sound-toggle {
                    top: 10px;
                    right: 10px;
                    width: 40px;
                    height: 40px;
                    font-size: 16px;
                }

                .reward-preview-content {
                    padding: 40px 25px;
                }

                .reward-icon {
                    font-size: 60px;
                }

                .reward-title {
                    font-size: 22px;
                }

                .reward-multiplier {
                    font-size: 56px;
                }

                .reward-amount {
                    font-size: 24px;
                }

                .countdown-timer {
                    font-size: 36px;
                }
            }
        </style>
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    <div class="game-container">
        <!-- Reward Preview Overlay -->
        <div class="reward-preview-overlay" id="rewardPreviewOverlay">
            <div class="reward-preview-content">
                <!-- Sparkle effects -->
                <div class="sparkle" style="top: 10%; left: 20%; animation-delay: 0s;"></div>
                <div class="sparkle" style="top: 20%; left: 80%; animation-delay: 0.3s;"></div>
                <div class="sparkle" style="top: 80%; left: 15%; animation-delay: 0.6s;"></div>
                <div class="sparkle" style="top: 70%; left: 85%; animation-delay: 0.9s;"></div>
                <div class="sparkle" style="top: 40%; left: 10%; animation-delay: 1.2s;"></div>
                <div class="sparkle" style="top: 50%; left: 90%; animation-delay: 1.5s;"></div>

                <div class="reward-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <h2 class="reward-title">POSSIBLE REWARD</h2>
                <div class="reward-multiplier" id="previewMultiplier">?x</div>
                <div class="reward-amount" id="previewAmount">? Credits</div>
                <p class="reward-subtitle">✨ Spin to reveal your prize! ✨</p>
                <div class="countdown-timer" id="countdownTimer">3</div>
            </div>
        </div>

        <!-- Sound Toggle Button -->
        <button class="sound-toggle" id="soundToggle">
            <i class="fas fa-volume-up"></i>
        </button>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="game-card p-4 mt-4">
                        <!-- Wheel -->
                        <div class="wheel-container mb-4">
                            <div class="wheel" id="wheel">
                                <div class="segment-label label-1">LOSE</div>
                                <div class="segment-label label-2">WIN</div>
                                <div class="segment-label label-3">LOSE</div>
                                <div class="segment-label label-4">JACKPOT</div>
                                <div class="segment-label label-5">LOSE</div>
                                <div class="segment-label label-6">WIN</div>
                            </div>
                            <div class="wheel-pointer">
                                <i class="fas fa-caret-down"></i>
                            </div>
                            <div class="wheel-center">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>

                        <!-- Bet Controls -->
                        <div class="bet-controls mb-4">
                            <div class="text-center">
                                <label class="form-label fw-bold fs-5">BET AMOUNT</label>
                            </div>
                            <div class="d-flex justify-content-center align-items-center gap-3">
                                <button class="btn btn-outline-danger"
                                        wire:click="decrementBet"
                                        wire:loading.attr="disabled">
                                    <i class="fas fa-minus"></i>
                                </button>

                                <div class="text-center">
                                    <input type="number"
                                           wire:model="betAmount"
                                           class="form-control form-control-lg text-center fw-bold border-3"
                                           style="width: 150px; border-color: #3498db;"
                                           min="1"
                                           max="10000">
                                    <small class="text-muted">Min: 5 - Max: 1,000</small>
                                </div>

                                <button class="btn btn-outline-success"
                                        wire:click="incrementBet"
                                        wire:loading.attr="disabled">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Spin Button -->
                        <div class="text-center mb-4">
                            <button id="spinButton"
                                    wire:loading.attr="disabled"
                                    class="spin-btn">
                                <span wire:loading.remove>
                                    <i class="fas fa-play me-2"></i>SPIN NOW
                                </span>
                                <span wire:loading>
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    SPINNING...
                                </span>
                            </button>
                        </div>

                        <div class="credit-display text-center mb-4">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-2">
                                        <i class="fas fa-wallet me-2"></i>
                                        <strong>YOUR CREDIT</strong>
                                    </div>
                                    <h6 class="fw-bold text-white mb-0">{{ number_format($credit) }}</h6>
                                </div>
                            </div>
                        </div>

                        <!-- Flash Messages -->
                        @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Result Display -->
                        {{-- @if($result)
                            <div class="result-alert">
                                <div class="alert
                                    @if($result === 'win') alert-success
                                    @elseif($result === 'jackpot') alert-warning
                                    @else alert-info @endif
                                    alert-dismissible fade show">
                                    <div class="text-center">
                                        @if($result === 'win')
                                            <h5 class="alert-heading">
                                                <i class="fas fa-trophy me-2"></i>CONGRATULATIONS!
                                            </h5>
                                            <p class="mb-2">You won <strong>{{ number_format($reward) }} credits!</strong></p>
                                            <p class="mb-0 text-muted">Multiplier: {{ number_format($reward / $betAmount, 1) }}x</p>
                                        @elseif($result === 'jackpot')
                                            <h5 class="alert-heading">
                                                <i class="fas fa-crown me-2"></i>JACKPOT!
                                            </h5>
                                            <p class="mb-2">You won the massive jackpot of</p>
                                            <h4 class="text-warning fw-bold">{{ number_format($reward) }} credits!</h4>
                                        @else
                                            <h5 class="alert-heading">
                                                <i class="fas fa-redo me-2"></i>BETTER LUCK NEXT TIME
                                            </h5>
                                            <p class="mb-0">Keep trying!</p>
                                        @endif
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            </div>
                        @endif --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Audio Elements -->
    <audio id="spinSound" preload="auto">
        <source src="{{asset('sounds/spin/spinSound.mp3')}}" type="audio/mpeg">
    </audio>
    <audio id="winSound" preload="auto">
        <source src="{{asset('sounds/spin/winSound.mp3')}}" type="audio/mpeg">
    </audio>
    <audio id="jackpotSound" preload="auto">
        <source src="{{asset('sounds/spin/jackpotSound.mp3')}}" type="audio/mpeg">
    </audio>
    <audio id="loseSound" preload="auto">
        <source src="{{asset('sounds/spin/loseSound.mp3')}}" type="audio/mpeg">
    </audio>
    <audio id="clickSound" preload="auto">
        <source src="{{asset('sounds/spin/clickSound.mp3')}}" type="audio/mpeg">
    </audio>
    <audio id="tickSound" preload="auto">
        <source src="https://assets.mixkit.co/sfx/preview/mixkit-arcade-game-jump-coin-216.mp3" type="audio/mpeg">
    </audio>
    <audio id="previewSound" preload="auto">
        <source src="{{asset('sounds/spin/rewardPreview.mp3')}}" type="audio/mpeg">
    </audio>

    @section('footer')
        <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <script>
            // Sound Management
            class SoundManager {
                constructor() {
                    this.sounds = {
                        spin: document.getElementById('spinSound'),
                        win: document.getElementById('winSound'),
                        jackpot: document.getElementById('jackpotSound'),
                        lose: document.getElementById('loseSound'),
                        click: document.getElementById('clickSound'),
                        tick: document.getElementById('tickSound'),
                        preview: document.getElementById('previewSound')
                    };
                    this.enabled = true;
                    this.init();
                }

                init() {
                    Object.values(this.sounds).forEach(sound => {
                        if (sound) sound.volume = 0.6;
                    });
                    if (this.sounds.spin) this.sounds.spin.volume = 0.4;
                    if (this.sounds.tick) this.sounds.tick.volume = 0.3;
                    if (this.sounds.preview) this.sounds.preview.volume = 0.7;

                    const toggleBtn = document.getElementById('soundToggle');
                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', () => this.toggle());
                    }
                    this.updateToggleIcon();
                }

                play(soundName) {
                    if (!this.enabled || !this.sounds[soundName]) return;
                    this.sounds[soundName].currentTime = 0;
                    this.sounds[soundName].play().catch(e => console.log('Sound play failed:', e));
                }

                stop(soundName) {
                    if (this.sounds[soundName]) {
                        this.sounds[soundName].pause();
                        this.sounds[soundName].currentTime = 0;
                    }
                }

                toggle() {
                    this.enabled = !this.enabled;
                    this.updateToggleIcon();
                    if (this.enabled) this.play('click');
                }

                updateToggleIcon() {
                    const toggleBtn = document.getElementById('soundToggle');
                    if (toggleBtn) {
                        const icon = toggleBtn.querySelector('i');
                        if (icon) {
                            icon.className = this.enabled ? 'fas fa-volume-up' : 'fas fa-volume-mute';
                            toggleBtn.title = this.enabled ? 'Sound: ON' : 'Sound: OFF';
                        }
                    }
                }
            }

            const soundManager = new SoundManager();

            // Reward Preview Manager
            class RewardPreviewManager {
                constructor() {
                    this.overlay = document.getElementById('rewardPreviewOverlay');
                    this.multiplierEl = document.getElementById('previewMultiplier');
                    this.amountEl = document.getElementById('previewAmount');
                    this.countdownEl = document.getElementById('countdownTimer');
                    this.isActive = false;
                    this.countdownInterval = null;
                }

                show(betAmount) {
                    if (this.isActive) return;
                    this.isActive = true;

                    // Generate exciting random multipliers
                    const possibleMultipliers = [2, 3, 5, 10, 15, 20, 50, 100];
                    const randomMultiplier = possibleMultipliers[Math.floor(Math.random() * possibleMultipliers.length)];
                    const potentialWin = betAmount * randomMultiplier;

                    // Update display
                    this.multiplierEl.textContent = randomMultiplier + 'x';
                    this.amountEl.textContent = potentialWin.toLocaleString() + ' Credits';

                    // Show overlay
                    this.overlay.classList.add('active');
                    soundManager.play('preview');

                    // Start countdown
                    this.startCountdown();
                }

                hide() {
                    this.isActive = false;
                    this.overlay.classList.remove('active');
                    if (this.countdownInterval) {
                        clearInterval(this.countdownInterval);
                        this.countdownInterval = null;
                    }
                }

                startCountdown() {
                    let count = 3;
                    this.countdownEl.textContent = count;

                    this.countdownInterval = setInterval(() => {
                        count--;
                        if (count > 0) {
                            this.countdownEl.textContent = count;
                            soundManager.play('tick');
                        } else {
                            this.countdownEl.textContent = 'GO!';
                            soundManager.play('tick');
                            clearInterval(this.countdownInterval);

                            // Hide and trigger actual spin
                            setTimeout(() => {
                                this.hide();
                                this.triggerSpin();
                            }, 800);
                        }
                    }, 1000);
                }

                triggerSpin() {
                    // Trigger Livewire spin method
                    @this.call('spin');
                }
            }

            const rewardPreview = new RewardPreviewManager();

            // Intercept spin button click
            document.addEventListener('DOMContentLoaded', () => {
                const spinButton = document.getElementById('spinButton');
                if (spinButton) {
                    spinButton.addEventListener('click', function(e) {
                        // Check if not loading and has credit
                        const isLoading = this.hasAttribute('disabled');
                        const betAmount = parseInt(document.querySelector('input[wire\\:model="betAmount"]').value) || 100;

                        if (!isLoading) {
                            e.preventDefault();
                            e.stopPropagation();
                            soundManager.play('click');
                            rewardPreview.show(betAmount);
                        }
                    });
                }
            });

            // Listen for Livewire spin-wheel event
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('spin-wheel', (event) => {
                    console.log('Spin wheel event received:', event);
                    const data = event[0];
                    soundManager.play('spin');
                    startWheelSpin(data.angle, data);
                });
            });

            // Wheel spin function
            window.startWheelSpin = function(angle, spinData) {
                console.log('Starting wheel spin with angle:', angle);
                const wheel = document.getElementById('wheel');
                if (!wheel) return;

                wheel.style.transition = 'none';
                wheel.style.transform = 'rotate(0deg)';
                void wheel.offsetHeight;

                const fullSpins = 4 * 360;
                const targetRotation = fullSpins + (360 - angle);

                setTimeout(() => {
                    wheel.style.transition = 'transform 4s cubic-bezier(0.2, 0.8, 0.3, 1)';
                    wheel.style.transform = `rotate(${targetRotation}deg)`;
                }, 100);

                playTickSounds();

                setTimeout(() => {
                    soundManager.stop('spin');
                    if (spinData.result === 'jackpot') soundManager.play('jackpot');
                    else if (spinData.result === 'win') soundManager.play('win');
                    else soundManager.play('lose');
                    showResultAlert(spinData);
                }, 4500);
            };

            function playTickSounds() {
                let tickCount = 0;
                const maxTicks = 8;
                const tickTimer = setInterval(() => {
                    if (tickCount < maxTicks) {
                        soundManager.play('tick');
                        tickCount++;
                    } else {
                        clearInterval(tickTimer);
                    }
                }, 500);
            }

            function showResultAlert(data) {
                let title, html, icon;
                if (data.result === 'jackpot') {
                    title = '🎉 JACKPOT! 🎉';
                    icon = 'success';
                    html = `<div class="text-center">
                        <h4 class="text-warning fw-bold">CONGRATULATIONS!</h4>
                        <p>You hit the JACKPOT!</p>
                        <h2 class="text-success fw-bold my-3">${data.reward.toLocaleString()} CREDITS</h2>
                        <p class="text-muted">You are our lucky winner!</p>
                    </div>`;
                } else if (data.result === 'win') {
                    title = '🎊 YOU WON!';
                    icon = 'success';
                    html = `<div class="text-center">
                        <h5 class="text-success fw-bold">Congratulations!</h5>
                        <p>You won:</p>
                        <h3 class="text-success fw-bold my-2">${data.reward.toLocaleString()} CREDITS</h3>
                        <p class="text-muted">Great spin!</p>
                    </div>`;
                } else {
                    title = '😢 TRY AGAIN';
                    icon = 'info';
                    html = `<div class="text-center">
                        <p>Better luck next time!</p>
                        <p class="text-muted">Keep spinning!</p>
                    </div>`;
                }

                Swal.fire({
                    title: title,
                    html: html,
                    icon: icon,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'SPIN AGAIN',
                    allowOutsideClick: false
                });
            }

            // Play click sound for buttons
            document.addEventListener('click', function(e) {
                const button = e.target.closest('button');
                if (button && button.id !== 'soundToggle' && button.id !== 'spinButton') {
                    soundManager.play('click');
                }
            });

            console.log('Lucky Spin Game JavaScript loaded successfully');
            console.log('Reward Preview System ready!');
        </script>
    @endsection
</div>

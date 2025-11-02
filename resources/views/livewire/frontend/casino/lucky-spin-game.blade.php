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
        <link rel="stylesheet" href="https://unpkg.com/augmented-ui@2/augmented.css">
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
                color: #2e0bf7;
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

            .label-1 { top: 10%; left: 60%; transform: rotate(30deg); }
            .label-2 { top: 10%; left: 15%; transform: rotate(-30deg); }
            .label-3 { top: 50%; left: 0%; transform: rotate(-90deg); }
            .label-4 { top: 50%; left: 75%; transform: rotate(90deg); }
            .label-5 { top: 85%; left: 60%; transform: rotate(150deg); }
            .label-6 { top: 80%; left: 15%; transform: rotate(-150deg); }

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
                transition: all 0.3s ease;
            }

            .credit-display.credit-updating {
                animation: creditPulse 0.5s ease-in-out infinite;
                box-shadow: 0 5px 25px rgba(46, 204, 113, 0.6);
            }

            @keyframes creditPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); background: linear-gradient(135deg, #27ae60, #2ecc71); }
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

            /* Enhanced Reward Preview Styles - More Professional & Compact */
            .reward-preview-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                /* background: rgba(0, 0, 0, 0.7);  */
                background: transparent;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                backdrop-filter: blur(3px);
            }

            .reward-preview-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            .reward-preview-content {
                /* background: linear-gradient(145deg, #0a0a1a, #1a1a2e); */
                background: linear-gradient(145deg, rgba(0,0,0,0.45), rgba(30,30,30,0.25));
                box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.08);

                padding: 30px 25px;
                text-align: center;
                color: white;
                max-width: 380px;
                width: 85%;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0, 255, 255, 0.3),
                            0 0 0 2px rgba(0, 255, 255, 0.1),
                            inset 0 0 20px rgba(0, 255, 255, 0.1);
                transform: scale(0.8) translateY(20px);
                transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                overflow: hidden;
            }

            .reward-preview-overlay.active .reward-preview-content {
                transform: scale(1) translateY(0);
            }

            .reward-preview-content::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg,
                    transparent 0%,
                    rgba(0, 255, 255, 0.05) 30%,
                    rgba(0, 255, 255, 0.1) 50%,
                    rgba(0, 255, 255, 0.05) 70%,
                    transparent 100%);
                z-index: 0;
            }

            .reward-icon {
                font-size: 50px;
                margin-bottom: 15px;
                animation: subtleBounce 2s infinite;
                color: cyan;
                position: relative;
                z-index: 1;
                filter: drop-shadow(0 0 10px cyan);
            }

            @keyframes subtleBounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-15px); }
                60% { transform: translateY(-7px); }
            }

            .reward-title {
                display: inline-block;
                padding: 0.4em 1.5em;
                font-size: 18px;
                font-weight: bold;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                margin-bottom: 10px;
                color: #000;
                background: #ffff00;
                border-radius: 8px;
                position: relative;
                z-index: 1;
                box-shadow: 0 0 15px yellow;
            }

            .reward-multiplier {
                font-size: 52px;
                font-weight: bold;
                color: #ffff;
                margin: 15px 0;
                text-shadow: 0 0 20px #000;
                animation: gentlePulse 1.5s infinite;
                position: relative;
                z-index: 1;
            }

            @keyframes gentlePulse {
                0%, 100% {
                    transform: scale(1);
                    text-shadow: 0 0 20px #ff3366;
                }
                50% {
                    transform: scale(1.05);
                    text-shadow: 0 0 30px #ff3366;
                }
            }

            .reward-amount {
                font-size: 24px;
                margin: 10px 0;
                font-weight: bold;
                color: #ffcc00;
                text-shadow: 0 0 15px #ffcc00;
                position: relative;
                z-index: 1;
            }

            .reward-subtitle {
                font-size: 14px;
                opacity: 0.9;
                margin-top: 15px;
                color: #a0a0ff;
                position: relative;
                z-index: 1;
            }

            .countdown-timer {
                font-size: 20px;
                font-weight: bold;
                color: #00ffff;
                margin-top: 20px;
                text-shadow: 0 0 15px cyan;
                animation: countdownPulse 1s infinite;
                position: relative;
                z-index: 1;
            }

            @keyframes countdownPulse {
                0%, 100% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.1); opacity: 0.8; }
            }

            /* Sparkle Effects - More Subtle */
            .sparkle {
                position: absolute;
                width: 3px;
                height: 3px;
                background: #ff3366;
                border-radius: 50%;
                box-shadow: 0 0 8px #ff3366;
                animation: sparkleFloat 2.5s infinite;
                z-index: 0;
            }

            @keyframes sparkleFloat {
                0% { transform: translateY(0) scale(0); opacity: 0; }
                50% { opacity: 1; }
                100% { transform: translateY(-150px) scale(1); opacity: 0; }
            }

            /* Compact SweetAlert Styles */
            .swal2-popup {
                max-width: 380px !important;
                width: 90% !important;
                border-radius: 16px !important;
                padding: 20px !important;
            }

            .swal2-title {
                font-size: 22px !important;
                padding: 0 1em !important;
            }

            .swal2-html-container {
                font-size: 14px !important;
                margin: 1em 0 !important;
            }

            .swal2-confirm {
                padding: 10px 25px !important;
                font-size: 14px !important;
                border-radius: 8px !important;
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
                    padding: 25px 20px;
                    max-width: 320px;
                    width: 80%;
                }

                .reward-icon {
                    font-size: 40px;
                }

                .reward-title {
                    font-size: 16px;
                    padding: 0.3em 1.2em;
                }

                .reward-multiplier {
                    font-size: 44px;
                }

                .reward-amount {
                    font-size: 20px;
                }

                .countdown-timer {
                    font-size: 18px;
                }

                /* Even more compact SweetAlert on mobile */
                .swal2-popup {
                    max-width: 320px !important;
                    width: 85% !important;
                    padding: 18px !important;
                }

                .swal2-title {
                    font-size: 20px !important;
                }

                .swal2-html-container {
                    font-size: 13px !important;
                }
            }

            @media (max-width: 480px) {
                .reward-preview-content {
                    padding: 20px 15px;
                    max-width: 280px;
                }

                .reward-icon {
                    font-size: 35px;
                }

                .reward-title {
                    font-size: 14px;
                }

                .reward-multiplier {
                    font-size: 38px;
                }

                .reward-amount {
                    font-size: 18px;
                }

                .reward-subtitle {
                    font-size: 12px;
                }

                .countdown-timer {
                    font-size: 16px;
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
        <!-- Enhanced Reward Preview Overlay -->
        <div class="reward-preview-overlay" id="rewardPreviewOverlay">
            <div class="reward-preview-content">
                <!-- Subtle sparkle effects -->
                <div class="sparkle" style="top: 15%; left: 25%; animation-delay: 0s;"></div>
                <div class="sparkle" style="top: 25%; left: 75%; animation-delay: 0.4s;"></div>
                <div class="sparkle" style="top: 75%; left: 20%; animation-delay: 0.8s;"></div>
                <div class="sparkle" style="top: 65%; left: 80%; animation-delay: 1.2s;"></div>

                <div class="reward-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="reward-title">POTENTIAL WIN</div>
                <div class="reward-multiplier" id="previewMultiplier">?x</div>
                <div class="reward-amount" id="previewAmount">? Credits</div>
                <p class="reward-subtitle">Spin to reveal your prize!</p>
                <div class="countdown-timer" id="countdownTimer">3</div>
            </div>
        </div>

        <!-- Sound Toggle Button -->
        {{-- <button class="sound-toggle" id="soundToggle">
            <i class="fas fa-volume-up"></i>
        </button> --}}

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="game-card p-4 mt-4">
                        <!-- Wheel -->
                        <div class="wheel-container mb-4">
                            <div class="wheel" id="wheel">
                                <div class="segment-label label-1">MISSED</div>
                                <div class="segment-label label-2">LUCKY SHOT</div>
                                <div class="segment-label label-3">MISSED</div>
                                <div class="segment-label label-4">LUCKY SHOT</div>
                                <div class="segment-label label-5">MISSED</div>
                                <div class="segment-label label-6">LUCKY SHOT</div>
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
                                    <small class="text-muted">Min: {{$minAmaunt}} - Max: {{$maxAmaunt}}</small>
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
                                    type="button"
                                    class="spin-btn">
                                <span class="spin-text">
                                    <i class="fas fa-play me-2"></i>SPIN NOW
                                </span>
                                <span class="spinning-text" style="display: none;">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    SPINNING...
                                </span>
                            </button>
                        </div>

                        <div class="credit-display text-center mb-4" id="creditDisplay">
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

            // Enhanced Reward Preview Manager
            class RewardPreviewManager {
                constructor() {
                    this.overlay = document.getElementById('rewardPreviewOverlay');
                    this.multiplierEl = document.getElementById('previewMultiplier');
                    this.amountEl = document.getElementById('previewAmount');
                    this.countdownEl = document.getElementById('countdownTimer');
                    this.isActive = false;
                    this.countdownInterval = null;
                }

                async show(betAmount) {
                    if (this.isActive) return;
                    this.isActive = true;

                    // Get preview result from backend
                    const previewData = await @this.call('previewResult');
                    console.log('Preview data:', previewData);

                    const displayMultiplier = previewData.multiplier;
                    const potentialWin = betAmount * displayMultiplier;

                    // Update display
                    this.multiplierEl.textContent = displayMultiplier + 'x';
                    this.amountEl.textContent = potentialWin.toLocaleString() + ' Credits';

                    // Show overlay with smooth animation
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
                            }, 500);
                        }
                    }, 1000);
                }

                triggerSpin() {
                    // Trigger Livewire spin method
                    console.log('Triggering actual spin...');

                    // Show loading state
                    const spinButton = document.getElementById('spinButton');
                    if (spinButton) {
                        spinButton.disabled = true;
                        spinButton.querySelector('.spin-text').style.display = 'none';
                        spinButton.querySelector('.spinning-text').style.display = 'inline';
                    }

                    @this.call('spin').then(() => {
                        console.log('Spin completed');
                        // Reset button state
                        if (spinButton) {
                            spinButton.disabled = false;
                            spinButton.querySelector('.spin-text').style.display = 'inline';
                            spinButton.querySelector('.spinning-text').style.display = 'none';
                        }
                    }).catch((error) => {
                        console.error('Spin error:', error);
                        // Reset button state on error
                        if (spinButton) {
                            spinButton.disabled = false;
                            spinButton.querySelector('.spin-text').style.display = 'inline';
                            spinButton.querySelector('.spinning-text').style.display = 'none';
                        }
                    });
                }
            }

            const rewardPreview = new RewardPreviewManager();

            // Intercept spin button click
            document.addEventListener('DOMContentLoaded', () => {
                const spinButton = document.getElementById('spinButton');
                if (spinButton) {
                    spinButton.addEventListener('click', async function(e) {
                        e.preventDefault();

                        // Check if not loading
                        const isLoading = this.hasAttribute('disabled');
                        if (isLoading) return;

                        const betAmount = parseInt(document.querySelector('input[wire\\:model="betAmount"]').value) || 100;

                        soundManager.play('click');
                        await rewardPreview.show(betAmount);
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

                    // Update credit after spin completes (only if won)
                    if (spinData.reward > 0) {
                        animateCreditUpdate(spinData.initial_credit, spinData.final_credit);
                    }

                    showResultAlert(spinData);
                }, 4500);
            };

            function animateCreditUpdate(startCredit, endCredit) {
                const creditDisplay = document.getElementById('creditDisplay');
                const duration = 1500; // 1.5 seconds
                const startTime = Date.now();
                const difference = endCredit - startCredit;

                // Add pulsing animation class
                if (creditDisplay) {
                    creditDisplay.classList.add('credit-updating');
                }

                function updateCredit() {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / duration, 1);

                    // Easing function for smooth animation
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    const currentCredit = Math.floor(startCredit + (difference * easeOut));

                    // Update the Livewire component
                    @this.set('credit', currentCredit);

                    if (progress < 1) {
                        requestAnimationFrame(updateCredit);
                    } else {
                        // Ensure final value is set
                        @this.set('credit', endCredit);

                        // Remove animation class
                        if (creditDisplay) {
                            setTimeout(() => {
                                creditDisplay.classList.remove('credit-updating');
                            }, 500);
                        }
                    }
                }

                requestAnimationFrame(updateCredit);
            }

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

                // Use actual multiplier from data
                const displayMultiplier = data.multiplier || 0;

                if (data.result === 'jackpot') {
                    title = '🎉 JACKPOT!';
                    html = `<div class="text-center">
                        <h5 class="text-warning fw-bold mb-2">CONGRATULATIONS!</h5>
                        <p class="mb-2">You hit the JACKPOT!</p>
                        <h4 class="text-success fw-bold my-2">${data.reward.toLocaleString()} CREDITS</h4>
                        ${displayMultiplier > 0 ? `<p class="text-muted small">${displayMultiplier.toFixed(1)}x multiplier!</p>` : ''}
                    </div>`;

                    Swal.fire({
                        title: title,
                        html: html,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'SPIN AGAIN',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'compact-swal'
                        }
                    });
                } else if (data.result === 'win') {
                    title = '🎊 YOU WON!';
                    html = `<div class="text-center">
                        <p class="mb-2">Congratulations! You won:</p>
                        <h4 class="text-success fw-bold my-2">${data.reward.toLocaleString()} CREDITS</h4>
                        <p class="text-info fw-bold small">Multiplier: ${displayMultiplier}x</p>
                    </div>`;

                    Swal.fire({
                        title: title,
                        html: html,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'SPIN AGAIN',
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'compact-swal'
                        }
                    });
                }
            }

            // Play click sound for buttons
            document.addEventListener('click', function(e) {
                const button = e.target.closest('button');
                if (button && button.id !== 'soundToggle' && button.id !== 'spinButton') {
                    soundManager.play('click');
                }
            });

            console.log('Lucky Spin Game JavaScript loaded successfully');
            console.log('Enhanced Reward Preview System ready!');
        </script>
    @endsection
</div>

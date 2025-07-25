<div>
    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.95); z-index: 9999;" wire:ignore.self>
            <div class="modal-dialog modal-fullscreen-sm-down modal-lg modal-dialog-centered">
                <div class="modal-content bg-dark text-white" style="min-height: 100vh;">
                    <!-- Header -->
                    <div class="modal-header bg-gradient-primary border-0 p-2">
                        <div class="w-100 text-center">
                            <h5 class="modal-title mb-1">
                                🎰 {{ $currentLottery->name ?? 'Live Draw' }} 🎰
                            </h5>
                            @if($isDrawing && !$drawComplete)
                                @php $timerInfo = $this->getDynamicTimerInfo() @endphp
                                <div class="draw-progress-info">
                                    <small class="text-light d-block">
                                        Prize {{ $currentPrizeIndex + 1 }} of {{ $timerInfo['prize_count'] }}
                                        | Auto-complete in: {{ $timerInfo['remaining_time'] }}
                                    </small>
                                    <div class="progress mt-1" style="height: 3px;">
                                        <div class="progress-bar bg-warning"
                                             style="width: {{ $timerInfo['progress_percentage'] }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-body p-2" style="overflow-y: auto; max-height: calc(100vh - 120px);">
                        @if($errorMessage)
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $errorMessage }}
                                <button type="button" class="close" wire:click="$set('errorMessage', '')" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if($isDrawing && !$drawComplete)
                            <div class="draw-stage text-center">
                                @if($currentPrizeIndex < count($centralDrawResults))
                                    @php $currentResult = $centralDrawResults[$currentPrizeIndex] @endphp

                                    <!-- Prize Info - Mobile Optimized -->
                                    <div class="prize-info mb-3 p-2 bg-gradient-warning rounded">
                                        <h4 class="mb-1 text-dark">{{ $currentResult['prize_position'] }}</h4>
                                        <h5 class="mb-1 text-success">৳{{ number_format($currentResult['prize_amount'], 0) }}</h5>
                                        <small class="text-dark">Prize {{ $currentPrizeIndex + 1 }} of {{ count($centralDrawResults) }}</small>
                                    </div>

                                    <!-- Mobile Analog Meter -->
                                    <div class="mobile-meter-container mb-3">
                                        <div class="mobile-meter-frame">
                                            <div class="mobile-digit-display" id="digitDisplay">
                                                @for($i = 0; $i < 8; $i++)
                                                    <div class="mobile-digit-wheel" data-digit="{{ $i }}" id="wheel-{{ $i }}">
                                                        <div class="mobile-digit-roller" id="roller-{{ $i }}">
                                                            @for($j = 0; $j <= 9; $j++)
                                                                <span class="mobile-digit-number">{{ $j }}</span>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                            <div class="mobile-meter-glow"></div>
                                        </div>

                                        <!-- Result Display -->
                                        @if($showCurrentResult && $currentWinningNumber)
                                            <div class="mobile-result-display mt-3 animate__animated animate__bounceIn">
                                                <h5 class="text-success mb-2">🎉 Winning Number 🎉</h5>
                                                <div class="mobile-winning-number mb-2">{{ $currentWinningNumber }}</div>

                                                <div class="winner-info p-2 bg-info rounded">
                                                    <small class="d-block text-white">Winner: {{ $currentResult['winner_name'] }}</small>
                                                    <small class="d-block text-warning">৳{{ number_format($currentResult['prize_amount'], 0) }}</small>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Mobile Countdown -->
                                        @if($isCountingDown && $currentPrizeIndex < count($centralDrawResults) - 1)
                                            <div class="mobile-countdown mt-3">
                                                <div class="mobile-countdown-circle">
                                                    <div class="countdown-number">{{ $countdown }}</div>
                                                </div>
                                                <p class="text-info mt-2 mb-0">Next prize...</p>
                                            </div>
                                        @endif

                                        <!-- Final Prize Message -->
                                        @if($showCurrentResult && $currentPrizeIndex >= count($centralDrawResults) - 1)
                                            <div class="final-message mt-3 p-2 bg-success rounded">
                                                <h6 class="text-white mb-1">🏆 All Prizes Drawn! 🏆</h6>
                                                <small class="text-light">Completing draw...</small>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($drawComplete)
                            <div class="final-results text-center">
                                <h4 class="text-success mb-3">🏆 Draw Complete! 🏆</h4>

                                <!-- Mobile Results Summary -->
                                <div class="mobile-results-summary">
                                    @foreach(collect($centralDrawResults)->reverse() as $index => $result)
                                        <div class="mobile-result-card mb-2 p-2 bg-gradient-info rounded">
                                            <div class="row align-items-center text-center">
                                                <div class="col-3">
                                                    <small class="d-block font-weight-bold">{{ $result['prize_position'] }}</small>
                                                </div>
                                                <div class="col-3">
                                                    <small class="mobile-ticket-number">{{ $result['winning_ticket_number'] }}</small>
                                                </div>
                                                <div class="col-3">
                                                    <small class="text-warning font-weight-bold">৳{{ number_format($result['prize_amount'], 0) }}</small>
                                                </div>
                                                <div class="col-3">
                                                    <small class="text-truncate">{{ $result['winner_name'] }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Mobile Draw Summary -->
                                <div class="mobile-draw-summary mt-3 p-2 bg-dark rounded">
                                    <h6 class="text-info mb-1">Draw Summary</h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <small class="d-block">Total Prizes</small>
                                            <small class="text-warning">{{ count($centralDrawResults) }}</small>
                                        </div>
                                        <div class="col-6">
                                            <small class="d-block">Total Amount</small>
                                            <small class="text-success">৳{{ number_format(collect($centralDrawResults)->sum('prize_amount'), 0) }}</small>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-success btn-block mt-3" wire:click="closeModal">
                                    Close
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Audio Elements -->
    <audio id="drawStartSound" preload="auto">
        <source src="https://www.soundjay.com/misc/sounds/bell-ringing-05.wav" type="audio/wav">
    </audio>
    <audio id="spinningSound" preload="auto" loop>
        <source src="https://www.soundjay.com/misc/sounds/slot-machine-01.wav" type="audio/wav">
    </audio>
    <audio id="winnerSound" preload="auto">
        <source src="https://www.soundjay.com/misc/sounds/success-fanfare-trumpets-01.wav" type="audio/wav">
    </audio>
    <audio id="completeSound" preload="auto">
        <source src="https://www.soundjay.com/misc/sounds/victory-fanfare-01.wav" type="audio/wav">
    </audio>


<style>
/* Mobile-First Design */
.mobile-meter-container {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    border-radius: 15px;
    padding: 15px;
    margin: 10px auto;
    max-width: 100%;
    box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);
}

.mobile-meter-frame {
    position: relative;
    background: #000;
    border: 2px solid #00ffff;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
}

.mobile-digit-display {
    display: flex;
    justify-content: center;
    gap: 3px;
    flex-wrap: wrap;
}

.mobile-digit-wheel {
    width: 35px;
    height: 50px;
    background: linear-gradient(145deg, #0a0a0a, #1a1a1a);
    border: 1px solid #00ff41;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 0 10px rgba(0, 255, 65, 0.4);
}

.mobile-digit-roller {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 500px; /* 10 digits × 50px each */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.mobile-digit-number {
    width: 100%;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    color: #00ff41;
    text-shadow: 0 0 5px #00ff41;
    line-height: 1;
}

.mobile-digit-wheel.spinning .mobile-digit-roller {
    animation: mobileDigitSpin 0.1s linear infinite;
}

@keyframes mobileDigitSpin {
    0% { transform: translateY(0); }
    100% { transform: translateY(-50px); }
}

.mobile-meter-glow {
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    background: linear-gradient(45deg, transparent, rgba(0, 255, 255, 0.1), transparent);
    border-radius: 15px;
    animation: mobileGlowPulse 2s ease-in-out infinite;
}

@keyframes mobileGlowPulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.8; }
}

.mobile-winning-number {
    font-size: 2rem;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    color: #ffd700;
    text-shadow: 0 0 10px #ffd700;
    letter-spacing: 3px;
    word-break: break-all;
}

.mobile-countdown {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.mobile-countdown-circle {
    width: 60px;
    height: 60px;
    border: 3px solid #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 123, 255, 0.2);
    animation: mobilePulse 1s ease-in-out infinite;
}

@keyframes mobilePulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.countdown-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.mobile-result-card {
    border-left: 3px solid #ffd700;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05)) !important;
}

.mobile-ticket-number {
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
    font-weight: bold;
    color: #ffd700;
    word-break: break-all;
}

.prize-info {
    border: 2px solid rgba(255, 193, 7, 0.5);
}

.winner-info {
    border: 1px solid rgba(23, 162, 184, 0.3);
}

.final-message {
    border: 2px solid rgba(40, 167, 69, 0.5);
}

.mobile-draw-summary {
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 0;
        max-width: 100%;
        height: 100vh;
    }

    .modal-content {
        height: 100vh;
        border-radius: 0;
    }

    .mobile-digit-wheel {
        width: 30px;
        height: 45px;
    }

    .mobile-digit-number {
        height: 45px;
        font-size: 1.2rem;
    }

    .mobile-digit-roller {
        height: 450px; /* 10 digits × 45px each */
    }

    .mobile-winning-number {
        font-size: 1.5rem;
        letter-spacing: 2px;
    }
}

@media (max-width: 400px) {
    .mobile-digit-wheel {
        width: 28px;
        height: 40px;
    }

    .mobile-digit-number {
        height: 40px;
        font-size: 1rem;
    }

    .mobile-digit-roller {
        height: 400px; /* 10 digits × 40px each */
    }

    .mobile-winning-number {
        font-size: 1.3rem;
        letter-spacing: 1px;
    }
}
</style>

<script>
document.addEventListener('livewire:initialized', function () {
    let animationIntervals = [];
    let countdownInterval;
    let currentWinningNumber = '';
    let audioElements = {};
    let autoCompleteInterval;

    // Initialize audio elements
    audioElements.drawStart = document.getElementById('drawStartSound');
    audioElements.spinning = document.getElementById('spinningSound');
    audioElements.winner = document.getElementById('winnerSound');
    audioElements.complete = document.getElementById('completeSound');

    // Sound control functions
    function playSound(type) {
        if (audioElements[type]) {
            audioElements[type].currentTime = 0;
            audioElements[type].play().catch(e => console.log('Audio play failed:', e));
        }
    }

    function stopSound(type) {
        if (audioElements[type]) {
            audioElements[type].pause();
            audioElements[type].currentTime = 0;
        }
    }

    function stopAllSounds() {
        Object.keys(audioElements).forEach(type => {
            stopSound(type);
        });
    }

    // Livewire event listeners
    Livewire.on('playSound', function(data) {
        playSound(data[0].type);
    });

    Livewire.on('stopAllSounds', function() {
        stopAllSounds();
    });

    Livewire.on('animateDigits', function(data) {
        currentWinningNumber = data[0].winningNumber;
        startMobileDigitAnimation();

        // Show result after 8 seconds
        setTimeout(() => {
            @this.call('showResult');
        }, 8000);
    });

    Livewire.on('stopAnimation', function() {
        stopMobileDigitAnimation();
        stopSound('spinning');
        showMobileFinalNumber(currentWinningNumber);
    });

    Livewire.on('startCountdown', function() {
        startCountdownTimer();
    });

    Livewire.on('scheduleDrawCompletion', function() {
        setTimeout(() => {
            @this.call('completeDraw');
        }, 3000);
    });

    function startMobileDigitAnimation() {
        clearAllIntervals();

        const wheels = document.querySelectorAll('.mobile-digit-wheel');

        wheels.forEach((wheel, wheelIndex) => {
            wheel.classList.add('spinning');
            const roller = wheel.querySelector('.mobile-digit-roller');

            let currentPosition = 0;
            const interval = setInterval(() => {
                currentPosition -= 50; // Mobile digit height
                if (currentPosition <= -500) { // Reset after 10 digits
                    currentPosition = 0;
                }
                roller.style.transform = `translateY(${currentPosition}px)`;
            }, 100);

            animationIntervals.push(interval);
        });
    }

    function stopMobileDigitAnimation() {
        const wheels = document.querySelectorAll('.mobile-digit-wheel');
        wheels.forEach(wheel => {
            wheel.classList.remove('spinning');
        });

        clearAllIntervals();
    }

    function showMobileFinalNumber(ticketNumber) {
        if (!ticketNumber || ticketNumber.length !== 8) {
            console.error('Invalid ticket number:', ticketNumber);
            return;
        }

        const wheels = document.querySelectorAll('.mobile-digit-wheel');
        const digits = ticketNumber.split('');

        wheels.forEach((wheel, index) => {
            const roller = wheel.querySelector('.mobile-digit-roller');
            const targetDigit = parseInt(digits[index]);

            // Calculate position for mobile
            const targetPosition = -(targetDigit * 50); // Mobile digit height

            setTimeout(() => {
                roller.style.transition = 'transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                roller.style.transform = `translateY(${targetPosition}px)`;

                setTimeout(() => {
                    const digitNumbers = roller.querySelectorAll('.mobile-digit-number');
                    digitNumbers[targetDigit].style.color = '#ffd700';
                    digitNumbers[targetDigit].style.textShadow = '0 0 10px #ffd700';
                }, 800);
            }, index * 200);
        });
    }

    function startCountdownTimer() {
        countdownInterval = setInterval(() => {
            @this.call('decrementCountdown');
        }, 1000);
    }

    function clearAllIntervals() {
        animationIntervals.forEach(interval => clearInterval(interval));
        animationIntervals = [];

        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }

        if (autoCompleteInterval) {
            clearInterval(autoCompleteInterval);
            autoCompleteInterval = null;
        }
    }

    // Cleanup
    document.addEventListener('livewire:navigating', function() {
        clearAllIntervals();
        stopAllSounds();
    });

    window.addEventListener('beforeunload', function() {
        clearAllIntervals();
        stopAllSounds();
    });

    Livewire.on('startAutoCompleteCountdown', function(data) {
        const duration = data[0].duration || 300; // Default 5 minutes
        console.log('Starting auto-complete countdown for', duration, 'seconds');

        autoCompleteInterval = setInterval(() => {
            @this.call('decrementAutoCompleteTimer');
        }, 1000);
    });
});
</script>
</div>

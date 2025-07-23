<div>
    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.9); z-index: 9999;" wire:ignore.self>
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header bg-gradient-primary border-0">
                        <h3 class="modal-title text-center w-100">
                            🎰 {{ $currentLottery->name }} - Live Draw 🎰
                        </h3>
                    </div>

                    <div class="modal-body p-4">
                        @if($isDrawing && !$drawComplete)
                            <div class="draw-stage text-center">
                                @if($currentPrizeIndex < count($currentLottery->prizes ?? []))
                                    @php $currentPrize = $currentLottery->prizes[$currentPrizeIndex] @endphp

                                    <div class="prize-announcement mb-4">
                                        <h2 class="text-warning mb-2">{{ $currentPrize->position }} Prize</h2>
                                        <h3 class="text-success">৳{{ number_format($currentPrize->amount, 2) }}</h3>
                                    </div>

                                    <!-- Analog Meter Display -->
                                    <div class="analog-meter-container">
                                        <div class="meter-frame">
                                            <div class="digit-display" id="digitDisplay">
                                                @for($i = 0; $i < 8; $i++)
                                                    <div class="digit-wheel" data-digit="{{ $i }}" id="wheel-{{ $i }}">
                                                        <div class="digit-roller" id="roller-{{ $i }}">
                                                            @for($j = 0; $j <= 9; $j++)
                                                                <span class="digit-number">{{ $j }}</span>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>

                                            <div class="meter-glow"></div>
                                            <div class="scanning-line"></div>
                                        </div>

                                        <!-- Result Display (Hidden until animation completes) -->
                                        @if($showCurrentResult && $currentWinningNumber)
                                            <div class="result-display mt-4 animate__animated animate__bounceIn">
                                                <h2 class="text-success mb-3">🎉 Winning Number 🎉</h2>
                                                <div class="winning-number">{{ $currentWinningNumber }}</div>

                                                @if($currentPrizeIndex < count($currentLottery->prizes) - 1)
                                                    <div class="winner-info mt-3">
                                                        <p class="text-info">Winner: {{ collect($drawResults)->last()['winner_name'] ?? 'Unknown' }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Countdown for next prize -->
                                        @if($isCountingDown && $currentPrizeIndex < count($currentLottery->prizes) - 1)
                                            <div class="countdown-container mt-4">
                                                <div class="countdown-circle">
                                                    <div class="countdown-number">{{ $countdown }}</div>
                                                    <div class="countdown-text">seconds</div>
                                                </div>
                                                <p class="text-info mt-2">Next prize coming up...</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($drawComplete)
                            <div class="final-results text-center">
                                <h2 class="text-success mb-4">🏆 Draw Complete! 🏆</h2>

                                <div class="results-summary">
                                    @foreach(collect($drawResults)->reverse() as $result)
                                        <div class="result-card mb-3 p-3 bg-gradient-info rounded">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <h5 class="mb-0">{{ $result['prize_position'] }}</h5>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="winning-ticket">{{ $result['winning_ticket_number'] }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h6 class="text-warning mb-0">৳{{ number_format($result['prize_amount'], 2) }}</h6>
                                                </div>
                                                <div class="col-md-3">
                                                    <small>{{ $result['winner_name'] }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button class="btn btn-success btn-lg mt-4" wire:click="closeModal">
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
        <source src="{{ asset('sounds/lottery/drawStart.mp3') }}" type="audio/mpeg">
        {{-- <source src="https://www.soundjay.com/misc/sounds/bell-ringing-05.wav" type="audio/wav"> --}}
    </audio>
    <audio id="spinningSound" preload="auto" loop>
        {{-- <source src="https://www.soundjay.com/misc/sounds/slot-machine-01.wav" type="audio/wav"> --}}
            <source src="{{ asset('sounds/lottery/spinning.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="winnerSound" preload="auto">
        <source src="{{ asset('sounds/lottery/winner.mp3') }}" type="audio/mpeg">
        {{-- <source src="https://www.soundjay.com/misc/sounds/success-fanfare-trumpets-01.wav" type="audio/wav"> --}}
    </audio>
    <audio id="completeSound" preload="auto">
        <source src="{{ asset('sounds/lottery/complete.mp3') }}" type="audio/mpeg">
        {{-- <source src="https://www.soundjay.com/misc/sounds/victory-fanfare-01.wav" type="audio/wav"> --}}
    </audio>


<style>
.analog-meter-container {
    background: radial-gradient(circle, #1a1a2e, #16213e);
    border-radius: 20px;
    padding: 40px;
    margin: 20px auto;
    max-width: 800px;
    position: relative;
    box-shadow:
        0 0 50px rgba(0, 255, 255, 0.3),
        inset 0 0 50px rgba(0, 0, 0, 0.5);
}

.meter-frame {
    position: relative;
    background: #000;
    border: 3px solid #00ffff;
    border-radius: 15px;
    padding: 30px;
    box-shadow:
        0 0 30px rgba(0, 255, 255, 0.5),
        inset 0 0 30px rgba(0, 255, 255, 0.1);
}

.digit-display {
    display: flex;
    justify-content: center;
    gap: 8px;
    position: relative;
    z-index: 2;
}

.digit-wheel {
    width: 70px;
    height: 100px;
    background: linear-gradient(145deg, #0a0a0a, #1a1a1a);
    border: 2px solid #00ff41;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    box-shadow:
        0 0 20px rgba(0, 255, 65, 0.4),
        inset 0 0 20px rgba(0, 0, 0, 0.8);
}

.digit-roller {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 1000px; /* 10 digits × 100px each */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.digit-number {
    width: 100%;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    color: #00ff41;
    text-shadow:
        0 0 10px #00ff41,
        0 0 20px #00ff41,
        0 0 30px #00ff41;
    line-height: 1;
}

.digit-wheel.spinning .digit-roller {
    animation: digitSpin 0.1s linear infinite;
}

@keyframes digitSpin {
    0% { transform: translateY(0); }
    100% { transform: translateY(-100px); }
}

.meter-glow {
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: linear-gradient(45deg, transparent, rgba(0, 255, 255, 0.1), transparent);
    border-radius: 20px;
    animation: glowPulse 2s ease-in-out infinite;
}

@keyframes glowPulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.8; }
}

.scanning-line {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0, 255, 255, 0.6), transparent);
    animation: scanLine 3s linear infinite;
}

@keyframes scanLine {
    0% { left: -100%; }
    100% { left: 100%; }
}

.winning-number {
    font-size: 4rem;
    font-weight: bold;
    font-family: 'Courier New', monospace;
    color: #ffd700;
    text-shadow:
        0 0 20px #ffd700,
        0 0 40px #ffd700;
    letter-spacing: 8px;
    background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57);
    background-size: 300% 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: gradientShift 2s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.countdown-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.countdown-circle {
    width: 120px;
    height: 120px;
    border: 4px solid #007bff;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 123, 255, 0.3));
    box-shadow: 0 0 30px rgba(0, 123, 255, 0.5);
    animation: pulse 1s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.countdown-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #007bff;
    text-shadow: 0 0 10px rgba(0, 123, 255, 0.8);
}

.countdown-text {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: -5px;
}

.result-card {
    border-left: 5px solid #ffd700;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05)) !important;
    backdrop-filter: blur(10px);
}

.winning-ticket {
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    font-weight: bold;
    color: #ffd700;
}

.prize-announcement {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(40, 167, 69, 0.2));
    border-radius: 15px;
    padding: 20px;
    border: 2px solid rgba(255, 193, 7, 0.5);
}

.winner-info {
    background: rgba(23, 162, 184, 0.1);
    border-radius: 10px;
    padding: 10px;
    border: 1px solid rgba(23, 162, 184, 0.3);
}
</style>

<script>
document.addEventListener('livewire:initialized', function () {
    let animationIntervals = [];
    let countdownInterval;
    let currentWinningNumber = '';
    let audioElements = {};

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
        startDigitAnimation();

        // Show result after 8 seconds
        setTimeout(() => {
            @this.call('showResult');
        }, 8000);
    });

    Livewire.on('stopAnimation', function() {
        stopDigitAnimation();
        stopSound('spinning');
        showFinalNumber(currentWinningNumber);
    });

    Livewire.on('startCountdown', function() {
        startCountdownTimer();
    });

    function startDigitAnimation() {
        // Clear any existing intervals
        clearAllIntervals();

        const wheels = document.querySelectorAll('.digit-wheel');

        wheels.forEach((wheel, wheelIndex) => {
            wheel.classList.add('spinning');
            const roller = wheel.querySelector('.digit-roller');

            // Create spinning effect by continuously moving the roller
            let currentPosition = 0;
            const interval = setInterval(() => {
                currentPosition -= 100; // Move up by one digit height
                if (currentPosition <= -1000) { // Reset after 10 digits
                    currentPosition = 0;
                }
                roller.style.transform = `translateY(${currentPosition}px)`;
            }, 100); // Change digit every 100ms

            animationIntervals.push(interval);
        });
    }

    function stopDigitAnimation() {
        const wheels = document.querySelectorAll('.digit-wheel');
        wheels.forEach(wheel => {
            wheel.classList.remove('spinning');
        });

        clearAllIntervals();
    }

    function showFinalNumber(ticketNumber) {
        if (!ticketNumber || ticketNumber.length !== 8) {
            console.error('Invalid ticket number:', ticketNumber);
            return;
        }

        const wheels = document.querySelectorAll('.digit-wheel');
        const digits = ticketNumber.split('');

        wheels.forEach((wheel, index) => {
            const roller = wheel.querySelector('.digit-roller');
            const targetDigit = parseInt(digits[index]);

            // Calculate the position to show the target digit
            const targetPosition = -(targetDigit * 100);

            // Animate to the final position with a stagger effect
            setTimeout(() => {
                roller.style.transition = 'transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                roller.style.transform = `translateY(${targetPosition}px)`;

                // Add golden glow effect
                setTimeout(() => {
                    const digitNumbers = roller.querySelectorAll('.digit-number');
                    digitNumbers[targetDigit].style.color = '#ffd700';
                    digitNumbers[targetDigit].style.textShadow = '0 0 20px #ffd700, 0 0 40px #ffd700';
                }, 800);
            }, index * 200); // Stagger the reveal
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
    }

    // Clear intervals when component is destroyed
    document.addEventListener('livewire:navigating', function() {
        clearAllIntervals();
        stopAllSounds();
    });

    // Clear intervals when modal is closed
    window.addEventListener('beforeunload', function() {
        clearAllIntervals();
        stopAllSounds();
    });
});
</script>
</div>

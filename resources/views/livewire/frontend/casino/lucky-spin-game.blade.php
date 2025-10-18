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

            /* Corrected label positions */
            .label-1 { top: 10%; left: 70%; transform: rotate(30deg); }      /* LOSE */
            .label-2 { top: 10%; left: 30%; transform: rotate(-30deg); }     /* WIN */
            .label-3 { top: 50%; left: 10%; transform: rotate(-90deg); }     /* LOSE */
            .label-4 { top: 50%; left: 80%; transform: rotate(90deg); }      /* JACKPOT */
            .label-5 { top: 85%; left: 70%; transform: rotate(150deg); }     /* LOSE */
            .label-6 { top: 85%; left: 30%; transform: rotate(-150deg); }    /* WIN */

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

            .pool-display {
                background: linear-gradient(135deg, #f1c40f, #f39c12);
                color: #2c3e50;
                border-radius: 50px;
                padding: 12px 25px;
                font-weight: bold;
                box-shadow: 0 5px 15px rgba(241, 196, 15, 0.4);
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
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="game-card p-4">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <h1 class="fw-bold text-primary">
                                <i class="fas fa-diamond me-2"></i>LUCKY SPIN
                            </h1>
                            <p class="text-muted">Test your luck and win big!</p>
                        </div>

                        <!-- Jackpot Pool -->
                        <div class="text-center mb-4">
                            <div class="pool-display d-inline-block">
                                <i class="fas fa-crown me-2"></i>
                                <strong>JACKPOT:</strong>
                                <span id="poolAmount">{{ number_format($poolAmount) }}</span>
                                <small class="ms-1">credits</small>
                            </div>
                        </div>

                        <!-- Wheel -->
                        <div class="wheel-container mb-4">
                            <div class="wheel" id="wheel">
                                <!-- Corrected Segment labels with proper positioning -->
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
                            <div class="text-center mb-3">
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
                                    <small class="text-muted">Min: 1 - Max: 10,000</small>
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
                            <button wire:click="spin"
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

                        <!-- User Info -->
                        <div class="credit-display text-center mb-4">
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-2">
                                        <i class="fas fa-wallet me-2"></i>
                                        <strong>YOUR CREDIT</strong>
                                    </div>
                                    <h4 class="fw-bold text-white mb-0">{{ number_format($credit) }}</h4>
                                </div>
                                <div class="col-6">
                                    <div class="mb-2">
                                        <i class="fas fa-coins me-2"></i>
                                        <strong>CURRENT BET</strong>
                                    </div>
                                    <h4 class="fw-bold text-white mb-0">{{ number_format($betAmount) }}</h4>
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
                        @if($result)
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
                                            <p class="mb-0">Keep trying! The jackpot is growing.</p>
                                        @endif
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            </div>
                        @endif
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
            // Listen for Livewire event
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('spin-wheel', (event) => {
                    console.log('Spin wheel event received:', event);
                    const data = event[0]; // Livewire v3 passes data as array
                    startWheelSpin(data.angle, data);
                });
            });

            // Global function to start wheel spin
            window.startWheelSpin = function(angle, spinData) {
                console.log('Starting wheel spin with angle:', angle, 'Data:', spinData);

                const wheel = document.getElementById('wheel');
                if (!wheel) {
                    console.error('Wheel element not found!');
                    return;
                }

                // Reset wheel position
                wheel.style.transition = 'none';
                wheel.style.transform = 'rotate(0deg)';

                // Force reflow
                wheel.offsetHeight;

                // Calculate total rotation (4 full spins + target angle)
                const fullSpins = 4 * 360;
                const targetRotation = fullSpins + (360 - angle);

                console.log('Target rotation:', targetRotation);

                // Apply animation
                setTimeout(() => {
                    wheel.style.transition = 'transform 4s cubic-bezier(0.2, 0.8, 0.3, 1)';
                    wheel.style.transform = `rotate(${targetRotation}deg)`;
                }, 50);

                // Update pool amount with animation
                updatePoolAmount(spinData.pool_after);

                // Show result after animation
                setTimeout(() => {
                    showResultAlert(spinData);
                }, 4200);
            };

            // Update pool amount with animation
            function updatePoolAmount(newAmount) {
                const poolElement = document.getElementById('poolAmount');
                if (poolElement) {
                    const currentAmount = parseInt(poolElement.textContent.replace(/,/g, '')) || 0;
                    animateValue(poolElement, currentAmount, newAmount, 2000);
                }
            }

            // Animate number value
            function animateValue(element, start, end, duration) {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = value.toLocaleString();
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            }

            // Show result alert
            function showResultAlert(data) {
                let title, html, icon;

                if (data.result === 'jackpot') {
                    title = '🎉 JACKPOT! 🎉';
                    icon = 'success';
                    html = `
                        <div class="text-center">
                            <h4 class="text-warning fw-bold">CONGRATULATIONS!</h4>
                            <p>You hit the JACKPOT!</p>
                            <h2 class="text-success fw-bold my-3">${data.reward.toLocaleString()} CREDITS</h2>
                            <p class="text-muted">You are our lucky winner!</p>
                        </div>
                    `;
                } else if (data.result === 'win') {
                    title = '🎊 YOU WON!';
                    icon = 'success';
                    html = `
                        <div class="text-center">
                            <h5 class="text-success fw-bold">Congratulations!</h5>
                            <p>You won:</p>
                            <h3 class="text-success fw-bold my-2">${data.reward.toLocaleString()} CREDITS</h3>
                            <p class="text-muted">Great spin!</p>
                        </div>
                    `;
                } else {
                    title = '😢 TRY AGAIN';
                    icon = 'info';
                    html = `
                        <div class="text-center">
                            <p>Better luck next time!</p>
                            <p class="text-muted">The jackpot is still growing...</p>
                        </div>
                    `;
                }

                Swal.fire({
                    title: title,
                    html: html,
                    icon: icon,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'SPIN AGAIN'
                });
            }

            // Test function to verify wheel works
            function testWheel() {
                const testAngle = 180; // Jackpot segment
                window.startWheelSpin(testAngle, {
                    result: 'jackpot',
                    reward: 50000,
                    pool_before: 50000,
                    pool_after: 0
                });
            }

            console.log('Lucky Spin Game JavaScript loaded successfully');
            console.log('Test function available: testWheel()');
        </script>
    @endsection
</div>

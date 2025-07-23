<div>
    @section('meta_description')
      <meta name="description" content="Housieblitz - Lottery">
    @endsection
    @section('title')
        <title>Housieblitz | Lottery</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
    @endsection

    @section('preloader')
        {{-- <livewire:layout.frontend.preloader /> --}}
    @endsection
    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('pwa_alart')
        <livewire:layout.frontend.pwa_alart />
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    <div class="page-content-wrapper">
        <div>
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h4 class="card-title text-center">{{ $lottery->name }} - Draw results</h4>
                            </div>
                            <div class="card-body">
                                @if($lottery->status === 'completed' && $lottery->results->count() > 0)
                                    <div class="results-container">
                                        <h5 class="text-success mb-4">🏆 Draw done! 🏆</h5>

                                        <div class="row">
                                            @foreach($lottery->results->sortBy('prize.rank') as $result)
                                                <div class="col-md-6 col-lg-4 mb-4">
                                                    <div class="card border-success">
                                                        <div class="card-body text-center">
                                                            <h5 class="card-title text-primary">{{ $result->prize->position }}</h5>
                                                            <div class="winning-number mb-3">{{ $result->winning_ticket_number }}</div>
                                                            <h6 class="text-success">{{ number_format($result->prize_amount, 2) }} Credit</h6>
                                                            <p class="text-muted">{{ $result->user->name }}</p>
                                                            <small class="text-muted">{{ $result->drawn_at->format('d M y- H:i') }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($lottery->status === 'active')
                                    <div class="alert alert-info text-center">
                                        <h5>The draw has not yet taken place</h5>
                                        <p>Date of draw : {{ $lottery->draw_date->format('d M y - H:i') }}</p>
                                    </div>
                                @else
                                    <div class="alert alert-warning text-center">
                                        <h5>This lottery has been canceled.</h5>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($showDrawModal)
                <div class="modal fade show d-block" style="background: rgba(0,0,0,0.8);" wire:ignore.self>
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h4 class="modal-title">{{ $lottery->name }} - লাইভ ড্র</h4>
                            </div>
                            <div class="modal-body text-center">
                                @if($isDrawing && !$drawComplete)
                                    <div class="draw-container">
                                        <h5 class="mb-4">ড্র চলছে...</h5>
                                        <div class="analog-meter">
                                            <div class="digit-display">
                                                @for($i = 0; $i < 8; $i++)
                                                    <div class="digit-wheel" id="digit-{{ $i }}">
                                                        @for($j = 0; $j <= 9; $j++)
                                                            <span class="digit">{{ $j }}</span>
                                                        @endfor
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="spinner-border text-primary mt-3" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                @endif

                                @if($drawComplete && $drawResults)
                                    <div class="results-container">
                                        <h5 class="mb-4 text-success">ড্র সম্পন্ন!</h5>
                                        <div class="results-list">
                                            @foreach($drawResults as $result)
                                                <div class="result-item mb-3 p-3 border rounded">
                                                    <h6 class="text-primary">{{ $result->prize->position }} প্রাইজ</h6>
                                                    <div class="winning-number">
                                                        <strong>বিজয়ী নম্বর: {{ $result->winning_ticket_number }}</strong>
                                                    </div>
                                                    <div class="prize-amount text-success">
                                                        প্রাইজ: ৳{{ number_format($result->prize_amount, 2) }}
                                                    </div>
                                                    <div class="winner-name text-muted">
                                                        বিজয়ী: {{ $result->user->name }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                @if($drawComplete)
                                    <button type="button" class="btn btn-primary" wire:click="closeDrawModal">
                                        বন্ধ করুন
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <style>
            .analog-meter {
                background: #1a1a1a;
                border-radius: 15px;
                padding: 30px;
                margin: 20px auto;
                max-width: 600px;
                box-shadow: inset 0 0 20px rgba(0,255,0,0.3);
            }

            .digit-display {
                display: flex;
                justify-content: center;
                gap: 10px;
            }

            .digit-wheel {
                width: 60px;
                height: 80px;
                background: #000;
                border: 2px solid #00ff00;
                border-radius: 8px;
                overflow: hidden;
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .digit {
                color: #00ff00;
                font-size: 2.5rem;
                font-weight: bold;
                font-family: 'Courier New', monospace;
                text-shadow: 0 0 10px #00ff00;
                line-height: 1;
                position: absolute;
                transition: transform 0.1s ease;
            }

            .digit-wheel.spinning .digit {
                animation: digitSpin 0.1s linear infinite;
            }

            @keyframes digitSpin {
                0% { transform: translateY(-100%); }
                100% { transform: translateY(100%); }
            }

            .result-item {
                background: linear-gradient(135deg, #f8f9fa, #e9ecef);
                border-left: 4px solid #007bff !important;
            }

            .winning-number {
                font-size: 1.5rem;
                font-weight: bold;
                font-family: 'Courier New', monospace;
                color: #dc3545;
                letter-spacing: 2px;
            }

            .prize-amount {
                font-size: 1.2rem;
                font-weight: bold;
            }

            .draw-container {
                min-height: 300px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
        </style>

        <script>
            document.addEventListener('livewire:initialized', function () {
                // Auto-check for draw time every 30 seconds
                setInterval(function() {
                    @this.call('checkDrawTime');
                }, 30000);

                // Simulate analog meter animation when drawing
                Livewire.on('startDrawAnimation', function(results) {
                    simulateAnalogMeterDraw(results);
                });
            });

            function simulateAnalogMeterDraw(results) {
                const digitWheels = document.querySelectorAll('.digit-wheel');

                // Start spinning animation
                digitWheels.forEach(wheel => {
                    wheel.classList.add('spinning');
                    const digits = wheel.querySelectorAll('.digit');

                    let currentDigit = 0;
                    const spinInterval = setInterval(() => {
                        digits.forEach(digit => digit.style.opacity = '0.3');
                        digits[currentDigit].style.opacity = '1';
                        currentDigit = (currentDigit + 1) % 10;
                    }, 100);

                    // Stop spinning after random time (8-10 seconds)
                    setTimeout(() => {
                        clearInterval(spinInterval);
                        wheel.classList.remove('spinning');

                        // Show final number
                        const finalDigit = Math.floor(Math.random() * 10);
                        digits.forEach(digit => digit.style.opacity = '0.3');
                        digits[finalDigit].style.opacity = '1';

                    }, Math.random() * 2000 + 8000); // 8-10 seconds
                });

                // Show results after animation
                setTimeout(() => {
                    @this.set('drawComplete', true);
                }, 12000);
            }
        </script>

    </div>



    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>

    @endsection
</div>

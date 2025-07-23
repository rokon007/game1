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

    <div class="page-content-wrapper py-3">
        <div class="container" style="max-width: 650px; width: 100%; margin: 0 auto; padding: 0 15px;">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Active Lotteries</h2>
                        <span class="badge bg-primary">{{ $lotteries->count() }} Active</span>
                    </div>

                    @if(session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-3">
                        @forelse($lotteries as $lottery)
                            <div class="col-12">
                                <div class="card lottery-card shadow-sm border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">{{ $lottery->name }}</h5>
                                            <span class="badge bg-info">
                                                {{ $lottery->draw_date->diffForHumans() }}
                                            </span>
                                        </div>

                                        <div class="d-flex justify-content-between mb-3">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="fas fa-ticket-alt me-1"></i>
                                                    ৳{{ number_format($lottery->price, 2) }} per ticket
                                                </small>
                                            </div>
                                            <div>
                                                <small class="text-muted">
                                                    <i class="fas fa-chart-line me-1"></i>
                                                    {{ $lottery->getTotalTicketsSold() }} sold
                                                </small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <h6 class="d-flex align-items-center mb-2">
                                                <i class="fas fa-award me-2 text-warning"></i>
                                                Prize Pool
                                            </h6>
                                            <div class="row g-2 prize-tiles">
                                                @foreach($lottery->prizes as $prize)
                                                    <div class="col-4">
                                                        <div class="prize-tile text-center p-2 rounded">
                                                            <div class="prize-position bg-primary text-white rounded-circle mx-auto mb-1">
                                                                {{ $prize->position }}
                                                            </div>
                                                            <div class="prize-amount fw-bold">
                                                                ৳{{ number_format($prize->amount, 2) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <a href="{{ route('lottery.draw', $lottery->id) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-eye me-1"></i> View Results
                                            </a>
                                            <button class="btn btn-primary btn-sm"
                                                    wire:click="selectLottery({{ $lottery->id }})"
                                                    data-bs-toggle="modal" data-bs-target="#purchaseModal">
                                                <i class="fas fa-shopping-cart me-1"></i> Buy Tickets
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info text-center py-4">
                                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                                    <h5>No Active Lotteries</h5>
                                    <p class="mb-0">There are currently no active lotteries available</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Purchase Modal -->
            @if($selectedLottery)
                <div class="modal fade" id="purchaseModal" tabindex="-1" wire:ignore.self>
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $selectedLottery->name }} - Ticket Purchase</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Ticket Price:</span>
                                        <strong>৳{{ number_format($selectedLottery->price, 2) }}</strong>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Number of Tickets:</label>
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" wire:click="decrementQuantity" type="button">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" wire:model="ticketQuantity"
                                            min="1" max="10" wire:change="calculateTotal">
                                        <button class="btn btn-outline-secondary" wire:click="incrementQuantity" type="button">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3 p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Cost:</span>
                                        <strong class="h5">৳{{ number_format($selectedLottery->price * $ticketQuantity, 2) }}</strong>
                                    </div>
                                </div>

                                @auth
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Your Credit:</span>
                                            <strong class="{{ auth()->user()->credit < ($selectedLottery->price * $ticketQuantity) ? 'text-danger' : 'text-success' }}">
                                                ৳{{ number_format(auth()->user()->credit, 2) }}
                                            </strong>
                                        </div>
                                    </div>
                                @endauth
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" wire:click="purchaseTickets">
                                    <i class="fas fa-shopping-cart me-1"></i> Purchase
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Live Draw Modal Component -->
            <livewire:frontend.lottery.live-draw-modal />
        </div>

        <style>
            @media (max-width: 576px) {
                .container {
                    max-width: 380px !important;
                    padding-left: 15px;
                    padding-right: 15px;
                }
            }

            .lottery-card {
                transition: all 0.3s ease;
                border-radius: 10px;
                overflow: hidden;
                border: none;
            }

            .lottery-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }

            .prize-tiles {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }

            .prize-tile {
                background-color: #f8f9fa;
                transition: all 0.2s;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .prize-tile:hover {
                background-color: #e9ecef;
                transform: scale(1.05);
            }

            .prize-position {
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                font-weight: bold;
            }

            .prize-amount {
                font-size: 13px;
                color: #28a745;
            }

            .modal-content {
                border-radius: 12px;
            }

            .input-group button {
                width: 40px;
            }

            .input-group input {
                max-width: 60px;
                text-align: center;
            }
        </style>

        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('updateTotal', (total) => {
                    console.log('Total updated:', total);
                });
            });

            Echo.channel('lottery-channel')
                .listen('DrawStarted', (e) => {
                    Swal.fire({
                        title: 'Draw Started!',
                        text: e.lottery_name + ' draw has begun!',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });

                    Livewire.dispatch('startLiveDraw', e.lottery_id);
                });
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

<div>
    @section('meta_description')
      <meta name="description" content="Housieblitz - Buy Ticket Sheets">
    @endsection
    @section('title')
        <title>Housieblitz | Buy Ticket</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            .game-card {
                transition: all 0.3s ease;
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid rgba(0, 0, 0, 0.08);
                margin-bottom: 1.8rem;
                cursor: pointer;
                background: white;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .game-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
            }

            .game-card.active {
                border-color: #4a90e2;
                box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.25);
            }

            .game-header {
                background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);
                color: white;
                padding: 1.2rem 1.8rem;
            }

            .prize-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 16px;
                padding: 24px;
            }

            .prize-card {
                background: #ffffff;
                border-radius: 10px;
                padding: 18px 12px;
                text-align: center;
                box-shadow: 0 3px 8px rgba(0, 0, 0, 0.03);
                border: 1px solid #f0f2f8;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .prize-card:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 4px;
                background: linear-gradient(90deg, #4a90e2, #5e60ce);
            }

            .prize-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            }

            .prize-label {
                font-size: 0.9rem;
                color: #6c757d;
                margin-bottom: 8px;
                font-weight: 500;
            }

            .prize-amount {
                font-size: 1.25rem;
                font-weight: 700;
                color: #2c3e50;
                letter-spacing: -0.5px;
            }

            .ticket-price-tag {
                position: absolute;
                top: 18px;
                right: 18px;
                background: #ffffff;
                color: #4a90e2;
                padding: 6px 18px;
                border-radius: 30px;
                font-weight: 700;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border: 1px solid #eaeff9;
            }

            .ticket-price-tag2 {
                background: #2c3e50;
                color: white;
                padding: 6px 18px;
                border-radius: 30px;
                font-weight: 700;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                border: none;
            }

            .ticket-price-tag3 {
                background: #e74c3c;
                color: white;
                padding: 6px 18px;
                border-radius: 30px;
                font-weight: 700;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                border: none;
            }

            .balance-card {
                background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
                color: white;
                border-radius: 14px;
                overflow: hidden;
                margin-bottom: 2rem;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            }

            .btn-buy {
                background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
                border: none;
                border-radius: 10px;
                font-weight: 600;
                padding: 14px 28px;
                transition: all 0.3s ease;
                letter-spacing: 0.5px;
                font-size: 1.05rem;
                box-shadow: 0 4px 12px rgba(39, 174, 96, 0.25);
            }

            .btn-buy:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 20px rgba(39, 174, 96, 0.35);
            }

            .terms-checkbox label {
                cursor: pointer;
                color: #4a5568;
                font-size: 0.95rem;
            }

            .game-time-badge {
                background: rgba(255, 255, 255, 0.2);
                border-radius: 20px;
                padding: 5px 14px;
                font-size: 0.9rem;
            }

            .empty-state {
                text-align: center;
                padding: 3.5rem 2rem;
                color: #a0aec0;
                background: #f9fafc;
                border-radius: 14px;
                margin-top: 1.5rem;
            }

            .empty-state i {
                font-size: 4.5rem;
                margin-bottom: 1.5rem;
                color: #e2e8f0;
            }

            .section-title {
                font-size: 1rem;
                font-weight: 700;
                color: #2c3e50;
                /* margin-bottom: 1.8rem; */
                position: relative;
                padding-bottom: 0.8rem;
            }

            .section-title:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 130px;
                height: 4px;
                background: linear-gradient(90deg, #4a90e2, #5e60ce);
                border-radius: 4px;
            }

            .ticket-badge {
                position: absolute;
                top: -10px;
                right: -10px;
                background: #e74c3c;
                color: white;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
                z-index: 10;
            }
        </style>
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    <div class="page-content-wrapper">
        <div class="container" style="display: {{ $buyMode ? 'block' : 'none' }}">
            <div class="py-4">
                <!-- Balance Card -->
                <div class="balance-card">
                    <div class="card-body">
                        <div class="py-3 px-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white mb-2">Your Balance</h6>
                                    <h6 class="text-white mb-0">
                                        <i class="fas fa-wallet me-2"></i>
                                        {{ number_format($blance, 2) }} Credit
                                    </h6>
                                </div>
                                <div class="text-end">
                                    <small class="d-block text-white-80">Ticket Price</small>
                                    @if($selectedGameId)
                                        @php $game = $games->firstWhere('id', $selectedGameId); @endphp
                                        <h6 class="text-white mb-0">{{ $game ? number_format($game->ticket_price, 2) : '0.00' }} Credit</h6>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="section-title">Available Games</h6>
                    <div class="text-muted" style="font-size: 1rem; padding-bottom: 0.8rem;">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ $games->count() }} upcoming games
                    </div>
                </div>

                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($games->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h5 class="mb-3">No Upcoming Games</h5>
                        <p class="text-muted mb-0">There are no active games available at the moment. Please check back later.</p>
                    </div>
                @else
                    <div class="game-list">
                        @foreach($games as $game)
                            <div class="game-card {{ $selectedGameId == $game->id ? 'active' : '' }}"
                                 wire:click="$set('selectedGameId', {{ $game->id }})">

                                @if($selectedGameId == $game->id)
                                    <div class="ticket-badge">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                @endif

                                <div class="game-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 text-white">{{ $game->title ?? 'Housie Game' }}</h5>
                                        <span class="game-time-badge">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            {{ \Carbon\Carbon::parse($game->scheduled_at)->format('d M y') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="">
                                    <div class="card-body p-4"
                                    @if($selectedGameId == $game->id)
                                     style=""
                                    @else
                                     style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);"
                                    @endif
                                     >
                                        @if($selectedGameId == $game->id)
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="ticket-price-tag3">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ \Carbon\Carbon::parse($game->scheduled_at)->format('g:i A') }}
                                                    </div>
                                                    <div class="ticket-price-tag2">
                                                        <i class="fas fa-ticket-alt me-1"></i>
                                                        {{ number_format($game->ticket_price, 2) }} Credit
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="mb-3 text-center text-uppercase" style="color: #4a90e2; letter-spacing: 1px;">Prize Structure</h6>

                                            <div class="prize-grid">
                                                <div class="prize-card">
                                                    <div class="prize-label">Corner Prize</div>
                                                    <div class="prize-amount">{{ number_format($game->corner_prize, 2) }} Credit</div>
                                                </div>
                                                <div class="prize-card">
                                                    <div class="prize-label">Top Line Prize</div>
                                                    <div class="prize-amount">{{ number_format($game->top_line_prize, 2) }} Credit</div>
                                                </div>
                                                <div class="prize-card">
                                                    <div class="prize-label">Middle Line Prize</div>
                                                    <div class="prize-amount">{{ number_format($game->middle_line_prize, 2) }} Credit</div>
                                                </div>
                                                <div class="prize-card">
                                                    <div class="prize-label">Bottom Line Prize</div>
                                                    <div class="prize-amount">{{ number_format($game->bottom_line_prize, 2) }} Credit</div>
                                                </div>
                                                <div class="prize-card">
                                                    <div class="prize-label">Full House Prize</div>
                                                    <div class="prize-amount">{{ number_format($game->full_house_prize, 2) }} Credit</div>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <div class="terms-checkbox mb-4">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input class="form-check-input me-2" wire:model="agreements.{{ $game->id }}" type="checkbox" id="termsAccept-{{ $game->id }}" required>
                                                        <label class="form-check-label" for="termsAccept-{{ $game->id }}">
                                                            I agree to the <a href="#" class="text-primary">Terms & Conditions</a> and understand that all purchases are final.
                                                        </label>
                                                    </div>
                                                    @error('agreements.'.$game->id)
                                                        <div class="text-danger mt-2">
                                                            <i class="fas fa-exclamation-circle me-1"></i>
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <button wire:click="buySheet"
                                                        class="btn btn-buy w-100 text-white"
                                                        wire:loading.attr="disabled"
                                                        wire:target="buySheet">
                                                    <span wire:loading.delay wire:target="buySheet" class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                    <span>
                                                        <i class="fas fa-shopping-cart me-2"></i>
                                                        Buy Ticket Sheet Now
                                                    </span>
                                                </button>
                                            </div>
                                        @else
                                            <div class="py-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="ticket-price-tag3">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ \Carbon\Carbon::parse($game->scheduled_at)->format('g:i A') }}
                                                    </div>
                                                    <div class="ticket-price-tag2">
                                                        <i class="fas fa-ticket-alt me-1"></i>
                                                        {{ number_format($game->ticket_price, 2) }} Credit
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Ticket Sheet View -->
        <div class="container" style="display: {{ $sheetShowMode ? 'block' : 'none' }}">
            @if ($sheetShowMode)
                <div class="ticket-sheet-view mt-4">
                    <div class="sheet-header card mb-0 border-bottom-0 rounded-bottom-0">
                        <div class="card-header bg-gradient-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0 text-white">
                                    <i class="fas fa-ticket-alt me-2"></i>
                                    Ticket Sheet: {{ $sheetUid }}
                                </h4>
                                <button class="btn btn-sm btn-light" wire:click="$set('sheetShowMode', false)">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Games
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Game Time: {{ $tickets->first()->game->scheduled_at->format('d M Y, h:i A') }}
                                </span>
                                <span class="badge bg-primary">
                                    <i class="fas fa-ticket-alt me-1"></i>
                                    {{ $tickets->count() }} Tickets
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="sheet-body card border-top-0 rounded-top-0">
                        <div class="card-body p-4">
                            <div class="tickets-grid">
                                @foreach ($tickets as $ticket)
                                    <div class="ticket-card mb-4 position-relative">
                                        <div class="ticket-header bg-light py-2 text-center">
                                            <h6 class="mb-0">Ticket #{{ explode('-', $ticket->ticket_number)[1] }}</h6>
                                        </div>
                                        <table class="table table-bordered mb-0">
                                            <tbody>
                                                @php
                                                    $numbers = is_string($ticket->numbers)
                                                        ? json_decode($ticket->numbers, true)
                                                        : $ticket->numbers;
                                                @endphp

                                                @if (is_array($numbers))
                                                    @foreach ($numbers as $row)
                                                        <tr>
                                                            @foreach ($row as $cell)
                                                                <td class="text-center {{ $cell ? 'bg-white' : 'bg-light' }}"
                                                                    style="width: 11%; height: 50px; font-size: 1.15rem; position: relative;">
                                                                    @if($cell)
                                                                        {{ $cell }}
                                                                        @if($ticket->is_winner && in_array($cell, $ticket->winning_numbers ?? []))
                                                                            <div class="winning-dot" style="
                                                                                position: absolute;
                                                                                bottom: 5px;
                                                                                right: 5px;
                                                                                width: 10px;
                                                                                height: 10px;
                                                                                background: #27ae60;
                                                                                border-radius: 50%;
                                                                            "></div>
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="9" class="text-center text-danger py-2">
                                                            Invalid ticket data
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                        @if($ticket->is_winner)
                                            <div class="winner-badge">
                                                <span class="badge bg-success p-2">
                                                    <i class="fas fa-trophy me-1"></i> Winner
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-5 text-center">
                                <button class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-download me-2"></i> Download Sheet
                                </button>
                                <button class="btn btn-outline-primary btn-lg ms-3 px-5">
                                    <i class="fas fa-share-alt me-2"></i> Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .ticket-sheet-view {
                max-width: 900px;
                margin: 0 auto;
            }

            .tickets-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 2rem;
            }

            .ticket-card {
                position: relative;
                border: 1px solid #dee2e6;
                border-radius: 12px;
                overflow: hidden;
                transition: all 0.3s ease;
                background: white;
                box-shadow: 0 6px 18px rgba(0,0,0,0.05);
            }

            .ticket-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 25px rgba(0,0,0,0.1);
            }

            .winner-badge {
                position: absolute;
                top: 15px;
                right: 15px;
                z-index: 10;
            }

            .ticket-header {
                background: linear-gradient(135deg, #4a90e2 0%, #5e60ce 100%);
                color: white;
            }

            .bg-gradient-primary {
                background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);
            }

            table {
                width: 100%;
                table-layout: fixed;
                border-collapse: separate;
                border-spacing: 0;
            }

            table td {
                font-weight: bold;
                padding: 0.75rem;
                text-align: center;
                height: 55px;
                position: relative;
                border: 1px solid #e9ecef;
            }
        </style>
    @endpush

    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('showError', (message) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Purchase Failed',
                        text: message,
                        confirmButtonColor: '#4a90e2',
                    });
                });

                Livewire.on('showSuccess', (message) => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Purchase Successful',
                        text: message,
                        confirmButtonColor: '#4a90e2',
                        willClose: () => {
                            // Optional: Auto scroll to ticket section
                            document.querySelector('.ticket-sheet-view')?.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            });
        </script>
    @endsection
</div>

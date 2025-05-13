<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>Housieblitz|Game Room</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            .custom-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background-color: #ffc107;
                color: #fff;
                padding: 5px 10px;
                font-size: 12px;
                border-radius: 50px;
            }
            .currency-icon {
                display: inline-block;
                vertical-align: middle;
                margin-right: 1px;
            }
        </style>
    @endsection

    @section('preloader')
        {{-- <livewire:layout.frontend.preloader /> --}}
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('pwa_alart')
        <livewire:layout.frontend.pwa_alart />
    @endsection
        <div x-data="{
                    showWinnerAlert: false,
                    winTitle: '',
                    winMessage: '',
                    winPattern: ''
                }"
                x-init="
                    Echo.channel('game.{{ $games_Id }}')
                        .listen('.number.announced', (e) => {
                            console.log('Received:', e.number);
                            $wire.loadNumbers();
                        });

                    window.addEventListener('winner-alert', (event) => {
                        winTitle = event.detail.title;
                        winMessage = event.detail.message;
                        winPattern = event.detail.pattern;
                        showWinnerAlert = true;

                        // Play winning sound
                        let winSound = new Audio('/sounds/winner.mp3');
                        winSound.play();

                        // Auto hide after 5 seconds
                        setTimeout(() => {
                            showWinnerAlert = false;
                        }, 5000);
                    });
                ">
            <!-- Winner Alert -->
            <div x-show="showWinnerAlert"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90"
                class="fixed top-20 right-5 z-50 bg-success text-white p-4 rounded-lg shadow-lg max-w-md"
                style="display: none;">
                <div class="flex items-center">
                    <div class="mr-3">
                        <i class="fas fa-trophy fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-lg" x-text="winTitle"></h5>
                        <p x-text="winMessage"></p>
                    </div>
                </div>
                <button @click="showWinnerAlert = false" class="absolute top-2 right-2 text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="page-content-wrapper">
                <div class="container px-3 py-3">
                    <!-- Game Header Section -->
                    <div class="game-header mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-dice me-2"></i>Game Room
                            </h6>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2 fs-6">
                                    <i class="fas fa-clock me-1"></i>
                                    <span id="gameTimer">00:00</span>
                                </span>
                                <span class="badge bg-dark fs-6">
                                    <i class="fas fa-users me-1"></i>
                                    <span id="playerCount">10</span> Players
                                </span>
                            </div>
                        </div>

                        <!-- Winning Patterns Section -->
                        <div class="winning-patterns mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-secondary">
                                    <i class="fas fa-trophy me-2"></i>Winning Patterns
                                </h6>
                            </div>
                            <div class="patterns-container d-flex flex-wrap gap-2">
                                @foreach(['corner' => 'Corner', 'top_line' => 'Top Line', 'middle_line' => 'Middle Line', 'bottom_line' => 'Bottom Line', 'full_house' => 'Full House'] as $key => $pattern)
                                    <div class="pattern-badge p-2 rounded {{ $this->hasWonPattern($key) ? 'bg-success text-white' : 'bg-light text-muted' }}">
                                        <i class="fas {{ $this->hasWonPattern($key) ? 'fa-check-circle' : 'fa-circle' }} me-1"></i>
                                        {{ $pattern }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Announced Numbers Section -->
                        <div class="announced-numbers mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-secondary">
                                    <i class="fas fa-bullhorn me-2"></i>Called Numbers
                                </h6>
                                <small class="text-muted">Total: {{ count($announcedNumbers) }}</small>
                            </div>
                            <div class="called-numbers-container bg-white p-3 rounded shadow-sm" style="min-height: 80px;">
                                @if(count($announcedNumbers) > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($announcedNumbers as $num)
                                            <span class="called-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px; font-weight: bold;">
                                                {{ $num }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle me-2"></i>No numbers called yet
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Sheet Section -->
                    <div class="ticket-sheet-container mb-5">
                        <div class="card border-0 shadow-lg">
                            <!-- Sheet Header -->
                            <div class="card-header bg-gradient-primary text-white py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                                    <div class="mb-2 mb-md-0">
                                        <h6 class="mb-1 d-flex align-items-center">
                                            <i class="fas fa-ticket-alt me-2"></i>
                                            <span class="ms-2 font-monospace">{{ $sheet_Id }}</span>
                                        </h6>
                                        <span class="badge bg-danger text-dark fs-7 me-2">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ \Carbon\Carbon::parse($sheetTickets[0]['game']['scheduled_at'])->format('d M Y h:i A') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tickets Grid -->
                            <div class="card-body p-3 p-md-4 bg-light">
                                <div class="row">
                                    @foreach($sheetTickets as $ticket)
                                        <div class="col-12 mb-3">
                                            <div class="ticket-card position-relative">
                                                <div class="card h-100 shadow-sm border-0 overflow-hidden">
                                                    @if($ticket['is_winner'])
                                                    <div class="card-header bg-white py-2 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Ticket #{{ explode('-', $ticket['number'])[1] }}</span>
                                                            <div>
                                                                @if(in_array('corner', $ticket['winning_patterns'] ?? []))
                                                                    <span class="badge bg-warning text-dark me-1">Corner</span>
                                                                @endif
                                                                @if(in_array('top_line', $ticket['winning_patterns'] ?? []))
                                                                    <span class="badge bg-info text-white me-1">Top Line</span>
                                                                @endif
                                                                @if(in_array('middle_line', $ticket['winning_patterns'] ?? []))
                                                                    <span class="badge bg-primary text-white me-1">Middle Line</span>
                                                                @endif
                                                                @if(in_array('bottom_line', $ticket['winning_patterns'] ?? []))
                                                                    <span class="badge bg-secondary text-white me-1">Bottom Line</span>
                                                                @endif
                                                                @if(in_array('full_house', $ticket['winning_patterns'] ?? []))
                                                                    <span class="badge bg-success text-white">Full House</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="card-body p-0">
                                                        <table class="table table-bordered mb-0 ticket-table w-100">
                                                            <tbody>
                                                                @foreach($ticket['numbers'] as $rowIndex => $row)
                                                                    <tr class="{{ $rowIndex === 0 ? 'top-row' : ($rowIndex === 1 ? 'middle-row' : 'bottom-row') }}">
                                                                        @foreach($row as $colIndex => $cell)
                                                                            <td class="text-center p-1
                                                                                {{ $cell ? (in_array($cell, $announcedNumbers) ? 'bg-success text-white' : 'bg-white') : 'bg-transparent' }}
                                                                                {{ ($rowIndex === 0 && $colIndex === 0) || ($rowIndex === 0 && $colIndex === 8) ||
                                                                                ($rowIndex === 2 && $colIndex === 0) || ($rowIndex === 2 && $colIndex === 8) ? 'corner-cell' : '' }}"
                                                                                style="height: 35px; font-size: 0.9rem;">
                                                                                @if($cell)
                                                                                    <span class="number-cell {{ in_array($cell, $announcedNumbers) ? 'text-white' : '' }}">
                                                                                        {{ $cell }}
                                                                                    </span>
                                                                                @endif
                                                                            </td>
                                                                        @endforeach
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @push('styles')
                    <style>
                        /* Custom Styles */
                        .called-number {
                            transition: all 0.3s ease;
                        }
                        .called-number:hover {
                            transform: scale(1.1);
                            background-color: #dc3545 !important;
                        }
                        .ticket-card:hover {
                            transform: translateY(-5px);
                            transition: all 0.3s ease;
                        }
                        .ticket-table td {
                            position: relative;
                        }
                        .number-cell {
                            display: inline-block;
                            width: 24px;
                            height: 24px;
                            line-height: 24px;
                            border-radius: 50%;
                        }
                        .ticket-table tr:first-child td {
                            border-top: 2px solid #dee2e6;
                        }
                        .ticket-table tr:last-child td {
                            border-bottom: 2px solid #dee2e6;
                        }
                        .ticket-table td:first-child {
                            border-left: 2px solid #dee2e6;
                        }
                        .ticket-table td:last-child {
                            border-right: 2px solid #dee2e6;
                        }

                        /* Corner cells highlight */
                        .corner-cell {
                            position: relative;
                        }
                        .corner-cell::before {
                            content: '';
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            border: 2px solid transparent;
                            pointer-events: none;
                        }
                        .corner-cell.bg-success::before {
                            border-color: #ffc107;
                        }

                        /* Line highlighting */
                        .top-row.completed td,
                        .middle-row.completed td,
                        .bottom-row.completed td {
                            position: relative;
                        }
                        .top-row.completed td::after,
                        .middle-row.completed td::after,
                        .bottom-row.completed td::after {
                            content: '';
                            position: absolute;
                            left: 0;
                            top: 50%;
                            width: 100%;
                            height: 2px;
                            background-color: #ffc107;
                            z-index: 1;
                        }

                        /* Pattern badges */
                        .pattern-badge {
                            transition: all 0.3s ease;
                            font-size: 0.85rem;
                        }
                        .pattern-badge.bg-success {
                            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
                        }

                        /* Responsive adjustments */
                        @media (max-width: 768px) {
                            .called-number {
                                width: 32px !important;
                                height: 32px !important;
                                font-size: 0.9rem;
                            }
                            .ticket-table td {
                                height: 28px !important;
                                font-size: 0.8rem !important;
                            }
                        }

                        /* Ensure full width for single column layout */
                        .ticket-table {
                            table-layout: fixed;
                        }
                        .ticket-table td {
                            width: 11.11%; /* Equal width for 9 columns */
                        }

                        /* Winner animation */
                        @keyframes winner-glow {
                            0% { box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); }
                            50% { box-shadow: 0 0 20px rgba(40, 167, 69, 0.8); }
                            100% { box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); }
                        }
                        .ticket-card .card.winner {
                            animation: winner-glow 2s infinite;
                        }
                    </style>
                @endpush
            </div>
        </div>
    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
        {{-- <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script> --}}

    @endsection
</div>

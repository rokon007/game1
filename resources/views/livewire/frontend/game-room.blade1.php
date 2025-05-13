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
            <div x-data x-init="
                    Echo.channel('game.{{ $games_Id }}')
                        .listen('.number.announced', (e) => {
                            console.log('Received:', e.number);
                            $wire.loadNumbers();
                        });
                ">
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
                                            {{-- <small class="d-block">
                                                <i class="fas fa-gamepad me-1"></i>
                                                {{ $sheetTickets[0]['game']['title'] }}
                                            </small> --}}
                                            <span class="badge bg-danger text-dark fs-7 me-2">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ \Carbon\Carbon::parse($sheetTickets[0]['game']['scheduled_at'])->format('d M Y h:i A') }}
                                            </span>
                                        </div>
                                        <div class="text-md-end">
                                            {{-- <span class="badge bg-light text-dark fs-6 me-2">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ \Carbon\Carbon::parse($sheetTickets[0]['game']['scheduled_at'])->format('d M Y h:i A') }}
                                            </span> --}}
                                            {{-- <span class="badge bg-info text-white fs-6">
                                                <i class="fas fa-ticket me-1"></i>
                                                {{ count($sheetTickets) }} Tickets
                                            </span> --}}
                                        </div>
                                    </div>
                                </div>

                                <!-- Tickets Grid - Modified for single column layout -->
                                <div class="card-body p-3 p-md-4 bg-light">
                                    <div class="row">
                                        @foreach($sheetTickets as $ticket)
                                            <div class="col-12 mb-3"> <!-- Full width column with bottom margin -->
                                                <div class="ticket-card position-relative">
                                                    <div class="card h-100 shadow-sm border-0 overflow-hidden">
                                                        @if($ticket['is_winner'])
                                                        <div class="card-header bg-white py-2 border-bottom">
                                                            <small class="text-muted d-flex justify-content-between">
                                                                {{-- <span>Ticket #{{ explode('-', $ticket['ticket_number'])[1] }}</span> --}}
                                                                @if($ticket['is_winner'])
                                                                    <span class="badge bg-success rounded-pill">
                                                                        <i class="fas fa-trophy me-1"></i>Winner
                                                                    </span>
                                                                @endif
                                                            </small>
                                                        </div>
                                                        @endif
                                                        <div class="card-body p-0">
                                                            <table class="table table-bordered mb-0 ticket-table w-100"> <!-- Full width table -->
                                                                <tbody>
                                                                    @foreach($ticket['numbers'] as $row)
                                                                        <tr>
                                                                            @foreach($row as $cell)
                                                                                <td class="text-center p-1 {{ $cell ? (in_array($cell, $announcedNumbers) ? 'bg-success text-white' : 'bg-white') : 'bg-transparent' }}"
                                                                                    style="height: 35px; font-size: 0.9rem;">
                                                                                    @if($cell)
                                                                                        <span class="number-cell {{ in_array($cell, $announcedNumbers) ? 'text-white' : '' }}">
                                                                                            {{ $cell }}
                                                                                            @if(in_array($cell, $announcedNumbers))
                                                                                                {{-- <i class="fas fa-check ms-1 small"></i> --}}
                                                                                            @endif
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
                        </style>
                    @endpush

                    {{-- @push('scripts')
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                const gameRoomComponent = document.querySelector('#game-room-inner');
                                const componentId = gameRoomComponent?.getAttribute('wire:id');

                                if (!componentId) {
                                    console.error("Livewire component ID not found.");
                                    return;
                                }

                                Echo.channel("game.{{ $gameId }}")
                                    .listen('.number.announced', (e) => {
                                        console.log("New Number:", e.number);
                                        const livewireComponent = window.Livewire.find(componentId);
                                        if (livewireComponent) {
                                            livewireComponent.call('loadNumbers');
                                        } else {
                                            console.error("Correct GameRoom Livewire component not found.");
                                        }
                                    });
                            });
                        </script>
                    @endpush --}}







                    {{-- <script>
                        // Real-time updates with Echo
                        Echo.channel('game.{{ $gameId }}')
                            .listen('.number.announced', (e) => {
                                Livewire.dispatch('numberReceived', e.number);
                                // Add visual effect for new number
                                const numberElement = document.createElement('span');
                                numberElement.className = 'called-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center animate__animated animate__bounceIn';
                                numberElement.style.width = '40px';
                                numberElement.style.height = '40px';
                                numberElement.style.fontWeight = 'bold';
                                numberElement.textContent = e.number;

                                const container = document.querySelector('.called-numbers-container .d-flex');
                                if(container) {
                                    container.prepend(numberElement);

                                    // Highlight corresponding numbers in tickets
                                    document.querySelectorAll(`.number-cell`).forEach(cell => {
                                        if(cell.textContent.trim() == e.number) {
                                            cell.closest('td').classList.add('bg-success', 'text-white');
                                            cell.innerHTML = `${e.number} <i class="fas fa-check ms-1 small"></i>`;
                                        }
                                    });
                                }
                            });
                    </script> --}}
                    {{-- @script
                        <script>
                            // Livewire কম্পোনেন্ট লোড হওয়ার পর ইভেন্ট লিসেনার সেটআপ
                            Livewire.on('component.initialized', (component) => {
                                if (component.name === 'game-room') {
                                    const gameId = @js($gameId);
                                    window.initializeGameListeners?.(gameId);
                                }
                            });
                        </script>
                    @endscript --}}

                    {{-- @push('scripts')
                        <script>
                            window.updateAnnouncedNumbers = function(number) {
                                // ডিবাগিং জন্য লগ
                                console.log('Updating UI with number:', number);

                                // বিকল্প ১: Livewire ইভেন্ট ডিসপ্যাচ
                                Livewire.dispatch('number-announced', { number: number });

                                // বিকল্প ২: DOM আপডেট (যদি সরাসরি UI পরিবর্তন করতে চান)
                                const numbersContainer = document.getElementById('announced-numbers');
                                if (numbersContainer) {
                                    // চেক করুন যে নম্বরটি ইতিমধ্যে আছে কিনা
                                    const existingNumbers = Array.from(numbersContainer.querySelectorAll('.number-badge'))
                                                            .map(el => parseInt(el.textContent));

                                    if (!existingNumbers.includes(number)) {
                                        const numberBadge = document.createElement('span');
                                        numberBadge.className = 'number-badge badge bg-primary m-1 animate__animated animate__bounceIn';
                                        numberBadge.textContent = number;
                                        numbersContainer.appendChild(numberBadge);

                                        // স্ক্রল টু লেটেস্ট নম্বর
                                        numbersContainer.scrollLeft = numbersContainer.scrollWidth;
                                    }
                                }

                                // বিকল্প ৩: Alpine.js রিয়েক্টিভিটি ব্যবহার (যদি Alpine ব্যবহার করেন)
                                if (window.Alpine && Alpine.store('game')) {
                                    Alpine.store('game').announcedNumbers.push(number);
                                }
                            };
                        </script>
                    @endpush --}}
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

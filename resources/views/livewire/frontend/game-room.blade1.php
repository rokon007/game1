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
        {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
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


            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 9999;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .modal-content {
                background: white;
                padding: 2rem;
                border-radius: 0.5rem;
                max-width: 90%;
                width: 500px;
                text-align: center;
            }
             .gameOver-container {
                position: relative;
                /* height: 170px; */
                overflow: hidden;
            }
            .gameOver-container .gameOver-text {
                position: absolute;
                top: 50%; /* কন্টেইনারের মাঝখানে সেট করা */
                left: 50%;
                transform: translate(-50%, -50%) rotate(-15deg); /* হালকা ঘুরিয়ে দেওয়া */
                font-size: 36px; /* টেক্সটের আকার */
                color:black; /* স্টাম্পের জন্য হালকা লাল রঙ */
                font-weight: bold;
                text-transform: uppercase; /* টেক্সটকে বড়হাতের করে দেওয়া */
                white-space: nowrap; /* এক লাইনে রাখার জন্য */
                pointer-events: none; /* টেক্সটকে ক্লিক করা নিষিদ্ধ */
                background-color: hsl(45, 100%, 51%);
                border: 1px solid black; /* স্টাম্পের বর্ডার */
                border-radius: 50%; /* গোলাকার আকৃতি */
                padding: 20px 40px; /* স্টাম্পের জায়গা ঠিক করার জন্য প্যাডিং */
                box-shadow: 0 0 15px rgba(255, 0, 0, 0.3); /* হালকা শেডো */

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
    winPattern: '',
    lastAnnouncedNumber: null
}"
x-init="
    Echo.channel('game.{{ $games_Id }}')
        .listen('.number.announced', (e) => {
            console.log('Received via Echo:', e);
            lastAnnouncedNumber = e.number;
            $wire.handleNumberAnnounced(e);
        });

    // Livewire 3 event listeners
    Livewire.on('numberAnnounced', (data) => {
        console.log('Number announced event:', data);
        lastAnnouncedNumber = data.number;

        // Play sound for new number
        let numberSound = new Audio('/sounds/number-called.mp3');
        numberSound.play();

        // Highlight the new number with animation
        setTimeout(() => {
            const numberElements = document.querySelectorAll('.called-number');
            numberElements.forEach(el => {
                if (el.textContent.trim() == lastAnnouncedNumber) {
                    el.classList.add('highlight-new');
                    setTimeout(() => {
                        el.classList.remove('highlight-new');
                    }, 3000);
                }
            });
        }, 500);
    });

    Livewire.on('winner-alert', (data) => {
        winTitle = data.title;
        winMessage = data.message;
        winPattern = data.pattern;
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
    {{-- <div x-show="showWinnerAlert"
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
    </div> --}}

    <!-- Bootstrap CSS -->
{{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Font Awesome (optional for trophy icon) -->
    {{-- <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <div x-data="{
            showWinnerAlert: false,
            winTitle: 'You Won!',
            winMessage: 'Congratulations on winning the game!',
            openModal() {
                this.showWinnerAlert = true;
                const modal = new bootstrap.Modal(this.$refs.winnerModal);
                modal.show();
            }
        }" x-init="openModal()">

        <!-- Bootstrap Modal -->
        <div class="modal fade" tabindex="-1" x-ref="winnerModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-success text-white">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">
                            <i class="fas fa-trophy me-2 text-warning"></i>
                            <span x-text="winTitle"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                            @click="showWinnerAlert = false"></button>
                    </div>
                    <div class="modal-body">
                        <p x-text="winMessage"></p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" @click="showWinnerAlert = false">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Bootstrap JS Bundle (with Popper) -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}


    <div class="page-content-wrapper">
        <div class="container px-3 py-3">
            <button wire:click="winnerSelfAnnounced">Test Trigger</button>
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
                    <div class="gameOver-container called-numbers-container bg-white p-3 rounded shadow-sm" style="min-height: 80px;">
                        @if ($gameOver==1)
                           <div class="gameOver-text">Game Over</div>
                        @endif
                        @if(count($announcedNumbers) > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($announcedNumbers as $num)
                                    <span class="called-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; font-weight: bold;"
                                        :class="{'highlight-new': lastAnnouncedNumber == {{ $num }}}">
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
                                @if(isset($sheetTickets[0]['game']['scheduled_at']))
                                <span class="badge bg-danger text-dark fs-7 me-2">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ \Carbon\Carbon::parse($sheetTickets[0]['game']['scheduled_at'])->format('d M Y h:i A') }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tickets Grid -->
                    <div class="card-body p-3 p-md-4 bg-light">
                        <div class="row">
                            @foreach($sheetTickets as $ticket)
                                <div class="col-12 mb-3">
                                    <div class="ticket-card position-relative">
                                        <div class="card h-100 shadow-sm border-0 overflow-hidden {{ $ticket['is_winner'] ? 'winner' : '' }}">
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
                                                            <tr class="{{ $rowIndex === 0 ? 'top-row' : ($rowIndex === 1 ? 'middle-row' : 'bottom-row') }}
                                                                {{ $rowIndex === 0 && $this->hasWonPattern('top_line') ? 'completed' : '' }}
                                                                {{ $rowIndex === 1 && $this->hasWonPattern('middle_line') ? 'completed' : '' }}
                                                                {{ $rowIndex === 2 && $this->hasWonPattern('bottom_line') ? 'completed' : '' }}">
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
                .called-number.highlight-new {
                    animation: pulse-glow 2s ease-in-out;
                    background-color: #ffc107 !important;
                    color: #000 !important;
                    transform: scale(1.2);
                    z-index: 10;
                    box-shadow: 0 0 15px rgba(255, 193, 7, 0.8);
                }
                @keyframes pulse-glow {
                    0% { transform: scale(1); box-shadow: 0 0 0 rgba(255, 193, 7, 0); }
                    50% { transform: scale(1.3); box-shadow: 0 0 20px rgba(255, 193, 7, 0.8); }
                    100% { transform: scale(1.2); box-shadow: 0 0 15px rgba(255, 193, 7, 0.5); }
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

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Audio for announced numbers
            @this.on('play-number-audio', (data) => {
                const number = data.number;
                playNumberAudio(number);
            });

            // Audio for winning patterns
            @this.on('play-winner-audio', (data) => {
                const pattern = data.pattern;
                playWinnerAudio(pattern);
            });

            // Function to play audio for announced numbers
            function playNumberAudio(number) {
                console.log(`Playing audio for number: ${number}`);

                // Map number to audio file name (you can use a more efficient approach for all 90 numbers)
                const numberWords = [
                    'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
                    'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen',
                    'twenty', 'twenty-one', 'twenty-two', 'twenty-three', 'twenty-four', 'twenty-five', 'twenty-six', 'twenty-seven', 'twenty-eight', 'twenty-nine',
                    'thirty', 'thirty-one', 'thirty-two', 'thirty-three', 'thirty-four', 'thirty-five', 'thirty-six', 'thirty-seven', 'thirty-eight', 'thirty-nine',
                    'forty', 'forty-one', 'forty-two', 'forty-three', 'forty-four', 'forty-five', 'forty-six', 'forty-seven', 'forty-eight', 'forty-nine',
                    'fifty', 'fifty-one', 'fifty-two', 'fifty-three', 'fifty-four', 'fifty-five', 'fifty-six', 'fifty-seven', 'fifty-eight', 'fifty-nine',
                    'sixty', 'sixty-one', 'sixty-two', 'sixty-three', 'sixty-four', 'sixty-five', 'sixty-six', 'sixty-seven', 'sixty-eight', 'sixty-nine',
                    'seventy', 'seventy-one', 'seventy-two', 'seventy-three', 'seventy-four', 'seventy-five', 'seventy-six', 'seventy-seven', 'seventy-eight', 'seventy-nine',
                    'eighty', 'eighty-one', 'eighty-two', 'eighty-three', 'eighty-four', 'eighty-five', 'eighty-six', 'eighty-seven', 'eighty-eight', 'eighty-nine',
                    'ninety'
                ];

                // Get the audio file name based on the number
                const audioFileName = number >= 0 && number <= 90 ? `${numberWords[number]}.mp3` : null;

                if (audioFileName) {
                    const audioPath = `/sounds/numbers/${audioFileName}`;
                    playAudio(audioPath);
                }
            }

            // Function to play audio for winning patterns
            function playWinnerAudio(pattern) {
                console.log(`Playing audio for winning pattern: ${pattern}`);

                // Map pattern to audio file name
                const patternAudioMap = {
                    'corner': 'corner_numbers.mp3',
                    'top_line': 'top_line.mp3',
                    'middle_line': 'middle_line.mp3',
                    'bottom_line': 'bottom_line.mp3',
                    'full_house': 'full_house.mp3'
                };

                const audioFileName = patternAudioMap[pattern] || null;

                if (audioFileName) {
                    const audioPath = `/sounds/winners/${audioFileName}`;
                    playAudio(audioPath);
                }
            }

            // Helper function to play audio
            function playAudio(audioPath) {
                console.log(`Playing audio file: ${audioPath}`);

                // Create and play audio element
                const audio = new Audio(audioPath);

                // Add error handling
                audio.onerror = function() {
                    console.error(`Error playing audio file: ${audioPath}`);
                };

                // Play the audio
                audio.play().catch(error => {
                    console.error('Error playing audio:', error);
                });
            }
        });
    </script>

    <!-- game-room.blade.php -->
        <div x-data="{
                    showNumberModal: false,
                    currentNumber: null,
                    callNumber: null,
                    showSpin: true,
                    init() {
                        console.log('Alpine component initialized');

                        window.Echo.channel('game.{{ $games_Id }}')
                            .listen('.number.announced', (e) => {
                                console.log('Event received:', e);
                                this.showNumberModal = true;
                                this.currentNumber = e.number;
                                this.callNumber = e.number;
                                this.showSpin = true;

                                setTimeout(() => {
                                    this.showSpin = false;
                                    console.log('Spin animation stopped');
                                }, 3000);

                                setTimeout(() => {
                                    this.showNumberModal = false;
                                    console.log('Modal closed');
                                    $wire.handleNumberAnnounced(e);
                                }, 6000);
                            });
                    }
                }">
            <!-- Debug Modal State -->
            <div x-show="showNumberModal"
                x-transition.opacity.duration.300ms
                class="modal-overlay">
                <div class="modal-content">
                    {{-- <template x-if="showSpin">
                        <div class="text-8xl py-8 text-blue-500">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </template> --}}
                     <template x-if="showSpin">
                        <div class="text-center p-8 bg-white rounded-xl shadow-2xl">
                            <div class="mb-6 text-2xl font-bold text-blue-600">
                                Number is being selected...
                            </div>
                            <div class="flex justify-center">
                                <img
                                    src="https://media2.giphy.com/media/LL5FiPJnlFnjy/giphy.gif"
                                    alt="Spinning wheel"
                                    class="w-64 h-64 object-contain border-4 border-blue-100 rounded-full shadow-inner"
                                >
                            </div>
                            <div class="mt-6 text-gray-500">
                                Please wait while we pick your lucky number
                            </div>
                        </div>
                    </template>
                    <template x-if="!showSpin">
                        <div style="font-size: 32px; font-weight: 900; color: #dc3545;"
                        x-text="currentNumber"></div>

                    </template>
                </div>
            </div>


        </div>

        <!-- মডাল HTML -->
            {{-- <div  class="modal fade" id="gameOverModal" tabindex="-1" aria-hidden="true">
                <!-- ... বিদ্যমান মডাল কাঠামো ... -->

                <div class="modal-body text-center position-relative pb-4 pt-0">
                    <!-- ডাইনামিক আইকন এর জন্য ডিভ -->
                    <div id="modalIcon" class="mx-auto mt-4"></div>

                    <h3 class="modal-title text-white mb-3" id="gameOverModalLabel"></h3>
                    <p class="text-light mb-4" id="gameOverMessage"></p>

                    <!-- উইনার লিস্ট -->
                    <div class="row g-3 mb-4">
                        @if($games_Id && isset($winners))
                            <div class="col-12">
                                <div class="list-group">
                                    @foreach($winners->take(5) as $winner)
                                        <a class="list-group-item list-group-item-action d-flex align-items-start gap-3 mb-3">
                                            <!-- ইউজার অ্যাভাটার -->
                                            <div class="position-relative">
                                                @if($winner->user->avatar)
                                                    <img src="{{ $winner->user->avatar }}" class="rounded-circle" width="48" height="48">
                                                @else
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                        {{ strtoupper(substr($winner->user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle" style="display: {{ $winner->user->is_online ? 'block' : 'none' }};"></span>
                                            </div>

                                            <!-- উইনার ডিটেইলস -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <strong>{{ $winner->user->name }}</strong>
                                                    <small class="text-muted">{{ $winner->won_at->diffForHumans() }}</small>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }}">
                                                        @if($winner->pattern == 'corner') চার কোণা
                                                        @elseif($winner->pattern == 'top_line') উপরের লাইন
                                                        @elseif($winner->pattern == 'middle_line') মাঝের লাইন
                                                        @elseif($winner->pattern == 'bottom_line') নিচের লাইন
                                                        @elseif($winner->pattern == 'full_house') ফুল হাউস
                                                        @endif
                                                    </span>
                                                    <span class="badge bg-primary rounded-pill text-white">
                                                        {{ $winner->prize_amount }} ক্রেডিট
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- একশন বাটন -->
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-danger btn-lg py-3 fw-bold">
                            আবার খেলুন
                        </button>
                        <button type="button" class="btn btn-outline-light btn-lg py-3">
                            মেনুতে যান
                        </button>
                    </div>
                </div>
            </div> --}}

            <!-- জাভাস্ক্রিপ্ট -->
            {{-- <script>
                document.addEventListener('livewire:initialized', () => {
                    // মডাল ইনিশিয়ালাইজেশন
                    const gameOverModal = new bootstrap.Modal(document.getElementById('gameOverModal'));

                    const showModal = (data, isWinner = false) => {
                        // মডাল কন্টেন্ট আপডেট
                        const iconDiv = document.getElementById('modalIcon');
                        const titleEl = document.getElementById('gameOverModalLabel');
                        const messageEl = document.getElementById('gameOverMessage');

                        if (isWinner) {
                            iconDiv.innerHTML = `
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 80px; height: 80px;">
                                    <i class="bi bi-trophy fs-1 text-white"></i>
                                </div>`;
                            titleEl.textContent = data.title || 'উইনার ঘোষণা';
                            messageEl.textContent = 'অভিনন্দন বিজয়ীদের!';
                        } else {
                            iconDiv.innerHTML = `
                                <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 80px; height: 80px;">
                                    <i class="bi bi-emoji-frown fs-1 text-white"></i>
                                </div>`;
                            titleEl.textContent = data.title || 'গেম শেষ';
                            messageEl.textContent = data.message || 'গেমটি সম্পন্ন হয়েছে';
                        }

                        // মডাল শো
                        gameOverModal.show();
                    };

                    // Livewire ইভেন্ট লিসেনার
                    Livewire.on('showWinnerModal', (data) => {
                        showModal(data, true);
                        // new Audio('sounds/winner.mp3').play().catch(console.error);
                    });

                    Livewire.on('showGameOverModal', (data) => {
                        showModal(data, false);
                        // new Audio('sounds/game-over.mp3').play().catch(console.error);
                    });
                });
            </script> --}}


            <!-- Modal HTML -->
            {{-- <div wire:ignore.self class="modal fade" id="gameOverModal" tabindex="-1" aria-labelledby="gameOverModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow" style="background: linear-gradient(135deg, #7f0d00 0%, #2c3e50 100%);">
                        <!-- Animated background elements -->
                        <div class="position-absolute w-100 h-100 opacity-25">
                            <div class="position-absolute top-0 start-0 rounded-circle bg-danger" style="width: 100px; height: 100px; filter: blur(30px);"></div>
                            <div class="position-absolute bottom-0 end-0 rounded-circle bg-danger" style="width: 100px; height: 100px; filter: blur(30px);"></div>
                        </div>

                        <div class="modal-header border-0 position-relative">
                            <div class="mx-auto mt-4">
                                <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="bi bi-emoji-frown fs-1 text-white"></i>
                                </div>
                            </div>
                        </div>

                        <div class="modal-body text-center position-relative pb-4 pt-0">
                            <h3 class="modal-title text-white mb-3" id="gameOverModalLabel"></h3>
                            <p class="text-light mb-4" id="gameOverMessage">You've run out of time!</p>

                            <!-- Stats Row -->
                            @if($games_Id)
                                <div class="row g-3 mb-4">
                                    @php
                                        $winners = App\Models\Winner::with(['user', 'ticket'])
                                                ->where('game_id', $games_Id)
                                                ->orderByDesc('won_at')
                                                ->get();
                                    @endphp
                                    @if(count($winners) > 0)
                                        <div class="col-12">
                                            <div class="notification-area pb-2">
                                                <div class="list-group" >
                                                    @foreach($winners->take(5) as $winner)
                                                        <a class="list-group-item list-group-item-action d-flex align-items-start gap-3 mb-3">
                                                            <!-- Avatar -->
                                                            <div class="position-relative">
                                                                @if($winner->user->avatar)
                                                                    <img src="{{ $winner->user->avatar->avatar }}" class="rounded-circle" width="48" height="48" alt="{{ $winner->user->name }}">
                                                                @else

                                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                                        <strong>{{ strtoupper(substr($winner->user->name, 0, 1)) }}</strong>
                                                                    </div>
                                                                @endif
                                                                <span
                                                                    class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"
                                                                    style="display: {{$winner->user->is_online ? 'block' : 'none'}};">
                                                                </span>
                                                            </div>
                                                            <!-- Content -->
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between">
                                                                    <strong>{{$winner->user->name}}</strong>
                                                                    <small class="text-muted">{{ $winner->won_at->diffForHumans() }}</small>
                                                                </div>
                                                                @php
                                                                    $patternNames = [
                                                                        'corner' => 'Four Corners',
                                                                        'top_line' => 'Top Line',
                                                                        'middle_line' => 'Middle Line',
                                                                        'bottom_line' => 'Bottom Line',
                                                                        'full_house' => 'Full House'
                                                                    ];
                                                                @endphp
                                                                <div class="d-flex justify-content-between">
                                                                    <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }}">
                                                                        {{ $patternNames[$winner->pattern] ?? ucfirst($winner->pattern) }}
                                                                    </span>
                                                                    <small class="text-muted">Prize Amount</small> <span class="badge bg-primary rounded-pill text-white">{{$winner->user->name}}</span>
                                                                </div>
                                                            </div>

                                                        </a>

                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            @endif


                            <div class="d-grid gap-3">
                                <button type="button" class="btn btn-danger btn-lg py-3 fw-bold" onclick="restartGame()">
                                    Play Again
                                </button>
                                <button type="button" class="btn btn-outline-light btn-lg py-3" onclick="goToMainMenu()">
                                    Main Menu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- JavaScript to control the modal -->
            {{-- <script>
                // Function to show game over modal
                function showGameOver(score, level, message, title) {
                    // Update modal content
                    document.getElementById('gameScore').textContent = score.toLocaleString();
                    document.getElementById('gameLevel').textContent = level;
                    document.getElementById('gameOverMessage').textContent = message;
                    document.getElementById('gameOverModalLabel').textContent = title;

                    // Play sound
                    const audio = new Audio('sounds/game-over.mp3');
                    audio.play().catch(e => console.log("Audio play failed:", e));

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('gameOverModal'));
                    modal.show();
                }

                // Example functions for buttons
                function restartGame() {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('gameOverModal'));
                    modal.hide();
                    // Add your restart logic here
                    console.log("Restarting game...");
                }

                function goToMainMenu() {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('gameOverModal'));
                    modal.hide();
                    // Add your menu navigation logic here
                    console.log("Going to main menu...");
                }

                // Livewire event listener (if using Livewire)
                document.addEventListener('livewire:init', () => {
                    Livewire.on('gameOver', (data) => {
                    showGameOver(data.score || 0, data.level || 1, data.message || 'Game Over');
                    });
                });
            </script> --}}

            {{-- <div x-data="{
                    winners: [],
                    showWinnerModal: false,
                    showGameOverModal: false,
                    init() {
                        // উইনার নোটিফিকেশন
                        Livewire.on('showWinnerModal', (data) => {
                            this.winners = data.winners;
                            this.showWinnerModal = true;

                            // অডিও প্লে করুন
                            //new Audio('{{ asset('sounds/game-over.mp3') }}').play();
                        });

                        // গেম ওভার নোটিফিকেশন
                        Livewire.on('showGameOver', (data) => {
                            this.winners = data.winners;
                            this.showGameOverModal = true;

                            // অডিও প্লে করুন
                            new Audio('{{ asset('sounds/game-over.mp3') }}').play();
                        });
                    }
                }">

                <!-- গেম ওভার মোডাল -->
                <div x-show="showGameOverModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.7)">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content bg-dark text-white">
                            <div class="modal-header border-0">
                                <h5 class="modal-title">Game Over - Winners</h5>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-dark">
                                        <thead>
                                            <tr>
                                                <th>Player</th>
                                                <th>Patterns Won</th>
                                                <th>Total Prize</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="winner in winners" :key="winner.user.id">
                                                <tr>
                                                    <td x-text="winner.user.name"></td>
                                                    <td>
                                                        <span x-text="winner.patterns.join(', ')" class="badge bg-success me-1"></span>
                                                    </td>
                                                    <td x-text="winner.total_prize + ' credits'"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button @click="showGameOverModal = false" class="btn btn-primary">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
            @if ($winnerAllart)
                <!-- উইনার মোডাল -->
                <style>
                    .progress-bar {
                        transition: width 0.3s ease;
                        background-color: #4e73df;
                    }

                    .progress-bar-striped {
                        background-image: linear-gradient(
                            45deg,
                            rgba(255, 255, 255, 0.15) 25%,
                            transparent 25%,
                            transparent 50%,
                            rgba(255, 255, 255, 0.15) 50%,
                            rgba(255, 255, 255, 0.15) 75%,
                            transparent 75%,
                            transparent
                        );
                        background-size: 1rem 1rem;
                    }

                    .progress-bar-animated {
                        animation: progress-bar-stripes 1s linear infinite;
                    }

                    @keyframes progress-bar-stripes {
                        0% {
                            background-position-x: 1rem;
                        }
                    }
                </style>
                <div x-data="{
                            transferProgress: 0,
                            init() {
                                // ইভেন্ট লিসেনার
                                Livewire.on('progressUpdated', (progress) => {
                                    this.transferProgress = progress;
                                });

                                // প্রগেস বার অ্যানিমেশন
                                const interval = setInterval(() => {
                                    if(this.transferProgress < 100) {
                                        this.transferProgress += Math.floor(Math.random() * 10) + 1;
                                        if(this.transferProgress > 100) this.transferProgress = 100;

                                        // Livewire এ প্রগেস আপডেট করুন
                                        @this.dispatch('updateProgress', { progress: this.transferProgress });
                                    } else {
                                        clearInterval(interval);
                                        this.dispatch('transfer-completed');
                                    }
                                }, 80);
                                // ইভেন্ট লিসেনার
                                this.$el.addEventListener('transfer-completed', () => {
                                    alert('ক্রেডিট ট্রান্সফার সম্পন্ন হয়েছে!');
                                });
                            }
                        }">
                    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7)">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow" style="background: linear-gradient(135deg, #7f0d00 0%, #2c3e50 100%);">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">Game Over - Winners</h5>
                                </div>
                                <div class="modal-body">
                                    <!-- উইনার লিস্ট -->
                                    <div class="row g-3 mb-4">
                                        @if($games_Id && isset($winners))
                                            <div class="col-12">
                                                <div class="list-group">
                                                    @foreach($winners->take(5) as $winner)
                                                        <a class="list-group-item list-group-item-action d-flex align-items-start gap-3 mb-3">
                                                            <!-- ইউজার অ্যাভাটার -->
                                                            <div class="position-relative">
                                                                @if($winner->user->avatar)
                                                                    <img src="{{ $winner->user->avatar }}" class="rounded-circle" width="48" height="48">
                                                                @else
                                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                                        {{ strtoupper(substr($winner->user->name, 0, 1)) }}
                                                                    </div>
                                                                @endif
                                                                <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle" style="display: {{ $winner->user->is_online ? 'block' : 'none' }};"></span>
                                                            </div>

                                                            <!-- উইনার ডিটেইলস -->
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between">
                                                                    <strong>{{ $winner->user->name }}</strong>
                                                                    <small class="text-muted">{{ $winner->won_at->diffForHumans() }}</small>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }}">
                                                                        @if($winner->pattern == 'corner') চার কোণা
                                                                        @elseif($winner->pattern == 'top_line') উপরের লাইন
                                                                        @elseif($winner->pattern == 'middle_line') মাঝের লাইন
                                                                        @elseif($winner->pattern == 'bottom_line') নিচের লাইন
                                                                        @elseif($winner->pattern == 'full_house') ফুল হাউস
                                                                        @endif
                                                                    </span>
                                                                    <span class="badge bg-primary rounded-pill text-white">
                                                                        {{ $winner->prize_amount }} ক্রেডিট
                                                                    </span>
                                                                </div>
                                                                @if($loop->first)
                                                                    <div class="mt-2">
                                                                        <div class="d-flex justify-content-between mb-1">
                                                                            <small>ক্রেডিট ট্রান্সফার প্রগেস</small>
                                                                            {{-- <small>
                                                                                <span x-text="transferProgress">0</span>%
                                                                            </small> --}}
                                                                            <small x-text="typeof transferProgress === 'number' ? transferProgress + '%' : '100%'"></small>
                                                                        </div>
                                                                        <div class="progress" style="height: 8px;">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                                                role="progressbar"
                                                                                x-bind:style="'width: ' + transferProgress + '%'"
                                                                                x-bind:aria-valuenow="transferProgress"
                                                                                aria-valuemin="0"
                                                                                aria-valuemax="100">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button @click="winnerAllart = false" class="btn btn-primary">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

             @if ($gameOverAllart)
                <!-- উইনার মোডাল -->
                <style>
                    .progress-bar {
                        transition: width 0.3s ease;
                        background-color: #4e73df;
                    }

                    .progress-bar-striped {
                        background-image: linear-gradient(
                            45deg,
                            rgba(255, 255, 255, 0.15) 25%,
                            transparent 25%,
                            transparent 50%,
                            rgba(255, 255, 255, 0.15) 50%,
                            rgba(255, 255, 255, 0.15) 75%,
                            transparent 75%,
                            transparent
                        );
                        background-size: 1rem 1rem;
                    }

                    .progress-bar-animated {
                        animation: progress-bar-stripes 1s linear infinite;
                    }

                    @keyframes progress-bar-stripes {
                        0% {
                            background-position-x: 1rem;
                        }
                    }
                </style>
                <div x-data="{
                            transferProgress: 0,
                            init() {
                                // ইভেন্ট লিসেনার
                                Livewire.on('progressUpdated', (progress) => {
                                    this.transferProgress = progress;
                                });

                                // প্রগেস বার অ্যানিমেশন
                                const interval = setInterval(() => {
                                    if(this.transferProgress < 100) {
                                        this.transferProgress += Math.floor(Math.random() * 10) + 1;
                                        if(this.transferProgress > 100) this.transferProgress = 100;

                                        // Livewire এ প্রগেস আপডেট করুন
                                        @this.dispatch('updateProgress', { progress: this.transferProgress });
                                    } else {
                                        clearInterval(interval);
                                        this.dispatch('transfer-completed');
                                    }
                                }, 80);
                                // ইভেন্ট লিসেনার
                                this.$el.addEventListener('transfer-completed', () => {
                                    alert('ক্রেডিট ট্রান্সফার সম্পন্ন হয়েছে!');
                                });
                            }
                        }">
                    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7)">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow" style="background: linear-gradient(135deg, #7f0d00 0%, #2c3e50 100%);">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title">Game Over - Winners</h5>
                                </div>
                                <div class="modal-body">
                                    <!-- উইনার লিস্ট -->
                                    <div class="row g-3 mb-4">
                                        @if($games_Id && isset($winners))
                                            <div class="col-12">
                                                <div class="list-group">
                                                    @foreach($winners->take(5) as $winner)
                                                        <a class="list-group-item list-group-item-action d-flex align-items-start gap-3 mb-3">
                                                            <!-- ইউজার অ্যাভাটার -->
                                                            <div class="position-relative">
                                                                @if($winner->user->avatar)
                                                                    <img src="{{ $winner->user->avatar }}" class="rounded-circle" width="48" height="48">
                                                                @else
                                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                                        {{ strtoupper(substr($winner->user->name, 0, 1)) }}
                                                                    </div>
                                                                @endif
                                                                <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle" style="display: {{ $winner->user->is_online ? 'block' : 'none' }};"></span>
                                                            </div>

                                                            <!-- উইনার ডিটেইলস -->
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between">
                                                                    <strong>{{ $winner->user->name }}</strong>
                                                                    <small class="text-muted">{{ $winner->won_at->diffForHumans() }}</small>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }}">
                                                                        @if($winner->pattern == 'corner') চার কোণা
                                                                        @elseif($winner->pattern == 'top_line') উপরের লাইন
                                                                        @elseif($winner->pattern == 'middle_line') মাঝের লাইন
                                                                        @elseif($winner->pattern == 'bottom_line') নিচের লাইন
                                                                        @elseif($winner->pattern == 'full_house') ফুল হাউস
                                                                        @endif
                                                                    </span>
                                                                    <span class="badge bg-primary rounded-pill text-white">
                                                                        {{ $winner->prize_amount }} ক্রেডিট
                                                                    </span>
                                                                </div>
                                                                @if($loop->first)
                                                                    <div class="mt-2">
                                                                        <div class="d-flex justify-content-between mb-1">
                                                                            <small>ক্রেডিট ট্রান্সফার প্রগেস</small>
                                                                            {{-- <small>
                                                                                <span x-text="transferProgress">0</span>%
                                                                            </small> --}}
                                                                            <small x-text="typeof transferProgress === 'number' ? transferProgress + '%' : '100%'"></small>
                                                                        </div>
                                                                        <div class="progress" style="height: 8px;">
                                                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                                                role="progressbar"
                                                                                x-bind:style="'width: ' + transferProgress + '%'"
                                                                                x-bind:aria-valuenow="transferProgress"
                                                                                aria-valuemin="0"
                                                                                aria-valuemax="100">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button @click="winnerAllart = false" class="btn btn-primary">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- @if($winnerAllart)
                <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-success">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Transaction Successful</h5>
                            </div>
                            <div class="modal-body text-center">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0 fs-5">Your transaction was completed successfully.</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button class="btn btn-success" wire:click="$set('transactionSuccess', false)">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif --}}

</div>

    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        {{-- <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script> --}}

    @endsection
</div>

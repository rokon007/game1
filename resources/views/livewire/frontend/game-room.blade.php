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
                overflow: hidden;
            }
            .gameOver-container .gameOver-text {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-15deg);
                font-size: 36px;
                color:black;
                font-weight: bold;
                text-transform: uppercase;
                white-space: nowrap;
                pointer-events: none;
                background-color: hsl(45, 100%, 51%);
                border: 1px solid black;
                border-radius: 50%;
                padding: 20px 40px;
                box-shadow: 0 0 15px rgba(255, 0, 0, 0.3);
            }
            #gameTimer {
                    font-family: 'Courier New', monospace;
                    font-weight: bold;
                    animation: pulse 1s infinite;
                }

                @keyframes pulse {
                    0% { opacity: 1; }
                    50% { opacity: 0.7; }
                    100% { opacity: 1; }
                }
        </style>
    @endsection

    @section('preloader')
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
        // Echo listener for number announced
        Echo.channel('game.{{ $games_Id }}')
            .listen('.number.announced', (e) => {
                console.log('Number announced via Echo:', e);
                lastAnnouncedNumber = e.number;
                $wire.handleNumberAnnounced(e);
            })
            .listen('.game.winner', (e) => {
                console.log('Winner announced via Echo:', e);
                $wire.handleWinnerAnnounced(e);
            });

        // Livewire event listeners
        Livewire.on('numberAnnounced', (data) => {
            console.log('Number announced event:', data);
            lastAnnouncedNumber = data.number;

            let numberSound = new Audio('/sounds/number-called.mp3');
            numberSound.play();

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

            let winSound = new Audio('/sounds/winner.mp3');
            winSound.play();

            setTimeout(() => {
                showWinnerAlert = false;
            }, 5000);
        });

        Livewire.on('winnerAnnounced', (data) => {
            console.log('Winner announced via Livewire:', data);
            // আপনার UI আপডেট লজিক এখানে যোগ করুন
        });
    ">

    <div class="page-content-wrapper">
        <div class="container px-3 py-3">
            <!-- ডিবাগ বাটন যোগ করুন -->
            {{-- <div class="mb-3">
                <button wire:click="testWinnerHandler" class="btn btn-warning btn-sm">Test Winner Handler</button>
                <button wire:click="winnerSelfAnnounced" class="btn btn-info btn-sm">Test Winner Alert</button>
            </div> --}}

            <!-- Game Header Section -->
            <div class="game-header mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-dice me-2"></i>Game Room
                    </h6>
                    <div class="d-flex align-items-center">
                        <div wire:poll.1000ms="updateTimer">
                            <span class="badge bg-danger me-2 fs-6">
                                <i class="fas fa-clock me-1"></i>
                                {{ $remainingTime }}
                            </span>
                        </div>
                        <span class="badge bg-dark fs-6">
                            <i class="fas fa-users me-1"></i>
                            <span id="playerCount">{{$totalParticipants}}</span> Players
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
                        @foreach($winnerPattarns as $pattern)
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $this->getPatternColor($pattern->pattern) }}">
                                    @if($pattern->pattern == 'corner') Corner
                                    @elseif($pattern->pattern == 'top_line') Top
                                    @elseif($pattern->pattern == 'middle_line') Middle
                                    @elseif($pattern->pattern == 'bottom_line') Bottom
                                    @elseif($pattern->pattern == 'full_house') Full house
                                    @endif
                                </span>
                                {{-- <span class="badge bg-primary rounded-pill text-white">
                                    {{ $pattern->prize_amount }} Credit
                                </span> --}}
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

                .pattern-badge {
                    transition: all 0.3s ease;
                    font-size: 0.85rem;
                }
                .pattern-badge.bg-success {
                    box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
                }

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

                .ticket-table {
                    table-layout: fixed;
                }
                .ticket-table td {
                    width: 11.11%;
                }

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

    <!-- Modal -->
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

    <!-- Intro Video Modal -->
    <div x-data="{
        showIntroVideo: true,
        videoLoaded: false,
        init() {
            const video = this.$refs.introVideo;
            const audio = this.$refs.introAudio;
            video.addEventListener('canplay', () => {
                this.videoLoaded = true;
                video.play().catch(error => {
                    console.error('Video autoplay failed:', error);
                });
                audio.play().catch(error => {
                console.error('Audio playback failed:', error);
            });
                if (window.innerWidth <= 768) {
                    if (video.requestFullscreen) {
                        video.requestFullscreen().catch(err => console.error('Fullscreen error:', err));
                    } else if (video.webkitRequestFullscreen) {
                        video.webkitRequestFullscreen();
                    } else if (video.msRequestFullscreen) {
                        video.msRequestFullscreen();
                    }
                }
            });
            video.addEventListener('ended', () => {
                this.showIntroVideo = false;
                 audio.pause();
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
            });
        },
        skipVideo() {
            this.showIntroVideo = false;
            const video = this.$refs.introVideo;
            const audio = this.$refs.introAudio;
            audio.pause();
            video.pause();
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
        }
        }">
        <div x-show="showIntroVideo" class="modal-overlay" x-transition.opacity.duration.300ms>
            <div class="modal-content">
                <video x-ref="introVideo" id="intro-video" playsinline muted>
                    <source src="{{ asset('videos/intro.mp4') }}" type="video/mp4">
                    <source src="{{ asset('videos/intro.webm') }}" type="video/webm">
                    Your browser does not support the video tag.
                </video>
                <audio x-ref="introAudio" id="intro-audio">
                    <source src="{{ asset('audio/intro.mp3') }}" type="audio/mpeg">
                    Your browser does not support the audio tag.
                </audio>
                <div x-show="!videoLoaded" id="intro-video-loading">
                     <img src="https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExcmk4bnVqeHBscXdvank4YTY3NWMzNDIyc3U4NDNnY3lrZWwybW9leiZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/IwSG1QKOwDjQk/giphy.gif" alt="Loading" class="w-32 h-32">
                </div>
                <button x-on:click="skipVideo" id="skip-video" class="btn btn-danger btn-sm rounded-pill px-4 py-2 text-white">
                    <i class="fas fa-forward me-1"></i> Skip
                </button>
            </div>
        </div>
    </div>

    <!-- Winner Modal -->
    @if ($winnerAllart)
        <div x-data="{
            transferProgress: 0,
            init() {
                // Event Listener for Progress Updates
                Livewire.on('progressUpdated', (progress) => {
                    this.transferProgress = progress;
                });

                // Progress Bar Animation
                const interval = setInterval(() => {
                    if (this.transferProgress < 100) {
                        this.transferProgress += Math.floor(Math.random() * 5) + 2;
                        if (this.transferProgress > 100) this.transferProgress = 100;

                        // Dispatch Progress Update to Livewire
                        @this.dispatch('updateProgress', { progress: this.transferProgress });
                    } else {
                        clearInterval(interval);
                        this.dispatch('transfer-completed');
                    }
                }, 100);

                // Event Listener for Transfer Completion
                this.$el.addEventListener('transfer-completed', () => {
                    this.dispatch('progressCompleted');
                });
            }
        }">
            <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg rounded-3" style="background: linear-gradient(135deg, #1e3a8a 0%, #6b21a8 100%); overflow: hidden;">
                        <div class="modal-header border-0 px-3 pt-3 pb-2">
                            <h5 class="modal-title text-white fw-bold">🏆 Winner Announcement</h5>
                        </div>
                        <div class="modal-body p-3">
                            <div class="row g-2">
                                @if($games_Id && isset($winners))
                                    <div class="col-12">
                                        <div class="list-group">
                                            {{-- @foreach($winners->take(5) as $winner) --}}
                                            @foreach($winners as $winner)
                                                <div class="list-group-item list-group-item-action d-flex align-items-center gap-2 mb-2 rounded-3 shadow-sm p-2" style="background: rgba(255,255,255,0.1); transition: transform 0.2s; cursor: pointer; overflow: hidden;" @mouseover="this.style.transform='scale(1.02)'" @mouseout="this.style.transform='scale(1)'">
                                                    <div class="position-relative flex-shrink-0">
                                                        @if($winner->user->avatar)
                                                            <img src="{{ $winner->user->avatar }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                                                        @else
                                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 16px;">
                                                                {{ strtoupper(substr($winner->user->name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle" style="display: {{ $winner->user->is_online ? 'block' : 'none' }};"></span>
                                                    </div>
                                                    <div class="flex-grow-1" style="overflow-wrap: break-word; word-break: break-word;">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <strong class="text-white" style="font-size: 0.9rem;">{{ Str::limit($winner->user->unique_id, 20) }}</strong>
                                                            <small class="text-light opacity-75" style="font-size: 0.75rem;">{{ $winner->won_at->diffForHumans() }}</small>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                                            <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }} rounded-pill px-2 py-1" style="font-size: 0.8rem;">
                                                                @if($winner->pattern == 'corner') Four Corner
                                                                @elseif($winner->pattern == 'top_line') Top Line
                                                                @elseif($winner->pattern == 'middle_line') Middle Line
                                                                @elseif($winner->pattern == 'bottom_line') Bottom Line
                                                                @elseif($winner->pattern == 'full_house') Full House
                                                                @endif
                                                            </span>
                                                            <span class="badge bg-success rounded-pill px-2 py-1 text-white" style="font-size: 0.8rem;">
                                                                {{ $winner->prize_amount }} Credit
                                                            </span>
                                                        </div>
                                                        @if($loop->first)
                                                            <div class="mt-2">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <small class="text-light" style="font-size: 0.75rem;">Credit Transfer Progress</small>
                                                                    <small class="text-light" style="font-size: 0.75rem;" x-text="typeof transferProgress === 'number' ? transferProgress + '%' : '100%'"></small>
                                                                </div>
                                                                <div class="progress rounded-pill" style="height: 8px;">
                                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
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
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-3 pb-3">
                            <button wire:click="$set('winnerAllart', false)" class="btn btn-light w-100 rounded-pill py-2 fw-semibold">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
            document.addEventListener('winnerAllartMakeFalse', () => {
                setTimeout(() => {
                    @this.call('winnerAllartMakeFalseMethod');
                    @this.call('manageNotification');
                }, 10000); // ৫ সেকেন্ড বিলম্ব
            });

            document.addEventListener('openGameoverModal', () => {
                setTimeout(() => {
                    @this.call('oprenGameoverModalAfterdelay');
                }, 19000); // ৫ সেকেন্ড বিলম্ব
            });
    </script>

    {{-- <!-- Game Over Modal -->
    @if ($gameOverAllart)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="background: linear-gradient(135deg, #7f0d00 0%, #2c3e50 100%);">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white">Game Over</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-4">
                            @if($games_Id && isset($winners))
                                <div class="col-12">
                                    <div class="list-group">
                                        @foreach($winners->take(5) as $winner)
                                            <a class="list-group-item list-group-item-action d-flex align-items-start gap-3 mb-3">
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

                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>{{ $winner->user->name }}</strong>
                                                        <small class="text-muted">{{ $winner->won_at->diffForHumans() }}</small>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }}">
                                                            @if($winner->pattern == 'corner') Four Corner
                                                            @elseif($winner->pattern == 'top_line') Top line
                                                            @elseif($winner->pattern == 'middle_line') Middle line
                                                            @elseif($winner->pattern == 'bottom_line') Bottom line
                                                            @elseif($winner->pattern == 'full_house') Full house
                                                            @endif
                                                        </span>
                                                        <span class="badge bg-primary rounded-pill text-white">
                                                            {{ $winner->prize_amount }} Credit
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="row g-3 mb-4">
                            <h6 class="text-white">Try your luck again by purchasing a sheet for the upcoming event!</h6>
                            <a href="{{route('buy_ticket')}}" class="btn btn-sm btn-round btn-primary">Buy now</a>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button wire:click="$set('gameOverAllart', false)" class="btn btn-primary">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif --}}

    <!-- Game Over Modal -->
    @if ($gameOverAllart)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg rounded-3" style="background: linear-gradient(135deg, #1e3a8a 0%, #6b21a8 100%); overflow: hidden;">
                    <div class="modal-header border-0 px-3 pt-3 pb-2">
                        <h5 class="modal-title text-white fw-bold">🎮 Game Over</h5>
                    </div>
                    <div class="modal-body p-3">
                        <div class="row g-2 mb-3">
                            @if($games_Id && isset($winners))
                                <div class="col-12">
                                    <div class="list-group">
                                        @foreach($winners as $winner)
                                            <div class="list-group-item list-group-item-action d-flex align-items-center gap-2 mb-2 rounded-3 shadow-sm p-2" style="background: rgba(255,255,255,0.1); transition: transform 0.2s; cursor: pointer; overflow: hidden;" @mouseover="this.style.transform='scale(1.02)'" @mouseout="this.style.transform='scale(1)'">
                                                <div class="position-relative flex-shrink-0">
                                                    @if($winner->user->avatar)
                                                        <img src="{{ $winner->user->avatar }}" class="rounded-circle" width="36" height="36" style="object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 16px;">
                                                            {{ strtoupper(substr($winner->user->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <span class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle" style="display: {{ $winner->user->is_online ? 'block' : 'none' }};"></span>
                                                </div>
                                                <div class="flex-grow-1" style="overflow-wrap: break-word; word-break: break-word;">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="text-white" style="font-size: 0.9rem;">{{ Str::limit($winner->user->unique_id, 20) }}</strong>
                                                        <small class="text-light opacity-75" style="font-size: 0.75rem;">{{ $winner->won_at->diffForHumans() }}</small>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                                        <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }} rounded-pill px-2 py-1" style="font-size: 0.8rem;">
                                                            @if($winner->pattern == 'corner') Four Corner
                                                            @elseif($winner->pattern == 'top_line') Top Line
                                                            @elseif($winner->pattern == 'middle_line') Middle Line
                                                            @elseif($winner->pattern == 'bottom_line') Bottom Line
                                                            @elseif($winner->pattern == 'full_house') Full House
                                                            @endif
                                                        </span>
                                                        <span class="badge bg-success rounded-pill px-2 py-1 text-white" style="font-size: 0.8rem;">
                                                            {{ $winner->prize_amount }} Credit
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="row g-2 text-center">
                            <h6 class="text-white mb-2" style="font-size: 0.9rem;">Try your luck again by purchasing a sheet for the upcoming event!</h6>
                            <a href="{{ route('buy_ticket') }}" class="btn btn-success w-100 rounded-pill py-2 fw-semibold shadow-sm" style="transition: transform 0.2s;" @mouseover="this.style.transform='scale(1.05)'" @mouseout="this.style.transform='scale(1)'">Buy Now</a>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-3 pb-3">
                        <button wire:click="$set('gameOverAllart', false)" class="btn btn-light w-100 rounded-pill py-2 fw-semibold">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    </div>

    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @endsection
</div>

<main>
    @section('title')
        <title>Admin | Number Announcer</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            /* Optimized and organized CSS */
            .game-stats-card .card-icon {
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                font-size: 1.5rem;
            }

            .participant-chip {
                transition: all 0.3s ease;
                cursor: pointer;
                border-radius: 20px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            .participant-chip:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            }

            .participant-chip.active {
                border: 2px solid #0d6efd;
                background: linear-gradient(45deg, #e6f0ff, #d0e2ff);
            }

            .number-grid {
                display: grid;
                grid-template-columns: repeat(10, 1fr);
                gap: 8px;
                max-height: 450px;
                overflow-y: auto;
            }

            .number-badge {
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                font-weight: 700;
                transition: all 0.3s ease;
                position: relative;
            }

            .number-badge.called {
                background: linear-gradient(135deg, #0d6efd, #0b5ed7);
                color: white;
                transform: scale(1.05);
                box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
            }

            .number-badge.new-called {
                animation: pulse 1.5s infinite;
                background: linear-gradient(135deg, #ffc107, #fd7e14);
                color: #000;
                box-shadow: 0 0 15px rgba(255, 193, 7, 0.6);
            }

            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }

            .ticket-card {
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                transition: all 0.3s ease;
            }

            .ticket-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
            }

            .ticket-card.winner {
                animation: winner-glow 2s infinite;
                border: 2px solid #28a745;
            }

            @keyframes winner-glow {
                0% { box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); }
                50% { box-shadow: 0 0 20px rgba(40, 167, 69, 0.8); }
                100% { box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); }
            }

            .ticket-table {
                table-layout: fixed;
                border-collapse: separate;
                border-spacing: 2px;
            }

            .ticket-table td {
                height: 35px;
                text-align: center;
                vertical-align: middle;
                position: relative;
                background-color: #f8f9fa;
                border-radius: 4px;
            }

            .ticket-table .called {
                background: linear-gradient(135deg, #28a745, #218838);
                color: white;
                font-weight: bold;
            }

            .winner-badge {
                position: absolute;
                top: -10px;
                right: -10px;
                z-index: 10;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                background: #ffc107;
                color: #000;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }

            .action-btn {
                border-radius: 30px;
                padding: 8px 20px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .announcer-section {
                background: linear-gradient(135deg, #f8f9fa, #e9ecef);
                border-radius: 10px;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            }

            .winner-modal {
                background: linear-gradient(135deg, #7f0d00 0%, #2c3e50 100%);
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            }

            .game-over-stamp {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-15deg);
                font-size: 3rem;
                font-weight: 800;
                color: #b31b1b;
                text-transform: uppercase;
                opacity: 0.7;
                pointer-events: none;
                z-index: 5;
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

    <main class="page-content">
        <!-- Enhanced Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card game-stats-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-gradient-purple text-white me-3">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted small">Total Participants</p>
                                <h4 class="mb-0">{{ $totalParticipants }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card game-stats-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-gradient-success text-white me-3">
                                <i class="bi bi-ticket-perforated"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted small">Total Sheets Sold</p>
                                <h4 class="mb-0">{{ $totalSheetsSold }}</h4>
                                <p class="mb-0 text-muted small">@ {{ $game->ticket_price }} each</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card game-stats-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-gradient-danger text-white me-3">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted small">Total Sales</p>
                                <h4 class="mb-0">{{ number_format($totalSalesAmount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card game-stats-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-gradient-info text-white me-3">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-muted small">Total Prize Pool</p>
                                <h4 class="mb-0">{{ number_format($totalPrizeAmount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row g-3 mb-4">
            <div class="col-auto">
                <button wire:click='redirectAllPlayers' class="btn btn-danger action-btn">
                    <i class="bi bi-arrow-right-circle me-2"></i>Redirect All Players
                </button>
            </div>
            <div class="col-auto">
                <button wire:click='broadcastNotice' class="btn btn-primary action-btn">
                    <i class="bi bi-broadcast me-2"></i>Broadcast Notice
                </button>
            </div>
        </div>

        <!-- Participants Section -->
        <div class="card border-0 shadow-sm radius-10 mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <h5 class="mb-0 text-primary fw-bold">Participant Users</h5>
                    <div class="ms-auto position-relative">
                        <div class="position-absolute top-50 translate-middle-y search-icon px-3">
                            <i class="bi bi-search"></i>
                        </div>
                        <input class="form-control ps-5" wire:model.live='search' type="text" placeholder="Search participants...">
                    </div>
                </div>

                <hr>

                <div class="d-flex flex-wrap gap-3">
                    @forelse ($newParticipantsUser as $participant)
                        <div class="participant-chip p-3 d-flex align-items-center"
                             wire:click='setUserSheet({{$participant->id}})'
                             :class="{'active': {{ $selectedParticipantId == $participant->id ? 'true' : 'false' }}}">
                            <img src="{{asset('assets/backend/upload/image/user/user.jpg')}}"
                                 class="rounded-circle me-2"
                                 width="40"
                                 height="40"
                                 alt="User">
                            <div>
                                <strong>ID : {{$participant->unique_id}}</strong>
                                <div class="d-flex align-items-center">
                                    <strong>{{ $participant->name ?? 'Unknown User' }}</strong>
                                    @if($participant->is_online)
                                        <span class="badge bg-success ms-2">Online</span>
                                    @endif
                                </div>
                                <small class="text-muted d-block">{{ $participant->last_login_location ?? 'Unknown location' }}</small>

                                @php
                                    $userWins = $winners->where('user_id', $participant->user_id)->count();
                                @endphp
                                @if($userWins > 0)
                                    <span class="badge bg-warning mt-1">
                                        <i class="bi bi-trophy me-1"></i> {{ $userWins }} Wins
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-people display-4 text-muted"></i>
                            <p class="mt-3 mb-0">No participants found</p>
                        </div>
                    @endforelse
                </div>

                @if ($newParticipantsUser->hasPages())
                    <div class="mt-4">
                        {{ $newParticipantsUser->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Game Control Section -->
        <div class="row">
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm radius-10 mb-4 announcer-section">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h5 class="mb-0">Announce Number for <span class="text-primary">{{ $game->title }}</span></h5>

                            <button wire:click="callNextNumber"
                                    class="btn btn-outline-primary action-btn"
                                    :disabled="$gameOver">
                                <i class="bi bi-shuffle me-2"></i> Call Random Number
                            </button>
                        </div>

                        <hr>

                        <form wire:submit.prevent="announceNumber">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <select wire:model="selectedNumber"
                                            class="form-select form-select-lg"
                                            :disabled="$gameOver">
                                        <option value="">Select Number (1-90)</option>
                                        @for ($i = 1; $i <= 90; $i++)
                                            <option value="{{ $i }}"
                                                @if(in_array($i, $calledNumbers)) disabled style="color: #ccc;" @endif>
                                                {{ $i }} @if(in_array($i, $calledNumbers)) (Called) @endif
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit"
                                            class="btn btn-primary btn-md w-100 action-btn"
                                            :disabled="$gameOver || !$selectedNumber">
                                        <i class="bi bi-megaphone me-2"></i> Announce
                                    </button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                Announced Numbers
                                <span class="badge bg-primary rounded-pill">{{ count($calledNumbers) }}/90</span>
                            </h5>
                            <small class="text-muted">Latest: {{ end($calledNumbers) ?: 'None' }}</small>
                        </div>

                        <div class="position-relative p-4 bg-white rounded-3 shadow-sm gameOver-container">
                            @if ($gameOver)
                                <div class="gameOver-text">Game Over</div>
                            @endif

                            <div class="number-grid">
                                @for ($i = 1; $i <= 90; $i++)
                                    <div class="number-badge {{ in_array($i, $calledNumbers) ? 'called' : '' }}
                                         {{ $i == end($calledNumbers) ? 'new-called' : '' }}">
                                        {{ $i }}
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Preview Section -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm radius-10">
                    <div class="card-body">
                        <h5 class="mb-4">Ticket Preview</h5>

                        @if ($sheetTickets)
                            <div class="sheet-container">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">
                                                <i class="bi bi-ticket-perforated me-1"></i>
                                                {{ $sheetTickets[0]['game']['title'] }}
                                            </span>
                                            <span class="badge bg-primary">
                                                Sheet ID: {{ $sheetTickets[0]['sheet_id'] ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="card-body p-3">
                                        @foreach($sheetTickets as $ticket)
                                            <div class="ticket-card mb-4 {{ $ticket['is_winner'] ? 'winner' : '' }}">
                                                <div class="card border-0">
                                                    @if($ticket['is_winner'])
                                                        <div class="winner-badge">
                                                            <i class="bi bi-trophy"></i>
                                                        </div>
                                                    @endif

                                                    <div class="card-body p-2">
                                                        <table class="table table-bordered mb-0 ticket-table">
                                                            <tbody>
                                                                @foreach($ticket['numbers'] as $rowIndex => $row)
                                                                    <tr>
                                                                        @foreach($row as $colIndex => $cell)
                                                                            <td class="{{ $cell && in_array($cell, $announcedNumbers) ? 'called' : '' }}">
                                                                                @if($cell)
                                                                                    {{ $cell }}
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
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-ticket-perforated display-4 text-muted"></i>
                                <p class="mt-3 mb-0">Select a participant to view ticket</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Winners Section -->
        <div class="card border-0 shadow-sm radius-10">
            <div class="card-body">
                <h5 class="mb-4">Recent Winners</h5>

                @if(count($winners) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Player</th>
                                    <th>Ticket</th>
                                    <th>Pattern</th>
                                    <th>Prize</th>
                                    <th>Won At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($winners->take(5) as $winner)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $winner->user->avatar ?? asset('assets/backend/upload/image/user/user.jpg') }}"
                                                    class="rounded-circle me-2"
                                                    width="36"
                                                    height="36"
                                                    alt="Winner">
                                                <span>{{ $winner->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>#{{ $winner->ticket->ticket_number }}</td>
                                        <td>
                                            @php
                                                $patternNames = [
                                                    'corner' => 'Four Corners',
                                                    'top_line' => 'Top Line',
                                                    'middle_line' => 'Middle Line',
                                                    'bottom_line' => 'Bottom Line',
                                                    'full_house' => 'Full House'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }}">
                                                {{ $patternNames[$winner->pattern] ?? ucfirst($winner->pattern) }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ number_format($winner->prize_amount, 2) }}</td>
                                        <td>{{ $winner->won_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-emoji-frown display-4 text-muted"></i>
                        <p class="mt-3 mb-0">No winners yet</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Modals -->
    @if($showNumberModal)
        <!-- Number Announcement Modal -->
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 overflow-hidden">
                    <div class="modal-body text-center p-5">
                        <h3 class="mb-4 text-primary">Announcing Number</h3>
                        <div class="number-display d-flex justify-content-center align-items-center my-4">
                            <div class="d-flex justify-content-center align-items-center bg-primary text-white rounded-circle"
                                 style="width: 150px; height: 150px;">
                                <h1 class="display-1 fw-bold">{{ $currentAnnouncedNumber }}</h1>
                            </div>
                        </div>
                        <div class="progress mt-4" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar"
                                 style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($winnerAllart)
        <!-- Winner Announcement Modal -->
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.7)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content winner-modal">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white">🎉 Winner Announcement 🎉</h5>
                    </div>
                    <div class="modal-body">
                        @foreach($winners->take(3) as $winner)
                            <div class="card border-0 bg-transparent text-white mb-3">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            <img src="{{ $winner->user->avatar ?? asset('assets/backend/upload/image/user/user.jpg') }}"
                                                class="rounded-circle border border-3 border-warning"
                                                width="60"
                                                height="60"
                                                alt="Winner">
                                            <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1"></span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1">{{ $winner->user->name }}</h5>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-warning text-dark me-2">
                                                    <i class="bi bi-ticket-perforated me-1"></i> #{{ $winner->ticket->ticket_number }}
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-currency-dollar me-1"></i> {{ number_format($winner->prize_amount, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <span class="badge bg-{{ $this->getPatternColor($winner->pattern) }}">
                                                {{ $patternNames[$winner->pattern] ?? ucfirst($winner->pattern) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button class="btn btn-light px-4 py-2 rounded-pill" wire:click="$set('winnerAllart', false)">
                            Continue Game
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($gameOverAllart)
        <!-- Game Over Modal -->
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.8)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-center border-0 overflow-hidden">
                    <div class="modal-header border-0 justify-content-center">
                        <h1 class="modal-title text-danger fw-bold display-4">GAME OVER</h1>
                    </div>
                    <div class="modal-body py-5 position-relative">
                        <div class="position-absolute top-50 start-50 translate-middle opacity-25">
                            <i class="bi bi-trophy display-1 text-warning"></i>
                        </div>

                        <div class="position-relative z-1">
                            <h3 class="text-white mb-4">Congratulations to Our Winners!</h3>
                            <div class="row justify-content-center">
                                @foreach($winners->take(3) as $winner)
                                    <div class="col-md-4 mb-4">
                                        <div class="card border-warning bg-transparent text-white">
                                            <div class="card-body">
                                                <div class="position-relative mb-3">
                                                    <img src="{{ $winner->user->avatar ?? asset('assets/backend/upload/image/user/user.jpg') }}"
                                                        class="rounded-circle border border-3 border-warning mx-auto"
                                                        width="80"
                                                        height="80"
                                                        alt="Winner">
                                                </div>
                                                <h5 class="mb-1">{{ $winner->user->name }}</h5>
                                                <div class="d-flex justify-content-center">
                                                    <span class="badge bg-warning text-dark me-2">
                                                        {{ number_format($winner->prize_amount, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-5 pt-3">
                                <h4 class="text-light mb-4">Join Our Next Game!</h4>
                                <a href="{{route('buy_ticket')}}" class="btn btn-warning btn-lg px-5 py-3 rounded-pill fw-bold">
                                    <i class="bi bi-ticket-perforated me-2"></i> Buy Tickets Now
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button class="btn btn-outline-light px-4 py-2" wire:click="$set('gameOverAllart', false)">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($b_notice)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.7)">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content border-0 overflow-hidden" style="box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    <!-- Modal Header -->
                    <div class="modal-header bg-warning text-dark py-4 position-relative">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-warning" style="opacity: 0.1;"></div>
                        <div class="position-relative z-1 w-100 text-center">
                            <h3 class="modal-title fw-bold mb-2">
                                <i class="bi bi-broadcast-pin me-2"></i>
                                Broadcast Notice to Players
                            </h3>
                            <p class="mb-0 text-dark opacity-75">Message will be delivered to all players in the game room</p>
                        </div>
                        <button type="button" class="btn-close position-absolute top-15 end-15 z-1" wire:click="$set('b_notice', false)" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body py-4 px-4 px-md-5">
                        <div class="row g-4">
                            <!-- Title Input -->
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text"
                                        class="form-control border-2 border-warning bg-light"
                                        id="title"
                                        wire:model="ntitle"
                                        placeholder="Enter notice title"
                                        style="height: 60px; font-size: 1.1rem;">
                                    <label for="title" class="text-muted">
                                        <i class="bi bi-card-heading me-2"></i>Notice Title
                                    </label>
                                </div>
                                <div class="form-text text-end text-muted">
                                    {{ strlen($ntitle) }}/60 characters
                                </div>
                            </div>

                            <!-- Message Input -->
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control border-2 border-warning bg-light"
                                            id="body"
                                            wire:model="nbody"
                                            placeholder="Enter notice message"
                                            style="height: 150px; font-size: 1.1rem;"></textarea>
                                    <label for="body" class="text-muted">
                                        <i class="bi bi-chat-text me-2"></i>Message Content
                                    </label>
                                </div>
                                <div class="form-text text-end text-muted">
                                    {{ strlen($nbody) }}/250 characters
                                </div>
                            </div>

                            <!-- Preview Section -->
                            {{-- <div class="col-12 mt-3">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning bg-opacity-10 py-2">
                                        <h6 class="mb-0">
                                            <i class="bi bi-eye me-2"></i>Message Preview
                                        </h6>
                                    </div>
                                    <div class="card-body bg-light">
                                        <div class="d-flex mb-3">
                                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-megaphone fs-5"></i>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="mb-1">{{ $ntitle ? $ntitle : '[Notice Title]' }}</h5>
                                                <small class="text-muted">Game Admin • Just now</small>
                                            </div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <p class="mb-0">{{ $nbody ? $nbody : 'Your message will appear here...' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light justify-content-center py-3">
                        <button class="btn btn-lg btn-warning rounded-pill px-5 py-3 fw-bold shadow-sm" wire:click="broadcast">
                            <i class="bi bi-send-check me-2"></i>Broadcast Message
                        </button>
                        <button class="btn btn-lg btn-outline-secondary rounded-pill px-4 py-3" wire:click="$set('b_notice', false)">
                            <i class="bi bi-x-lg me-2"></i>Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            // Initialize Select2
            $(document).ready(function() {
                $('.pu-select').select2();
            });

            // Livewire event listeners
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('closeNumberModalAfterDelay', () => {
                    setTimeout(() => {
                        document.getElementById('numberSpinAnimation').style.display = 'none';
                        document.getElementById('numberDisplay').style.display = 'block';
                    }, 3000);

                    setTimeout(() => {
                        @this.set('showNumberModal', false);
                    }, 5000);
                });
            });
        </script>
    @endpush

    @section('JS')
        @include('livewire.layout.backend.inc.js')
        <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>

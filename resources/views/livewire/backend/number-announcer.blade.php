<main>
    @section('title')
        <title>Admin | Number Announcer</title>
    @endsection
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
            <style>
                .rounded-md {
                    border-radius: 0.375rem; /* 6px */
                }

                .modal {
                    transition: opacity 0.3s ease;
                }
                .fa-spinner {
                    color: #4a5568;
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
        <!-- Statistics Cards (same as before) -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Participants</p>
                                <h4 class="my-1">{{ $totalParticipants }}</h4>
                                <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> Unique Players</p>
                            </div>
                            <div class="widget-icon-large bg-gradient-purple text-white ms-auto">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Sheets Sold</p>
                                <h4 class="my-1">{{ $totalSheetsSold }}</h4>
                                <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> @ {{ $game->ticket_price }} each</p>
                            </div>
                            <div class="widget-icon-large bg-gradient-success text-white ms-auto">
                                <i class="bi bi-ticket-perforated"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Sales</p>
                                <h4 class="my-1">{{ number_format($totalSalesAmount, 2) }}</h4>
                                <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> Total Revenue</p>
                            </div>
                            <div class="widget-icon-large bg-gradient-danger text-white ms-auto">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Prize Pool</p>
                                <h4 class="my-1">{{ number_format($totalPrizeAmount, 2) }}</h4>
                                <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> To be distributed</p>
                            </div>
                            <div class="widget-icon-large bg-gradient-info text-white ms-auto">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="d-flex align-items-center mb-3">
                    <button wire:click='redirectAllPlayers' class="btn btn-sm btn-danger btn-round">Redirect All Players</button>
                </div>
            </div>
        </div>

        <div class="row">
        <div class="col-12 col-lg-12 col-xl-12 d-flex">
            <div class="card radius-10 w-100">
                <div class="card-body" style="position: relative;">
                    <div class="row row-cols-1 row-cols-lg-2 g-3 align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Announce Number for {{ $game->title }}</h5>
                        </div>
                        <div class="col">
                            <div class="d-flex align-items-center justify-content-sm-end gap-3 cursor-pointer">
                                <button wire:click="callNextNumber" class="btn btn-outline-primary">
                                    <i class="bi bi-shuffle"></i> Call Random Number
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-4">
                        <form wire:submit.prevent="announceNumber">
                            <div class="row gy-3">
                                <div class="col-md-6">
                                    <select wire:model="selectedNumber" id="number" class="form-control">
                                        <option value="">Select Number (1-90)</option>
                                        @for ($i = 1; $i <= 90; $i++)
                                            <option value="{{ $i }}"
                                                @if(in_array($i, $calledNumbers)) disabled style="color: #ccc;" @endif>
                                                {{ $i }} @if(in_array($i, $calledNumbers)) (Called) @endif
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-6 text-end d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-megaphone"></i> Announce Number
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <hr>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5 class="mt-4 mb-3 font-semibold">
                                Announced Numbers ({{ count($calledNumbers) }}/90)
                                <small class="text-muted">Latest: {{ end($calledNumbers) ?: 'None' }}</small>
                            </h5>
                            <div class="overflow-auto">
                                <div class="gameOver-container d-flex flex-wrap gap-2">
                                    @if ($gameOver==1)
                                        <div class="gameOver-text">Game Over</div>
                                    @endif
                                    @foreach($calledNumbers as $number)
                                        <span class="badge bg-primary rounded-pill p-2">{{ $number }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if (session()->has('success'))
                                <div class="alert border-0 bg-success alert-dismissible fade show py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-3 text-white"><i class="bi bi-check-circle-fill"></i></div>
                                        <div class="ms-3">
                                            <div class="text-white">{{ session('success') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session()->has('error'))
                                <div class="alert border-0 bg-danger alert-dismissible fade show py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-3 text-white"><i class="bi bi-x-circle-fill"></i></div>
                                        <div class="ms-3">
                                            <div class="text-white">{{ session('error') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(count($winners) > 0)
                                <div class="mt-3">
                                    <h5 class="mb-3">Recent Winners</h5>
                                    <div class="list-group">
                                        @foreach($winners->take(3) as $winner)
                                            <div class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $winner->user->avatar ?? asset('assets/backend/upload/image/user/user.jpg') }}"
                                                         class="rounded-circle me-3" width="40" height="40">
                                                    <div>
                                                        <strong>{{ $winner->user->name }}</strong>
                                                        <div class="text-muted small">Ticket #{{ $winner->ticket_number }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div wire:poll.6s>
                <div class="col-12 col-lg-9 col-xl-9 d-flex">
                    <div class="card radius-10 w-100">
                        <div class="card-body">
                            <!-- Number Announcer Section (same as before) -->
                            <!-- ... existing number announcer UI ... -->
                            @php
                                $winners1 = App\Models\Winner::with(['user', 'ticket'])
                                        ->where('game_id', $gameId)
                                        ->orderByDesc('won_at')
                                        ->get();
                            @endphp
                            <!-- Updated Winners Display -->
                            @if(count($winners1) > 0)
                                <div class="mt-4">
                                    <h5 class="mb-3">Recent Winners by Pattern</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Player</th>
                                                    <th>Ticket</th>
                                                    <th>Pattern</th>
                                                    <th>Won At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($winners1->take(5) as $winner)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $winner->user->avatar ?? asset('assets/backend/upload/image/user/user.jpg') }}"
                                                                    class="rounded-circle me-2" width="30" height="30">
                                                                {{ $winner->user->name }}
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
                                                        <td>{{ $winner->won_at->diffForHumans() }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div wire:poll.6s>
                @php
                $totalParticipants =App\Models\Ticket::where('game_id', $this->gameId)
                                    ->distinct('user_id')
                                    ->count('user_id');
                @endphp
                <div class="col-12 col-lg-3 col-xl-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-9">
                                    <h5 class="mb-0">Participants ({{ $totalParticipants }})</h5>
                                </div>
                                <div class="col-3">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-horizontal-rounded font-22 text-option"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Export List</a></li>
                                            <li><a class="dropdown-item" href="#">Print</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="client-message ps" style="max-height: 600px; overflow-y: auto;">
                            @foreach($participants as $participant)
                                <div class="d-flex align-items-center gap-3 client-messages-list border-bottom p-3">
                                    <img src="{{ $participant->user->avatar ?? asset('assets/backend/upload/image/user/user.jpg') }}"
                                        class="rounded-circle" width="45" height="45" alt="{{ $participant->user->name }}">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 d-flex justify-content-between">
                                            {{ $participant->user->name }}
                                            <span class="badge bg-primary">{{ $participant->ticket_count }} tickets</span>
                                        </h6>
                                        <p class="mb-0 text-muted small">
                                            Joined {{$participant->created_at ? $participant->created_at->diffForHumans() : 'Just now' }}
                                        </p>
                                    </div>
                                    @php
                                        $userWins = $winners->where('user_id', $participant->user_id)->count();
                                    @endphp
                                    @if($userWins > 0)
                                        <span class="badge bg-success" title="{{ $userWins }} wins">
                                            <i class="bi bi-trophy"></i> {{ $userWins }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- number-announcer.blade.php -->
        <div>
            <!-- Your existing content -->

            <!-- Number Announcement Modal -->
            @if($showNumberModal)
                <div class="modal fade show d-block" tabindex="-1" aria-labelledby="numberModalLabel" aria-hidden="true" style="background: rgba(0,0,0,0.5);">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center py-5">
                                <div id="numberSpinAnimation" style="font-size: 5rem; height: 120px;">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </div>
                                <div id="numberDisplay" style="font-size: 5rem; display: none;">
                                    {{ $currentAnnouncedNumber }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($ridirectAllart)
                <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-success">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Users Redirect To Game Room Successful</h5>
                            </div>
                            <div class="modal-body text-center">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0 fs-5">The users who have purchased the game sheet and logged in are being redirected to the game room.</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button class="btn btn-success" wire:click="$set('ridirectAllart', false)">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Winner Modal -->
            @if ($winnerAllart)
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
                                <h5 class="modal-title text-white">Winner Announcement</h5>
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
                                                            @if($loop->first)
                                                                <div class="mt-2">
                                                                    <div class="d-flex justify-content-between mb-1">
                                                                        <small>Credit Transfer Progress</small>
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
                                <button wire:click="$set('winnerAllart', false)" class="btn btn-primary">Close</button>
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

            <!-- Game Over Modal -->
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
            @endif

            @push('scripts')
                <script>
                    document.addEventListener('livewire:initialized', () => {
                        Livewire.on('closeNumberModalAfterDelay', () => {
                            // Show spin for 6 seconds
                            setTimeout(() => {
                                document.getElementById('numberSpinAnimation').style.display = 'none';
                                document.getElementById('numberDisplay').style.display = 'block';
                            }, 6000);

                            // Close modal after total 9 seconds
                            setTimeout(() => {
                                @this.set('showNumberModal', false);
                            }, 9000);
                        });
                    });
                </script>
            @endpush
        </div>
    </main>

    @push('scripts')
        <script>
            // Listen for number announced events
            document.addEventListener('DOMContentLoaded', function() {
                window.Echo.channel('game.' + {{ $gameId }})
                    .listen('NumberAnnounced', (data) => {
                        // Refresh the component when a new number is announced
                        Livewire.dispatch('numberAnnounced', { number: data.number });
                    });
            });
        </script>
    @endpush



    @section('JS')
         @include('livewire.layout.backend.inc.js')
         <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>


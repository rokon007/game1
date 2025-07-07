<main>
    @section('title')
        <title>Admin | Number Announcer</title>
    @endsection
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
              <!-- Select2 CSS -->
                <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

                <!-- Select2 JS -->
                <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

                .pu-select option {
                    padding: 10px;
                    margin: 2px 0;
                    border-radius: 5px;
                }
                .pu-select option:hover {
                    background-color: #f8f9fa;
                }
                .pu-select:focus {
                    border-color: #86b7fe;
                    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                }
                .select2-container--default .select2-selection--single {
                    height: auto;
                    padding: 0.5rem;
                }
                .select2-container--default .select2-selection--single .select2-selection__rendered {
                    line-height: normal;
                }
                .select2-container--default .select2-selection__arrow {
                    height: 100%;
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
        <style>
            .chip2 {
                display: inline-block;
                height: 64px;
                padding: 0 12px;
                margin-right: 1rem;
                margin-bottom: 1rem;
                font-size: 14px;
                font-weight: 500;
                line-height: 32px;
                color: rgba(0, 0, 0, .7);
                cursor: pointer;
                background-color: #f1f1f1;
                border: 1px solid rgba(0, 0, 0, .15);
                border-radius: 16px;
                -webkit-transition: all .3s linear;
                transition: all .3s linear;
                box-shadow: none;
                }
                .chip2 img {
                float: left;
                width: 32px;
                height: 32px;
                margin: 0 8px 0 -12px;
                border-radius: 50%;
                }

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

        <div class="card radius-10 w-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                      <h5 class="mb-0 text-primary fw-bold">Participant Users</h5>
                       <div class="ms-auto position-relative">
                         <div class="position-absolute top-50 translate-middle-y search-icon px-3"><i class="bi bi-search"></i></div>
                         <input class="form-control ps-5" wire:model.live='search' type="text" placeholder="search">
                       </div>
                   </div>

                <hr>
                <div class="row row-cols-auto g-3">
                    @if (!empty($newParticipantsUser) && $newParticipantsUser->count())
                        @foreach ($newParticipantsUser as $participant)
                            <div class="chip2" wire:click='setUserSheet({{$participant->id}})' style="cursor: pointer">
                                <img src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="U">
                                <div class="list-inline d-sm-flex mb-0 d-none">
                                    <a  class="list-inline-item d-flex align-items-center text-secondary">
                                        @if($participant->is_online)
                                        <small class="bx bxs-circle me-1 chart-online"></small>
                                        @endif
                                        {{ $participant->name ?? 'Unknown User' }}
                                    </a>
                                </div>
                                <div class="list-inline d-sm-flex mb-0 d-none">
                                    <a  class="list-inline-item d-flex align-items-center text-secondary">
                                        <i class="bx bx-images me-1"></i>
                                        {{ $participant->last_login_location ?? 'Unknown location' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-3">
                            {{ $newParticipantsUser->links() }}
                        </div>
                    @else
                        <p>No participants found</p>
                    @endif
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

                            <div class="mt-3">
                                <h5 class="mb-3 text-primary fw-bold">{{$unique_id}}</h5>

                            </div>

                            <div class="mt-3">
                                @if ($sheetTickets)
                                    <div class="sheet-container card shadow-sm mb-0"> <!-- mb-0 যোগ করা হয়েছে -->
                                        <!-- হেডার কার্ড বডির ভিতরে নিয়ে আসা হয়েছে -->

                                        <div class="card-body py-2" style="background-color: #f8f9fa; border-top: 1px solid rgba(0,0,0,.125);"> <!-- border-top যোগ করা হয়েছে -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted fs-6">
                                                    <i class="fas fa-gamepad me-1"></i>
                                                    {{ $sheetTickets[0]['game']['title'] }}
                                                </span>
                                                <span class="badge bg-info text-white fs-6">
                                                    @foreach($sheetTickets as $ticket)
                                                        @if ($loop->first)
                                                            Sheet ID: {{ $ticket['sheet_id'] ?? 'N/A' }}
                                                        @endif
                                                    @endforeach
                                                </span>
                                            </div>
                                        </div>

                                        <!-- টিকেট কন্টেইনার -->
                                        <div class="card-body p-2 p-md-3" style="background-color: #f8f9fa;"> <!-- একই ব্যাকগ্রাউন্ড কালার -->
                                            <!-- Tickets Grid -->
                                            <div class="card-body p-3 p-md-4 bg-light">
                                                <div class="row">
                                                    @foreach($sheetTickets as $ticket)
                                                        <div class="col-12">
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
                                                                                        {{ $rowIndex === 0 && $this->hasWonPattern('top_line') ? 'completed1' : '' }}
                                                                                        {{ $rowIndex === 1 && $this->hasWonPattern('middle_line') ? 'completed1' : '' }}
                                                                                        {{ $rowIndex === 2 && $this->hasWonPattern('bottom_line') ? 'completed1' : '' }}">
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
                                @endif
                            </div>


                                {{-- <script>
                                    document.addEventListener('livewire:initialized', function () {
                                        $('.pu-select').select2({
                                            templateResult: function (data) {
                                                if (!data.id) {
                                                    return data.text;
                                                }
                                                var $participant = {!! json_encode($newParticipants->toArray()) !!};
                                                var participant = $participant.find(p => p.user_id == data.id);

                                                if (!participant) {
                                                    return data.text;
                                                }

                                                var $template = $(`
                                                    <div class="d-flex align-items-center gap-3">
                                                        <img src="${participant.user.avatar || '{{ asset('assets/backend/upload/image/user/user.jpg') }}'}"
                                                            class="rounded-circle border border-2 border-primary"
                                                            width="40"
                                                            height="40"
                                                            alt="name"
                                                            style="object-fit: cover;">
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="fw-semibold">${participant.user.name || 'name'}</span>
                                                                <p>${participant.user.last_login_location || 'Unknown'}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `);
                                                return $template;
                                            },
                                            templateSelection: function (data) {
                                                if (!data.id) {
                                                    return data.text;
                                                }
                                                var $participant = {!! json_encode($newParticipants->toArray()) !!};
                                                var participant = $participant.find(p => p.user_id == data.id);

                                                return participant ? participant.user.name : data.text;
                                            }
                                        });

                                        // লাইভওয়্যারের সাথে সিঙ্ক করা
                                        $('.pu-select').on('change', function (e) {
                                            @this.set('selectedUser', e.target.value);
                                        });

                                        // লাইভওয়্যার আপডেটের সময় Select2 রি-ইনিশিয়ালাইজ
                                        Livewire.on('updateSelect2', () => {
                                            $('.pu-select').select2('destroy').select2({
                                                templateResult: function (data) {
                                                    if (!data.id) {
                                                        return data.text;
                                                    }
                                                    var $participant = {!! json_encode($newParticipants->toArray()) !!};
                                                    var participant = $participant.find(p => p.user_id == data.id);

                                                    if (!participant) {
                                                        return data.text;
                                                    }

                                                    var $template = $(`
                                                        <div class="d-flex align-items-center gap-3">
                                                            <img src="${participant.user.avatar || '{{ asset('assets/backend/upload/image/user/user.jpg') }}'}"
                                                                class="rounded-circle border border-2 border-primary"
                                                                width="40"
                                                                height="40"
                                                                alt="name"
                                                                style="object-fit: cover;">
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="fw-semibold">${participant.user.name || 'name'}</span>
                                                                    <p>${participant.user.last_login_location || 'Unknown'}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    `);
                                                    return $template;
                                                },
                                                templateSelection: function (data) {
                                                    if (!data.id) {
                                                        return data.text;
                                                    }
                                                    var $participant = {!! json_encode($newParticipants->toArray()) !!};
                                                    var participant = $participant.find(p => p.user_id == data.id);

                                                    return participant ? participant.user.name : data.text;
                                                }
                                            });
                                        });
                                    });
                                </script> --}}


                            {{-- @if(count($winners) > 0)
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
                            @endif --}}
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
                                                @foreach($winners1 as $winner)
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
                        <div class="client-message ps" style="max-height: 600px; overflow-y: auto;" wire:ignore>
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


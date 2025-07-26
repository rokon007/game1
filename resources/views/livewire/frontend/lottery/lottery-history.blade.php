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
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom">
                            <h4 class="mb-0 text-center">Lottery History</h4>
                        </div>
                        <div class="card-body">
                            <!-- Navigation Tabs -->
                            <ul class="nav nav-tabs nav-justified mb-4">
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'my_tickets' ? 'active' : '' }}"
                                    href="#" wire:click.prevent="setActiveTab('my_tickets')">
                                        <i class="fas fa-ticket-alt me-1"></i> My Tickets
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'my_winnings' ? 'active' : '' }}"
                                    href="#" wire:click.prevent="setActiveTab('my_winnings')">
                                        <i class="fas fa-trophy me-1"></i> My Winnings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'all_results' ? 'active' : '' }}"
                                    href="#" wire:click.prevent="setActiveTab('all_results')">
                                        <i class="fas fa-list-ol me-1"></i> All Results
                                    </a>
                                </li>
                            </ul>

                            <!-- My Tickets Tab -->
                            @if($activeTab === 'my_tickets')
                                <div class="section-heading d-flex align-items-center pt-1 justify-content-between">
                                    <h6>My Lottery Tickets</h6>
                                    <span class="text-secondary">Total: {{ $myTickets->total() }}</span>
                                </div>

                                <div class="notification-wrapper" style="max-height: 500px; overflow-y: auto;">
                                    <div class="notification-area pb-2">
                                        <div class="list-group">
                                            @forelse($myTickets ?? [] as $ticket)
                                                <div class="list-group-item d-flex align-items-center border-3 py-3">
                                                    <div class="noti-info flex-grow-1">
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <h6 class="mb-1">{{ $ticket->lottery->name }}</h6>
                                                            <span class="badge bg-primary text-white">{{ $ticket->ticket_number }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                {{ $ticket->purchased_at->format('d M y') }}
                                                            </small>
                                                            <div>
                                                                @if($ticket->lottery->status === 'active')
                                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                                @elseif($ticket->lottery->status === 'completed')
                                                                    <span class="badge bg-success text-white">Completed</span>
                                                                @else
                                                                    <span class="badge bg-danger text-white">Cancelled</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        @php
                                                            $winning = $ticket->lottery->results->where('lottery_ticket_id', $ticket->id)->first();
                                                        @endphp
                                                        @if($winning)
                                                            <div class="mt-2 text-success">
                                                                <i class="fas fa-trophy me-1"></i>
                                                                Won {{ $winning->prize->position }} Prize ({{ number_format($winning->prize_amount, 2) }} Credit)
                                                            </div>
                                                        @elseif($ticket->lottery->status === 'completed')
                                                            <div class="mt-2 text-secondary">
                                                                <i class="fas fa-times-circle me-1"></i>
                                                                Not Won
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-4">
                                                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                                    <h5>No tickets found</h5>
                                                    <p class="text-muted">You haven't purchased any lottery tickets yet</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                @if(isset($myTickets) && $myTickets->hasPages())
                                    <div class="mt-3 d-flex justify-content-center">
                                        {{ $myTickets->links() }}
                                    </div>
                                @endif
                            @endif

                            <!-- My Winnings Tab -->
                            @if($activeTab === 'my_winnings')
                                <div class="row g-3">
                                    @forelse($myWinnings ?? [] as $winning)
                                        <div class="col-12">
                                            <div class="card border-success shadow-sm">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h5 class="mb-1 text-success">
                                                                <i class="fas fa-trophy me-2"></i>{{ $winning->prize->position }} Prize
                                                            </h5>
                                                            <p class="mb-1 small text-muted">{{ $winning->lottery->name }}</p>
                                                            <span class="badge bg-primary">
                                                                #{{ $winning->winning_ticket_number }}
                                                            </span>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="h5 text-success">{{ number_format($winning->prize_amount, 2) }} Credit</span>
                                                            <p class="mb-0 small text-muted">{{ $winning->drawn_at->format('d M y') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-info text-center">
                                                <i class="fas fa-info-circle me-2"></i> You haven't won any prizes yet.
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                @if(isset($myWinnings) && $myWinnings->hasPages())
                                    <div class="mt-3 d-flex justify-content-center">
                                        {{ $myWinnings->links() }}
                                    </div>
                                @endif
                            @endif

                            <!-- All Results Tab -->
                            @if($activeTab === 'all_results')
                                <div class="row g-3">
                                    @forelse($allResults ?? [] as $lottery)
                                        <div class="col-12">
                                            <div class="card shadow-sm">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h5 class="mb-1">{{ $lottery->name }}</h5>
                                                            <p class="mb-1 small text-muted">
                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                {{ $lottery->draw_date->format('d M y H:i') }}
                                                            </p>
                                                            <span class="badge bg-info">
                                                                {{ $lottery->results->count() }} Prizes
                                                            </span>
                                                        </div>
                                                        <button class="btn btn-sm btn-primary"
                                                                wire:click="viewLotteryDetails({{ $lottery->id }})">
                                                            View
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-info text-center">
                                                <i class="fas fa-info-circle me-2"></i> No completed lotteries found.
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                @if(isset($allResults) && $allResults->hasPages())
                                    <div class="mt-3 d-flex justify-content-center">
                                        {{ $allResults->links() }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Details Modal -->
            @if($selectedLottery)
                <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" wire:ignore.self>
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $selectedLottery->name }} - Results</h5>
                                <button type="button" class="btn-close" wire:click="closeDetails"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-4 p-3 bg-light rounded">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <p class="mb-0"><strong>Draw Date:</strong></p>
                                            <p class="mb-0">{{ $selectedLottery->draw_date->format('d M y H:i') }}</p>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <p class="mb-0"><strong>Ticket Price:</strong></p>
                                            <p class="mb-0">{{ number_format($selectedLottery->price, 2) }} Credit</p>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mb-3">Winners:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Prize</th>
                                                <th>Ticket No.</th>
                                                <th>Winner</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedLottery->results->sortBy('prize.rank') as $result)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">{{ $result->prize->position }}</span>
                                                    </td>
                                                    <td>
                                                        <code>{{ $result->winning_ticket_number }}</code>
                                                    </td>
                                                    <td>{{ $result->user->unique_id }}</td>
                                                    <td class="text-success fw-bold">
                                                        {{ number_format($result->prize_amount, 2) }} Credit
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeDetails">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
    @endsection

    <style>
        /* Responsive container */
        @media (max-width: 576px) {
            .container {
                max-width: 380px !important;
                padding-left: 15px;
                padding-right: 15px;
            }

            .nav-tabs .nav-link {
                font-size: 12px;
                padding: 8px 5px;
            }
        }

        /* Notification style for tickets */
        .notification-wrapper {
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
        }

        .notification-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        .notification-wrapper::-webkit-scrollbar-track {
            background: #f8f9fa;
        }

        .notification-wrapper::-webkit-scrollbar-thumb {
            background-color: #dee2e6;
            border-radius: 6px;
        }

        .noti-icon {
            width: 40px;
            height: 40px;
            background-color: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .noti-info {
            flex-grow: 1;
        }

        .list-group-item {
            border-radius: 8px !important;
            margin-bottom: 8px;
            transition: all 0.2s;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        /* Custom enhancements */
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
            border: none;
            padding: 10px;
            position: relative;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background-color: transparent;
        }

        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #0d6efd;
        }

        .card {
            border-radius: 10px;
        }

        .badge {
            font-weight: 500;
            padding: 5px 8px;
        }
    </style>
</div>

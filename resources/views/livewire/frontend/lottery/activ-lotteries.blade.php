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
                            <h4 class="mb-0 text-center">Active Lotteries</h4>
                        </div>
                        <div class="card-body">
                            <div class="section-heading d-flex align-items-center pt-1 justify-content-between">
                                <h6>My Active Lottery Tickets</h6>
                                <span class="text-secondary">Total Lotteries: {{ count($groupedTickets) }}</span>
                            </div>

                            <div class="notification-wrapper" style="max-height: 500px; overflow-y: auto;">
                                <div class="notification-area pb-2">
                                    <div class="list-group">
                                        @forelse($groupedTickets as $lotteryId => $tickets)
                                            @php
                                                $lottery = $tickets->first()->lottery;
                                                $isExpanded = in_array($lotteryId, $expandedLotteries);
                                            @endphp

                                            <!-- Lottery Group Header -->
                                            <div class="list-group-item border-3 py-3" wire:click="toggleLottery({{ $lotteryId }})" style="cursor: pointer;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">{{ $lottery->name }}</h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar-alt me-1"></i>
                                                            Draw Date: {{ $lottery->draw_date->format('d M y') }}
                                                        </small>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-primary text-white me-2">
                                                            {{ count($tickets) }} Tickets
                                                        </span>
                                                        <i class="fas fa-chevron-{{ $isExpanded ? 'up' : 'down' }}"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tickets List (shown when expanded) -->
                                            @if($isExpanded)
                                                @foreach($tickets as $ticket)
                                                    <div class="list-group-item border-3 py-3 pl-5" style="border-left: 3px solid #0d6efd;">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Ticket No:</span>
                                                            <span class="badge bg-primary text-white">{{ $ticket->ticket_number }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar-alt me-1"></i>
                                                                Purchased: {{ $ticket->created_at->format('d M y') }}
                                                            </small>
                                                            <div>
                                                                @if($ticket->lottery->status === 'active')
                                                                    <span class="badge bg-warning text-dark">Active</span>
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
                                                @endforeach
                                            @endif
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
                        </div>
                    </div>
                </div>
            </div>
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
        /* Previous styles remain the same */
        .list-group-item {
            transition: all 0.3s ease;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        .pl-5 {
            padding-left: 3rem !important;
        }
    </style>
</div>

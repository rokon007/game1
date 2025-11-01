<main>
    @section('title')
        <title>Admin - Crash Game Dashboard</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />

    @endsection

    <div wire:poll.100ms="getGames" class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
            <div class="breadcrumb-title pe-3">Crash Game Dashboard</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="#">Crash Game Dashboard</a></li>
                    </ol>
                </nav>
            </div>
            {{-- <div class="ms-auto">
                <a href="{{ route('admin.lottery.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bx bx-arrow-back"></i> ফিরে যান
                </a>
            </div> --}}
        </div>
        <!--end breadcrumb-->

        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form wire:submit.prevent="$refresh" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">শুরুর তারিখ</label>
                                    <input type="date" wire:model.live="dateFrom" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">শেষ তারিখ</label>
                                    <input type="date" wire:model.live="dateTo" class="form-control">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-2"></i>ফিল্টার করুন
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">মোট গেম</h6>
                                    <h3 class="mb-0">{{ number_format($stats['total_games']) }}</h3>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-gamepad"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">মোট বেট</h6>
                                    <h3 class="mb-0">৳{{ number_format($stats['total_bets'], 2) }}</h3>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">মোট পেআউট</h6>
                                    <h3 class="mb-0">৳{{ number_format($stats['total_payouts'], 2) }}</h3>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">হাউস প্রফিট</h6>
                                    <h3 class="mb-0">৳{{ number_format($stats['house_profit'], 2) }}</h3>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-trophy"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">হাউস এজ</h6>
                                    <h3 class="mb-0">{{ number_format($stats['house_edge'], 2) }}%</h3>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-percentage"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">গড় ক্র্যাশ পয়েন্ট</h6>
                                    <h3 class="mb-0">{{ number_format($stats['avg_crash_point'], 2) }}x</h3>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Total Bet Pool</h6>
                                    <h3 class="mb-0">৳{{ number_format($pool['total_bet_pool'], 2) }}</h3>
                                </div>
                                <div class="fs-1 opacity-50">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Games List -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>সাম্প্রতিক গেমসমূহ
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>গেম আইডি</th>
                                            <th>ক্র্যাশ পয়েন্ট</th>
                                            <th>খেলোয়াড়</th>
                                            <th>মোট বেট</th>
                                            <th>পেআউট</th>
                                            <th>প্রফিট</th>
                                            <th>কমিসন</th>
                                            <th>সময়</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($games as $game)
                                            @php
                                                $betStats = $game->bets->first();
                                                $totalBet = $betStats->total_bet ?? 0;
                                                $totalPayout = $betStats->total_payout ?? 0;
                                                $profit = $totalBet - $totalPayout;
                                                $commission=$game->admin_commission_amount;
                                            @endphp
                                            <tr>
                                                <td><span class="badge bg-secondary">#{{ $game->id }}</span></td>
                                                <td>
                                                    <span class="badge {{ $game->crash_point >= 2 ? 'bg-success' : 'bg-danger' }}">
                                                        {{ number_format($game->crash_point, 2) }}x
                                                    </span>
                                                </td>
                                                <td>{{ $betStats->bet_count ?? 0 }}</td>
                                                <td>৳{{ number_format($totalBet, 2) }}</td>
                                                <td>৳{{ number_format($totalPayout, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ $profit > 0 ? 'bg-success' : 'bg-danger' }}">
                                                        ৳{{ number_format($profit, 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-muted small">
                                                    ৳{{ number_format($commission, 2) }}
                                                </td>
                                                <td class="text-muted small">
                                                     {{ $game->created_at->format('d/m H:i') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    কোন গেম নেই
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($games->hasPages())
                            <div class="card-footer bg-white">
                                {{ $games->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Top Winners -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-crown me-2"></i>শীর্ষ বিজয়ী
                            </h6>
                        </div>
                        <div class="card-body p-2">
                            @forelse($topWinners as $index => $winner)
                                <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-warning text-dark me-2">#{{ $index + 1 }}</span>
                                        <span class="fw-semibold">{{ $winner->user->name }}</span>
                                    </div>
                                    <span class="badge bg-success">৳{{ number_format($winner->total_profit, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-center text-muted py-3 mb-0">কোন ডেটা নেই</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Recent Bets -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-history me-2"></i>সাম্প্রতিক বেট
                            </h6>
                        </div>
                        <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                            @forelse($recentBets as $bet)
                                <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                    <div>
                                        <div class="fw-semibold small">{{ $bet->user->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            Game #{{ $bet->game->id }}
                                            @if($bet->isWon())
                                                <span class="badge bg-success">Won</span>
                                            @elseif($bet->isLost())
                                                <span class="badge bg-danger">Lost</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold small">৳{{ number_format($bet->bet_amount, 2) }}</div>
                                        @if($bet->isWon())
                                            <div class="text-success" style="font-size: 0.75rem;">
                                                +৳{{ number_format($bet->profit, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-muted py-3 mb-0">কোন বেট নেই</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
@endpush

    </div>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
        <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>

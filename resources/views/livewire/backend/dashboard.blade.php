<main>
    @section('title')
        <title>Admin | Dashboard</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endsection

    <main class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Dashboard</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Overview</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-4">
            <!-- Balance Card -->
            <div class="col">
                <div wire:poll.500ms="getCredit" class="card radius-10 bg-primary bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-white">Account Balance</p>
                                <h4 class="mb-0 text-white">৳ {{ number_format($user_credit) }}</h4>
                            </div>
                            <div class="ms-auto fs-2 text-white">
                                <i class="bi bi-wallet2"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-3">
                            <button wire:click='addMony' class="btn btn-sm btn-light text-primary px-4 radius-10">
                                <i class="bi bi-plus-circle me-1"></i> Add Money
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pool Amount Card -->
            <div class="col">
                <div wire:poll.500ms="updatePoolAmount" class="card radius-10 bg-warning bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-1 text-black">Pool Amount</p>
                                <h4 class="mb-0 text-black">{{ number_format($poolAmount) }}</h4>
                            </div>
                            <div class="ms-auto fs-2 text-black">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height:4px;">
                            <div class="progress-bar bg-black" role="progressbar"
                                style="width: {{ $progressPercent }}%;">
                            </div>
                        </div>
                        <small class="text-black">{{ number_format($progressPercent, 2) }}% of Jackpot Limit ({{ number_format($jackpotLimit) }})</small>
                    </div>
                </div>
            </div>

            <!-- Total Users Card -->
            <div class="col">
                <div class="card radius-10 bg-info bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-white">Total Users</p>
                                <h4 class="mb-0 text-white">{{ $totalUsers }}</h4>
                            </div>
                            <div class="ms-auto fs-2 text-white">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height:4px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users Card -->
            <div class="col">
                <div class="card radius-10 bg-success bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-white">Active Users</p>
                                <h4 class="mb-0 text-white">{{ \App\Models\User::where('is_online', true)->count() }}</h4>
                            </div>
                            <div class="ms-auto fs-2 text-white">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height:4px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Card -->
            <div class="col">
                <div class="card radius-10 bg-danger bg-gradient">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-white">Transactions</p>
                                <h4 class="mb-0 text-white">{{ \App\Models\Transaction::count() }}</h4>
                            </div>
                            <div class="ms-auto fs-2 text-white">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height:4px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Add this to your admin dashboard --}}
        <div class="row">
            <div class="col-12 col-lg-12 col-xl-12">
                <div class="card radius-10">
                    <div class="card-header bg-transparent">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0">🎰 Lucky Spin Statistics (Last 24 Hours)</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            use App\Models\LuckySpin;
                            use App\Models\SystemPool;
                            use App\Models\SystemSetting;
                            use App\Models\Transaction;

                            // Get stats for last 24 hours
                            $yesterday = now()->subDay();

                            $stats = [
                                'total_spins' => LuckySpin::where('created_at', '>=', $yesterday)->count(),
                                'total_bet' => LuckySpin::where('created_at', '>=', $yesterday)->sum('bet_amount'),
                                'total_won' => LuckySpin::where('created_at', '>=', $yesterday)->sum('reward_amount'),
                                'wins' => LuckySpin::where('created_at', '>=', $yesterday)->where('result', 'win')->count(),
                                'jackpots' => LuckySpin::where('created_at', '>=', $yesterday)->where('result', 'jackpot')->count(),
                                'loses' => LuckySpin::where('created_at', '>=', $yesterday)->where('result', 'lose')->count(),
                            ];

                            $stats['win_rate'] = $stats['total_spins'] > 0
                                ? round(($stats['wins'] / $stats['total_spins']) * 100, 2)
                                : 0;

                            $stats['rtp'] = $stats['total_bet'] > 0
                                ? round(($stats['total_won'] / $stats['total_bet']) * 100, 2)
                                : 0;

                            $stats['house_profit'] = $stats['total_bet'] - $stats['total_won'];

                            // Admin commission earned
                            $stats['admin_commission'] = Transaction::where('user_id', 1)
                                ->where('type', 'credit')
                                ->where('details', 'like', '%Lucky Spin Commission%')
                                ->where('created_at', '>=', $yesterday)
                                ->sum('amount');

                            // Current pool
                            $pool = SystemPool::first();
                            $stats['current_pool'] = $pool ? $pool->total_collected : 0;

                            // Settings
                            $jackpot_limit = (int) SystemSetting::getValue('jackpot_limit', 100000);
                            $expected_win_rate = (int) SystemSetting::getValue('win_chance_percent', 20);

                            // Pool progress
                            $stats['pool_progress'] = $jackpot_limit > 0
                                ? round(($stats['current_pool'] / $jackpot_limit) * 100, 2)
                                : 0;

                            // Status indicators
                            $win_rate_status = abs($stats['win_rate'] - $expected_win_rate) <= 5 ? 'success' : 'warning';
                            $pool_status = $stats['pool_progress'] >= 100 ? 'danger' : ($stats['pool_progress'] >= 75 ? 'warning' : 'info');
                        @endphp

                        <div class="row g-3">
                            {{-- Total Spins --}}
                            <div class="col-md-3">
                                <div class="card border-0 bg-light-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-1 text-primary">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </div>
                                            <div class="ms-auto">
                                                <h4 class="mb-0 text-primary">{{ number_format($stats['total_spins']) }}</h4>
                                                <p class="mb-0 text-secondary">Total Spins</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Total Bet --}}
                            <div class="col-md-3">
                                <div class="card border-0 bg-light-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-1 text-info">
                                                <i class="bi bi-currency-dollar"></i>
                                            </div>
                                            <div class="ms-auto">
                                                <h4 class="mb-0 text-info">{{ number_format($stats['total_bet']) }}</h4>
                                                <p class="mb-0 text-secondary">Total Bet</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- House Profit --}}
                            <div class="col-md-3">
                                <div class="card border-0 bg-light-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-1 text-success">
                                                <i class="bi bi-graph-up-arrow"></i>
                                            </div>
                                            <div class="ms-auto">
                                                <h4 class="mb-0 text-success">{{ number_format($stats['house_profit']) }}</h4>
                                                <p class="mb-0 text-secondary">House Profit</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Admin Commission --}}
                            <div class="col-md-3">
                                <div class="card border-0 bg-light-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-1 text-warning">
                                                <i class="bi bi-cash-stack"></i>
                                            </div>
                                            <div class="ms-auto">
                                                <h4 class="mb-0 text-warning">{{ number_format($stats['admin_commission']) }}</h4>
                                                <p class="mb-0 text-secondary">Admin Commission</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Additional Stats Row --}}
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="mb-3">Win Distribution</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>🔴 Loses:</span>
                                            <strong>{{ number_format($stats['loses']) }} ({{ $stats['total_spins'] > 0 ? round(($stats['loses'] / $stats['total_spins']) * 100, 1) : 0 }}%)</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>🟢 Wins:</span>
                                            <strong>{{ number_format($stats['wins']) }} ({{ $stats['total_spins'] > 0 ? round(($stats['wins'] / $stats['total_spins']) * 100, 1) : 0 }}%)</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>👑 Jackpots:</span>
                                            <strong class="text-warning">{{ number_format($stats['jackpots']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="mb-3">Performance Metrics</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Win Rate:</span>
                                            <strong class="text-{{ $win_rate_status }}">{{ $stats['win_rate'] }}%
                                                <small class="text-muted">(Expected: {{ $expected_win_rate }}%)</small>
                                            </strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>RTP:</span>
                                            <strong>{{ $stats['rtp'] }}%</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>House Edge:</span>
                                            <strong>{{ 100 - $stats['rtp'] }}%</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Current Pool Status --}}
                        <div class="row g-3 mt-2">
                            <div class="col-12">
                                <div class="card border-{{ $pool_status }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">🏦 Current Pool Status</h6>
                                            <span class="badge bg-{{ $pool_status }}">{{ $stats['pool_progress'] }}% to Jackpot</span>
                                        </div>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-{{ $pool_status }} progress-bar-striped progress-bar-animated"
                                                role="progressbar"
                                                style="width: {{ min($stats['pool_progress'], 100) }}%"
                                                aria-valuenow="{{ $stats['pool_progress'] }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                                {{ number_format($stats['current_pool']) }} / {{ number_format($jackpot_limit) }} credits
                                            </div>
                                        </div>
                                        @if($stats['pool_progress'] >= 100)
                                            <div class="alert alert-danger mt-3 mb-0">
                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                <strong>Jackpot Active!</strong> Next spin has a chance to win the jackpot.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Actions --}}
                        <div class="row g-3 mt-2">
                            <div class="col-12">
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('admin.system_settings') }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-gear me-1"></i> Configure Settings
                                    </a>
                                    <a href="#" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-list me-1"></i> View All Spins
                                    </a>
                                    <button class="btn btn-sm btn-info" onclick="location.reload()">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh Stats
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Recharge Modal -->
        @if ($rechargeModal)
            <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow">
                        <form wire:submit.prevent='updateUser'>
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-wallet2 me-2"></i>Account Recharge
                                </h5>
                                <button type="button" class="btn-close btn-close-white" wire:click='closeRechargeModal'></button>
                            </div>
                            <div class="modal-body">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <input type="hidden" wire:model='rechargeUser_id'>

                                        <!-- Amount Input -->
                                        <div class="mb-3" style="display: {{$amountMode ? 'block' : 'none'}}">
                                            <label class="form-label">Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">৳</span>
                                                <input wire:model='amount' type="number" class="form-control" placeholder="Enter amount">
                                            </div>
                                            @error('amount')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <!-- Confirmation -->
                                        <div style="display: {{$confirmMode ? 'block' : 'none'}}">
                                            <div class="alert alert-success border-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-0 text-success">Recharge Amount: ৳{{ number_format($amount) }}</h6>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Enter Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                    <input wire:model='password' type="password" class="form-control" placeholder="Your password">
                                                </div>
                                                @error('password')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                @if ($amountMode)
                                    <button wire:click="rechargeNext" type="button" class="btn btn-primary">
                                        <i class="bi bi-arrow-right me-1"></i>Next
                                    </button>
                                @else
                                    <button wire:click="comfirm('{{ $rechargeUser_id }}')" type="button" class="btn btn-success">
                                        <span wire:loading.delay wire:target="comfirm" class="spinner-border spinner-border-sm me-1"></span>
                                        <i class="bi bi-check-circle me-1"></i>Confirm
                                    </button>
                                @endif
                                <button type="button" class="btn btn-secondary" wire:click='closeRechargeModal'>
                                    <i class="bi bi-x-circle me-1"></i>Close
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success Modal -->
        @if($transactionSuccess)
            <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                    <div class="modal-content border-0">
                        <div class="modal-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                            </div>
                            <h5 class="mb-3 text-success">Success!</h5>
                            <p class="mb-4">Transaction completed successfully</p>
                            <button class="btn btn-success px-4" wire:click="$set('transactionSuccess', false)">
                                <i class="bi bi-check-lg me-1"></i>OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Recent Activities -->
        <div class="card mt-4 radius-10">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Transaction::latest()->take(5)->get() as $transaction)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $transaction->user->avatar ?? asset('assets/images/avatars/avatar-1.png') }}"
                                             class="rounded-circle" width="35" height="35" alt="">
                                        <div class="ms-2">
                                            <h6 class="mb-0">{{ $transaction->user->name }}</h6>
                                            <small class="text-muted">{{ $transaction->user->mobile }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type == 'credit' ? 'success' : 'danger' }}-subtle text-{{ $transaction->type == 'credit' ? 'success' : 'danger' }} p-2 radius-30">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>৳{{ number_format($transaction->amount) }}</td>
                                <td>{{ $transaction->created_at->format('d M, Y') }}</td>
                                <td>
                                    <span class="badge bg-success p-2 radius-30">Completed</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
    @endsection
</main>

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
                <div class="card radius-10 bg-primary bg-gradient">
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

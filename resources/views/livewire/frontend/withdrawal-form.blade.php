<div>
    @section('meta_description')
        <meta name="title" content="Housieblitz Withdrawal">
        <meta name="description" content="Withdraw your winnings from Housieblitz securely and quickly.">
        <meta name="keywords" content="Housieblitz withdrawal, withdraw money, bKash withdrawal, Nagad withdrawal">
        <meta name="author" content="Housieblitz">
    @endsection

    @section('title')
        <title>Housieblitz | Withdrawal</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            .page-content-wrapper {
                background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
                min-height: 100vh;
            }
            .withdrawal-header {
                background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                color: white;
                border-radius: 15px;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }
            .instruction-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                border: none;
            }
            .method-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                padding: 1.5rem;
                margin-bottom: 1rem;
                border: none;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
            }
            .method-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
            .method-card.active {
                border: 2px solid #11998e;
                background: rgba(17, 153, 142, 0.05);
            }
            .form-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                border: none;
            }
            .status-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                padding: 1.5rem;
                margin-bottom: 1rem;
                border: none;
            }
            .btn-withdrawal {
                background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                border: none;
                border-radius: 50px;
                padding: 0.75rem 2rem;
                font-weight: 600;
                color: white;
                box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
                transition: all 0.3s ease;
            }
            .btn-withdrawal:hover {
                transform: translateY(-2px);
                box-shadow: 0 7px 20px rgba(17, 153, 142, 0.4);
                color: white;
            }
            .badge-status {
                padding: 0.5rem 1rem;
                border-radius: 50px;
                font-size: 0.8rem;
                font-weight: 600;
            }
            .badge-pending {
                background-color: rgba(255, 193, 7, 0.1);
                color: #856404;
            }
            .badge-approved {
                background-color: rgba(40, 167, 69, 0.1);
                color: #155724;
            }
            .badge-rejected {
                background-color: rgba(220, 53, 69, 0.1);
                color: #721c24;
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

    <div class="page-content-wrapper">
        <!-- Rules Section -->
        @if($ruleSection)
            <div class="container py-4">
                <div class="withdrawal-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2">Withdraw Funds</h1>
                            <p style="color: #e4edf5;" class="mb-0">Withdraw your winnings securely to your preferred payment method</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <i class="fas fa-money-bill-wave fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <div class="instruction-card">
                    <h4 class="mb-3 text-success"><i class="fas fa-info-circle me-2"></i>Withdrawal Instructions</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-wallet me-2"></i>Minimum Withdrawal</h6>
                                <p class="mb-0 fs-5 fw-bold text-primary">{{ $min_amount }} ৳</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-wallet me-2"></i>Maximum Withdrawal</h6>
                                <p class="mb-0 fs-5 fw-bold text-primary">{{ $max_amount }} ৳</p>
                            </div>
                        </div>
                    </div>

                    <ol class="list-group list-group-numbered mb-3">
                        <li class="list-group-item border-0">
                            Enter the amount you want to withdraw (Minimum: {{ $min_amount }}৳, Maximum: {{ $max_amount }}৳)
                        </li>
                        <li class="list-group-item border-0">
                            Ensure you have sufficient balance in your account
                        </li>
                        <li class="list-group-item border-0">
                            Select your preferred withdrawal method
                        </li>
                        <li class="list-group-item border-0">
                            Provide your correct account number for the selected method
                        </li>
                        <li class="list-group-item border-0">
                            Withdrawal requests are processed within 24 hours
                        </li>
                    </ol>

                    <div class="alert alert-warning">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Note:</strong>
                        You can only have one pending withdrawal request at a time. Processing time may vary depending on the payment method.
                    </div>
                </div>

                <!-- Amount Input -->
                <div class="form-card">
                    <h5 class="mb-3"><i class="fas fa-money-bill me-2"></i>Enter Withdrawal Amount</h5>
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">Amount (৳)</label>
                            <input type="number"
                                   class="form-control form-control-lg"
                                   wire:model="amount"
                                   placeholder="Enter amount between {{ $min_amount }} - {{ $max_amount }}"
                                   min="{{ $min_amount }}"
                                   max="{{ $max_amount }}">
                            @error('amount')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-withdrawal w-100" wire:click="nextToPaymentMethod">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                    @if(auth()->user()->credit)
                        <div class="mt-3">
                            <small class="text-muted">
                                Available Balance: <strong>{{ auth()->user()->credit }} ৳</strong>
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Payment Method Selection -->
        @if($paymentMethodSection)
            <div class="container py-4">
                <div class="withdrawal-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2">Select Payment Method</h1>
                            <p style="color: #e4edf5;" class="mb-0">Choose how you want to receive your money</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <i class="fas fa-mobile-alt fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- bKash -->
                    <div class="col-md-6">
                        <div class="method-card" wire:click="selectMethod('bKash')">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <img src="{{ asset('assets/frontend/img/paymentmethod/bikash.png') }}"
                                         alt="bKash"
                                         class="img-fluid">
                                </div>
                                <div class="col-9">
                                    <h5 class="mb-1">bKash</h5>
                                    <p class="text-muted mb-0">Instant withdrawal to your bKash account</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nagad -->
                    <div class="col-md-6">
                        <div class="method-card" wire:click="selectMethod('Nagad')">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <img src="{{ asset('assets/frontend/img/paymentmethod/nagad.png') }}"
                                         alt="Nagad"
                                         class="img-fluid">
                                </div>
                                <div class="col-9">
                                    <h5 class="mb-1">Nagad</h5>
                                    <p class="text-muted mb-0">Fast withdrawal to your Nagad account</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rocket -->
                    <div class="col-md-6">
                        <div class="method-card" wire:click="selectMethod('Rocket')">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <img src="{{ asset('assets/frontend/img/paymentmethod/roket.png') }}"
                                         alt="Rocket"
                                         class="img-fluid">
                                </div>
                                <div class="col-9">
                                    <h5 class="mb-1">Rocket</h5>
                                    <p class="text-muted mb-0">Quick withdrawal to your Rocket account</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upay -->
                    <div class="col-md-6">
                        <div class="method-card" wire:click="selectMethod('Upay')">
                            <div class="row align-items-center">
                                <div class="col-3">
                                    <img src="{{ asset('assets/frontend/img/paymentmethod/upay.png') }}"
                                         alt="Upay"
                                         class="img-fluid"
                                         style="max-height: 40px;">
                                </div>
                                <div class="col-9">
                                    <h5 class="mb-1">Upay</h5>
                                    <p class="text-muted mb-0">Easy withdrawal to your Upay account</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-secondary" wire:click="newRequest">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </button>
                </div>
            </div>
        @endif

        <!-- Submit Section -->
        @if($submitSection)
            <div class="container py-4">
                <div class="withdrawal-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2">Withdrawal Details</h1>
                            <p style="color: #e4edf5;" class="mb-0">Confirm your withdrawal information</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <i class="fas fa-clipboard-check fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <form wire:submit.prevent="submitWithdrawalRequest">
                    <div class="form-card">
                        <h5 class="mb-4"><i class="fas fa-credit-card me-2"></i>Payment Information</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Withdrawal Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">৳</span>
                                    <input type="text" class="form-control" value="{{ $amount }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <input type="text" class="form-control" value="{{ $method }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Your {{ $method }} Account Number *</label>
                            <input type="text"
                                   class="form-control"
                                   wire:model="account_number"
                                   placeholder="Enter your {{ $method }} account number">
                            @error('account_number')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Notes (Optional)</label>
                            <textarea class="form-control"
                                      wire:model="user_notes"
                                      rows="3"
                                      placeholder="Any additional information..."></textarea>
                            @error('user_notes')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Important</h6>
                            <ul class="mb-0">
                                <li>Ensure your account number is correct</li>
                                <li>Withdrawal processing time: 24-48 hours</li>
                                <li>Amount will be deducted from your balance immediately</li>
                            </ul>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary w-100" wire:click="newRequest">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-withdrawal w-100">
                                <span wire:loading.delay.long wire:target="submitWithdrawalRequest"
                                      class="spinner-border spinner-border-sm me-2"
                                      role="status"
                                      aria-hidden="true"></span>
                                Submit Withdrawal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- Request Status -->
        @if($requestStatus)
            <div class="container py-4">
                <div class="withdrawal-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-2">Withdrawal Status</h1>
                            <p style="color: #e4edf5;" class="mb-0">Track your withdrawal requests</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <i class="fas fa-history fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>

                @if($withdrawalStatus && count($withdrawalStatus) > 0)
                    @foreach($withdrawalStatus as $request)
                        <div class="status-card">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge-status badge-{{ $request->status }}">
                                            {{ strtoupper($request->status) }}
                                        </span>
                                        <span class="ms-3 text-muted">
                                            {{ $request->created_at->format('M d, Y h:i A') }}
                                        </span>
                                    </div>
                                    <h5 class="mb-1">{{ $request->amount }} ৳</h5>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-wallet me-2"></i>
                                        {{ $request->method }} - {{ $request->account_number }}
                                    </p>
                                    @if($request->user_notes)
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-sticky-note me-2"></i>
                                            {{ $request->user_notes }}
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-md-end">
                                    @if($request->status === 'pending')
                                        <button class="btn btn-danger"
                                                wire:click="cancelRequest({{ $request->id }})"
                                                wire:confirm="Are you sure you want to cancel this withdrawal request?">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="status-card text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No Pending Withdrawals</h4>
                        <p class="text-muted">You don't have any pending withdrawal requests.</p>
                    </div>
                @endif

                <div class="text-center mt-4">
                    <button class="btn btn-withdrawal" wire:click="newRequest">
                        <i class="fas fa-plus me-2"></i>New Withdrawal Request
                    </button>
                </div>

                <!-- Withdrawal History -->
                <div class="form-card mt-4">
                    <h5 class="mb-3"><i class="fas fa-list me-2"></i>Withdrawal History</h5>
                    @php
                        $allWithdrawals = \App\Models\WithdrawalRequest::where('user_id', auth()->id())
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();
                    @endphp

                    @if($allWithdrawals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allWithdrawals as $history)
                                        <tr>
                                            <td>{{ $history->created_at->format('M d, Y') }}</td>
                                            <td>{{ $history->amount }} ৳</td>
                                            <td>{{ $history->method }}</td>
                                            <td>
                                                <span class="badge-status badge-{{ $history->status }}">
                                                    {{ strtoupper($history->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No withdrawal history found.</p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Flash Messages -->
        @if(session()->has('success'))
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    @section('footer')
        <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
        <script>
            // Auto-hide alerts after 5 seconds
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(alert => {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });
        </script>
    @endsection
</div>

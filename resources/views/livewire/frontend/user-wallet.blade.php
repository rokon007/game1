<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>Housieblitz|Wallet</title>
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

            /* New Styles for Enhanced Design */
            .page-content-wrapper {
                background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
                min-height: 100vh;
            }
            .wallet-header {
                background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
                color: white;
                border-radius: 15px;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }
            .balance-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                border: none;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .balance-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
            .balance-card .card-title {
                font-size: 1rem;
                color: #6c757d;
                margin-bottom: 0.5rem;
            }
            .balance-card .balance-amount {
                font-size: 2.2rem;
                font-weight: 700;
                color: #28a745;
                margin-bottom: 0;
            }
            .balance-icon {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1rem;
                font-size: 1.5rem;
            }
            .balance-icon.wallet {
                background: rgba(40, 167, 69, 0.1);
                color: #28a745;
            }
            .balance-icon.ticket {
                background: rgba(13, 110, 253, 0.1);
                color: #0d6efd;
            }
            .action-buttons .btn {
                border-radius: 50px;
                padding: 0.75rem 1.5rem;
                font-weight: 600;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }
            .action-buttons .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 7px 14px rgba(0,0,0,0.15);
            }
            .transaction-card {
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                border: none;
                overflow: hidden;
            }
            .transaction-card .card-header {
                background: white;
                border-bottom: 1px solid #eaeaea;
                padding: 1.25rem 1.5rem;
            }
            .transaction-card .card-title {
                margin-bottom: 0;
                font-weight: 700;
                color: #2c3e50;
            }
            .table-responsive {
                border-radius: 0 0 15px 15px;
            }
            .table {
                margin-bottom: 0;
            }
            .table thead th {
                border-top: none;
                background-color: #f8f9fa;
                font-weight: 600;
                color: #495057;
                padding: 1rem 0.75rem;
            }
            .table tbody td {
                padding: 1rem 0.75rem;
                vertical-align: middle;
            }
            .table tbody tr {
                transition: background-color 0.2s ease;
            }
            .table tbody tr:hover {
                background-color: #f8f9fa;
            }
            .badge-transaction {
                padding: 0.5rem 0.75rem;
                border-radius: 50px;
                font-size: 0.75rem;
                font-weight: 600;
            }
            .badge-credit {
                background-color: rgba(40, 167, 69, 0.1);
                color: #28a745;
            }
            .badge-debit {
                background-color: rgba(220, 53, 69, 0.1);
                color: #dc3545;
            }
            .empty-state {
                padding: 3rem 1rem;
                text-align: center;
                color: #6c757d;
            }
            .empty-state i {
                font-size: 3rem;
                margin-bottom: 1rem;
                color: #dee2e6;
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
        <div class="container py-4">
            <!-- Wallet Header -->
            <div class="wallet-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 style="color: #eaeaea;" class="mb-2">My Wallet</h1>
                        <p style="color: #eaeaea;" class="mb-0">Manage your funds and track your transactions</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <i class="fas fa-wallet fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- Balance Section -->
            <div class="row mb-4">
                <!-- Account Balance -->
                <div class="col-md-6 mb-3">
                    <div class="balance-card h-100">
                        <div class="d-flex align-items-center">
                            <div class="balance-icon wallet">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title">Account Balance</h5>
                                <p class="balance-amount">{{ $user->credit ?? 0 }} ৳</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Tickets -->
                <div class="col-md-6 mb-3">
                    <div class="balance-card h-100">
                        <div class="d-flex align-items-center">
                            <div class="balance-icon ticket">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="card-title">Bonus Balance</h5>
                                <p class="balance-amount">{{ $user->bonus_credit ?? 0 }} ৳</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons mb-4">
                <a href="{{route('rifleAccount')}}" class="btn btn-success me-2">
                    <i class="fas fa-plus-circle me-2"></i>Add Credit
                </a>
                <a href="#" class="btn btn-warning">
                    <i class="fas fa-hand-holding-usd me-2"></i>Withdraw Request
                </a>
            </div>

            <!-- Transaction History -->
            {{-- <div class="card transaction-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Transaction History
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $txn)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge-transaction text-capitalize
                                                {{ $txn->type === 'credit' ? 'badge-credit' : 'badge-debit' }}">
                                                <i class="fas {{ $txn->type === 'credit' ? 'fa-arrow-down me-1' : 'fa-arrow-up me-1' }}"></i>
                                                {{ $txn->type }}
                                            </span>
                                        </td>
                                        <td class="fw-bold {{ $txn->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                            {{ $txn->type === 'credit' ? '+' : '-' }}{{ $txn->amount }} ৳
                                        </td>
                                        <td>{{ $txn->description }}</td>
                                        <td class="text-muted">{{ $txn->created_at->format('d M Y h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-receipt"></i>
                                                <h5>No transactions yet</h5>
                                                <p>Your transaction history will appear here</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>

    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>

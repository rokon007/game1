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
            <h3 class="mb-4">My Wallet</h3>

            <div class="row mb-4">
                <!-- Account Balance -->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Account Balance</h5>
                            <p class="fs-4 text-success fw-bold">{{ $user->credit ?? 0 }} ৳</p>
                        </div>
                    </div>
                </div>

                <!-- Total Wins -->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Total Wins</h5>
                            <p class="fs-4">{{ $user->total_wins ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Total Participations -->
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Total Participations</h5>
                            <p class="fs-4">{{ $user->total_participations ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Tickets -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Today's Tickets</h5>
                            <p class="fs-4">{{ $user->todays_tickets ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mb-4">
                <a href="{{route('rifleAccount')}}" class="btn btn-success me-2">Add Credit</a>
                <a href="#" class="btn btn-warning">Withdraw Request</a>
            </div>

            <!-- Transaction History -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Transaction History</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
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
                                        <td class="text-capitalize">{{ $txn->type }}</td>
                                        <td>{{ $txn->amount }} ৳</td>
                                        <td>{{ $txn->description }}</td>
                                        <td>{{ $txn->created_at->format('d M Y h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
    @endsection
</div>

<main>
    @section('title')
        <title>Admin | {{ $lottery->name }} - বিস্তারিত</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
        <style>
            .lottery-header {
                background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
                color: white;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .info-card {
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                transition: all 0.3s ease;
            }
            .info-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
            }
            .profit-badge {
                font-size: 0.9rem;
                padding: 5px 12px;
                border-radius: 20px;
            }
            .ticket-result-badge {
                font-size: 0.75rem;
                padding: 3px 8px;
                border-radius: 4px;
            }
            .nav-tabs .nav-link.active {
                font-weight: 600;
                border-bottom: 3px solid #2575fc;
            }
            .winner-card {
                background: rgba(37, 117, 252, 0.1);
                border-left: 3px solid #2575fc;
            }
        </style>
    @endsection

    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">লটারি ব্যবস্থাপনা</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lottery.index') }}">লটারি তালিকা</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $lottery->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <!-- Lottery Header -->
        <div class="lottery-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">{{ $lottery->name }}</h3>
                    <p class="mb-0">
                        <i class="bi bi-calendar-event"></i> ড্র তারিখ: {{ $lottery->draw_date->format('d/m/Y h:i A') }}
                        <span class="badge bg-white text-primary ms-2">
                            {{ $lottery->status === 'active' ? 'সক্রিয়' : ($lottery->status === 'completed' ? 'সম্পন্ন' : 'বাতিল') }}
                        </span>
                    </p>
                </div>
                <a href="{{ route('admin.lottery.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> তালিকায় ফিরুন
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <div class="col">
                <div class="info-card card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">বিক্রিত টিকিট</h6>
                                <h3 class="mb-0">{{ $lottery->getTotalTicketsSold() }}</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-ticket-perforated text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="info-card card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">মোট আয়</h6>
                                <h3 class="mb-0">৳{{ number_format($lottery->getTotalRevenue(), 2) }}</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-cash-coin text-white" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="info-card card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">লাভ/লোকসান</h6>
                                <h3 class="mb-0">
                                    <span class="{{ $lottery->getTotalRevenue() - $lottery->prizes->sum('amount') >= 0 ? 'text-success' : 'text-danger' }}">
                                        ৳{{ number_format($lottery->getTotalRevenue() - $lottery->prizes->sum('amount'), 2) }}
                                    </span>
                                </h3>
                            </div>
                            <div class="{{ $lottery->getTotalRevenue() - $lottery->prizes->sum('amount') >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 p-3 rounded">
                                <i class="bi bi-graph-up-arrow {{ $lottery->getTotalRevenue() - $lottery->prizes->sum('amount') >= 0 ? 'text-white' : 'text-white' }}" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs nav-primary mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i> ওভারভিউ
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#prizes" type="button" role="tab">
                    <i class="bi bi-trophy me-1"></i> প্রাইজ ({{ $lottery->prizes->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tickets" type="button" role="tab">
                    <i class="bi bi-ticket-perforated me-1"></i> টিকিট ({{ $lottery->getTotalTicketsSold() }})
                </button>
            </li>
            @if($lottery->pre_selected_winners)
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#preselected" type="button" role="tab">
                    <i class="bi bi-star me-1"></i> পূর্বনির্ধারিত বিজয়ী
                </button>
            </li>
            @endif
        </ul>

        <!-- Tab Content -->
        <div class="tab-content py-3">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-info-square me-2"></i>লটারি তথ্য</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">টিকিটের মূল্য:</th>
                                        <td>৳{{ number_format($lottery->price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>ড্র এর তারিখ:</th>
                                        <td>{{ $lottery->draw_date->format('d/m/Y h:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>স্ট্যাটাস:</th>
                                        <td>
                                            <span class="badge bg-{{ $lottery->status === 'active' ? 'success' : ($lottery->status === 'completed' ? 'primary' : 'danger') }}">
                                                {{ $lottery->status === 'active' ? 'সক্রিয়' : ($lottery->status === 'completed' ? 'সম্পন্ন' : 'বাতিল') }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>অটো ড্র:</th>
                                        <td>
                                            <span class="badge bg-{{ $lottery->auto_draw ? 'primary' : 'secondary' }}">
                                                {{ $lottery->auto_draw ? 'হ্যাঁ' : 'না' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>তৈরি হয়েছে:</th>
                                        <td>{{ $lottery->created_at->format('d/m/Y h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>আর্থিক তথ্য</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">বিক্রিত টিকিট:</th>
                                        <td>{{ $lottery->getTotalTicketsSold() }}</td>
                                    </tr>
                                    <tr>
                                        <th>মোট আয়:</th>
                                        <td>৳{{ number_format($lottery->getTotalRevenue(), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>মোট প্রাইজ:</th>
                                        <td>৳{{ number_format($lottery->prizes->sum('amount'), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>লাভ/লোকসান:</th>
                                        <td>
                                            <span class="profit-badge bg-{{ $lottery->getTotalRevenue() - $lottery->prizes->sum('amount') >= 0 ? 'success' : 'danger' }}">
                                                ৳{{ number_format($lottery->getTotalRevenue() - $lottery->prizes->sum('amount'), 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prizes Tab -->
            <div class="tab-pane fade" id="prizes" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        @foreach($lottery->prizes->sortBy('rank') as $prize)
                            @php
                                $result = $lottery->results->where('lottery_prize_id', $prize->id)->first();
                            @endphp
                            <div class="winner-card card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">{{ $prize->position }} <small class="text-muted">(র‍্যাঙ্ক: {{ $prize->rank }})</small></h5>
                                            <h4 class="text-primary mb-2">৳{{ number_format($prize->amount, 2) }}</h4>
                                        </div>
                                        <div class="text-end">
                                            @if($result)
                                                <div>
                                                    <span class="badge bg-success">বিজয়ী নির্বাচিত</span>
                                                    <div class="mt-1">
                                                        <small class="text-muted">টিকিট:</small> {{ $result->winning_ticket_number }}<br>
                                                        <small class="text-muted">বিজয়ী:</small> {{ $result->user->name }}
                                                    </div>
                                                </div>
                                            @elseif($lottery->status === 'active')
                                                <span class="badge bg-secondary">ড্র হয়নি</span>
                                            @else
                                                <span class="badge bg-danger">কোন বিজয়ী নেই</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tickets Tab -->
            <div class="tab-pane fade" id="tickets" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>টিকিট নম্বর</th>
                                        <th>ক্রেতা</th>
                                        <th>ক্রয়ের তারিখ</th>
                                        <th>ফলাফল</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lottery->tickets as $ticket)
                                        <tr>
                                            <td>{{ $ticket->ticket_number }}</td>
                                            <td>{{ $ticket->user->name }}</td>
                                            <td>{{ $ticket->purchased_at->format('d/m/Y h:i A') }}</td>
                                            <td>
                                                @php
                                                    $result = $lottery->results->where('lottery_ticket_id', $ticket->id)->first();
                                                @endphp
                                                @if($result)
                                                    <span class="ticket-result-badge bg-success">
                                                        {{ $result->prize->position }} - ৳{{ number_format($result->prize_amount, 2) }}
                                                    </span>
                                                @elseif($lottery->status === 'completed')
                                                    <span class="ticket-result-badge bg-secondary">জয় হয়নি</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="bi bi-ticket-perforated text-muted" style="font-size: 2rem;"></i>
                                                <h5 class="mt-2 text-muted">কোন টিকিট বিক্রি হয়নি</h5>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pre-selected Winners Tab -->
            @if($lottery->pre_selected_winners)
            <div class="tab-pane fade" id="preselected" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h6 class="mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i>পূর্ব-নির্ধারিত বিজয়ী</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i> যদি টিকিট বিক্রি কম হয় এবং লোকসানের সম্ভাবনা থাকে, তাহলে এই পূর্ব-নির্ধারিত বিজয়ীরা প্রাইজ পাবে।
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>প্রাইজ পজিশন</th>
                                        <th>টিকিট নম্বর</th>
                                        <th>স্ট্যাটাস</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lottery->pre_selected_winners as $position => $ticketNumber)
                                        <tr>
                                            <td>{{ $position }}</td>
                                            <td>{{ $ticketNumber }}</td>
                                            <td>
                                                @if($lottery->status === 'completed')
                                                    @php
                                                        $isWinner = $lottery->results->where('winning_ticket_number', $ticketNumber)->first();
                                                    @endphp
                                                    @if($isWinner)
                                                        <span class="badge bg-success">বিজয়ী হয়েছে</span>
                                                    @else
                                                        <span class="badge bg-secondary">ব্যবহৃত হয়নি</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-info">প্রস্তুত</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Action Buttons -->
        @if($lottery->status === 'active')
        <div class="d-flex justify-content-end gap-3 mt-4">
            <button class="btn btn-danger" onclick="confirmCancel({{ $lottery->id }})">
                <i class="bi bi-x-circle me-1"></i> লটারি বাতিল করুন
            </button>
            <button class="btn btn-warning" onclick="confirmDraw({{ $lottery->id }})">
                <i class="bi bi-shuffle me-1"></i> ম্যানুয়াল ড্র করুন
            </button>
        </div>
        @endif
    </div>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
        <script>
            function confirmDraw(lotteryId) {
                Swal.fire({
                    title: 'ড্র শুরু করবেন?',
                    text: "আপনি কি নিশ্চিত যে আপনি ম্যানুয়ালি ড্র করতে চান?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#fd7e14',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'হ্যাঁ, ড্র করুন!',
                    cancelButtonText: 'বাতিল'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('conductDraw', { lotteryId: lotteryId });
                    }
                });
            }

            function confirmCancel(lotteryId) {
                Swal.fire({
                    title: 'লটারি বাতিল করবেন?',
                    text: "এই লটারির সব ডাটা সংরক্ষিত থাকবে, কিন্তু আর নতুন টিকিট বিক্রি করা যাবে না!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'হ্যাঁ, বাতিল করুন!',
                    cancelButtonText: 'বাতিল'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('cancelLottery', { lotteryId: lotteryId });
                    }
                });
            }
        </script>
    @endsection
</main>

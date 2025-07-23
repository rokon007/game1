@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ $lottery->name }} - বিস্তারিত</h4>
                    <a href="{{ route('admin.lottery.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> তালিকায় ফিরুন
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">লটারি তথ্য</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">টিকিটের মূল্য:</th>
                                            <td>৳{{ number_format($lottery->price, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>ড্র এর তারিখ:</th>
                                            <td>{{ $lottery->draw_date->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>স্ট্যাটাস:</th>
                                            <td>
                                                <span class="badge badge-{{ $lottery->status === 'active' ? 'success' : ($lottery->status === 'completed' ? 'primary' : 'danger') }}">
                                                    {{ $lottery->status === 'active' ? 'সক্রিয়' : ($lottery->status === 'completed' ? 'সম্পন্ন' : 'বাতিল') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>অটো ড্র:</th>
                                            <td>{{ $lottery->auto_draw ? 'হ্যাঁ' : 'না' }}</td>
                                        </tr>
                                        <tr>
                                            <th>তৈরি হয়েছে:</th>
                                            <td>{{ $lottery->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">বিক্রয় তথ্য</h5>
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
                                            <td class="{{ $lottery->getTotalRevenue() - $lottery->prizes->sum('amount') >= 0 ? 'text-success' : 'text-danger' }}">
                                                ৳{{ number_format($lottery->getTotalRevenue() - $lottery->prizes->sum('amount'), 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prizes Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">প্রাইজ তালিকা</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>পজিশন</th>
                                            <th>পরিমাণ</th>
                                            <th>র‍্যাঙ্ক</th>
                                            <th>বিজয়ী</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lottery->prizes->sortBy('rank') as $prize)
                                            <tr>
                                                <td>{{ $prize->position }}</td>
                                                <td>৳{{ number_format($prize->amount, 2) }}</td>
                                                <td>{{ $prize->rank }}</td>
                                                <td>
                                                    @php
                                                        $result = $lottery->results->where('lottery_prize_id', $prize->id)->first();
                                                    @endphp
                                                    @if($result)
                                                        <div>
                                                            <strong>টিকিট:</strong> {{ $result->winning_ticket_number }}<br>
                                                            <strong>বিজয়ী:</strong> {{ $result->user->name }}
                                                        </div>
                                                    @elseif($lottery->status === 'active')
                                                        <span class="text-muted">ড্র হয়নি</span>
                                                    @else
                                                        <span class="text-danger">কোন বিজয়ী নেই</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pre-selected Winners Section -->
                    @if($lottery->pre_selected_winners)
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title">পূর্ব-নির্ধারিত বিজয়ী</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>প্রাইজ পজিশন</th>
                                                <th>টিকিট নম্বর</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lottery->pre_selected_winners as $position => $ticketNumber)
                                                <tr>
                                                    <td>{{ $position }}</td>
                                                    <td>{{ $ticketNumber }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tickets Section -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">বিক্রিত টিকিট</h5>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#ticketsCollapse">
                                দেখুন/লুকান
                            </button>
                        </div>
                        <div class="collapse" id="ticketsCollapse">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
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
                                                    <td>{{ $ticket->purchased_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @php
                                                            $result = $lottery->results->where('lottery_ticket_id', $ticket->id)->first();
                                                        @endphp
                                                        @if($result)
                                                            <span class="badge badge-success">
                                                                {{ $result->prize->position }} - ৳{{ number_format($result->prize_amount, 2) }}
                                                            </span>
                                                        @elseif($lottery->status === 'completed')
                                                            <span class="badge badge-secondary">জয় হয়নি</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">কোন টিকিট বিক্রি হয়নি</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4">
                        @if($lottery->status === 'active')
                            <button class="btn btn-warning" onclick="confirmDraw({{ $lottery->id }})">
                                ম্যানুয়াল ড্র করুন
                            </button>
                            <button class="btn btn-danger" onclick="confirmCancel({{ $lottery->id }})">
                                লটারি বাতিল করুন
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDraw(lotteryId) {
    if (confirm('আপনি কি নিশ্চিত যে আপনি ম্যানুয়ালি ড্র করতে চান?')) {
        // Send to Livewire component
        Livewire.dispatch('conductDraw', { lotteryId: lotteryId });
    }
}

function confirmCancel(lotteryId) {
    if (confirm('আপনি কি নিশ্চিত যে আপনি লটারি বাতিল করতে চান?')) {
        // Send to Livewire component
        Livewire.dispatch('cancelLottery', { lotteryId: lotteryId });
    }
}
</script>
@endsection

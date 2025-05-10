<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>Housieblitz|SheeT</title>
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
            @if($loading)
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading your ticket sheet...</p>
                </div>

            @elseif(empty($sheets))
                <div class="alert alert-info text-center py-4">
                    <i class="fas fa-ticket-alt fa-2x mb-3"></i>
                    <h4>You have not purchased any ticket sheets yet.</h4>
                    <a href="{{ route('buy_ticket') }}" class="btn btn-primary mt-3">
                        গেমস দেখুন
                    </a>
                </div>

            @elseif(!$selectedSheet)
                <!-- শীট লিস্ট ভিউ -->
                <div class="row">
                    <div class="col-12">
                        <h3 class="mb-4">
                            <i class="fas fa-ticket-alt me-2"></i>
                            Your ticket sheets
                        </h3>

                        {{-- <div class="list-group">
                            @foreach($sheets as $sheet)
                                @if($sheet['game'])
                                    <div class="mb-2">
                                        <button wire:click="showSheet('{{ $sheet['sheet_id'] }}')"
                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-layer-group me-2"></i>
                                                <strong>{{ $sheet['game']['title'] }}-{{ $sheet['sheet_id'] }}</strong>
                                            </div>
                                            <div>
                                                <span class="badge bg-primary rounded-pill me-2">
                                                    Game start time : {{ \Carbon\Carbon::parse($sheet['game']['scheduled_at'])->format('d M Y h:i A') }}
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div> --}}
                        <div class="list-group">
                            @foreach($sheets as $sheet)
                                @if($sheet['game'])
                                    <div class="mb-2">
                                        <button wire:click="showSheet('{{ $sheet['sheet_id'] }}')"
                                                class="list-group-item list-group-item-action d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-start">
                                            <div class="mb-1 mb-md-0">
                                                <i class="fas fa-layer-group me-2"></i>
                                                <strong>{{ $sheet['game']['title'] }} - {{ $sheet['sheet_id'] }}</strong>
                                            </div>
                                            <div>
                                                <span class="badge bg-primary rounded-pill">
                                                    Game start time: {{ \Carbon\Carbon::parse($sheet['game']['scheduled_at'])->format('d M Y h:i A') }}
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                    </div>
                </div>

            @else
                <!-- শীট ডিটেইল ভিউ - টিকেটগুলো একের নিচে এক -->

                <div class="mb-4">
    <button wire:click="backToList" class="btn btn-sm btn-outline-primary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Back
    </button>
    @if ($selectedSheet)
        <div class="sheet-container card shadow-sm mb-0"> <!-- mb-0 যোগ করা হয়েছে -->
            <!-- হেডার কার্ড বডির ভিতরে নিয়ে আসা হয়েছে -->
            {{-- <div class="card-header bg-primary text-white py-2 border-bottom-0"> <!-- border-bottom-0 যোগ করা হয়েছে -->
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-white fs-5">
                        <i class="fas fa-ticket-alt me-2"></i>
                        {{ $selectedSheet }}
                    </h6>
                    <span class="badge bg-light text-dark fs-6">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::parse($sheetTickets[0]['game']['scheduled_at'])->format('d M Y h:i A') }}
                    </span>
                </div>
            </div> --}}
            <div class="card-header bg-primary text-white py-2 border-bottom-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap"> <!-- flex-wrap যোগ করা হয়েছে -->
                    <h6 class="mb-0 text-white fs-5 text-nowrap pe-2"> <!-- text-nowrap এবং pe-2 যোগ করা হয়েছে -->
                        <i class="fas fa-ticket-alt me-2"></i>
                        {{ Str::limit($selectedSheet, 15) }} <!-- টেক্সট লিমিট করা হয়েছে -->
                    </h6>
                    <span class="badge bg-light text-dark fs-6 text-nowrap mt-1 mt-sm-0"> <!-- text-nowrap এবং mt ক্লাস যোগ করা হয়েছে -->
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::parse($sheetTickets[0]['game']['scheduled_at'])->format('d M Y h:i A') }}
                    </span>
                </div>
            </div>
            <div class="card-body py-2" style="background-color: #f8f9fa; border-top: 1px solid rgba(0,0,0,.125);"> <!-- border-top যোগ করা হয়েছে -->
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted fs-6">
                        <i class="fas fa-gamepad me-1"></i>
                        {{ $sheetTickets[0]['game']['title'] }}
                    </span>
                    <span class="badge bg-info text-white fs-6">
                        {{ count($sheetTickets) }} Tickets
                    </span>
                </div>
            </div>

            <!-- টিকেট কন্টেইনার -->
            <div class="card-body p-2 p-md-3" style="background-color: #f8f9fa;"> <!-- একই ব্যাকগ্রাউন্ড কালার -->
                <div class="tickets-grid">
                    @foreach($sheetTickets as $ticket)
                        <div class="ticket-item mb-3 shadow-sm">
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    @foreach($ticket['numbers'] as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td class="text-center {{ $cell ? 'bg-white' : '' }}"
                                                    style="width: 11%; height: 35px; font-size: 0.9rem;">
                                                    {{ $cell ?: '' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($ticket['is_winner'])
                                <div class="winner-badge">
                                    <span class="badge bg-success rounded-pill">Winner</span>
                                </div>
                            @endif
                            {{-- <div class="ticket-footer text-center py-1 bg-light">
                                <small class="text-muted">#{{ explode('-', $ticket['ticket_number'])[1] }}</small>
                            </div> --}}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .sheet-container {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,.125);
    }

    .tickets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        padding: 0.5rem;
    }

    .ticket-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
        background-color: white;
        border: 1px solid #e9ecef;
    }

    .ticket-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .winner-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 1;
    }

    table td {
        font-weight: bold;
        padding: 0.25rem;
        border-color: #dee2e6;
    }

    @media (max-width: 768px) {
        .tickets-grid {
            grid-template-columns: 1fr;
            padding: 0;
        }

        .sheet-container {
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
    }
    @media (max-width: 576px) {
    .card-header h6 {
        font-size: 0.9rem !important;
    }
    .card-header .badge {
        font-size: 0.4rem !important;
        /* padding: 0.25em 0.4em; */
    }
}

    .ticket-footer {
        border-top: 1px solid #e9ecef;
    }
</style>
@endpush


                <!-- শীট ডিটেইল ভিউ -->
                {{-- <div class="mb-4">
                    <button wire:click="backToList" class="btn btn-sm btn-outline-primary mb-3">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </button>
                    @if ($selectedSheet)
                        <div class="sheet-header card mb-0">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-white">
                                        <i class="fas fa-ticket-alt me-2"></i>
                                         {{ $selectedSheet }}
                                    </h5>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ \Carbon\Carbon::parse($sheetTickets[0]['game']['scheduled_at'])->format('d M Y h:i A') }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body py-2" style="background-color: cornsilk">
                                <div class="d-flex justify-content-between">
                                    <span>
                                        <i class="fas fa-gamepad me-1"></i>
                                        {{ $sheetTickets[0]['game']['title'] }}
                                    </span>
                                    <span class="badge bg-info">
                                        {{ count($sheetTickets) }} Tickets
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div> --}}

                <!-- টিকেট কন্টেইনার -->
                {{-- <div class="sheet-container card" style="background-color: cornsilk">
                    <div class="card-body p-3"> <!-- padding যোগ করা হয়েছে -->
                        <div class="tickets-grid">
                            @foreach($sheetTickets as $ticket)
                                <div class="ticket-item mb-4"> <!-- mb-4 ক্লাস যোগ করা হয়েছে -->
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            @foreach($ticket['numbers'] as $row)
                                                <tr>
                                                    @foreach($row as $cell)
                                                        <td class="text-center {{ $cell ? 'bg-light' : '' }}"
                                                            style="width: 11%; height: 40px;">
                                                            {{ $cell ?: '' }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if($ticket['is_winner'])
                                        <div class="winner-badge">
                                            <span class="badge bg-success">Winner</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div> --}}

                {{-- @push('styles')
                <style>
                    .tickets-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                        gap: 1.5rem; /* গ্যাপ বড় করা হয়েছে */
                        padding: 1rem;
                    }

                    .ticket-item {
                        position: relative;
                        border: 1px solid #dee2e6;
                        border-radius: 6px;
                        overflow: hidden;
                        transition: all 0.3s ease;
                        padding: 0.5rem; /* টিকেটের ভিতরে প্যাডিং */
                        background-color: white;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* সূক্ষ্ম শ্যাডো */
                    }

                    .ticket-item:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    }

                    /* টিকেটের মধ্যে আরও স্পেস */
                    .ticket-item + .ticket-item {
                        margin-top: 1.5rem;
                    }
                </style>
                @endpush

                @push('styles')
                <style>
                    .sheet-header {
                        border-radius: 8px;
                        overflow: hidden;
                    }

                    .sheet-container {
                        border: 1px solid #dee2e6;
                        border-radius: 8px;
                        overflow: hidden;
                    }

                    .tickets-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                        gap: 1rem;
                        padding: 1rem;
                    }

                    .ticket-item {
                        position: relative;
                        border: 1px solid #dee2e6;
                        border-radius: 6px;
                        overflow: hidden;
                        transition: all 0.3s ease;
                    }

                    .ticket-item:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    }

                    .winner-badge {
                        position: absolute;
                        top: 5px;
                        right: 5px;
                    }

                    table td {
                        font-weight: bold;
                        padding: 0.5rem;
                    }
                </style>
                @endpush --}}
            @endif
        </div>

        @push('styles')
        <style>
            .ticket-card {
                max-width: 600px;
                margin-left: auto;
                margin-right: auto;
            }
            .card {
                transition: all 0.3s ease;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .card:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            .list-group-item:hover {
                background-color: #f8f9fa;
            }
            table td {
                font-weight: bold;
            }
            .ticket-container {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
        </style>
        @endpush
    </div>
    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>

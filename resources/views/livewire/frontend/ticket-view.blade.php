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
                            <h6 class="mb-3"><i class="fas fa-ticket-alt me-2"></i>{{ $selectedSheet }}
                            <br><small> Play on :{{ \Carbon\Carbon::parse($sheetTickets[0]['game']['scheduled_at'])->format('d M Y h:i A') }}</small></h6>
                        @endif
                </div>

                <div class="ticket-container">
                    @foreach($sheetTickets as $ticket)
                        <div class="ticket-card mb-4">
                            <div class="card border-{{ $ticket['is_winner'] ? 'success' : 'primary' }}">
                                <div class="card-header bg-{{ $ticket['is_winner'] ? 'success' : 'primary' }} text-white py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-ticket-alt me-2"></i>
                                             {{-- Ticket #{{ explode('-', $ticket['number'])[2] }} --}}
                                             Ticket #{{ explode('-', $ticket['number'])[1] }}
                                        </span>
                                        @if($ticket['is_winner'])
                                            <span class="badge bg-warning text-dark">Winner</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-body p-2">
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
                                </div>

                                {{-- <div class="card-footer bg-light py-2 small">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $ticket['created_at'] }}
                                </div> --}}
                            </div>
                        </div>
                    @endforeach
                </div>
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

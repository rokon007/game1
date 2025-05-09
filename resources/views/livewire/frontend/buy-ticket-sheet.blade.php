<div>
    @section('meta_description')
      <meta name="description" content="Housieblitz ">
    @endsection
    @section('title')
        <title>Housieblitz|Buy Ticket</title>
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
        <div class="container" style="display: {{ $buyMode ? 'block' : 'none' }}">
            <div class="checkout-wrapper-area py-3">
                <div class="credit-card-info-wrapper">
                    {{-- <img class="d-block mb-4" src="img/bg-img/credit-card.png" alt=""> --}}
                    <h2 class="text-lg font-semibold mb-4">Buy Ticket Sheet</h2>

                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                  <div class="pay-credit-card-form">

                      <div class="mb-3">
                        <label for="paypalEmail">Your Current Bllance</label>
                        <input class="form-control" type="text" id="blance" wire:model='blance'><small class="ms-1"><i class="ti ti-lock-square-rounded me-1"></i>Secure online payments at the speed of want.<a class="mx-1" href="#">Learn More</a></small>
                      </div>
                      <div class="mb-3">
                        <label for="paypalPassword">Select Game Schedule</label>
                        <select wire:model.live="selectedGameId" id="game" class="form-control">
                            <option value="">-- Select a Game --</option>
                            @foreach($games as $game)
                                <option value="{{ $game->id }}">
                                    {{ $game->title ?? 'Game' }} - {{ \Carbon\Carbon::parse($game->scheduled_at)->format('g:i A') }} - ৳{{ $game->ticket_price }}
                                </option>
                            @endforeach
                        </select>
                      </div>
                      <button wire:click="buySheet" class="btn btn-primary btn-lg w-100" @disabled(!$selectedGameId)>
                        <span wire:loading.delay.long wire:target="buySheet" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Buy Sheet
                    </button>

                  </div>
                </div>
              </div>


        </div>
        {{-- <div class="container" style="display: {{ $sheetShowMode ? 'block' : 'none' }}">
            @if ($sheetShowMode)
                @if ($tickets->isNotEmpty())
                    <h4 class="mb-3">Your Ticket Sheet: {{ $sheetUid }}</h4>

                    <div class="row">
                        @foreach ($tickets as $ticket)
                            <div class="col-12 mb-4">
                                <div class="card shadow border-0">
                                    <div class="card-header text-center bg-primary text-white">
                                        Ticket #: {{ $ticket->ticket_number }}
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-bordered text-center m-0">
                                            <tbody>
                                                @php
                                                    $numbers = is_string($ticket->numbers) ? json_decode($ticket->numbers, true) : $ticket->numbers;
                                                @endphp

                                                @if (is_array($numbers))
                                                    @foreach ($numbers as $row)
                                                        <tr>
                                                            @foreach ($row as $cell)
                                                                <td style="width: 11%; height: 40px;">
                                                                    {{ $cell ?: '' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr><td colspan="9" class="text-danger">Invalid ticket data</td></tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p>No tickets found.</p>
                @endif
            @endif
        </div> --}}
        <div class="container" style="display: {{ $sheetShowMode ? 'block' : 'none' }}">
            @if ($sheetShowMode)
                <div class="ticket-sheet-view mt-4">
                    <!-- শীট হেডার (শীটের সাথে সংযুক্ত) -->
                    <div class="sheet-header card mb-0 border-bottom-0 rounded-bottom-0">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0 text-white">
                                    <i class="fas fa-layer-group me-2"></i>
                                     {{ $sheetUid }}
                                </h4>
                                <a href="{{route('ticket')}}" class="btn btn-sm btn-light">
                                    <i class="fas fa-arrow-left me-1"></i> All Tickets
                                </a>
                            </div>
                        </div>
                        <div class="card-body py-2 bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Game Time: {{ $tickets->first()->game->scheduled_at->format('d M Y, h:i A') }}
                                </span>
                                <span class="badge bg-primary">
                                    {{ $tickets->count() }} Tickets
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- টিকেট কন্টেইনার -->
                    <div class="sheet-body card border-top-0 rounded-top-0">
                        <div class="card-body p-3">
                            <div class="tickets-grid">
                                @foreach ($tickets as $ticket)
                                    <div class="ticket-card mb-4">
                                        <table class="table table-bordered mb-0">
                                            <tbody>
                                                @php
                                                    $numbers = is_string($ticket->numbers)
                                                        ? json_decode($ticket->numbers, true)
                                                        : $ticket->numbers;
                                                @endphp

                                                @if (is_array($numbers))
                                                    @foreach ($numbers as $row)
                                                        <tr>
                                                            @foreach ($row as $cell)
                                                                <td class="text-center {{ $cell ? 'bg-light' : '' }}"
                                                                    style="width: 11%; height: 40px;">
                                                                    {{ $cell ?: '' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="9" class="text-center text-danger py-2">
                                                            Invalid ticket data
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                        @if($ticket->is_winner)
                                            <div class="winner-badge">
                                                <span class="badge bg-success">Winner</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @push('styles')
            <style>
                .ticket-sheet-view {
                    max-width: 800px;
                    margin: 0 auto;
                }

                .sheet-header {
                    border-radius: 6px 6px 0 0 !important;
                }

                .sheet-body {
                    border-radius: 0 0 6px 6px !important;
                    padding: 1.5rem !important; /* প্যাডিং বাড়ানো হয়েছে */
                }

                .tickets-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                    gap: 1.5rem; /* টিকেটগুলোর মধ্যে গ্যাপ */
                }

                .ticket-card {
                    position: relative;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    overflow: hidden;
                    transition: all 0.3s ease;
                    margin-bottom: 1.5rem; /* প্রতিটি টিকেট কার্ডের নিচে গ্যাপ */
                }

                .ticket-card:last-child {
                    margin-bottom: 0; /* শেষ টিকেটে মার্জিন রিমুভ */
                }

                .ticket-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                }

                .winner-badge {
                    position: absolute;
                    top: 8px;
                    right: 8px;
                }

                table {
                    width: 100%;
                }

                table td {
                    font-weight: bold;
                    padding: 0.5rem;
                    text-align: center;
                    height: 40px;
                }
            </style>
        @endpush

        {{-- @push('styles')
            <style>
                .ticket-sheet-view {
                    max-width: 800px;
                    margin: 0 auto;
                }

                .sheet-header {
                    border-radius: 6px 6px 0 0 !important;
                }

                .sheet-body {
                    border-radius: 0 0 6px 6px !important;
                }

                .tickets-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                    gap: 1.5rem; /* টিকেটগুলোর মধ্যে গ্যাপ */
                }

                .ticket-card {
                    position: relative;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    overflow: hidden;
                    transition: all 0.3s ease;
                }

                .ticket-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                }

                .winner-badge {
                    position: absolute;
                    top: 8px;
                    right: 8px;
                }

                table td {
                    font-weight: bold;
                    padding: 0.5rem;
                }
            </style>
        @endpush --}}
    </div>
    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>

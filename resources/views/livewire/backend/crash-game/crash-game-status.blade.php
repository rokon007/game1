<main>
    @section('title')
        <title>Admin - Crash Game Status</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />

    @endsection

    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
            <div class="breadcrumb-title pe-3">Crash Game Status</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="#">Crash Game Status</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="container py-4">
            <div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-{{ $isRunning ? 'success' : 'danger' }} text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-gamepad me-2"></i>Game Control
                                </h4>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <h5>Current Status:</h5>
                                    <span class="badge bg-{{ $isRunning ? 'success' : 'danger' }} fs-6 p-2">
                                        {{ $isRunning ? 'RUNNING' : 'STOPPED' }}
                                    </span>
                                </div>

                                <div class="btn-group" role="group">
                                    <button type="button" wire:click="startGame"
                                            class="btn btn-success" {{ $isRunning ? 'disabled' : '' }}>
                                        <i class="fas fa-play me-1"></i> Start Game
                                    </button>

                                    <button type="button" wire:click="stopGame"
                                            class="btn btn-warning" {{ !$isRunning ? 'disabled' : '' }}>
                                        <i class="fas fa-stop me-1"></i> Stop Game
                                    </button>

                                    <button type="button" wire:click="restartGame" class="btn btn-info">
                                        <i class="fas fa-redo me-1"></i> Restart
                                    </button>
                                </div>

                                @if (session()->has('message'))
                                    <div class="alert alert-success mt-3 mb-0">
                                        {{ session('message') }}
                                    </div>
                                @endif

                                @if (session()->has('error'))
                                    <div class="alert alert-danger mt-3 mb-0">
                                        {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Current Game Info
                                </h4>
                            </div>
                            <div class="card-body">
                                @if($currentGame)
                                    <p><strong>Game ID:</strong> #{{ $currentGame->id }}</p>
                                    <p><strong>Status:</strong>
                                        <span class="badge bg-{{ $currentGame->status == 'running' ? 'success' : 'warning' }}">
                                            {{ strtoupper($currentGame->status) }}
                                        </span>
                                    </p>
                                    <p><strong>Crash Point:</strong> {{ $currentGame->crash_point }}x</p>
                                    <p><strong>Total Bets:</strong> ৳{{ number_format($currentGame->total_bet_amount, 2) }}</p>
                                    <p><strong>Players:</strong> {{ $currentGame->bets->count() }}</p>
                                    <p><strong>Created:</strong> {{ $currentGame->created_at->format('M d, H:i:s') }}</p>
                                @else
                                    <p class="text-muted text-center py-3">No active game</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Recent Games
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Game ID</th>
                                                <th>Crash Point</th>
                                                <th>Total Bets</th>
                                                <th>Players</th>
                                                <th>House Profit</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentGames as $game)
                                            <tr>
                                                <td>#{{ $game->id }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $game->crash_point >= 2 ? 'success' : 'warning' }}">
                                                        {{ $game->crash_point }}x
                                                    </span>
                                                </td>
                                                <td>৳{{ number_format($game->total_bet_amount, 2) }}</td>
                                                <td>{{ $game->bets->count() }}</td>
                                                <td class="{{ $game->total_bet_amount - $game->total_payout > 0 ? 'text-success' : 'text-danger' }}">
                                                    ৳{{ number_format($game->total_bet_amount - $game->total_payout, 2) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $game->status == 'crashed' ? 'danger' : 'info' }}">
                                                        {{ strtoupper($game->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $game->created_at->format('M d, H:i') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">
                                                    No games found
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
@endpush

    </div>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
        <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>

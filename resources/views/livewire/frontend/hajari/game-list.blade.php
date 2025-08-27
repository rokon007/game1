<div>
    @section('meta_description')
      <meta name="description" content="Housieblitz">
    @endsection
    @section('title')
        <title>Housieblitz|Hajari</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
          <style>
    /* ... existing styles ... */

    /* Fixed Modal Styles */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1040;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: none;
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1050;
        width: 100%;
        height: 100%;
        overflow-x: hidden;
        overflow-y: auto;
        outline: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        box-sizing: border-box;
    }

    .modal-dialog {
        position: relative;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        pointer-events: none;
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: none;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        outline: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .modal-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-title i {
        font-size: 1.5rem;
    }

    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 20px;
        background: #f8fafc;
        overflow-y: auto;
    }

    .modal-main-text {
        font-size: 1.1rem;
        color: #2d3748;
        margin-bottom: 15px;
        font-weight: 500;
        line-height: 1.4;
    }

    .modal-subtext {
        color: #718096;
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 0;
    }

    .modal-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        padding: 16px 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        gap: 12px;
        background: #fff;
        position: sticky;
        bottom: 0;
        z-index: 10;
    }

    .modal-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 120px;
        flex: 1;
    }

    .modal-btn-secondary {
        background: #f8f9fa;
        color: #718096;
        border: 2px solid #e2e8f0;
    }

    .modal-btn-secondary:hover {
        background: #e2e8f0;
        border-color: #cbd5e0;
        transform: translateY(-2px);
    }

    .modal-btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .modal-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-close {
        padding: 0;
        margin: 0;
        background-color: transparent;
        border: 0;
        appearance: none;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: rgba(255, 255, 255, 0.8);
        text-shadow: none;
        opacity: 0.8;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .btn-close:hover {
        opacity: 1;
        background-color: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }

    /* Mobile-specific adjustments */
    @media (max-width: 575px) {
        .modal {
            padding: 10px;
            align-items: flex-start;
            padding-top: 40px;
        }

        .modal-dialog {
            max-width: 100%;
            margin: 0;
        }

        .modal-content {
            max-height: 80vh;
            height: 50vh !important;
        }

        .modal-header {
            padding: 16px;
        }

        .modal-title {
            font-size: 1.1rem;
        }

        .modal-body {
            padding: 16px;
        }

        .modal-main-text {
            font-size: 1rem;
        }

        .modal-subtext {
            font-size: 0.9rem;
        }

        .modal-footer {
            flex-direction: column;
            padding: 16px;
        }

        .modal-btn {
            width: 100%;
            min-width: auto;
        }
    }

    /* For very small devices */
    @media (max-width: 340px) {
        .modal-title {
            font-size: 1rem;
        }

        .modal-title i {
            font-size: 1.2rem;
        }

        .modal-btn {
            padding: 10px 16px;
            font-size: 0.9rem;
        }
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
        <div class="container">
            <div class="min-h-screen bg-gray-100 py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-3xl font-bold text-center text-gray-900">Hazari Card Games</h1>
                                <p class="text-gray-600 text-center mt-2">Join or create exciting card game matches</p>
                            </div>
                            <div class="flex gap-4 text-center">
                                <a class="btn btn-primary mt-3" href="{{ route('games.create') }}"><i class="fas fa-plus mr-2"></i>Create New Game</a>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="card coupon-card mb-3 mt-3 text-center">
                        <div class="card-body">
                            <nav class="-mb-px flex space-x-8">
                                <button wire:click="$set('filter', 'all')"
                                        class="btn  mt-3
                                            {{ $filter === 'all' ? 'btn-primary' : 'btn-warning' }}">
                                    All Games
                                    <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs">
                                        {{ \App\Models\HajariGame::count() }}
                                    </span>
                                </button>

                                <button wire:click="$set('filter', 'available')"
                                        class="btn  mt-3
                                            {{ $filter === 'available' ? 'btn-primary' : 'btn-warning' }}">
                                    Available to Join
                                    <span class="ml-2 bg-green-100 text-green-800 py-0.5 px-2.5 rounded-full text-xs">
                                        {{ \App\Models\HajariGame::where('status', 'pending')->whereDoesntHave('participants', function($q) { $q->where('user_id', Auth::id()); })->count() }}
                                    </span>
                                </button>

                                <button wire:click="$set('filter', 'my_games')"
                                        class="btn  mt-3
                                            {{ $filter === 'my_games' ? 'btn-primary' : 'btn-warning' }}">
                                    My Games
                                    <span class="ml-2 bg-blue-100 text-blue-800 py-0.5 px-2.5 rounded-full text-xs">
                                        {{ \App\Models\HajariGame::where('creator_id', Auth::id())->count() }}
                                    </span>
                                </button>

                                <button wire:click="$set('filter', 'invitations')"
                                        class="btn  mt-3
                                            {{ $filter === 'invitations' ? 'btn-primary' : 'btn-warning' }}">
                                    Invitations
                                    <span class="ml-2 bg-yellow-100 text-yellow-800 py-0.5 px-2.5 rounded-full text-xs">
                                        {{ \App\Models\HajariGameInvitation::where('invitee_id', Auth::id())->where('status', 'pending')->count() }}
                                    </span>
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Games Grid -->
                    <div class="row gy-3">
                        @forelse($games as $game)
                            <x-game-card :game="$game" :current-user="Auth::user()" />
                        @empty
                            <div class="col-span-full">
                                <x-empty-state
                                    title="No games found"
                                    description="There are no games matching your current filter."
                                    :show-create-button="$filter !== 'invitations'" />
                            </div>
                        @endforelse
                    </div>
                    {{-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($games as $game)
                            <x-game-card :game="$game" :current-user="Auth::user()" />
                        @empty
                            <div class="col-span-full">
                                <x-empty-state
                                    title="No games found"
                                    description="There are no games matching your current filter."
                                    :show-create-button="$filter !== 'invitations'" />
                            </div>
                        @endforelse
                    </div> --}}

                    <!-- Pagination -->
                    @if($games->hasPages())
                        <div class="mt-3 mb-5 pb-5">
                            {{ $games->links() }}
                        </div>
                    @endif
                </div>

                <!-- Success/Error Messages -->
                @if (session()->has('success'))
                    <x-notification type="success" :message="session('success')" />
                @endif

                @if (session()->has('error'))
                    <x-notification type="error" :message="session('error')" />
                @endif
                <livewire:components.toast />
            </div>
        </div>
    </div>

      <!-- Confirmation2 Modal -->
    <div class="modal-backdrop" wire:click="$set('showConfirmationModal', false)" style="display: {{ $showConfirmationModal ? 'block' : 'none' }};"></div>

    <div class="modal" tabindex="-1" style="display: {{ $showConfirmationModal ? 'flex' : 'none' }};">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-circle"></i>
                        Confirm Bid Deduction
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showConfirmationModal', false)" aria-label="Close">
                        &times;
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-main-text">
                        {{ $bid_amount }} Credit has been deducted from your account.
                    </p>
                    <p class="modal-subtext">
                        This amount will be deposited into the Admin's account and will be transferred to the winner after the game ends.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn modal-btn-secondary" wire:click="$set('showConfirmationModal', false)">
                        <i class="fas fa-times"></i>Cancel
                    </button>
                    <button type="button" class="modal-btn modal-btn-primary" wire:click="joinGame({{ $gameId }})">
                        <i class="fas fa-check"></i>Confirm
                    </button>
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

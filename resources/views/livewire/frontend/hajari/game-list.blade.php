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
                        <div class="mt-8">
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
    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>

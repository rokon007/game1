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
            .user-setting {
            height: 40px;
            border-radius: 30px;
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgb(0 0 0 / 8%);
            }
            .user-img {
            width: 40px;
            height: 40px;
            padding: 4px;
            border-radius: 50%;
            }
            .user-name {
            font-size: 15px;
            color: #5e636b;
            font-weight: 500;
            padding-right: 10px;
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
            <div class="checkout-wrapper-area py-3">
                <div class="credit-card-info-wrapper">
                    <h2 class="text-lg font-semibold mb-4 text-center">Create New Hazari Game</h2>

                    <form wire:submit.prevent="createGame" class="pay-credit-card-form">

                        <div class="mb-3">
                            <label>Game Title</label>
                            <input type="text" wire:model="title"
                                class="form-control focus:outline-none focus:ring-2 focus:ring-blue-500" id="title"
                                placeholder="Enter game title">
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label>Description (Optional)</label>
                            <textarea wire:model="description" rows="3"
                                    class="form-control focus:outline-none focus:ring-2 focus:ring-blue-500" id="description"
                                    placeholder="Game description..."></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Bid Amount (৳)</label>
                                <input type="number" wire:model="bid_amount" min="1" max="10000"
                                    class="form-control focus:outline-none focus:ring-2 focus:ring-blue-500" id="bid_amount">
                                @error('bid_amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label>Scheduled Time</label>
                                <input type="datetime-local" wire:model="scheduled_at"
                                    class="form-control focus:outline-none focus:ring-2 focus:ring-blue-500" id="scheduled_at">
                                @error('scheduled_at') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Invite Players</label>
                            <div class="relative">
                                <div class="mb-3">
                                    <input type="text" wire:model.live="search_users"
                                        class="form-control focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Search users by name or email...">
                                </div>

                                @if(!empty($available_users))
                                    <div class="notification-area pb-2">
                                        <div class="list-group" >
                                            @foreach($available_users as $user)
                                                <a style="cursor: pointer;" wire:click="addUser({{ $user['id'] }})" class="list-group-item list-group-item-action d-flex align-items-start gap-3 mb-3 ">
                                                    <div class="position-relative">
                                                        @if($user['avatar'])
                                                            <img src="{{ $user['avatar'] }}" alt="{{ $user['unique_id']  }}"
                                                                class="rounded-circle" width="48" height="48" >
                                                        @else
                                                            <div class="rounded-circle {{$user['is_online'] ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                                <strong>{{ strtoupper(substr($user['name'], 0, 1)) }}</strong>
                                                            </div>
                                                        @endif
                                                        <span
                                                            class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"
                                                            style="display: {{$user['is_online'] ? 'block' : 'none'}};">
                                                        </span>
                                                    </div>
                                                    <p class="font-medium m-0 agent-name" title="{{ $user['unique_id'] }}">{{ $user['unique_id'] }}</p>
                                                </a>

                                                {{-- <div wire:click="addUser({{ $user['id'] }})"
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b">
                                                    <div class="font-medium">{{ $user['name'] }}</div>
                                                    <div class="text-sm text-gray-500">{{ $user['email'] }}</div>
                                                </div> --}}
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($invited_users))
                                <div class="mt-3">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Invited Players:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($invited_users as $userId)
                                            @php $user = \App\Models\User::find($userId) @endphp


                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                                {{-- <div class="position-relative">
                                                        @if($user['avatar'])
                                                            <img src="{{ $user->avatar }}" alt="{{ $user->unique_id  }}"
                                                                class="user-img" >
                                                        @else
                                                            <div class="user-img {{$user->is_online ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                                <strong>{{ strtoupper(substr($user->name, 0, 1)) }}</strong>
                                                            </div>
                                                        @endif
                                                        <span
                                                            class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"
                                                            style="display: {{$user->is_online ? 'block' : 'none'}};">
                                                        </span>
                                                    </div> --}}

                                                {{-- <div class="user-name d-none d-sm-block">
                                                    {{ $user->unique_id }}
                                                </div> --}}
                                                    <div class="d-flex">
                                                        @if($user['avatar'])
                                                            <img src="{{ $user->avatar }}" alt="{{ $user->unique_id  }}"
                                                                class="rounded-circle" width="15" height="15" >
                                                        @else
                                                            <div class="rounded-circle {{$user->is_online ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 25px; height: 25px;">
                                                                <strong>{{ strtoupper(substr($user->name, 0, 1)) }}</strong>
                                                            </div>
                                                        @endif
                                                        {{ $user->unique_id }}
                                                        <button type="button" wire:click="removeUser({{ $userId }})"
                                                                class="ml-2 text-blue-600 hover:text-blue-800">×</button>
                                                    </div>
                                            </span>







                                            {{-- <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                                {{ $user->name }}
                                                <button type="button" wire:click="removeUser({{ $userId }})"
                                                        class="ml-2 text-blue-600 hover:text-blue-800">×</button>
                                            </span> --}}
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex text-center">
                            <a href="{{ route('games.index') }}"
                            class="btn btn-danger">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.delay.long wire:target="createGame" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Create Game
                            </button>
                        </div>
                    </form>
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

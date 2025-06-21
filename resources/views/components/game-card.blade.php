<div class="col-12">
    <!-- Single Vendor -->
    <div class="single-vendor-wrap bg-img p-4 bg-overlay" style="background-image: url('img/bg-img/12.jpg')">
        <div class="flex justify-between items-start mb-3">
            <h6 class="vendor-title text-white">
                {{ $game->title }}
            </h6>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $getStatusBadge()['class'] }}">
                {{ $getStatusBadge()['text'] }}
            </span>
        </div>
        <div class="vendor-info">
            {{-- <p class="mb-1 text-white">
                <i class="ti ti-map-pin me-1"></i>
                Dhaka, Bangladesh
            </p> --}}
            @if($game->description)
                <p class="mb-1 text-white">{{ $game->description }}</p>
            @endif
            <div class="flex items-center text-sm text-white">
                <i class="fas fa-user mr-2"></i>
                <span>Created by <span class="font-medium">{{ $game->creator->name }}</span></span>
            </div>

            <div class="flex items-center text-sm text-white">
                <i class="fas fa-coins mr-2"></i>
                <span>Bid Amount: <span class="font-semibold text-green-600">৳{{ number_format($game->bid_amount, 2) }}</span></span>
            </div>

            <div class="flex items-center text-sm text-white">
                <i class="fas fa-clock mr-2"></i>
                <span>{{ $game->scheduled_at->format('M d, Y h:i A') }}</span>
            </div>

            <div class="flex items-center text-sm text-white">
                <i class="fas fa-users mr-2"></i>
                <span>{{ $getParticipantsCount() }}/{{ $game->max_players }} Players</span>
            </div>

            {{-- <div class="ratings lh-1">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-user mr-2"></i>
                    <span>Created by <span class="font-medium">{{ $game->creator->name }}</span></span>
                </div>
                <span class="text-white">
                    (99% Positive Seller)
                </span>
            </div> --}}
        </div>

        <!-- Players Preview -->
        <div class="px-6 pb-4">
            <div class="flex items-center space-x-2">
                <span class="text-xs text-white">Players:</span>
                <div class="flex -space-x-2">
                    @foreach($game->participants->take(4) as $participant)
                        <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium border-2 border-white"
                            title="{{ $participant->user->name }}">
                            {{ substr($participant->user->name, 0, 1) }}
                        </div>
                    @endforeach

                    @if($getParticipantsCount() > 4)
                        <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-xs font-medium border-2 border-white">
                            +{{ $getParticipantsCount() - 4 }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 border-t">
            @if($isCreator())
                <!-- Creator Actions -->
                <div class="flex gap-2">
                    <a href="{{ route('games.show', $game) }}"
                    class="flex-1 btn btn-primary btn-sm mt-3">
                        Manage Game
                    </a>
                    @if($game->status === 'pending')
                        <button class="btn btn-danger btn-sm mt-3">
                            Cancel
                        </button>
                    @endif
                </div>
            @elseif($isParticipant())
                <!-- Participant Actions -->
                <a href="{{ route('games.show', $game) }}"
                class="btn btn-success btn-sm mt-3">
                    @if($game->status === 'playing')
                        Join Game Room
                    @else
                        View Game
                    @endif
                </a>
            @elseif($canJoin())
                <!-- Join Actions -->
                <div class="flex gap-2">
                    <button wire:click="joinGame({{ $game->id }})"
                            class="flex-1 btn btn-primary btn-sm mt-3">
                        Join Game
                    </button>
                    <button wire:click="requestToJoin({{ $game->id }})"
                            class="btn btn-secondary btn-sm mt-3">
                        Request
                    </button>
                </div>
            @else
                <!-- Cannot Join -->
                <div class="text-center text-danger text-sm py-2">
                    @if($game->status === 'completed')
                        Game Completed
                    @elseif($game->status === 'playing')
                        Game in Progress
                    @elseif($getParticipantsCount() >= $game->max_players)
                        Game Full
                    @else
                        Cannot Join
                    @endif
                </div>
            @endif
        </div>
        <!-- Vendor Profile-->
        <div class="vendor-profile shadow">
            <figure class="m-0">
                <img src="img/product/dw.png" alt="">
            </figure>
        </div>
    </div>
</div>






{{-- <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
    <!-- Game Header -->
    <div class="p-6 pb-4">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $game->title }}</h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $getStatusBadge()['class'] }}">
                {{ $getStatusBadge()['text'] }}
            </span>
        </div>

        @if($game->description)
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $game->description }}</p>
        @endif

        <!-- Game Info -->
        <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-user mr-2"></i>
                <span>Created by <span class="font-medium">{{ $game->creator->name }}</span></span>
            </div>

            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-coins mr-2"></i>
                <span>Bid Amount: <span class="font-semibold text-green-600">৳{{ number_format($game->bid_amount, 2) }}</span></span>
            </div>

            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-clock mr-2"></i>
                <span>{{ $game->scheduled_at->format('M d, Y h:i A') }}</span>
            </div>

            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-users mr-2"></i>
                <span>{{ $getParticipantsCount() }}/{{ $game->max_players }} Players</span>
            </div>
        </div>
    </div>

    <!-- Players Preview -->
    <div class="px-6 pb-4">
        <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-500">Players:</span>
            <div class="flex -space-x-2">
                @foreach($game->participants->take(4) as $participant)
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium border-2 border-white"
                         title="{{ $participant->user->name }}">
                        {{ substr($participant->user->name, 0, 1) }}
                    </div>
                @endforeach

                @if($getParticipantsCount() > 4)
                    <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-xs font-medium border-2 border-white">
                        +{{ $getParticipantsCount() - 4 }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="px-6 py-4 bg-gray-50 border-t">
        @if($isCreator())
            <!-- Creator Actions -->
            <div class="flex gap-2">
                <a href="{{ route('games.show', $game) }}"
                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition-colors">
                    Manage Game
                </a>
                @if($game->status === 'pending')
                    <button class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                        Cancel
                    </button>
                @endif
            </div>
        @elseif($isParticipant())
            <!-- Participant Actions -->
            <a href="{{ route('games.show', $game) }}"
               class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition-colors">
                @if($game->status === 'playing')
                    Join Game Room
                @else
                    View Game
                @endif
            </a>
        @elseif($canJoin())
            <!-- Join Actions -->
            <div class="flex gap-2">
                <button wire:click="joinGame({{ $game->id }})"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                    Join Game
                </button>
                <button wire:click="requestToJoin({{ $game->id }})"
                        class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                    Request
                </button>
            </div>
        @else
            <!-- Cannot Join -->
            <div class="text-center text-gray-500 text-sm py-2">
                @if($game->status === 'completed')
                    Game Completed
                @elseif($game->status === 'playing')
                    Game in Progress
                @elseif($getParticipantsCount() >= $game->max_players)
                    Game Full
                @else
                    Cannot Join
                @endif
            </div>
        @endif
    </div>
</div> --}}

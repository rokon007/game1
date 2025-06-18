<div>
    <div class="container" style="display: {{ $listMode ? 'block' : 'none' }}">
        @if(count($users) > 0)
            <div class="pb-3 pt-3">
                <div class="">
                    <div class="section-heading d-flex align-items-center justify-content-between dir-rtl">
                        <h6>Start a new conversation</h6>
                        <a class="btn btn-sm btn-light" wire:click="allUsers" style="cursor: pointer">
                            Users <i class="ms-1 ti ti-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Collection Slide -->
                    <div class="collection-slide owl-carousel owl-loaded owl-drag">
                        <div class="owl-stage-outer">
                            <div class="owl-stage d-flex" wire:ignore>
                                @foreach($users as $user)
                                    <div class="owl-item px-2">
                                        <div class="card collection-card text-center position-relative p-3">
                                            <a style="cursor: pointer;" wire:key="user-{{ $user->id }}" wire:click="startConversation({{ $user->id }})">
                                                @if($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="{{ $user->unique_id }}"
                                                        class="rounded-circle mx-auto mb-2"
                                                        style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-2"
                                                        style="width: 60px; height: 60px; font-size: 20px;">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </a>
                                            <p class="font-medium m-0">{{ $user->unique_id }}</p>

                                            <span
                                                class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-light rounded-circle"
                                                style="width: 10px; height: 10px; {{ in_array($user->id, $onlineUsers) ? 'display: block;' : 'display: none;' }}">
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Optional: Custom Nav Buttons if needed -->
                        {{-- <div class="owl-nav">
                            <button type="button" role="presentation" class="owl-prev">
                                <span aria-label="Previous">‹</span>
                            </button>
                            <button type="button" role="presentation" class="owl-next">
                                <span aria-label="Next">›</span>
                            </button>
                        </div> --}}
                    </div>
                </div>
            </div>
        @endif


        <div class="notification-area pb-2">
            <div class="list-group" wire:ignore>
                @forelse($conversations as $conversation)
                    @php $user = $conversation->users->first(); @endphp
                    <a
                        wire:key="conversation-{{ $conversation->id }}"
                        wire:click="selectConversation({{ $conversation->id }})"
                        class="list-group-item list-group-item-action d-flex align-items-start gap-3 mb-3 {{ $selectedConversation == $conversation->id ? 'active' : '' }}"
                        style="cursor: pointer;"
                    >
                        <!-- Avatar -->
                        <div class="position-relative">
                            @if($user)
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" class="rounded-circle" width="48" height="48" alt="{{ $user->unique_id }}">
                                @else
                                    <div class="rounded-circle {{$conversation->unread_count > 0 ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <strong>{{ strtoupper(substr($user->name, 0, 1)) }}</strong>
                                    </div>
                                @endif

                                <span
                                    class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"
                                    style="{{ in_array($user->id, $onlineUsers) ? '' : 'display: none;' }}">
                                </span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $conversation->is_group ? $conversation->name : $user?->unique_id }}</strong>
                                <small class="text-muted">{{ $conversation->lastMessage?->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small text-truncate w-75">
                                    {{ $conversation->lastMessage?->body ?? 'Start a conversation' }}
                                </span>
                                @if($conversation->unread_count > 0)
                                    <small class="text-muted">New</small> <span class="badge bg-primary rounded-pill text-white">{{ $conversation->unread_count }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-3 text-center text-muted">No conversations found.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="container" style="display: {{ $allUsersMode ? 'block' : 'none' }}">
        <div class="col-md-12 border-end vh-100 overflow-auto" style="min-width: 300px;">
            <!-- Search -->
            <div class="p-3 border-bottom">
                <input
                    type="text"
                    wire:model.live="query"
                    placeholder="Search users..."
                    class="form-control"
                >
            </div>
            <!-- New Users -->
            @if(count($users) > 0)
                <div class="mt-2">
                    <div class="p-3">
                        <h6 class="fw-semibold mb-2">Start a new conversation</h6>

                        @foreach($users as $user)
                            <div
                                wire:key="user-{{ $user->id }}"
                                wire:click="startConversation({{ $user->id }})"
                                class="d-flex align-items-center gap-3 p-2 rounded hover-shadow bg-white mt-2 mb-2"
                                style="cursor: pointer;"
                            >
                                <!-- Avatar -->
                                <div class="position-relative">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar }}" class="rounded-circle" width="40" height="40" alt="{{ $user->unique_id }}">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <strong>{{ strtoupper(substr($user->name, 0, 1)) }}</strong>
                                        </div>
                                    @endif

                                    <span
                                        class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"
                                        style="{{ in_array($user->id, $onlineUsers) ? '' : 'display: none;' }}">
                                    </span>
                                </div>

                                <!-- Info -->
                                <div class="flex-grow-1">
                                    <div class="fw-medium">{{ $user->unique_id }}</div>
                                    {{-- <div class="small text-muted">{{ $user->email ?? $user->mobile }}</div> --}}
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            console.log('ChatList component initialized');
        });
    </script>
</div>

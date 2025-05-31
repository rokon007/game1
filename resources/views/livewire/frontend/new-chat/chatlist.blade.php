

<div style="display :{{$chatList ? 'block' : 'none'}};">
    <style>
        .w-40-sm {
            width: 200px !important;
            }
            @media (min-width: 768px) {
            .w-40-sm {
                width: 300px !important;
            }
            }
            .agent-name {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100px; /* বা আপনার পছন্দসই ম্যাক্স width */
                display: inline-block;
                vertical-align: middle;
            }

            .collection-card {
                min-height: 100px; /* কার্ডের মিনিমাম হাইট ফিক্স করুন */
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .owl-item {
                width: 120px !important; /* ফিক্সড width সেট করুন */
            }
    </style>
    @if(count($users) > 0)
            <div class="pb-3 pt-3">
                <div class="">
                    <div class="section-heading d-flex align-items-center justify-content-between dir-rtl">
                        <h6>Select an agent for the conversation</h6>
                    </div>

                    <!-- Collection Slide -->
                    <div class="collection-slide owl-carousel owl-loaded owl-drag">
                        <div class="owl-stage-outer">
                            <div class="owl-stage d-flex" wire:ignore>
                                @foreach($users as $user)
                                    <div class="owl-item px-2">
                                        <div class="card collection-card text-center position-relative p-3">
                                            <a class="position-relative" style="cursor: pointer;" wire:click="createConversation({{$user->id}})">
                                                @if($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                                        class="rounded-circle mx-auto mb-2"
                                                        style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-2"
                                                        style="width: 60px; height: 60px; font-size: 20px;">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span
                                                    class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"
                                                    style="{{ $user->is_online ? 'display: block;' : 'display: none;' }}">
                                                </span>
                                            </a>
                                            <p class="font-medium m-0 agent-name" title="{{ $user->name }}">{{ $user->name }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    <h5 class="my-2 mb-2 ms-2 fs-5 text-secondary">Chats</h5>
        <div class="notification-area pb-2">
            <div class="list-group" >
                @if(count($conversations) > 0)
                    @foreach($conversations as $conversation)
                        <a
                            wire:click="chatUserSelected({{$conversation}}, {{$conversation->receiverInverseRelation->id}}, {{$conversation->senderInverseRelation->id}})"
                            class="list-group-item list-group-item-action d-flex align-items-start gap-3 mb-3 "
                            style="cursor: pointer;"
                            >
                            @if($conversation->receiver_id == auth()->user()->id)
                                <!-- Avatar -->
                                <div class="position-relative">
                                    @php
                                        $unread_count = $conversation->unreadMessages()->count();
                                    @endphp
                                    @if($conversation->senderInverseRelation->avatar)
                                        <img src="{{ $conversation->senderInverseRelation->avatar }}" class="rounded-circle" width="48" height="48" alt="{{ $user->name }}">
                                    @else

                                        <div class="rounded-circle {{$unread_count > 0 ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <strong>{{ strtoupper(substr($conversation->senderInverseRelation->name, 0, 1)) }}</strong>
                                        </div>
                                    @endif
                                    <span
                                        class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"
                                        style="display: {{$conversation->senderInverseRelation->is_online ? 'block' : 'none'}};">
                                    </span>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{$conversation->senderInverseRelation->name}}</strong>
                                        <small class="text-muted">{{ $conversation?->updated_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted small text-truncate w-40-sm">
                                            {{ $conversation->last_message ?? 'Start a conversation' }}
                                        </span>
                                        @if($unread_count > 0)
                                            <small class="text-muted">New</small> <span class="badge bg-primary rounded-pill text-white">{{ $unread_count }}</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <!-- Avatar -->
                                <div class="position-relative">
                                    @php
                                        $unread_count = $conversation->unreadMessages()->count();
                                    @endphp
                                    @if($conversation->receiverInverseRelation->avatar)
                                        <img src="{{ $conversation->receiverInverseRelation->avatar }}" class="rounded-circle" width="48" height="48" alt="{{ $user->name }}">
                                    @else

                                        <div class="rounded-circle {{$unread_count > 0 ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <strong>{{ strtoupper(substr($conversation->receiverInverseRelation->name, 0, 1)) }}</strong>
                                        </div>
                                    @endif
                                    <span
                                        class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"
                                        style="">
                                    </span>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{$conversation->receiverInverseRelation->name}}</strong>
                                        <small class="text-muted">{{ $conversation?->updated_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted small text-truncate w-40-sm">
                                            {{ $conversation->last_message ?? 'Start a conversation' }}
                                        </span>
                                        @if($unread_count > 0)
                                            <small class="text-muted">New</small> <span class="badge bg-primary rounded-pill text-white">{{ $unread_count }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </a>
                    @endforeach
                @else
                    <div class="p-3 text-center text-muted">No conversations found.</div>
                @endif
            </div>
        </div>
</div>


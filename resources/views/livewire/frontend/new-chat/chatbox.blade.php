 <div
    x-data="{
        scrollTop: 0,
        conversationElement: null,
        isAtBottom: true,
        init() {
            this.conversationElement = document.getElementById('messages-container');
            this.scrollToBottom();

            Livewire.on('new-message', () => {
                if (this.isAtBottom) {
                    this.$nextTick(() => {
                        this.scrollToBottom();
                    });
                }
            });

            this.conversationElement.addEventListener('scroll', () => {
                this.scrollTop = this.conversationElement.scrollTop;
                this.isAtBottom = this.conversationElement.scrollHeight -
                                 this.conversationElement.scrollTop -
                                 this.conversationElement.clientHeight < 50;

                if (this.scrollTop <= 0) {
                    Livewire.dispatch('loadMore1');
                }
            });
        },
        scrollToBottom() {
            if (this.conversationElement) {
                this.conversationElement.scrollTop = this.conversationElement.scrollHeight;
                this.isAtBottom = true;
            }
        }
    }"
    x-init="init()"
    id="messages-container"
    class="chat-wrapper"
    style="overflow-y: auto; max-height: 500px;">

    <style>
        .chat-wrapper {
            width: 100%;
            max-width: 100%;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .chat-wrapper::-webkit-scrollbar {
            display: none;
        }
        .chat-wrapper {
            width: 100%;
            max-width: 100%;
        }
        .chat-container {
            height: 80vh;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
        }
        .chat-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color:#CCCCFF;
            padding: 12px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            width: 100%;
        }
        .messages-container {
            flex: 1;
            /* overflow-y: auto; */
            padding: 16px 12px 80px;
            width: 100%;
        }
        .message-row {
            display: flex;
            margin-bottom: 16px;
            align-items: flex-end;
            width: 100%;
        }
        .message-row.sender {
            justify-content: flex-end;
        }
        .message-row.receiver {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 75%;
            min-width: 120px;
            padding: 10px 14px;
            border-radius: 18px;
            position: relative;
            word-break: break-word;
        }
        .sender .message-bubble {
            background-color: #007bff;
            color: white;
            border-bottom-right-radius: 4px;
            margin-left: 40px;
        }
        .receiver .message-bubble {
            background-color: #00FFFF;
            color: #333;
            border-bottom-left-radius: 4px;
            margin-right: 40px;
        }
        .message-time {
            font-size: 0.75rem;
            color: #777;
            margin-top: 4px;
        }
        .sender .message-time {
            text-align: right;
            padding-right: 8px;
        }
        .receiver .message-time {
            text-align: left;
            padding-left: 8px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 8px;
        }
        .online-indicator {
            width: 10px;
            height: 10px;
            background-color: #28a745;
            border-radius: 50%;
            display: inline-block;
            margin-left: 6px;
        }
        @media (max-width: 576px) {
            .chat-header {
                width: 100%;
                margin-left: 0;
                padding-left: 15px;
                padding-right: 15px;
            }
            .message-bubble {
                max-width: 85%;
            }
        }
    </style>

    <div class="chat-container">
        @if($selectedConversation)
            <!-- হেডার সেকশন -->
            <div class="chat-header border-bottom" wire:poll.5s>
                <div class="d-flex align-items-center">
                    @if($selectedConversation->receiver_id == auth()->user()->id)
                        @if($senderInstance->avatar)
                            <img class="user-avatar" src="{{ asset($senderInstance->avatar) }}" alt="{{ $senderInstance->name }}">
                        @else
                            <img class="user-avatar" src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="{{ $senderInstance->name }}">
                        @endif
                        <div class="ml-2">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold">{{ $senderInstance->name }}</span>
                                 @if($senderInstance->is_online)
                                    <span class="online-indicator ml-1"></span>
                                 @endif
                            </div>
                             @if($senderInstance->is_online)
                                <small class="text-muted">Online</small>
                            @else
                                <small class="text-muted">Ofline, Last seen at :{{ $senderInstance->last_seen_at ? $senderInstance->last_seen_at->diffForHumans() : 'Never' }}</small>
                            @endif
                        </div>
                    @else
                        @if($receiverInstance->avatar)
                            <img class="user-avatar" src="{{ asset($receiverInstance->avatar) }}" alt="{{ $receiverInstance->name }}">
                        @else
                            <img class="user-avatar" src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="{{ $receiverInstance->name }}">
                        @endif
                        <div class="ml-2">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold">{{ $receiverInstance->name }}</span>
                                @if($receiverInstance->is_online)
                                    <span class="online-indicator ml-1"></span>
                                 @endif
                            </div>
                            @if($receiverInstance->is_online)
                                <small class="text-muted">Online</small>
                            @else
                                <small class="text-muted">Ofline, Last seen at :{{ $receiverInstance->last_seen_at ? $receiverInstance->last_seen_at->diffForHumans() : 'Never' }}</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- মেসেজ কন্টেইনার -->
            <div id="messages-container" class="messages-container">
                @foreach($messages as $message)
                    @if($message->sender_id == auth()->user()->id)
                        <!-- সেন্ডার মেসেজ -->
                        <div class="message-row sender">
                            <div>
                                <div class="message-bubble">
                                    {{ $message->body }}
                                </div>
                                <div class="message-time">
                                    {{ $message->created_at->format('h:i A') }}
                                    <span class="message-status">
                                        @if($message->read)
                                            <i class="fas fa-check-double text-info" title="Read"></i>
                                        @else
                                            <i class="fas fa-check" title="Sent"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <img class="user-avatar" src="{{ auth()->user()->avatar ? asset(auth()->user()->avatar) : asset('assets/backend/upload/image/user/user.jpg') }}" alt="You">
                        </div>
                    @else
                        <!-- রিসিভার মেসেজ -->
                        <div class="message-row receiver">
                            <img class="user-avatar" src="{{ $message->userInverseRelation->avatar ? asset($message->userInverseRelation->avatar) : asset('assets/backend/upload/image/user/user.jpg') }}" alt="{{ $message->userInverseRelation->name }}">
                            <div>
                                <div class="message-bubble">
                                    {{ $message->body }}
                                </div>
                                <div class="message-time">
                                    {{ $message->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <!-- নো কনভারসেশন সিলেক্টেড স্টেট -->
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center">
                    <i class="far fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No conversation selected</h5>
                </div>
            </div>
        @endif
    </div>
</div>

 {{-- <div
    x-data="{
        scrollTop: 0,

        init() {
            this.conversationElement = document.getElementById('messages-container');
            this.scrollToBottom();
        },
        scrollToBottom() {
            this.$nextTick(() => {
                if (this.conversationElement) {
                    this.conversationElement.scrollTop = this.conversationElement.scrollHeight;
                }
            });
        }
    }"
    x-init="init()"
    @scrol-bottom.window="scrollToBottom()"
    @scroll="
        scrollTop = $el.scrollTop;
        if (scrollTop <= 0) {
            Livewire.dispatch('loadMore1');
        }
    "
    id="messages-container"



    class="chat-wrapper">
    <style>
        .chat-wrapper {
            width: 100%;
            max-width: 100%;
        }
        .chat-container {
            height: 80vh;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
        }
        .chat-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color:#CCCCFF;
            padding: 12px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            width: 100%;
        }
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px 80px;
            width: 100%;
        }
        .message-row {
            display: flex;
            margin-bottom: 16px;
            align-items: flex-end;
            width: 100%;
        }
        .message-row.sender {
            justify-content: flex-end;
        }
        .message-row.receiver {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 75%;
            min-width: 120px;
            padding: 10px 14px;
            border-radius: 18px;
            position: relative;
            word-break: break-word;
        }
        .sender .message-bubble {
            background-color: #007bff;
            color: white;
            border-bottom-right-radius: 4px;
            margin-left: 40px;
        }
        .receiver .message-bubble {
            background-color: #00FFFF;
            color: #333;
            border-bottom-left-radius: 4px;
            margin-right: 40px;
        }
        .message-time {
            font-size: 0.75rem;
            color: #777;
            margin-top: 4px;
        }
        .sender .message-time {
            text-align: right;
            padding-right: 8px;
        }
        .receiver .message-time {
            text-align: left;
            padding-left: 8px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 8px;
        }
        .online-indicator {
            width: 10px;
            height: 10px;
            background-color: #28a745;
            border-radius: 50%;
            display: inline-block;
            margin-left: 6px;
        }
        @media (max-width: 576px) {
            .chat-header {
                width: 100%;
                margin-left: 0;
                padding-left: 15px;
                padding-right: 15px;
            }
            .message-bubble {
                max-width: 85%;
            }
        }
    </style>

    <div class="chat-container">
        @if($selectedConversation)
            <!-- Chat Header -->
            <div class="chat-header border-bottom mt-1">
                <div class="d-flex align-items-center">
                    @if($selectedConversation->receiver_id == auth()->user()->id)
                    @if($senderInstance->avatar)
                        <img class="user-avatar" src="{{ asset($senderInstance->avatar) }}" alt="{{ $senderInstance->name }}">
                    @else
                        <img class="user-avatar" src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="{{ $senderInstance->name }}">
                    @endif
                        <div class="ml-2">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold">{{ $senderInstance->name }}</span>
                                <span class="online-indicator ml-1"></span>
                            </div>
                            <small class="text-muted">Online</small>
                        </div>
                    @else
                        @if($receiverInstance->avatar)
                            <img class="user-avatar" src="{{ asset($receiverInstance->avatar) }}" alt="{{ $senderInstance->name }}">
                        @else
                            <img class="user-avatar" src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="{{ $senderInstance->name }}">
                        @endif
                        <div class="ml-2">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold">{{ $receiverInstance->name }}</span>
                                <span class="online-indicator ml-1"></span>
                            </div>
                            <small class="text-muted">Online</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Messages Container -->
            <div id="messages-container" class="messages-container">
                @foreach($messages as $message)
                    @if($message->sender_id == auth()->user()->id)
                        <!-- Sender Message -->
                        <div class="message-row sender">
                            <div>
                                <div class="message-bubble">
                                    {{ $message->body }}
                                </div>
                                <div class="message-time">
                                    {{ $message->created_at->format('h:i A') }}
                                </div>
                            </div>
                            <img class="user-avatar" src="{{ auth()->user()->avatar ? asset(auth()->user()->avatar) : asset('assets/backend/upload/image/user/user.jpg') }}" alt="You">
                        </div>
                    @else
                        <!-- Receiver Message -->
                        <div class="message-row receiver">
                            <img class="user-avatar" src="{{ $message->userInverseRelation->avatar ? asset($message->userInverseRelation->avatar) : asset('assets/backend/upload/image/user/user.jpg') }}" alt="{{ $message->userInverseRelation->name }}">
                            <div>
                                <div class="message-bubble">
                                    {{ $message->body }}
                                </div>
                                <div class="message-time">
                                    {{ $message->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center">
                    <i class="far fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No conversation selected</h5>
                </div>
            </div>
        @endif
    </div>
</div> --}}

 {{-- <div>
    <style>
        .chat-container {
            height: 80vh;
            max-height: 80vh;
        }
        .chat-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: white;
            padding: 12px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px 80px;
        }
        .message-row {
            display: flex;
            margin-bottom: 16px;
            align-items: flex-end;
        }
        .message-row.sender {
            justify-content: flex-end;
        }
        .message-row.receiver {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 75%;
            min-width: 120px;
            padding: 10px 14px;
            border-radius: 18px;
            position: relative;
            word-break: break-word;
        }
        .sender .message-bubble {
            background-color: #007bff;
            color: white;
            border-bottom-right-radius: 4px;
            margin-left: 40px;
        }
        .receiver .message-bubble {
            background-color:#00FFFF;
            color: #333;
            border-bottom-left-radius: 4px;
            margin-right: 40px;
        }
        .message-time {
            font-size: 0.75rem;
            color: #777;
            margin-top: 4px;
            text-align: right;
        }
        .sender .message-time {
            text-align: right;
            padding-right: 8px;
        }
        .receiver .message-time {
            text-align: left;
            padding-left: 8px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 8px;
        }
        .online-indicator {
            width: 10px;
            height: 10px;
            background-color: #28a745;
            border-radius: 50%;
            display: inline-block;
            margin-left: 6px;
        }
        @media (max-width: 576px) {
            .chat-header, .message-input-container {
                width: 100vw;
                margin-left: -15px;
                padding-left: 15px;
                padding-right: 15px;
            }
            .message-bubble {
                max-width: 85%;
            }
        }
    </style>
    <div class="chat-container d-flex flex-column h-80 mb-4">
        @if($selectedConversation)
            <!-- Chat Header -->
            <div class="chat-header border-bottom">
                <div class="d-flex align-items-center">
                    @if($selectedConversation->receiver_id == auth()->user()->id)
                        <img class="user-avatar" src="{{ asset('storage/'. $senderInstance->avatar) }}">
                        <div class="ml-2">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold">{{$senderInstance->name}}</span>
                                <span class="online-indicator ml-1"></span>
                            </div>
                            <small class="text-muted">Online</small>
                        </div>
                    @else
                        <img class="user-avatar" src="{{ asset('storage/'. $receiverInstance->avatar) }}">
                        <div class="ml-2">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold">{{$receiverInstance->name}}</span>
                                <span class="online-indicator ml-1"></span>
                            </div>
                            <small class="text-muted">Online</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Messages Container -->
            <div class="messages-container">
                @foreach($messages as $message)
                    @if($message->sender_id == auth()->user()->id)
                        <!-- Sender Message -->
                        <div class="message-row sender">
                            <div>
                                <div class="message-bubble">
                                    {{$message->body}}
                                </div>
                                <div class="message-time">
                                    {{$message->created_at->format('h:i A')}}
                                </div>
                            </div>
                            <img class="user-avatar" src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : asset('images/default-avatar.png') }}">
                        </div>
                    @else
                        <!-- Receiver Message -->
                        <div class="message-row receiver">
                            <img class="user-avatar" src="{{ $message->userInverseRelation->avatar ? asset($message->userInverseRelation->avatar) : asset('images/default-avatar.png') }}">
                            <div>
                                <div class="message-bubble">
                                    {{$message->body}}
                                </div>
                                <div class="message-time">
                                    {{$message->created_at->format('h:i A')}}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center">
                    <i class="far fa-comments fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No conversation selected</h5>
                </div>
            </div>
        @endif
    </div>
</div> --}}

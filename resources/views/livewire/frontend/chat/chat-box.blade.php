
    {{-- <div
            x-data="chatBox()"
            x-init="init()"
            wire:poll.visible.10s="loadMessages"
            @new-message.window="handleNewMessage($event.detail)"
            @typing.window="handleTyping($event.detail)"
            @online-status.window="updateOnlineStatus($event.detail)"
            class=""
        > --}}


        <div>

            <!-- Debug Panel -->
<div class="mt-50px" style="position: fixed; top: 10px; right: 10px; background: #000; color: #fff; padding: 10px; border-radius: 5px; font-size: 12px; max-width: 300px;">
    <div><strong>Debug Info:</strong></div>
    <div>User ID: {{ Auth::id() }}</div>
    <div>CSRF: <span id="csrf-token">{{ csrf_token() }}</span></div>
    <div>Socket ID: <span id="socket-id">Not connected</span></div>
    <div>Echo Status: <span id="echo-status">Initializing...</span></div>
    <button onclick="testConnection()" style="margin-top: 5px;">Test Connection</button>
    <button onclick="testPresenceChannel()" style="margin-top: 5px;">Test Presence</button>
</div>

<script>
    function testConnection() {
        console.log('Testing connection...');

        // Test basic authentication
        fetch('/test-auth')
            .then(response => response.json())
            .then(data => {
                console.log('Auth test result:', data);
            })
            .catch(error => {
                console.error('Auth test error:', error);
            });

        // Test broadcasting
        fetch('/test-broadcast')
            .then(response => response.text())
            .then(data => {
                console.log('Broadcast test result:', data);
            })
            .catch(error => {
                console.error('Broadcast test error:', error);
            });
    }

    function testPresenceChannel() {
        const conversationId = prompt('Enter conversation ID:');
        if (conversationId) {
            fetch(`/test-presence/${conversationId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Presence test result:', data);
                })
                .catch(error => {
                    console.error('Presence test error:', error);
                });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Update debug info
        if (window.Echo) {
            document.getElementById('echo-status').textContent = 'Available';

            window.Echo.connector.pusher.connection.bind('connected', () => {
                document.getElementById('socket-id').textContent = window.Echo.socketId();
                document.getElementById('echo-status').textContent = 'Connected';
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                document.getElementById('socket-id').textContent = 'Disconnected';
                document.getElementById('echo-status').textContent = 'Disconnected';
            });
        } else {
            document.getElementById('echo-status').textContent = 'Not Available';
        }
    });
</script>
<div
x-data="{
        scrollTop: 0,
        {{-- conversationElement: null, --}}
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
    style="overflow-y: auto; max-height: 400px;"
>

        @if($conversation)
            <!-- Chat Header -->
            <div class="live-chat-intro mb-3">
                @if($conversation->is_group)
                    <div class="avatar-view">
                        {{ substr($conversation->name ?? 'G', 0, 1) }}
                    </div>
                @elseif($otherUser = $conversation->getOtherUser(auth()->user()))
                    <div class="avatar-view relative">
                        @if($otherUser->avatar)
                            <img src="{{ $otherUser->avatar }}" alt="{{ $otherUser->name }}" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('assets/backend/upload/image/user/user.jpg') }}" alt="">
                        @endif
                        <div
                            {{-- x-show="onlineUsers.includes({{ $otherUser->id ?? 0 }})" --}}
                            class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"
                        ></div>
                    </div>
                @endif

                <div class="status online">
                    @if($conversation->is_group)
                        {{ $conversation->name }}
                    @elseif($otherUser = $conversation->getOtherUser(auth()->user()))
                        {{ $otherUser->name }}
                        <span id="online-status-{{ $otherUser->id }}">
                            {{-- <span x-text="onlineUsers.includes({{ $otherUser->id }}) ? 'Online' : 'Offline'"></span> --}}
                        </span>
                    @endif
                </div>
            </div>


            <!-- Messages Container -->
            <div class="support-wrapper py-3">
                <div class="container">
                    <!-- Live Chat Wrapper-->
                    <div
                            id="messages-container"
                            class="live-chat-wrapper"
                            x-ref="messagesContainer"
                        >
                        @if($loadedMessages)
                            @foreach($messages as $msg)
                                <div
                                    class="message-item {{ $msg->user_id === Auth::id() ? 'user-message-content' : 'agent-message-content d-flex align-items-start' }}"
                                    data-message-id="{{ $msg->id }}"
                                >
                                <!-- Agent Thumbnail-->
                                @if($msg->user_id !=Auth::id() )
                                    <div class="agent-thumbnail me-2 mt-2">
                                        <img src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="">
                                    </div>
                                @endif
                                    <!-- Message content here -->
                                    @if($msg->attachment)
                                        <div class="mb-2">
                                            @if($msg->attachment_type === 'image')
                                                <img src="/storage/{{ $msg->attachment }}" class="rounded-lg max-w-full">
                                            @else
                                                <a href="/storage/{{ $msg->attachment }}" class="flex items-center text-blue-400 hover:underline">
                                                    <span>📎</span>
                                                    <span class="ml-1">Download Attachment</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                    @if($msg->body)
                                    <div class="{{ $msg->user_id === Auth::id() ? 'user-message-text' : 'agent-message-text' }}">
                                        <div class="d-block">
                                            <p>
                                                @if($msg->user_id ===Auth::id() )
                                                    <span class="text-white">
                                                        {{$msg->body}}&nbsp;&nbsp;
                                                        <a style="cursor: pointer;" wire:click='deleteMessage({{$msg->id}})'class="text-red">🗑️</a>
                                                    </span>
                                                @else
                                                    <span>{{$msg->body}}</span>
                                                @endif
                                            </p>

                                        </div>
                                        <span>{{ \Carbon\Carbon::parse($msg->created_at)->format('g:i A') }}</span>
                                    </div>
                                    @endif
                                    </div>
                            @endforeach

                            <!-- Typing Indicator -->
                            <div
                                {{-- x-show="typingUsers.length > 0" --}}
                                class="typing-indicator"
                            >
                                <div class="flex items-center">
                                    <div class="typing-dots">
                                        <span class="dot"></span>
                                        <span class="dot"></span>
                                        <span class="dot"></span>
                                    </div>
                                    {{-- <span x-text="getTypingText()" class="ml-2"></span> --}}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- Message Input -->
                <div class="type-text-form">
                    <form @submit.prevent="$wire.sendMessage()">
                        <div class="form-group file-upload mb-0">
                            <input type="file" id="attachment" wire:model="attachment"><i class="ti ti-plus"></i>
                        </div>
                        <textarea
                            class="form-control"
                            wire:model.live.debounce.500ms="message"
                            wire:keydown.enter.prevent="sendMessage"
                            name="message"
                            cols="30"
                            rows="10"
                            placeholder="Type your message">
                        </textarea>
                        <button type="submit">
                            <svg class="bi bi-arrow-right" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="flex items-center justify-center h-full">
                <p class="text-gray-500">Select a conversation to start chatting</p>
            </div>
        @endif


    {{-- <script>
    document.addEventListener('livewire:init', () => {
        const messageSound = new Audio('/sounds/message-received.mp3');
        messageSound.preload = 'auto';

        Livewire.on('sound', () => {
            messageSound.currentTime = 0;
            messageSound.play().catch(error => {
                console.error('Sound play failed:', error);
            });
        });
    });
</script> --}}


{{-- <script>
    function handleScroll() {
        const container = document.getElementById('messages-container');
        if (container.scrollTop === 0) {
            Livewire.dispatch('loadMoreMessages');
        }
    }
</script> --}}



        {{-- <script>
            function chatBox() {
                return {
                    typingUsers: [],
                    onlineUsers: [],
                    messageSound: new Audio('/sounds/message-received.mp3'),
                    typingSound: new Audio('/sounds/typing.mp3'),

                    init() {
                        console.log('🔵 chatBox initialized');
                        this.messageSound.preload = 'auto';
                        this.typingSound.preload = 'auto';

                        this.scrollToBottom();

                        Livewire.on('messageReceived', (data) => {
                            console.log('📩 Message received via Livewire:', data);
                            this.handleNewMessage(data);
                        });

                        Livewire.on('userTyping', (data) => {
                            console.log('✍️ Typing event via Livewire:', data);
                            this.handleTyping(data);
                        });


                        Livewire.on('scroll', (data) => {
                            console.log('✍️ rokon scroll:', data);
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        });




                        Livewire.on('onlineStatusUpdated', (users) => {
                            console.log('🟢 Online users updated via Livewire:', users);
                            this.updateOnlineStatus(users);
                        });

                        const observer = new MutationObserver(() => {
                            console.log('🔄 DOM mutated, triggering scrollToBottom');
                            this.scrollToBottom();
                        });

                        observer.observe(this.$refs.messagesContainer, {
                            childList: true,
                            subtree: true
                        });
                    },

                    scrollToBottom() {
                        this.$nextTick(() => {
                            if (this.$refs.messagesContainer) {
                                this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                                console.log('⬇️ Scrolled to bottom of messages');
                            }
                        });
                    },

                    handleNewMessage(detail) {
                        console.log('📬 Handling new message:', detail);
                        try {
                            this.messageSound.currentTime = 0;
                            this.messageSound.play();
                            console.log('🔊 Message sound played');
                        } catch (e) {
                            console.error('❌ Error playing message sound:', e);
                        }
                        this.scrollToBottom();
                    },

                    handleTyping(data) {
                        console.log('✍️ Handling typing:', data);
                        if (data.isTyping) {
                            if (!this.typingUsers.includes(data.userId)) {
                                this.typingUsers.push(data.userId);
                                console.log('➕ Added typing user:', data.userId);
                            }

                            try {
                                this.typingSound.currentTime = 0;
                                this.typingSound.play();
                                console.log('🔊 Typing sound played');
                            } catch (e) {
                                console.error('❌ Typing sound error:', e);
                            }
                        } else {
                            this.typingUsers = this.typingUsers.filter(id => id !== data.userId);
                            console.log('➖ Removed typing user:', data.userId);
                        }

                        this.scrollToBottom();
                    },

                    updateOnlineStatus(users) {
                        console.log('🔁 Updating online users:', users);
                        this.onlineUsers = users;
                    },

                    getTypingText() {
                        const count = this.typingUsers.length;
                        this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        console.log('💬 Typing users count:', count);
                        if (count === 1) return 'is typing...';
                        if (count > 1) return 'are typing...';
                        return '';
                    }
                }
            }
        </script> --}}
        <style>
            .avatar-view {
                width: 3.125rem;
                height: 3.125rem;
                border-radius: 50%;
                margin: 0 auto 0.5rem;
                display: block;
            }

            .user-message {
                @apply flex justify-end;
            }

            .other-message {
                @apply flex justify-start;
            }

            .message-content {
                @apply max-w-xs p-3 rounded-lg;
            }

            .user-message .message-content {
                @apply bg-blue-500 text-white rounded-br-none;
            }

            .other-message .message-content {
                @apply bg-gray-200 rounded-bl-none;
            }

            .typing-dots {
                display: inline-flex;
                align-items: center;
                height: 17px;
            }

            .typing-dots .dot {
                display: inline-block;
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background-color: #333;
                animation: typing 1.5s infinite ease-in-out;
                margin: 0 2px;
            }

            .typing-dots .dot:nth-child(2) {
                animation-delay: 0.2s;
            }

            .typing-dots .dot:nth-child(3) {
                animation-delay: 0.4s;
            }

            @keyframes typing {
                0%, 60%, 100% { transform: translateY(0); }
                30% { transform: translateY(-6px); }
            }
        </style>
    </div></div>




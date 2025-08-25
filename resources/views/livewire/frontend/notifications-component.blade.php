<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>Housieblitz|Notifications</title>
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
            .notification-wrapper {
                overflow-y: auto;
                max-height: 500px;
                padding-right: 10px;
                /* স্ক্রলবার লুকানো */
                -ms-overflow-style: none; /* IE and Edge */
                scrollbar-width: none; /* Firefox */
            }
            .notification-wrapper::-webkit-scrollbar {
                display: none; /* Chrome, Safari, and Opera */
            }
            .list-group-item:first-child {
                /* প্রথম নোটিফিকেশন হাইলাইট করা */
                background-color: #f8f9fa;
                border-left: 3px solid #007bff;
            }
            .list-group-item:hover {
                background-color: #e9ecef;
                cursor: pointer;
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
        <!-- Notification List -->
        <div class="container" style="display: {{$detailsMode ? 'none' : 'block'}};">
            <div class="section-heading d-flex align-items-center pt-3 justify-content-between rtl-flex-d-row-r">
                <h6>Notification(s)</h6>
                <span class="text-secondary">Unread: {{ $unreadCount }}</span>
            </div>
              <div
                x-data="{
                    scrollTop: 0,
                    conversationElement: null,
                    isAtBottom: true,
                    init() {
                        this.conversationElement = document.getElementById('notifications-container');
                        this.scrollToTop();

                        Livewire.on('new-notification', () => {
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

                            if (this.isAtBottom) {
                                Livewire.dispatch('loadMore');
                            }
                        });
                    },
                    scrollToTop() {
                        if (this.conversationElement) {
                            this.conversationElement.scrollTop = 0;
                            this.isAtBottom = false;
                        }
                    },
                    scrollToBottom() {
                        if (this.conversationElement) {
                            this.conversationElement.scrollTop = this.conversationElement.scrollHeight;
                            this.isAtBottom = true;
                        }
                    }
                }"
                x-init="init()"
                id="notifications-container"
                class="notification-wrapper"
            >

                <div class="notification-area pb-2">
                    <div class="list-group">
                        @foreach($notifications as $notification)
                            <a class="list-group-item d-flex align-items-center border-0
                                {{ $notification->read_at ? 'readed' : '' }}"
                                style="cursor: pointer"
                                wire:click="details('{{ $notification->id }}')">
                                <span class="noti-icon">
                                    <i class="{{ $notification->read_at ? 'ti ti-check' : 'ti ti-bell-ringing' }}"></i>
                                </span>
                                <div class="noti-info">
                                    <h6 class="mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Details -->
        <div class="container" style="display: {{$detailsMode ? 'block' : 'none'}};">
            @if($selectedNotification)
                <div class="notification-area pt-3 pb-2">
                    <div class="list-group-item d-flex py-3 bg-transparent">
                        <span class="noti-icon">
                            <i class="ti ti-check"></i>
                        </span>
                        <div class="noti-info">
                            <h6>{{ $selectedNotification->data['title'] ?? 'Notification Details' }}</h6>
                            {{-- <p>{{ $selectedNotification->data['text'] ?? 'No details available.' }}</p> --}}

                                {{-- @if(isset($selectedNotification->data['title']))
                                    <h4>{{ $selectedNotification->data['title'] }}</h4>
                                @endif --}}

                                @if(isset($selectedNotification->data['text']))
                                    <p>{{ $selectedNotification->data['text'] }}</p>
                                @endif

                                @if(isset($selectedNotification->data['user']))
                                    <p><strong>User:</strong> {{ $selectedNotification->data['user'] }}</p>
                                @endif

                                @if(isset($selectedNotification->data['amount']))
                                    <p><strong>Amount:</strong> {{ $selectedNotification->data['amount'] }}</p>
                                @endif

                                @if(isset($selectedNotification->data['method']))
                                    <p><strong>Method:</strong> {{ $selectedNotification->data['method'] }}</p>
                                @endif

                                @if(isset($selectedNotification->data['transaction_id']))
                                    <p><strong>Transaction ID:</strong> {{ $selectedNotification->data['transaction_id'] }}</p>
                                @endif

                                @if(isset($selectedNotification->data['game_entry_url']))
                                     <a class="btn btn-light" href="{{ $selectedNotification->data['game_entry_url'] }}">Join Now</a>
                                @endif

                            <a class="btn btn-light" href="#">View More</a>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary" wire:click="backToList">Back to Notifications</button>
            @endif
        </div>
    </div>


    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>

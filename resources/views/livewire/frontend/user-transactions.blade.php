<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>Housieblitz|Transiction</title>
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
                <h6>Transactions(s)</h6>

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
                        @foreach($transactions as $transaction)
                            <a class="list-group-item d-flex align-items-center border-0 readed"
                                style="cursor: pointer"
                                wire:click="details('{{ $transaction->id }}')">

                                <span class="noti-icon text-white">
                                    @if($transaction->type === 'credit')
                                        <i class="ti ti-arrow-down-left"></i>
                                    @elseif($transaction->type === 'debit')
                                        <i class="ti ti-arrow-up-right"></i>
                                    @else
                                        <i class="ti ti-bell"></i>
                                    @endif
                                </span>

                                <div class="noti-info">
                                    <h6 class="mb-1">{{ ucfirst($transaction->type) ?? 'type' }}</h6>
                                    <span>{{ $transaction->created_at->diffForHumans() }}</span>
                                </div>

                                <div style="margin-left: 10px;">
                                    <h6 class="mb-1">{{ number_format($transaction->amount, 2) ?? 'amount' }}</h6>
                                    <span>{{ $transaction->details }}</span>
                                </div>
                            </a>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Details -->
        <div class="container" style="display: {{$detailsMode ? 'block' : 'none'}};">
            @if($selectedTransactions)
                <div class="notification-area pt-3 pb-2">
                    <div class="list-group-item d-flex py-3 bg-transparent">
                        <span class="noti-icon">
                            <i class="ti ti-check"></i>
                        </span>
                        <div class="noti-info">
                            <h6>{{ $selectedTransactions->details ?? 'No Transactions Available' }}</h6>
                            <p>Amount : {{ $selectedTransactions->amount ?? 'No data available.' }}
                                <br> Type : {{ $transaction->type ?? 'type' }}
                                {{-- <br>{{ $transaction->created_at->diffForHumans() }} --}}
                            </p>


                                {{-- @if(isset($selectedNotification->data['title']))
                                    <h4>{{ $selectedNotification->data['title'] }}</h4>
                                @endif --}}





                            {{-- <a class="btn btn-light" href="#">View More</a> --}}
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary" wire:click="backToList">Back to Transactions</button>
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

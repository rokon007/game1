<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>Housieblitz|Top Users</title>
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
        <div class="container">
            <div class="section-heading d-flex align-items-center pt-3 justify-content-between rtl-flex-d-row-r">
                <h6>Top Users(s)</h6>
                <span class="text-secondary">Top: 20</span>
            </div>
              <div id="notifications-container" class="notification-wrapper">
                <div class="notification-area pb-2">
                    <div class="list-group">
                        @forelse($users as $index => $user)
                            <a class="list-group-item d-flex align-items-center border-0">
                                <span class="noti-icon">
                                    {{ $index + 1 }}
                                </span>
                                <div class="noti-info d-flex align-items-center">
                                    <h6 class="mb-1 me-2">{{ $user->unique_id }}</h6>

                                    <!-- Copy Icon -->
                                    <button class="btn btn-sm btn-outline-secondary copy-btn"
                                            data-uniqueid="{{ $user->unique_id }}"
                                            title="Copy">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </a>
                        @empty
                            <a class="list-group-item d-flex align-items-center border-0">
                                <span class="noti-icon">
                                    !
                                </span>
                                <div class="noti-info">
                                    <h6 class="mb-1">No users found</h6>
                                </div>
                            </a>
                        @endforelse
                    </div>

                    <!-- Script for copy functionality -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            document.querySelectorAll('.copy-btn').forEach(button => {
                                button.addEventListener('click', function () {
                                    let uniqueId = this.getAttribute('data-uniqueid');
                                    navigator.clipboard.writeText(uniqueId).then(() => {
                                        // ছোট একটি alert বা tooltip শো করতে চাইলে
                                        alert('Copied: ' + uniqueId);
                                    });
                                });
                            });
                        });
                    </script>

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

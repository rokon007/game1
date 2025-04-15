<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>{{ config('app.name', 'Laravel') }} | Refile</title>
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
        @if($ruleSection)
            <div class="container">
                <div class="profile-wrapper-area py-3">
                <!-- User Information-->
                <div class="card user-info-card">
                    <div class="card-body p-4 ">

                    <div class="text-center">

                        <h5 class=" text-white text-center">ব্যালেন্স রিফিল করার নিয়ম</h5>
                    </div>
                    </div>
                </div>
                <!-- User Meta Data-->
                <div class="card user-data-card">
                    <div class="card-body">
                        <div class="balance-refill-instruction card p-4">
                            <h4 class="mb-3 text-primary">How to Refill Your Balance</h4>
                            <ol class="list-group list-group-numbered mb-3">
                            <li class="list-group-item">
                                Send money to this number: <strong>01711111111</strong> using
                                <span class="badge bg-info text-dark">bKash</span>,
                                <span class="badge bg-success">Nagad</span>,
                                <span class="badge bg-warning text-dark">Rocket</span>, or
                                <span class="badge bg-secondary">Upay</span>.
                            </li>
                            <li class="list-group-item">
                                After the transaction is successful, take a screenshot of the confirmation message.
                            </li>
                            <li class="list-group-item">
                                Copy the <strong>Transaction ID</strong> from the confirmation message.
                            </li>
                            <li class="list-group-item">
                                Click the <strong>"Next"</strong> button to proceed to the next step.
                            </li>
                            </ol>
                            <div class="alert alert-info">
                            <strong>Note:</strong> Please double-check the transaction number before proceeding.
                            </div>
                        </div>

                        <button class="btn btn-primary btn-lg w-100"  wire:click='nextToPaymentMethod'>Next</button>
                    </div>
                </div>
                </div>
            </div>
        @endif
        @if($paymentMethodSection)
            <div class="container">
                <div class="profile-wrapper-area py-3">
                    <div class="card user-info-card">
                        <div class="card-body p-4 ">
                            <div class="text-center">
                                <h5 class=" text-white text-center">Please select the payment method you used.</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <!-- Checkout Wrapper-->
                <div class="checkout-wrapper-area py-3">
                <!-- Choose Payment Method-->
                <div class="choose-payment-method">
                    <div class="row g-2 justify-content-center rtl-flex-d-row-r">
                    <!-- Single Payment Method-->
                        <div class="col-6 col-md-5">
                            <div class="single-payment-method">
                                <a class="cash" wire:click='paymentBikash'>
                                    <img src="{{asset('assets/frontend/img/paymentmethod/bikash.png') }}" alt="Image" width="150" />
                                    {{-- <h6>Bikash</h6> --}}
                                </a>
                            </div>
                        </div>
                        <!-- Single Payment Method-->
                        <div class="col-6 col-md-5">
                            <div class="single-payment-method">
                                <a class="cash" wire:click='paymentNagad'>
                                    <img src="{{asset('assets/frontend/img/paymentmethod/nagad.png') }}" alt="Image" width="150" />
                                    {{-- <h6>Nagad</h6> --}}
                                </a>
                            </div>
                        </div>
                        <!-- Single Payment Method-->
                        <div class="col-6 col-md-5">
                            <div class="single-payment-method">
                                <a class="cash" wire:click='paymentRoket'>
                                    <img src="{{asset('assets/frontend/img/paymentmethod/roket.png') }}" alt="Image" height="50" width="150" />
                                    {{-- <h6>Roket</h6> --}}
                                </a>
                            </div>
                        </div>
                    <!-- Single Payment Method-->
                        <div class="col-6 col-md-5">
                            <div class="single-payment-method">
                                <a class="cash" wire:click='paymentUpay'>
                                    <img src="{{asset('assets/frontend/img/paymentmethod/upay.png') }}" alt="Image" width="100" />
                                    {{-- <h6>Upay</h6> --}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        @endif
        @if($submitSection)

        @endif

    </div>

    @section('footer')
        <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>

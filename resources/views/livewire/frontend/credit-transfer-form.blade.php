<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
    @endsection
    @section('title')
        <title>Altswave|Notifications</title>
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
        <div class="container" style="display: {{ $sendingForm ? 'block' : 'none' }}">
            <div class="profile-wrapper-area py-3">
                <!-- User Information-->
                <div class="card user-info-card">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="user-profile me-3"><i style="font-size:40px; color:#fff" class="ti ti-send"></i></div>
                        <div class="user-info">
                            <h5 class="mb-0 text-white">Credit Transfer</h5>
                        </div>
                    </div>
                </div>
                <!-- User Meta Data-->
                <div class="card user-data-card">
                    <div class="card-body">
                        <form  wire:submit.prevent='sendingNext'>

                            <div class="mb-3">
                                <div class="title mb-2"><i class="ti ti-phone"></i> <span>Receiver Mobile</span></div>
                                <input class="form-control" wire:model='mobile' type="text" placeholder="Enter Receiver Mobile Number">
                                @error('mobile')<small class="text-danger mb-2">{{ $message }}</small>@enderror
                            </div>

                            <div class="mb-3">
                                <div class="title mb-2"><i class="ti ti-currency-dollar"></i> <span>Amount</span></div>
                                <input class="form-control" wire:model='amount' type="text" placeholder="Enter amount">
                                @error('amount')<small class="text-danger mb-2">{{ $message }}</small>@enderror
                            </div>

                            <button class="btn btn-primary btn-lg w-100" type="sendingNextn">
                                <span wire:loading.delay.long wire:target="cancel" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Next
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" style="display: {{ $confermationForm ? 'block' : 'none' }}">
            <div class="profile-wrapper-area py-3">
                <!-- User Information-->
                <div class="card user-info-card">
                  <div class="card-body p-4 d-flex align-items-center">
                    <div class="user-profile me-3">
                        @if($receiverData)
                            @if ($receiverData->avatar)
                                <img src="{{ Storage::url($receiverData->avatar) }}" alt="">
                            @else
                                <img src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="">
                            @endif
                        @else
                            <img src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="">
                        @endif
                    </div>
                    <div class="user-info">
                        @if($receiverData)
                        <p class="mb-0 text-white">Mobile: {{$receiverData->mobile}}</p>
                        <p class="mb-0 text-white">Email: {{$receiverData->email}}</p>
                        <h5 class="mb-0 text-white">{{$receiverData->name}}</h5>
                        <h5 class="mb-0 text-white">Amount:{{$amount}}</h5>
                        @endif
                    </div>

                  </div>
                </div>
                <!-- User Meta Data-->
                <div class="card user-data-card">
                  <div class="card-body">
                    <form wire:submit.prevent='confirmationAction'>
                      <div class="mb-3">
                        <div class="title mb-2"><i class="ti ti-at"></i><span>Password</span></div>
                        <input class="form-control" type="password" wire:model='password'>
                        @error('password')<small class="text-danger mb-2">{{ $message }}</small>@enderror
                      </div>
                      <button class="btn btn-primary btn-lg w-100" type="submit">
                        <span wire:loading.delay.long wire:target="confirmationAction" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Confirm
                    </button>
                    </form>
                  </div>
                </div>
            </div>
        </div>

        <div class="container" style="display: {{ $successNotification ? 'block' : 'none' }}">
            <div class="mt-4">
                <div class="alert alert-success text-center mt-4" role="alert">
                    <h5 class="text-success"><i class="ti ti-check-circle"></i> Transfer Successful!</h5>
                    <p class="mb-3">Your credit has been successfully transferred.</p>
                    <button wire:click="$refresh" class="btn btn-success">Send Again</button>
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

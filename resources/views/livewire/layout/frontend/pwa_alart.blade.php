<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use App\Models\Cart;
use Livewire\Attributes\On;

new class extends Component
{
    public $addCartNotificationMood=false;
    public $addressRequireAllart=false;
    public $lowStockAllart=false;
    public $quantityErrorAllart=false;
    public $rifleRequestAllart=false;
    public $noteText;
    public $noteMood=false;


    #[On('sentRifleRequest')]
    public function sent_rifleRequest()
    {
        $this->rifleRequestAllart = true;
        $this->dispatch('rifleRequestAllartMakeFalse');
    }

    public function rifleRequestAllartMakeFalse()
    {
        $this->rifleRequestAllart = false;
    }

    #[On('notificationText')]
    public function showNote($text)
    {
        $this->noteText=$text;
        $this->noteMood = true;

        // ৫ সেকেন্ডের বিলম্বের পরে newNotificationMoodMakeFalse() কল করবে sleep(5); // ৫ সেকেন্ড বিলম্ব
        $this->dispatch('newNotificationMoodMakeFalse');
    }

    public function newNotificationMoodMakeFalse()
    {
        $this->noteMood = false;
    }

    #[On('showNotification')]
    public function show()
    {
        $this->addCartNotificationMood = true;

        // ৫ সেকেন্ডের বিলম্বের পরে addCartNotificationMoodMakeFalse() কল করবে sleep(5); // ৫ সেকেন্ড বিলম্ব
        $this->dispatch('addCartNotificationMoodMakeFalse');
    }

    public function addCartNotificationMoodMakeFalse()
    {
        $this->addCartNotificationMood = false;
    }

    #[On('addressRequire')]
     public function addressRequireAllartShow()
     {
        $this->addressRequireAllart = true;
        $this->dispatch('addressRequireAllartMakeFalse');
     }


     public function addressRequireAllartMakeFalse()
    {
        $this->addressRequireAllart = false;
    }

    #[On('insufficientStock')]
     public function lowStock()
     {
        $this->lowStockAllart = true;
        $this->dispatch('lowStockAllartMakeFalse');

     }

     public function lowStockAllartMakeFalse()
     {
        $this->lowStockAllart = false;
     }

      #[On('quantityError')]
     public function quantityErrorFunction()
     {
        $this->quantityErrorAllart = true;
        $this->dispatch('quantityErrorAllartMakeFalse');

     }

     public function quantityErrorAllartMakeFalse()
     {
        $this->quantityErrorAllart = false;
     }


}; ?>



   <div>
        @if (session()->has('login_success'))
            <div class="toast pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                <div class="toast-body">
                    <img style="width: 150px" src="{{asset('assets/frontend/img/core-img/PNG.png')}}" alt="">
                    <div class="content d-flex align-items-center mb-2">
                        <h6 class="mb-0">{{ session('login_success') }}</h6>
                        <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    {{-- <span class="mb-0 d-block">Click the<strong class="mx-1">Add to Home Screen</strong>button &amp; enjoy it like a regular app.</span> --}}
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="toast pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                <div class="toast-body">
                    <img style="width: 150px" src="{{asset('assets/frontend/img/core-img/PNG.png')}}" alt="">
                    <div class="content d-flex align-items-center mb-2">
                        <h6 class="mb-0">{{ session('error') }}</h6>
                        <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    {{-- <span class="mb-0 d-block">Click the<strong class="mx-1">Add to Home Screen</strong>button &amp; enjoy it like a regular app.</span> --}}
                </div>
            </div>
        @endif
        @if (session()->has('success'))
            <div class="toast pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                <div class="toast-body">
                    <img style="width: 150px" src="{{asset('assets/frontend/img/core-img/PNG.png')}}" alt="">
                    <div class="content d-flex align-items-center mb-2">
                        <h6 class="mb-0">{{ session('success') }}</h6>
                        <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    {{-- <span class="mb-0 d-block">Click the<strong class="mx-1">Add to Home Screen</strong>button &amp; enjoy it like a regular app.</span> --}}
                </div>
            </div>
        @endif
        @if ($addCartNotificationMood)
            <div class="pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                <div class="toast-body bg-success">
                    <div class="content d-flex align-items-center mb-2">
                         <img style="width:150px" src="{{asset('assets/frontend/img/core-img/cart.png')}}" alt="">
                        <h6 class="mb-0 text-white">Product added to cart successfully</h6>
                        <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    {{-- <span class="mb-0 d-block">Click the<strong class="mx-1">Add to Home Screen</strong>button &amp; enjoy it like a regular app.</span> --}}
                </div>
            </div>
        @endif

        @if ($noteMood)
            <div class="pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                <div class="toast-body bg-success">
                    <div class="content d-flex align-items-center mb-2">
                         <img style="width:150px" src="{{asset('assets/frontend/img/core-img/alert.png')}}" alt="">
                        <h6 class="mb-0 text-white">{{$noteText}}</h6>
                        <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        @if ($rifleRequestAllart)
            <div class="pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
                <div class="toast-body bg-success">
                    <div class="content d-flex align-items-center mb-2">
                        <img style="width:150px" src="{{asset('assets/frontend/img/core-img/cart.png')}}" alt="">
                        <h6 class="mb-0 text-white">Rifle request sent successfully</h6>
                        <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        @if ($addressRequireAllart)
        <div class="pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
            <div class="toast-body bg-warning">
                <div class="content d-flex align-items-center mb-2">
                    <img style="width: 50px" src="{{asset('assets/frontend/img/core-img/alert.png')}}" alt="">
                    <h6 class="mb-0">User Address Require !</h6>
                    <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                {{-- <span class="mb-0 d-block">Click the<strong class="mx-1">Add to Home Screen</strong>button &amp; enjoy it like a regular app.</span> --}}
            </div>
        </div>
    @endif

     @if ($lowStockAllart)
        <div class="pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
            <div class="toast-body bg-warning">
                <div class="content d-flex align-items-center mb-2">
                    <img style="width: 50px" src="{{asset('assets/frontend/img/core-img/alert.png')}}" alt="">
                    <h6 class="mb-0">Insufficient stock available !</h6>
                    <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                {{-- <span class="mb-0 d-block">Click the<strong class="mx-1">Add to Home Screen</strong>button &amp; enjoy it like a regular app.</span> --}}
            </div>
        </div>
    @endif

    @if ($quantityErrorAllart)
        <div class="pwa-install-alert shadow bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" data-bs-autohide="true">
            <div class="toast-body bg-warning">
                <div class="content d-flex align-items-center mb-2">
                    <img style="width: 50px" src="{{asset('assets/frontend/img/core-img/alert.png')}}" alt="">
                    <h6 class="mb-0">Quantity must be between 1 and 99 !</h6>
                    <button class="btn-close ms-auto" type="button" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                {{-- <span class="mb-0 d-block">Click the<strong class="mx-1">Add to Home Screen</strong>button &amp; enjoy it like a regular app.</span> --}}
            </div>
        </div>
    @endif
        <script>
            document.addEventListener('newNotificationMoodMakeFalse', () => {
                setTimeout(() => {
                    @this.call('newNotificationMoodMakeFalse');
                }, 10000); // ৫ সেকেন্ড বিলম্ব
            });


            document.addEventListener('addCartNotificationMoodMakeFalse', () => {
                setTimeout(() => {
                    @this.call('addCartNotificationMoodMakeFalse');
                }, 5000); // ৫ সেকেন্ড বিলম্ব
            });

            document.addEventListener('addressRequireAllartMakeFalse', () => {
                setTimeout(() => {
                    @this.call('addressRequireAllartMakeFalse');
                }, 5000); // ৫ সেকেন্ড বিলম্ব
            });

            document.addEventListener('lowStockAllartMakeFalse', () => {
                setTimeout(() => {
                    @this.call('lowStockAllartMakeFalse');
                }, 5000); // ৫ সেকেন্ড বিলম্ব
            });

            document.addEventListener('quantityErrorAllartMakeFalse', () => {
                setTimeout(() => {
                    @this.call('quantityErrorAllartMakeFalse');
                }, 5000); // ৫ সেকেন্ড বিলম্ব
            });
        </script>



    </div>

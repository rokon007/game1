<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;

use Livewire\Attributes\On;

new class extends Component
{
    public $cartCount = 0;


    public function mount()
    {
        // $sessionId = cookie('cart_session_id') ?? session()->getId();
        $sessionId = session()->getId();

    }

}; ?>



<div class="header-area" id="headerArea">
    <div class="container h-100 d-flex align-items-center justify-content-between d-flex rtl-flex-d-row-r">
      <!-- Logo Wrapper -->
      <div class="logo-wrapper"><a href="{{route('home')}}"><img style="width:200px" src="{{asset('assets/frontend/img/core-img/PNG.png')}}" alt=""></a></div>
      <div class="navbar-logo-container d-flex align-items-center">
        <!-- Cart Icon -->
        <div class="cart-icon-wrap"><a href="#"><i class="ti ti-basket-bolt"></i><span>{{$cartCount}}</span></a></div>
        @if (Route::has('login'))
            @auth
                <!-- User Profile Icon -->
                <div class="user-profile-icon ms-2">
                    <a href="{{route('userProfile')}}">
                        @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="">
                        @else
                        <img src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="">
                        @endif
                    </a>
                </div>
            @endauth
        @endif
        <!-- Navbar Toggler -->
        <div class="suha-navbar-toggler ms-2" data-bs-toggle="offcanvas" data-bs-target="#suhaOffcanvas" aria-controls="suhaOffcanvas">
          <div><span></span><span></span><span></span></div>
        </div>
      </div>
    </div>
  </div>




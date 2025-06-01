<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use App\Models\Ticket;
// use Livewire\Attributes\On;

new class extends Component
{
    // public $cartCount = 0;
    public $unreadCount = 0;


    // public function getListeners()
    // {
    //     if(auth()->user()){
    //         $auth_id = auth()->user()->id;
    //             return [
    //             "echo-private:notRefresh.{$auth_id},MessageSent"=>"broadcastedNotReceived",
    //         ];
    //     }

    // }

    public function broadcastedNotReceived($event)
    {
        dd('ok');
        $this->loadUnreadCount();
        //$this->dispatch($event);
    }


    public function mount()
    {
        // $sessionId = cookie('cart_session_id') ?? session()->getId();
        $sessionId = session()->getId();
        $this->loadUnreadCount();

    }

    public function loadNumbers()
    {
        $this->dispatch('load_numbers');
    }

    public function loadUnreadCount()
    {
        // Logged-in user এর আনরেড নোটিফিকেশন কাউন্ট
        if(auth()->user()){
          $this->unreadCount = Auth::user()->unreadNotifications->count();
        }
    }

}; ?>



<div class="header-area" id="headerArea">
    <div class="container h-100 d-flex align-items-center justify-content-between d-flex rtl-flex-d-row-r">
      <!-- Logo Wrapper -->
      <div class="logo-wrapper"><a href="{{route('home')}}"><img style="width:200px" src="{{asset('assets/frontend/img/core-img/PNG.png')}}" alt=""></a></div>
      <div class="navbar-logo-container d-flex align-items-center">
        <!-- Cart Icon -->
        {{-- <div class="cart-icon-wrap"><a href="#"><i class="ti ti-basket-bolt"></i><span>{{$cartCount}}</span></a></div> --}}
        @if (Route::has('login'))
            @auth
            <!-- User notifications Icon -->
                <div class="cart-icon-wrap ms-2">
                    <a href="{{route('notifications')}}">
                        <i class="ti ti-bell-ringing lni-tada-effect"></i>
                        {{-- <span wire:poll.1s> --}}
                        <span>
                            @php
                                $unreadCount1 = auth()->check() ? auth()->user()->unreadNotifications->count() : 0;
                            @endphp
                            {{$unreadCount1}}
                        </span>
                    </a>
                </div>
                <!-- User massege Icon -->
                <div class="cart-icon-wrap ms-2">
                    <a href="{{ route('chat') }}">
                        <i class="ti ti-message-circle"></i>

                        <livewire:frontend.header.unread-chat-count />
                    </a>

                </div>
            @endauth
        @endif
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




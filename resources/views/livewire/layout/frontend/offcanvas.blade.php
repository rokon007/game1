<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="offcanvas offcanvas-start suha-offcanvas-wrap" tabindex="-1" id="suhaOffcanvas" aria-labelledby="suhaOffcanvasLabel">
    <!-- Close button-->
    <button class="btn-close btn-close-white" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    <!-- Offcanvas body-->
    <div class="offcanvas-body">
        @if (Route::has('login'))
            @auth
                <!-- Sidenav Profile-->
                <div class="sidenav-profile">
                    <div class="user-profile">
                        @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="">
                        @else
                        <img src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="">
                        @endif
                    </div>
                    <div class="user-info">
                    <h5 class="user-name mb-1 text-white">{{auth()->user()->name}}</h5>
                    <p class="available-balance text-white">Current Balance TK<span class="counter">{{auth()->user()->credit}}</span></p>
                    </div>
                </div>
            @endauth
        @endif
      <!-- Sidenav Nav-->
      <ul class="sidenav-nav ps-0">

        {{-- <li class="suha-dropdown-menu"><a href="#"><i class="ti ti-building-store"></i>Shop Pages</a>
          <ul>
            <li><a href="shop-grid.html">Shop Grid</a></li>
            <li><a href="shop-list.html">Shop List</a></li>
            <li><a href="single-product.html">Product Details</a></li>
            <li><a href="featured-products.html">Featured Products</a></li>
            <li><a href="flash-sale.html">Flash Sale</a></li>
          </ul>
        </li>
        <li><a href="pages.html"><i class="ti ti-notebook"></i>All Pages</a></li>
        <li class="suha-dropdown-menu"><a href="wishlist-grid.html"><i class="ti ti-heart"></i>My Wishlist</a>
          <ul>
            <li><a href="wishlist-grid.html">Wishlist Grid</a></li>
            <li><a href="wishlist-list.html">Wishlist List</a></li>
          </ul>
        </li> --}}
        <li><a href="settings.html"><i class="ti ti-adjustments-horizontal"></i>Settings</a></li>
        @if (Route::has('login'))
            @auth
                <li><a href="{{route('transactions')}}"><i class="ti ti-bell-ringing lni-tada-effect"></i>Transactions<span class="ms-1 badge badge-warning">3</span></a></li>
                {{-- <li><a href="profile.html"><i class="ti ti-heart"></i>My Wishlist</a></li> --}}
                <li><a href="{{route('rifleAccount')}}"><i class="ti ti-adjustments-horizontal"></i>Rifle Account</a></li>
                <li><a href="{{route('profile')}}"><i class="ti ti-user"></i>My Profile</a></li>
                <li><a href="{{ route('wallet') }}"><i class="ti ti-wallet"></i> Wallet</a></li>
                <li><a href="{{ route('creditTransfer') }}"><i class="ti ti-transfer"></i> Credit Transfer</a></li>
                <li><a href="{{ route('gameLobby') }}"><i class="ti ti-grid-dots"></i> Game Lobby</a></li>
                <li><a href="{{ route('gameRoom') }}"><i class="ti ti-dice-3"></i> Game Room</a></li>
                <li><a href="{{ route('ticket') }}"><i class="ti ti-ticket"></i> Ticket</a></li>
                <li><a href="{{ route('gameHistory') }}"><i class="ti ti-history"></i> Game History</a></li>
                <li><a href="{{ route('withdrawal') }}"><i class="ti ti-cash-out"></i> Withdrawal</a></li>
                <li><a class="text-white" style="cursor: pointer" wire:click="logout"><i class="ti ti-logout"></i>Log Out</a></li>
            @else
                <li><a href="{{ route('login') }}"><i class="ti ti-logout"></i>Log in</a></li>
                @if (Route::has('register'))
                    <li><a href="{{ route('register') }}"><i class="ti ti-logout"></i>Register</a></li>
                @endif
            @endauth
        @endif
      </ul>
    </div>
  </div>

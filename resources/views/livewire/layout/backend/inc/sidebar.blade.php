<?php


use Illuminate\Support\Facades\Session;
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use App\Models\AppNotifications;

new class extends Component
{
  public function triggerToDeposit()
    {
        session()->flash('triggerDeposit', true); // সেশন ফ্ল্যাশ ভ্যারিয়েবল সেট করুন
        return redirect()->route('deposit');      // পেজ রিডাইরেক্ট করুন

    }
 /**
     * Log the current user out of the application.
     */
     public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
      <div>
          <img style="width:120px" src="{{asset('assets/frontend/img/core-img/PNG.png')}}"
               class="logo-icon"
               alt="logo icon">
      </div>

      <div class="toggle-icon ms-auto">
          <i class="bi bi-chevron-double-left"></i>
      </div>
  </div>

  <!-- Admin navigation -->
  <ul class="metismenu" id="menu">
      <li>
          <a href="{{ route('admin.dashboard') }}" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-house"></i></div>
              <div class="menu-title">Dashboard</div>
          </a>
      </li>

      <li>
          <a href="{{ route('admin.addBanner') }}" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
              <div class="menu-title">Add Baner Slider</div>
          </a>
      </li>
      <li>
        <a href="{{route('admin.prizes')}}" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">Prize Management</div>
        </a>
    </li>
    <li>
        <a href="{{route('admin.rifle_request_management')}}" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">Rifle Request Management</div>
        </a>
    </li>
    <li>
        <a href="{{route('admin.manage_game')}}" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">Manage Game</div>
        </a>
    </li>

    <li>
        <a href="{{route('admin.agent')}}" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">Agent Management</div>
        </a>
    </li>
    <li>
        <a href="#" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">Banner Management</div>
        </a>
    </li>

      {{-- <li>
          <a href="javascript:;" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-arrow-repeat"></i></div>
              <div class="menu-title">Transaction</div>
          </a>
          <ul>
              <li><a href="#"><i class="bi bi-cloud-upload"></i>Deposit</a></li>
              <li><a href=#"><i class="bi bi-check-circle"></i>Completed Scheme</a></li>
          </ul>
      </li> --}}
      <li>
          <a href="javascript:;" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-person"></i></div>
              <div class="menu-title">Profile Management</div>
          </a>
          <ul>
              <li><a href="{{ route('profile') }}"><i class="bi bi-person-badge"></i>Profile</a></li>
          </ul>
      </li>
      <li>
          <a wire:click="logout" style="cursor: pointer">
              <div class="parent-icon"><i class="bi bi-box-arrow-left"></i></div>
              <div class="menu-title">Logout</div>
          </a>
      </li>
  </ul>
  <!-- End navigation -->
</aside>


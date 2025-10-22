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
    {{-- <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <img src="{{ asset('assets/frontend/img/core-img/PNG.png') }}"
                 class="logo-icon me-2"
                 alt="logo"
                 style="height: 40px">
            <span class="logo-text fw-bold">Admin Panel</span>
        </div>
        <div class="toggle-icon ms-auto">
            <i class="bi bi-list collapse-icon"></i>
        </div>
    </div> --}}
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

    <!-- Navigation Menu -->
    <ul class="metismenu" id="menu">
        <!-- Dashboard -->
        <li>
            <a href="{{ route('admin.dashboard') }}" class="mm-active">
                <div class="parent-icon"><i class="bi bi-speedometer2"></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        <!-- Content Management -->
        <li class="menu-label">Content Management</li>
        <li>
            <a href="{{ route('admin.addBanner') }}">
                <div class="parent-icon"><i class="bi bi-images"></i></div>
                <div class="menu-title">Banner Slider</div>
            </a>
        </li>
        <li>
            <a href="#">
                <div class="parent-icon"><i class="bi bi-card-image"></i></div>
                <div class="menu-title">Banner Management</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.howto') }}">
                <div class="parent-icon"><i class="bi bi-book"></i></div>
                <div class="menu-title">How-To Guides</div>
            </a>
        </li>

        <!-- Game Management -->
        <li class="menu-label">Game Management</li>
        <li>
            <a href="{{ route('admin.manage_game') }}">
                <div class="parent-icon"><i class="bi bi-controller"></i></div>
                <div class="menu-title">Manage Housi Games</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.hajari_game_settings') }}">
                <div class="parent-icon"><i class="bi bi-gear"></i></div>
                <div class="menu-title">Hajari Settings</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.lottery.index') }}">
                <div class="parent-icon"><i class="bi bi-trophy"></i></div>
                <div class="menu-title">Lottery Management</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.refill_settings') }}">
                <div class="parent-icon"><i class="bi bi-gear-fill"></i></div>
                <div class="menu-title">Refill Settings</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.system_settings') }}">
                <div class="parent-icon"><i class="bi bi-gear-fill"></i></div>
                <div class="menu-title">Spin Settings</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.crash_game_dashboard') }}">
                <div class="parent-icon"><i class="bi bi-gear-fill"></i></div>
                <div class="menu-title">Crash Game Dashboard</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.rifle_request_management') }}">
                <div class="parent-icon"><i class="bi bi-card-checklist"></i></div>
                <div class="menu-title">Refill Requests</div>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.withdrawal_request_management') }}">
                <div class="parent-icon"><i class="bi bi-cash-coin"></i></div>
                <div class="menu-title">Withdrawal Requests</div>
            </a>
        </li>


        <!-- User Management -->
        <li class="menu-label">User Management</li>
        <li>
            <a href="{{ route('admin.user') }}">
                <div class="parent-icon"><i class="bi bi-people"></i></div>
                <div class="menu-title">Users</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.agent') }}">
                <div class="parent-icon"><i class="bi bi-person-badge"></i></div>
                <div class="menu-title">Agents</div>
            </a>
        </li>

        <!-- Settings -->
        <li class="menu-label">Settings</li>
        <li>
            <a href="{{ route('admin.referral-settings') }}">
                <div class="parent-icon"><i class="bi bi-share"></i></div>
                <div class="menu-title">Referral Program</div>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.welcomeBonus-settings') }}">
                <div class="parent-icon"><i class="bi bi-share"></i></div>
                <div class="menu-title">Welcome Bonus</div>
            </a>
        </li>

        <!-- Profile Section -->
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bi bi-person-circle"></i></div>
                <div class="menu-title">My Account</div>
            </a>
            <ul>
                <li>
                    <a href="{{ route('profile') }}">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                </li>
                <li>
                    <a wire:click="triggerToDeposit" style="cursor: pointer">
                        <i class="bi bi-wallet2 me-2"></i>Deposit Funds
                    </a>
                </li>
            </ul>
        </li>

        <!-- Logout -->
        <li>
            <a wire:click="logout" style="cursor: pointer" class="logout-item">
                <div class="parent-icon"><i class="bi bi-box-arrow-right"></i></div>
                <div class="menu-title">Logout</div>
            </a>
        </li>
    </ul>
    {{-- <style>
        .sidebar-wrapper {
            background: linear-gradient(180deg, #2a3042 0%, #1e222e 100%);
        }
        .sidebar-header {
            padding: 1.5rem 1.5rem 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .menu-label {
            color: #6c757d;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 1.5rem 1.5rem 0.5rem;
        }
        .metismenu a {
            transition: all 0.3s ease;
            color: #a1a5b7;
        }
        .metismenu a:hover,
        .metismenu a:focus,
        .metismenu a:active,
        .metismenu .mm-active > a {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .parent-icon {
            color: #5d78ff;
        }
        .logout-item {
            margin-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .logout-item:hover {
            color: #f1416c !important;
        }
        .logo-text {
            color: #fff;
            font-size: 1.1rem;
        }
    </style> --}}
</aside>



{{-- <aside class="sidebar-wrapper" data-simplebar="true">
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
        <a href="{{route('admin.user')}}" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">User Management</div>
        </a>
    </li>
    <li>
        <a href="{{route('admin.referral-settings')}}" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">Referral Settings</div>
        </a>
    </li>
    <li>
        <a href="{{route('admin.hajari_game_settings')}}" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">Hajari Game Settings</div>
        </a>
    </li>
    <li>
        <a href="{{route('admin.howto')}}" class="has-arrow">
            <div class="parent-icon"><i class="bi bi-wallet2"></i></div>
            <div class="menu-title">How To Guide Manager</div>
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
</aside> --}}


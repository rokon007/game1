<?php


use Illuminate\Support\Facades\Session;
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use App\Models\AppNotifications;
use App\Models\User;
use App\Models\Investment;

new class extends Component
{
  public $totalUsers;
  public $depositRequestCount;
  public $WithdrawRequesttCount;
  public function mount()
  {
    $this->totalUsers = User::where('is_admin',null)->count();
    // Fetch deposit request count
    $this->depositRequestCount = Investment::whereHas('user', function ($query) {
            $query->where('is_admin',null); // Exclude admins
        })->where('status','pending')  
        ->count();

        // Fetch Withdraw request count
        $this->WithdrawRequesttCount = Investment::whereHas('user', function ($query) {
            $query->where('is_admin',null); // Exclude admins
        })->where('status','WithdrawalRequestSubmitted') 
        ->count();

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
      {{-- Uncomment the image if needed --}}
      {{-- <div>
          <img src="{{ asset('public/backend/assets/images/logo-icon.png') }}" 
               style="height: 80px; width: 80px;" 
               class="logo-icon" 
               alt="logo icon">
      </div> --}}
      <div>
          <h4 style="color: #008000;">Max Deposit</h4>
      </div>
      <div class="toggle-icon ms-auto">
          <i class="bi bi-chevron-double-left"></i>
      </div>
  </div>

  <!-- Admin navigation -->
  <ul class="metismenu" id="menu">
      <li>
          <a href="{{ route('admin_dashboard') }}" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-house"></i></div>
              <div class="menu-title">Dashboard</div>
          </a>
      </li>
      <li class="position-relative">
          <a href="{{ route('admin_customer') }}" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-people"></i></div>
              <div class="menu-title">Customer</div>
          </a>
      </li>
      <li>
          <a href="javascript:;" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-list-task"></i></div>
              <div class="menu-title">
                Action &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                @if($depositRequestCount+$WithdrawRequesttCount!=0)
                <span class="badge rounded-pill bg-danger">
                  {{$depositRequestCount+$WithdrawRequesttCount}}
              </span>
              @endif
              </div>
          </a>
          <ul>
            <li>
                  <a href="{{ route('pending_deposit') }}">
                      <i class="bi bi-hourglass-split"></i>
                       Deposit Request&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                       @if($depositRequestCount!=0)
                       <span class="badge rounded-pill bg-danger">
                         {{$depositRequestCount}}
                     </span>
                     @endif
                  </a>
              </li>
              <li>
                  <a href="{{ route('pending_withdrawal') }}">
                      <i class="bi bi-currency-exchange"></i>
                       Withdraw Request&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                       @if($WithdrawRequesttCount!=0)
                       <span class="badge rounded-pill bg-danger">
                         {{$WithdrawRequesttCount}}
                     </span>
                     @endif
                  </a>
              </li>
          </ul>
          
      </li>
      <li>
          <a href="javascript:;" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-gear"></i></div>
              <div class="menu-title">Settings</div>
          </a>
          <ul>
              <li>
                  <a href="{{ route('admin_scheme') }}">
                      <i class="bi bi-briefcase"></i> Scheme
                  </a>
              </li>
              <li>
                  <a href="{{ route('rete_page') }}">
                      <i class="bi bi-bar-chart-line"></i> Rate
                  </a>
              </li>
          </ul>
      </li>
      <li>
          <a href="javascript:;" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-grid-3x3"></i></div>
              <div class="menu-title">Statement</div>
          </a>
          <ul>
              <li>
                  <a href="{{ route('deposit_statement') }}">
                      <i class="bi bi-journal-plus"></i> Credit Statement
                  </a>
              </li>
              <li>
                  <a href="{{ route('withdrawal_statement') }}">
                      <i class="bi bi-journal-minus"></i> Debit Statement
                  </a>
              </li>
          </ul>
      </li>
      <li>
          <a href="javascript:;" class="has-arrow">
              <div class="parent-icon"><i class="bi bi-person-badge"></i></div>
              <div class="menu-title">Profile Management</div>
          </a>
          <ul>
              <li>
                  <a href="{{ route('profile') }}">
                      <i class="bi bi-person-circle"></i> Profile
                  </a>
              </li>
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


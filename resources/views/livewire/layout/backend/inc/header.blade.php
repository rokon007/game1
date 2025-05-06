<?php


use Illuminate\Support\Facades\Session;
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
// use App\Models\Notification;
use Livewire\Attributes\On;

new class extends Component
{
  public $notifications = [];


    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
    public function mount(){
      $this->loadNotifications();
    }



    public function loadNotifications()
    {
        $this->notifications = auth()->user()->unreadNotifications ?? [];
    }

    // public function markAsRead($notificationId)
    // {
    //     $notification = auth()->user()->notifications()->find($notificationId);
    //     if ($notification) {
    //         $notification->markAsRead();
    //     }
    //     $this->loadNotifications(); // Reload notifications
    // }

    public function markAsReadAndRedirect($notificationId, $redirectUrl)
    {
        $notification = auth()->user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }

        // Livewire এর মাধ্যমে রিডিরেক্ট
        $this->redirect($redirectUrl);
    }
    public function markRead($notificationId)
    {
      $notification = auth()->user()->notifications()->find($notificationId);
      if ($notification) {
          $notification->markAsRead();
          $this->loadNotifications();
      }
    }
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }



}; ?>














 <header class="top-header">
      <nav class="navbar navbar-expand">
        <div class="mobile-toggle-icon d-xl-none">
            <i class="bi bi-list"></i>
          </div>
          <div class="top-navbar d-none d-xl-block">
          <ul class="navbar-nav align-items-center">
            <li class="nav-item">
                 <a class="nav-link" href="#">Dashboard of {{ auth()->user()->name }}</a>
            </li>

           <!--  <li class="nav-item">
               <a class="nav-link" href="app-emailbox.html">Email</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" href="javascript:;">Projects</a>
            </li>
            <li class="nav-item d-none d-xxl-block">
              <a class="nav-link" href="javascript:;">Events</a>
            </li>
            <li class="nav-item d-none d-xxl-block">
              <a class="nav-link" href="app-to-do.html">Todo</a>
            </li> -->

          </ul>
          </div>
          <div class="search-toggle-icon d-xl-none ms-auto">
            <i class="bi bi-search"></i>
          </div>
          <form class="searchbar d-none d-xl-flex ms-auto">
              <div class="position-absolute top-50 translate-middle-y search-icon ms-3"><i class="bi bi-search"></i></div>
              <input class="form-control" type="text" placeholder="Type here to search">
              <div class="position-absolute top-50 translate-middle-y d-block d-xl-none search-close-icon"><i class="bi bi-x-lg"></i></div>
          </form>
          <div class="top-navbar-right ms-3">
            <ul class="navbar-nav align-items-center">
            <li class="nav-item dropdown dropdown-large">
              <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                <div class="user-setting d-flex align-items-center gap-1">

                  <img src="{{asset('public/backend/upload/image/user/user.jpg')}}" class="user-img" alt="">

                  <div class="user-name d-none d-sm-block">{{auth()->user()->name}}</div>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                   <a class="dropdown-item" href="#">
                     <div class="d-flex align-items-center">

                        <img src="{{asset('public/backend/upload/image/user/user.jpg')}}" alt="" class="rounded-circle" width="60" height="60">

                        <div class="ms-3">
                          <h6 class="mb-0 dropdown-user-name">{{auth()->user()->name}}</h6>
                          @if(auth()->user()->is_admin==='admin')
                          <small class="mb-0 dropdown-user-designation text-secondary">Admin</small>
                          @else
                          <small class="mb-0 dropdown-user-designation text-secondary">Genaral User</small>
                          @endif
                        </div>
                     </div>
                   </a>
                 </li>
                 <li><hr class="dropdown-divider"></li>

                 @if(auth()->user()->is_admin==='admin')
                 <li>
                  <a class="dropdown-item" href="{{route('admin.dashboard')}}">
                     <div class="d-flex align-items-center">
                       <div class="setting-icon"><i class="bi bi-speedometer"></i></div>
                       <div class="setting-text ms-3"><span>Dashboard</span></div>
                     </div>
                   </a>
                </li>
                 @else
                 <li>
                  <a class="dropdown-item" href="{{route('admin.dashboard')}}">
                     <div class="d-flex align-items-center">
                       <div class="setting-icon"><i class="bi bi-speedometer"></i></div>
                       <div class="setting-text ms-3"><span>Dashboard</span></div>
                     </div>
                   </a>
                </li>
                 @endif


                 @if(auth()->user()->role==='admin')
                <li>
                  <a class="dropdown-item" href="#" target="blank">
                     <div class="d-flex align-items-center">
                       <div class="setting-icon"><i class="bi bi-cloud-arrow-down-fill"></i></div>
                       <div class="setting-text ms-3"><span>Panding Deposit</span></div>
                     </div>
                   </a>
                </li>
                @endif

                @if(auth()->user()->role==='admin')
                <li>
                  <a class="dropdown-item" href="#" target="blank">
                     <div class="d-flex align-items-center">
                       <div class="setting-icon"><i class="bi bi-cloud-arrow-down-fill"></i></div>
                       <div class="setting-text ms-3"><span>Panding Withdraw Request</span></div>
                     </div>
                   </a>
                </li>
                @endif

                 <li>
                    <a class="dropdown-item" href="#">
                       <div class="d-flex align-items-center">
                         <div class="setting-icon"><i class="bi bi-person-fill"></i></div>
                         <div class="setting-text ms-3"><span>Profile</span></div>
                       </div>
                     </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#">
                       <div class="d-flex align-items-center">
                         <div class="setting-icon"><i class="bi bi-gear-fill"></i></div>
                         <div class="setting-text ms-3"><span>Setting</span></div>
                       </div>
                     </a>
                  </li>



                  <li>
                    <a class="dropdown-item" href="#">
                       <div class="d-flex align-items-center">
                         <div class="setting-icon"><i class="bi bi-piggy-bank-fill"></i></div>
                         <div class="setting-text ms-3"><span>Completed Scheme</span></div>
                       </div>
                     </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#">
                       <div class="d-flex align-items-center">
                         <div class="setting-icon"><i class="bi bi-cloud-arrow-down-fill"></i></div>
                         <div class="setting-text ms-3"><span>Downloads</span></div>
                       </div>
                     </a>
                  </li>

                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item" href="#">
                       <div class="d-flex align-items-center">
                         <div class="setting-icon"><i class="bi bi-cloud-arrow-down-fill"></i></div>
                         <div class="setting-text ms-3"><span wire:click='logout' style="cursor: pointer">Logout</span></div>
                       </div>
                     </a>
                  </li>
              </ul>
            </li>



            {{-- <li class="nav-item dropdown dropdown-large">
              <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                <div class="projects">
                  <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end">


                 <div class="row row-cols-3 gx-2">
                    <div class="col">
                      <a href="javascript:;">
                      <div class="apps p-2 radius-10 text-center">
                         <div class="apps-icon-box mb-1 text-white bg-danger bg-gradient">
                           <i class="bi bi-people-fill"></i>
                         </div>
                         <p class="mb-0 apps-name">Users</p>
                      </div>
                    </a>
                   </div>


                  <div class="col">
                    <a href="pages-user-profile.html">
                    <div class="apps p-2 radius-10 text-center">
                       <div class="apps-icon-box mb-1 text-white bg-purple bg-gradient">
                        <i class="bi bi-person-circle"></i>
                       </div>
                       <p class="mb-0 apps-name">Account</p>
                     </div>
                    </a>
                  </div>

                  <div class="col">
                    <a href="ecommerce-orders-detail.html">
                    <div class="apps p-2 radius-10 text-center">
                       <div class="apps-icon-box mb-1 text-white bg-pink bg-gradient">
                        <i class="bi bi-credit-card-fill"></i>
                       </div>
                       <p class="mb-0 apps-name">Payment</p>
                    </div>
                    </a>
                  </div>

                  <div class="col">
                     <a class="dropdown-item" href="#" target="blank">
                    <div class="apps p-2 radius-10 text-center">
                       <div class="apps-icon-box mb-1 text-white bg-bronze bg-gradient">
                        <i class="bi bi-calendar-check-fill"></i>
                       </div>
                       <p class="mb-0 apps-name">Events</p>
                    </div>
                  </a>
                  </div>

                 </div><!--end row-->

              </div>
            </li> --}}


            <div wire:poll.30s>
            <li class="nav-item dropdown dropdown-large">
              <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                <div class="messages">
                  <span class="notify-badge">
                    {{ count($notifications) }}
                  </span>
                  <i class="bi bi-bell-fill"></i>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end p-0">
                <div class="p-2 border-bottom m-2">
                    <h5 class="h5 mb-0">Notifications</h5>
                </div>
               <div class="header-message-list p-2">
                  <div class="dropdown-item bg-light radius-10 mb-1">
                    <form class="dropdown-searchbar position-relative">
                      <div class="position-absolute top-50 start-0 translate-middle-y px-3 search-icon"><i class="bi bi-search"></i></div>
                      <input class="form-control" type="search" placeholder="Search Messages">
                    </form>
                  </div>


                    @if($notifications)
                        @forelse ($notifications as $notification)

                          <a class="dropdown-item"
                                href="#"
                                wire:click="markAsReadAndRedirect('{{ $notification->id }}', '{{ auth()->user()->role === 'admin' ? $notification->data['admin_link'] : $notification->data['user_link'] }}')">


                                <div class="d-flex align-items-center">
                                    <i class="bi bi-bell-fill" width="52" height="52"></i>
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-0 dropdown-msg-user">
                                            {{ $notification->data['title'] }}
                                            <span class="msg-time float-end text-secondary">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </h6>
                                        <small class="mb-0 dropdown-msg-text text-secondary d-flex align-items-center">
                                          @if (isset($notification->data['user_name']))
                                            by {{ $notification->data['user_name'] }}, Amount: {{ $notification->data['amount'] }}
                                          @endif

                                        </small>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <a class="dropdown-item">
                                <div class="text-center pt-4">
                                    <h6>No new notifications</h6>
                                </div>
                            </a>
                        @endforelse
                    @endif





              </div>
              <div class="p-2">
                <div><hr class="dropdown-divider"></div>
                  <a class="dropdown-item" href="#">
                    <div class="text-center">View All Notifications</div>
                  </a>
              </div>
             </div>
            </li>
            </div>

          </ul>
      </div>
      </nav>
    </header>























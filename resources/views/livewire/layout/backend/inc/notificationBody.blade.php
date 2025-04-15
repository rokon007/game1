<?php



use Livewire\Volt\Component;
use App\Models\Notification;
use Livewire\Attributes\On;

new class extends Component
{
  public $unread_count,$unread_notifications="",$read_notifications="";




    public function updateNotyfication(){
        $adminUser = auth()->user()->id;
        DB::update('update notifications set for_admin_id = ? where for_admin_id = ?',[$adminUser,0]);
		// return redirect()->back();
    }
    public function logout_go(){
        auth()->user()->logout();
        return redirect('/login');
    }
    #[On('notificationAlart')]
    public function mountBodyComponent(){
      $adminUser = auth()->user()->id;
    //   $this->unread_count=Notification::where('status',1)->whereNot('for_admin_id',$adminUser)->count();
    //   $this->unread_notifications=Notification::where('status','1')->whereNot('for_admin_id',$adminUser)->orderBy('id', 'DESC')->get();
    //   $this->read_notifications=Notification::where('status','1')->where('for_admin_id',$adminUser)->orderBy('id', 'DESC')->get();


      $this->unread_count=1;
      $this->unread_notifications=1;
      $this->read_notifications=1;
    }
    public function mount(){

      $this->mountBodyComponent();

    }
}; ?>














    <div class="header-notifications-list p-2" wire:poll="mountBodyComponent()">
        <div class="dropdown-item bg-light radius-10 mb-1">
          <form class="dropdown-searchbar position-relative">
            <div class="position-absolute top-50 start-0 translate-middle-y px-3 search-icon"><i class="bi bi-search"></i></div>
            <input class="form-control" type="search" placeholder="Search Messages">
          </form>
        </div>
        @if($unread_count!=null)
            <div class="dropdown-item bg-light radius-10 mb-1">
                <center><a style="color:red;cursor: pointer;" wire:click='updateNotyfication'>Mark All As Read</a></center>
            </div>
              {{-- @foreach($unread_notifications as $key)
                  <a class="dropdown-item" href="{{url($key->link)}}">
                        <div class="d-flex align-items-center">
                            <div class="notification-box">
                                  <i class="{{$key->icon}}"></i>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                  <h6 class="mb-0 dropdown-msg-user">
                                    {{$key->subject}}
                                </h6>
                                  <small class="mb-0 dropdown-msg-text text-secondary d-flex align-items-center">
                                    {{$key->text}}
                                  </small>
                                  <p class="msg-time text-secondary">
                                      {{ \Carbon\Carbon::parse($key->created_at)->diffForHumans() }}
                                  </p>
                            </div>
                        </div>
                  </a>
              @endforeach --}}
			@elseif($read_notifications!=null)

					 @foreach($read_notifications as $key)
              <a class="dropdown-item" href="{{url($key->link)}}">
                <div class="d-flex align-items-center">
                    <div class="notification-box">
                        <i class="{{$key->icon}}"></i>
                  </div>
                  <div class="ms-3 flex-grow-1">
                      <h6 class="mb-0 dropdown-msg-user">
                          {{$key->subject}}
                      </h6>
                      <small class="mb-0 dropdown-msg-text text-secondary d-flex align-items-center">
                          {{$key->text}}
                      </small>
                        <p class="msg-time text-secondary">
                              {{ \Carbon\Carbon::parse($key->created_at)->diffForHumans() }}
                        </p>
                    </div>
                </div>
              </a>
					@endforeach

			@endif
  </div>

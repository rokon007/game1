<?php


use Illuminate\Support\Facades\Session;
use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use App\Models\Notification;
use Livewire\Attributes\On;

new class extends Component
{
    public $unread_count,$unread_notifications="",$read_notifications="";

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
    public function SetEN(){
    App::setLocale('en');
    Session::put("locale",'en');

    }
    public function SetBN(){
    App::setLocale('bn');
    Session::put("locale",'bn');

    }
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
    public function mountComponent(){
    //   $adminUser =  auth()->user()->id;
    //   $this->unread_count=Notification::where('status',1)->whereNot('for_admin_id',$adminUser)->count();
    //   $this->unread_notifications=Notification::where('status','1')->whereNot('for_admin_id',$adminUser)->orderBy('id', 'DESC')->get();
    //   $this->read_notifications=Notification::where('status','1')->where('for_admin_id',$adminUser)->orderBy('id', 'DESC')->get();
    $adminUser =  auth()->user()->id;
    $this->unread_count=1;
    $this->read_notifications=1;
}
    public function mount(){

      $this->mountComponent();

    }
}; ?>














                  <div class="notifications">
                    <span class="notify-badge" wire:poll="mountComponent()">
                      @isset($unread_count)
                        {{$unread_count}}
                      @endisset
                    </span>
                    <i class="bi bi-bell-fill"></i>
                </div>

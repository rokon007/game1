<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.layout_login')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>



  <div class="login-wrapper d-flex align-items-center justify-content-center text-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-10 col-lg-8">
            <img class="big-logo" style="width:50%" src="{{asset('assets/frontend/img/core-img/PNG21.png')}}" alt="">
            <p class="form-group pt-4" style="color: white">Reset Password</p>
            <x-auth-session-status class="mb-4" :status="session('status')" />
          <!-- Register Form-->
          <div class="register-form mt-5">
            <form wire:submit="resetPassword">

              <div class="form-group text-start mb-4"><span>Email</span>
                <label for="email"><i class="ti ti-user"></i></label>
                <input class="form-control" wire:model="email" id="email"type="email" name="email" placeholder="Email" required autofocus>
                @error('email')<small class="text-danger mt-2">{{$message}}</small> @enderror
              </div>

              <div class="form-group text-start mb-4"><span>Password</span>
                <label for="email"><i class="ti ti-user"></i></label>
                <input class="form-control" wire:model="password" id="password" type="password" name="password" required autocomplete="new-password">
                @error('password')<small class="text-danger mt-2">{{$message}}</small> @enderror
              </div>

              <div class="form-group text-start mb-4"><span>Confirm Password</span>
                <label for="email"><i class="ti ti-user"></i></label>
                <input class="form-control" wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                @error('password_confirmation')<small class="text-danger mt-2">{{$message}}</small> @enderror
              </div>

              <button class="btn btn-warning btn-lg w-100" type="submit">Reset Password</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

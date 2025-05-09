<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.layout_login')] class extends Component
{
    public string $email = '';
    public $confirmation=false;

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);


        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );
        $this->confirmation=true;

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>


    <div>
        <div style="display:{{($confirmation ? 'none' : 'block' ) }}; ">
            <div class="login-wrapper d-flex align-items-center justify-content-center text-center">
                <div class="container">
                <div class="row justify-content-center">
                    <div class="col-10 col-lg-8">
                        <img class="big-logo" style="width:50%" src="{{asset('assets/frontend/img/core-img/PNG21.png')}}" alt="">
                        <p class="form-group pt-4" style="color: white">{{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</p>
                        <x-auth-session-status class="mb-4" style="color: white" :status="session('status')" />
                    <!-- Register Form-->
                    <div class="register-form mt-5">
                        <form wire:submit="sendPasswordResetLink">
                        <div class="form-group text-start mb-4"><span>Email</span>
                            <label for="email"><i class="ti ti-user"></i></label>
                            <input class="form-control" wire:model="email" id="email"type="email" name="email" placeholder="Email" required autofocus>
                            @error('email')<small class="text-danger mt-2">{{$message}}</small> @enderror
                        </div>
                        <button class="btn btn-warning btn-lg w-100" type="submit">
                            <span wire:loading.delay.long wire:target="sendPasswordResetLink" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Email Password Reset Link
                        </button>
                        </form>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div style="display:{{($confirmation ? 'block' : 'none' ) }};">
            <div class="login-wrapper d-flex align-items-center justify-content-center text-center">
                <div class="container">
                <div class="row justify-content-center">
                    <div class="col-10 col-lg-8">
                    <div class="success-check"><i class="ti ti-mail-check"></i></div>
                    <!-- Reset Password Message-->
                    <p class="text-white mt-3 mb-4">Password recovery email is sent successfully. Please check your inbox!</p>
                    <!-- Go Back Button--><a class="btn btn-warning btn-lg" href="{{route('home')}}">Go Home</a>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

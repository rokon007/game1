<?php

use App\Livewire\Forms\LoginForm;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.layout_login')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();
        // Store session data
        Session::flash('login_success', 'Welcome back, ' . auth()->user()->name . '!');
        if(auth()->user()->role == 'admin'){
            $this->redirectIntended(default: RouteServiceProvider::ADMINHOME, navigate: true);
        }else{


            // Regenerate the session ID to prevent session fixation attacks
            session()->regenerate();

            $redirectUrl = session('url.intended', RouteServiceProvider::HOME);

            $this->redirect($redirectUrl, navigate: true);
            // $this->redirectIntended(default: RouteServiceProvider::HOME, navigate: true);
        }

        // $this->redirectIntended(default: RouteServiceProvider::HOME, navigate: true);
    }
}; ?>

    <div class="login-wrapper d-flex align-items-center justify-content-center text-center">
        <!-- Background Shape-->
        <div class="background-shape"></div>
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-10 col-lg-8">
                <img class="big-logo" style="width:50%" src="{{asset('assets/frontend/img/core-img/PNG.png')}}" alt="">
                <!-- Session Status -->
                 <x-auth-session-status class="mb-4" :status="session('status')" />
                <!-- Register Form-->
                <div class="register-form mt-5">
                    <form wire:submit="login">
                    <div class="form-group text-start mb-4"><span>Email</span>
                        <label for="email"><i class="ti ti-user"></i></label>
                        <input class="form-control" wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="info@example.com">
                        <x-input-error :messages="$errors->get('form.email')" class="mt-2 text-danger" />
                    </div>
                    <div class="form-group text-start mb-4"><span>Password</span>
                        <label for="password"><i class="ti ti-key"></i></label>
                        <input class="form-control" wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Password">
                        <x-input-error :messages="$errors->get('form.password')" class="mt-2 text-danger" />
                    </div>
                     <!-- Remember Me -->
                    <div class="form-group text-start mb-4">
                        <span>
                            <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                            {{ __('Remember me') }}
                        </span>
                        <span></span>
                    </div>

                    <button class="btn btn-warning btn-lg w-100" type="submit">{{ __('Log in') }}</button>
                    </form>
                </div>
                <!-- Login Meta-->

                    <div class="login-meta-data">
                        @if (Route::has('password.request'))
                            <a class="forgot-password d-block mt-3 mb-1" href="{{ route('password.request') }}">{{ __('Forgot your password') }}?</a>
                        @endif
                        <p class="mb-0">{{__("Didn't have an account")}}?<a class="mx-1" href="{{ route('register') }}">Register Now</a></p>
                    </div>

            </div>
        </div>
    </div>

{{-- <div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div> --}}

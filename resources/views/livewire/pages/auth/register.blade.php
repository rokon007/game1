<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.layout_login')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'mobile' => ['required', 'string', 'regex:/^[0-9]{11}$/', 'unique:users,mobile'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        // $this->redirect(RouteServiceProvider::HOME, navigate: true);

        // $this->redirect(route('home'), navigate: true);

        // Regenerate the session ID to prevent session fixation attacks
        session()->regenerate();

        $redirectUrl = session('url.intended', RouteServiceProvider::HOME);

        $this->redirect($redirectUrl, navigate: true);
    }
}; ?>

    <div class="login-wrapper d-flex align-items-center justify-content-center text-center">
        <!-- Background Shape-->
        <div class="background-shape"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-10 col-lg-8">
                    <img class="big-logo" style="width:50%" src="{{asset('assets/frontend/img/core-img/PNG.png')}}" alt="">
                <!-- Register Form-->
                    <div class="register-form mt-5">
                        <form wire:submit="register">
                            <div class="form-group text-start mb-4"><span>{{__('Name')}}</span>
                                <label for="username"><i class="ti ti-user"></i></label>
                                <input class="form-control" wire:model="name" id="name" type="text" name="name" required autofocus autocomplete="name"  placeholder="{{__('Name')}}">
                                <x-input-error :messages="$errors->get('name')" class="mt-2 text-danger" />
                            </div>
                            <div class="form-group text-start mb-4"><span>{{__('Email')}}</span>
                                <label for="email"><i class="ti ti-at"></i></label>
                                <input class="form-control" wire:model="email" id="email" type="email" name="email" required autocomplete="username" placeholder="help@example.com">
                                <x-input-error :messages="$errors->get('email')" class="mt-2 text-danger" />
                            </div>
                            <div class="form-group text-start mb-4"><span>{{__('Mobile')}}</span>
                                <label for="username"><i class="ti ti-user"></i></label>
                                <input class="form-control" wire:model="mobile" id="mobile" type="text" inputmode="numeric" pattern="[0-9]*" name="mobile" required autofocus autocomplete="name"  placeholder="{{__('Mobile Number (11 digits)')}}">
                                <x-input-error :messages="$errors->get('mobile')" class="mt-2 text-danger" />
                            </div>
                            <div class="form-group text-start mb-4"><span>{{__('Password')}}</span>
                                <label for="password"><i class="ti ti-key"></i></label>
                                <input class="input-psswd form-control" wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Password">
                                <x-input-error :messages="$errors->get('password')" class="mt-2 text-danger" />
                            </div>
                            <div class="form-group text-start mb-4"><span>{{__('Confirm Password')}}</span>
                                <label for="password"><i class="ti ti-key"></i></label>
                                <input class="input-psswd form-control" wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="{{__('Confirm Password')}}">
                            </div>
                            <button class="btn btn-warning btn-lg w-100" type="submit">{{ __('Register') }}</button>
                        </form>
                    </div>
                     <!-- Login Meta-->
                    <div class="login-meta-data">
                        <p class="mt-3 mb-0">{{__('Already have an account')}}?<a class="mx-1" href="{{route('login')}}">{{ __('Log in') }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- <div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div> --}}

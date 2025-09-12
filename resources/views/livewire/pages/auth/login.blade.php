<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Request;

new #[Layout('layouts.layout_login')] class extends Component
{
    public LoginForm $form;

    /**
     * Generate a 7-character unique ID based on the first and last letters of the name and 5 random digits.
     */
    private function generateUniqueId(string $name): string
    {
        // Remove extra spaces and trim the name
        $name = trim(preg_replace('/\s+/', ' ', $name));

        // Get the first part of the name
        $nameParts = explode(' ', $name);
        $firstPart = $nameParts[0] ?? 'user';

        // Ensure the name has at least 2 characters; use defaults if not
        if (strlen($firstPart) < 2) {
            $firstPart = 'user';
        }

        // Get first and last letters, capitalize them
        $firstLetter = strtoupper(substr($firstPart, 0, 1));
        $lastLetter = strtoupper(substr($firstPart, -1));

        // Generate 5 random digits
        $randomDigits = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

        // Combine to form 7-character unique_id
        $uniqueId = $firstLetter . $lastLetter . $randomDigits;

        // Check if the unique_id already exists, regenerate digits if necessary
        while (User::where('unique_id', $uniqueId)->exists()) {
            $randomDigits = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $uniqueId = $firstLetter . $lastLetter . $randomDigits;
        }

        return $uniqueId;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $email = $this->form->email;
        $user = User::where('email', $email)->first();

        if ($user && $user->is_online) {
            $this->dispatch('show-alert',
                type: 'info',
                message: 'You are already logged in, please logout first'
            );
            return;
        }

        $this->validate();
        $this->form->authenticate();

        // Check if the user's unique_id is empty and generate one if needed
        $user = auth()->user();
        if (empty($user->unique_id)) {
            $user->unique_id = $this->generateUniqueId($user->name);
            $user->save();
        }

        //----------------------
        $ip = Request::ip();
        $location = 'Unknown';

        try {
            $response = Http::get("http://ip-api.com/json/{$ip}?fields=city,regionName,country");
            if ($response->successful()) {
                $data = $response->json();
                $location = "{$data['city']}, {$data['regionName']}, {$data['country']}";
            }
        } catch (\Exception $e) {
            // fallback location remains 'Unknown'
        }

        $user->update([
            'last_login_ip' => $ip,
            'last_login_location' => $location,
        ]);
        //----------------------

        Session::regenerate();

        $this->dispatch('show-alert',
            type: 'success',
            message: 'Welcome back, ' . $user->name . '!'
        );

        // Add a small delay before redirecting to allow the alert to show
        if ($user->role == 'admin') {
            $this->js('setTimeout(() => { window.location.href = "' . RouteServiceProvider::ADMINHOME . '"; }, 1500);');
        } else {
            $redirectUrl = session('url.intended', RouteServiceProvider::HOME);
            $this->js('setTimeout(() => { window.location.href = "' . $redirectUrl . '"; }, 1500);');
        }
    }
}; ?>

<div class="login-wrapper d-flex align-items-center justify-content-center text-center">
    <!-- Background Shape-->
    <div class="background-shape"></div>
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-10 col-lg-8">
            <img class="big-logo" style="width:50%" src="{{asset('assets/frontend/img/core-img/PNG2.png')}}" alt="">
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

                    <div class="input-group">
                        <input class="form-control" wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                            <i id="passwordToggleIcon" style="color: wheat;" class="ti ti-eye"></i>
                        </button>
                    </div>

                    <x-input-error :messages="$errors->get('form.password')" class="mt-2 text-danger" />
                </div>

                <script>
                    function togglePasswordVisibility() {
                        const passwordInput = document.getElementById("password");
                        const icon = document.getElementById("passwordToggleIcon");

                        if (passwordInput.type === "password") {
                            passwordInput.type = "text";
                            icon.classList.remove("ti-eye");
                            icon.classList.add("ti-eye-off");
                        } else {
                            passwordInput.type = "password";
                            icon.classList.remove("ti-eye-off");
                            icon.classList.add("ti-eye");
                        }
                    }
                </script>

                 <!-- Remember Me -->
                <div class="form-group text-start mb-4">
                    <span>
                        <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                        {{ __('Remember me') }}
                    </span>
                    <span></span>
                </div>

                <button class="btn btn-warning btn-lg w-100" type="submit">
                    <span wire:loading.delay.long wire:target="login" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ __('Log in') }}
                </button>
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

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
    // Listen for Livewire events
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('show-alert', (event) => {
            Swal.fire({
                icon: event.type,
                title: event.type.charAt(0).toUpperCase() + event.type.slice(1),
                text: event.message,
                confirmButtonColor: '#ffc107',
                background: '#1a1d21',
                color: '#fff'
            });
        });
    });

    // Also check for session messages on page load
    document.addEventListener('DOMContentLoaded', function() {
        @if (Session::has('login_success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ Session::get('login_success') }}',
                confirmButtonColor: '#ffc107',
                background: '#1a1d21',
                color: '#fff'
            });
        @endif

        @if (Session::has('login_info'))
            Swal.fire({
                icon: 'info',
                title: 'Info',
                text: '{{ Session::get('login_info') }}',
                confirmButtonColor: '#ffc107',
                background: '#1a1d21',
                color: '#fff'
            });
        @endif
    });
</script>

<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component
{
    use WithFileUploads;
    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public string $avatar = '';
    public $photo = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->mobile = Auth::user()->mobile;
        // $this->avatar = Auth::user()->avatar;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    // public function updateProfileInformation(): void
    // {
    //     $user = Auth::user();

    //     $validated = $this->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
    //         'mobile' => ['required', 'string'],
    //         'avatar' => ['nullable', 'image'],
    //     ]);

    //     $user->fill($validated);

    //     if ($user->isDirty('email')) {
    //         $user->email_verified_at = null;
    //     }

    //     $user->save();

    //     $this->dispatch('profile-updated', name: $user->name);
    // }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'mobile' => ['required', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        // Avatar আপলোড হলে সেভ করে দিন
        if ($this->photo) {
            $avatarPath = $this->photo->store('avatars', 'public');

            // পুরাতন ছবি ডিলিট করতে চাইলে এখানে unlink বা Storage::delete ব্যবহার করতে পারো
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $avatarPath;
        }

        // ফিল্ডগুলো আপডেট করুন
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
        ]);

        if (isset($validated['avatar'])) {
            $user->avatar = $validated['avatar'];
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: RouteServiceProvider::HOME);

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

{{-- <section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section> --}}

<div class="profile-wrapper-area py-3">
    <!-- User Information-->
    <div class="card user-info-card">
      <div class="card-body p-4 d-flex align-items-center">
        <div class="user-profile me-3">
             @if ($photo)
            <img src="{{ $photo->temporaryUrl() }}" >
            @elseif(auth()->user()->avatar)
            <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="">
            @else
            <img src="{{asset('assets/backend/upload/image/user/user.jpg')}}" alt="">
            @endif
          <div class="change-user-thumb">
            <form>
              <input class="form-control-file" wire:model="photo" type="file">
              <button><i class="ti ti-pencil"></i></button>
              @error('photo') <small class="text-danger">{{ $message }}</small> @enderror
            </form>
          </div>
        </div>
        <div class="user-info">
          <p class="mb-0 text-white">
            <div
            wire:transition
            wire:poll
            x-data="{ show: false }"
            x-on:profile-updated.window="show = true; setTimeout(() => show = false, 3000)"
            x-show="show"
            class="alert alert-success alert-dismissible fade show"
            role="alert"
            style="display: none;"
        >
            ✅ Profile updated successfully!
            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
        </div>
          </p>
          <h5 class="mb-0 text-white">{{$name}}</h5>
        </div>
      </div>
    </div>
    <!-- User Meta Data-->
    <div class="card user-data-card">
      <div class="card-body">
        <form wire:submit.prevent="updateProfileInformation">

          <div class="mb-3">
            <div class="title mb-2"><i class="ti ti-user"></i><span>Full Name</span></div>
            <input class="form-control" wire:model="name" type="text"  >
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
          <div class="mb-3">
            <div class="title mb-2"><i class="ti ti-phone"></i><span>Phone</span></div>
            <input class="form-control" wire:model="mobile" type="text" >
            @error('mobile') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
          <div class="mb-3">
            <div class="title mb-2"><i class="ti ti-mail"></i><span>Email Address</span></div>
            <input class="form-control" wire:model="email" type="email" \>
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          <button class="btn btn-primary btn-lg w-100" type="submit">
            Save All Changes
        </button>
        </form>
      </div>
    </div>
</div>

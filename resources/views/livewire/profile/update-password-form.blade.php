<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

{{-- <section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-6">
        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input wire:model="password" id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="password-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section> --}}

<div class="profile-wrapper-area py-3">
    <!-- User Information-->
    <div class="card user-info-card">
      <div class="card-body p-4 d-flex align-items-center">
        <h5 class="mb-0 text-white">Update Password</h5>


        <div class="user-info">
          <p class="mb-0 text-white">
            <div
            wire:transition
            wire:poll
            x-data="{ show: false }"
            x-on:password-updated.window="show = true; setTimeout(() => show = false, 3000)"
            x-show="show"
            class="alert alert-success alert-dismissible fade show"
            role="alert"
            style="display: none;"
        >
            ✅ Profile updated successfully!
            <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
        </div>
          </p>
        </div>
      </div>
    </div>
    <!-- User Meta Data-->
    <div class="card user-data-card">
      <div class="card-body">
        <form wire:submit.prevent="updatePassword">

          <div class="mb-3">
            <div class="title mb-2"><i class="ti ti-lock"></i><span>Current Password</span></div>
            <input class="form-control" wire:model="current_password" type="password"  >
            @error('current_password') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
          <div class="mb-3">
            <div class="title mb-2"><i class="ti ti-lock"></i><span>New Password</span></div>
            <input class="form-control" wire:model="password" type="password" >
            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
          <div class="mb-3">
            <div class="title mb-2"><i class="ti ti-lock"></i><span>Confirm Password</span></div>
            <input class="form-control" wire:model="password_confirmation" type="password" \>
            @error('password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
          </div>

          <button class="btn btn-primary btn-lg w-100" type="submit">
            Update Password
        </button>
        </form>
      </div>
    </div>
</div>

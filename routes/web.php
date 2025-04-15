<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use App\Livewire\Frontend\Home;
use App\Livewire\Frontend\ProfileComponent;
use App\Livewire\Frontend\RifleComponent;
use App\Livewire\Backend\Dashboard;
use App\Livewire\Backend\AdBannerManagementComponent;
use App\Livewire\Backend\Prize\ManagePrize;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//  Route::view('/', 'welcome');

Route::get('/', Home::class)->name('home');

// Admin Routes (requires admin role and authentication)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/add-banner', AdBannerManagementComponent::class)->name('addBanner');
    Route::get('/prizes', ManagePrize::class)->name('prizes');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/rifle-account', RifleComponent::class)->name('rifleAccount');
});

Route::get('/user-profile', ProfileComponent::class)->name('userProfile');
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

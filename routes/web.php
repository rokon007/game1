<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use App\Livewire\Frontend\Home;
use App\Livewire\Frontend\ProfileComponent;
use App\Livewire\Frontend\RifleComponent;
use App\Livewire\Frontend\NotificationsComponent;
use App\Livewire\Frontend\UserTransactions;
use App\Livewire\Frontend\UserWallet;
use App\Livewire\Frontend\CreditTransferForm;
use App\Livewire\Frontend\GameLobby;
use App\Livewire\Frontend\GameRoom;
use App\Livewire\Frontend\TicketView;
use App\Livewire\Frontend\UserGameHistory;
use App\Livewire\Frontend\WithdrawalForm;
use App\Livewire\Frontend\BuyTicketSheet;

use App\Livewire\Frontend\Hajari\GameCreate;
use App\Livewire\Frontend\Hajari\GameList;
use App\Livewire\Frontend\Hajari\HajariGameRoom;


use App\Livewire\Frontend\Chat\Chat;
use App\Livewire\Backend\Dashboard;
use App\Livewire\Backend\AdBannerManagementComponent;
use App\Livewire\Backend\RifleRequestManagementComponent;
use App\Livewire\Backend\Game\ManageGame;
use App\Livewire\Backend\Prize\ManagePrize;
use App\Livewire\Backend\NumberAnnouncer;
use App\Livewire\Backend\AgentComponent;
use App\Livewire\Frontend\NewChat\Main;

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
    Route::get('/rifle-request-management', RifleRequestManagementComponent::class)->name('rifle_request_management');
    Route::get('/manage-game', ManageGame::class)->name('manage_game');
    Route::get('/number-announcer/{gameId}', NumberAnnouncer::class)->name('number_announcer');
    Route::get('/agent', AgentComponent::class)->name('agent');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/rifle-account', RifleComponent::class)->name('rifleAccount');
    Route::get('/notifications', NotificationsComponent::class)->name('notifications');
    Route::get('/transactions', UserTransactions::class)->name('transactions');
    Route::get('/wallet', UserWallet::class)->name('wallet');
    Route::get('/creditTransfer', CreditTransferForm::class)->name('creditTransfer');
    Route::get('/game-lobby', GameLobby::class)->name('gameLobby');
    // Route::get('/game-room/{gameId}', GameRoom::class)->name('gameRoom');
    Route::get('/game-room/{gameId}/{sheetId?}', GameRoom::class)->name('gameRoom');
    Route::get('/ticket', TicketView::class)->name('ticket');
    Route::get('/game-history', UserGameHistory::class)->name('gameHistory');
    Route::get('/withdrawal', WithdrawalForm::class)->name('withdrawal');
    Route::get('/buy-ticket', BuyTicketSheet::class)->name('buy_ticket');

    //Hajari
    Route::get('/games', GameList::class)->name('games.index');
    Route::get('/games/create', GameCreate::class)->name('games.create');
    Route::get('/games/{game}', HajariGameRoom::class)->name('games.show');

    Route::get('/games/invitation/{invitation}', function(\App\Models\HajariGameInvitation $invitation) {
        return view('games.invitation', compact('invitation'));
    })->name('games.invitation');
});

Route::get('/chat', Main::class)->name('chat');

Route::get('/user-profile', ProfileComponent::class)->name('userProfile');
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

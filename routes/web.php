<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use App\Livewire\Frontend\Home;
use App\Livewire\Frontend\BannedUser;
use App\Livewire\Frontend\HowToUse;
use App\Livewire\Frontend\ContactSupport;
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
use App\Livewire\Frontend\TopUsers;

use App\Livewire\Frontend\Hajari\GameCreate;
use App\Livewire\Frontend\Hajari\GameList;
use App\Livewire\Frontend\Hajari\HajariGameRoom;


use App\Livewire\Frontend\Chat\Chat;
use App\Livewire\Backend\Dashboard;
use App\Livewire\Backend\RefillSettings;
use App\Livewire\Backend\User\UserComponent;
use App\Livewire\Backend\User\TransactionComponent;
use App\Livewire\Backend\AdBannerManagementComponent;
use App\Livewire\Backend\RifleRequestManagementComponent;
use App\Livewire\Backend\WithdrawalRequests;
use App\Livewire\Backend\Game\ManageGame;
use App\Livewire\Backend\Prize\ManagePrize;
use App\Livewire\Backend\NumberAnnouncer;
use App\Livewire\Backend\AgentComponent;
use App\Livewire\Backend\ReferralSettings;
use App\Livewire\Backend\HowToGuideManager;
use App\Livewire\Backend\HajariGameSettings;
use App\Livewire\Backend\WelcomeBonusSettings;
use App\Livewire\Frontend\NewChat\Main;
use App\Http\Controllers\CkeditorController;
use App\Livewire\SitemapXml;

use Illuminate\Http\Request;

use App\Livewire\Frontend\Lottery\LotteryList;
use App\Livewire\Frontend\Lottery\DrawAnimation;
use App\Livewire\Frontend\Lottery\LotteryHistory;
use App\Livewire\Frontend\Lottery\LiveDrawModal;
use App\Livewire\Frontend\Lottery\ActivLotteries;
use App\Livewire\Backend\Lottery\CreateLottery;
use App\Livewire\Backend\Lottery\LotteryIndex;
use App\Livewire\Backend\Lottery\Show;
use App\Livewire\Backend\Lottery\EditLottery;
//use App\Http\Controllers\LotteryController;

use App\Livewire\Frontend\Casino\LuckySpinGame;
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


Route::post('/set-timezone', function (Request $request) {
    session(['user_timezone' => $request->timezone]);

    if (auth()->check()) {
        auth()->user()->update(['timezone' => $request->timezone]);
    }

    return response()->json(['status' => 'timezone set']);
})->name('set.timezone');

Route::get('/how-to-use', HowToUse::class)->name('how.to.use');

Route::get('/', Home::class)->name('home');
// XML সাইটম্যাপ রুট
Route::get('/sitemap.xml', SitemapXml::class)->name('sitemap.xml');

// Admin Routes (requires admin role and authentication)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/refill-settings', RefillSettings::class)->name('refill_settings');
    Route::get('/user', UserComponent::class)->name('user');
    Route::get('/user-transactions/{id}', TransactionComponent::class)->name('user_transactions');
    Route::get('/add-banner', AdBannerManagementComponent::class)->name('addBanner');
    Route::get('/prizes', ManagePrize::class)->name('prizes');
    Route::get('/rifle-request-management', RifleRequestManagementComponent::class)->name('rifle_request_management');
    Route::get('/withdrawal-request-management', WithdrawalRequests::class)->name('withdrawal_request_management');
    Route::get('/manage-game', ManageGame::class)->name('manage_game');
    Route::get('/number-announcer/{gameId}', NumberAnnouncer::class)->name('number_announcer');
    Route::get('/agent', AgentComponent::class)->name('agent');
    Route::get('/referral-settings', ReferralSettings::class)->name('referral-settings');
    Route::get('/how-to-guides', HowToGuideManager::class)->name('howto');
    Route::get('/game-settings', HajariGameSettings::class)->name('hajari_game_settings');

    Route::get('/lottery', LotteryIndex::class)->name('lottery.index');
    Route::get('/lottery/create', CreateLottery::class)->name('lottery.create');
    Route::get('/lottery/{lottery}', Show::class)->name('lottery.show');
    Route::get('/lottery/{lottery}/edit', EditLottery::class)->name('lottery.edit');
    //WelcomeBonusSettings
    Route::get('/welcome-bonus-settings', WelcomeBonusSettings::class)->name('welcomeBonus-settings');

});



Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/banned', BannedUser ::class)->name('banned');
Route::get('/contact.support', ContactSupport ::class)->name('contact.support');

Route::middleware(['auth','banned'])->group(function(){
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
    Route::get('/top-users', TopUsers::class)->name('top_users');

    //Hajari
    Route::get('/games', GameList::class)->name('games.index');
    Route::get('/games/create', GameCreate::class)->name('games.create');
    Route::get('/games/{game}', HajariGameRoom::class)->name('games.show');

    Route::get('/games/invitation/{invitation}', function(\App\Models\HajariGameInvitation $invitation) {
        return view('games.invitation', compact('invitation'));
    })->name('games.invitation');

    Route::post('/ckeditor/upload', [CkeditorController::class, 'upload'])->name('ckeditor.upload');
    Route::post('/delete-image', [CkeditorController::class, 'deleteImage'])->name('delete.image');


    Route::get('/lottery', LotteryList::class)->name('lottery.index');
    Route::get('/lottery/{lottery}/draw', DrawAnimation::class)->name('lottery.draw');
    Route::get('/lottery/history', LotteryHistory::class)->name('lottery.history');
    Route::get('/lottery/live-draw', LiveDrawModal::class)->name('lottery.live-draw');
    Route::get('/lottery-active', ActivLotteries::class)->name('lottery_active');

    Route::get('/lucky-spin', LuckySpinGame::class)->name('lucky_spin');

});

Route::get('/chat', Main::class)->name('chat');

Route::get('/user-profile', ProfileComponent::class)->name('userProfile');
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

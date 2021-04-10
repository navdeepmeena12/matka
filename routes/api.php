<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CronJobController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\StarLineController;
use App\Http\Controllers\UserBankController;
use App\Http\Controllers\GaliController;
use App\Http\Controllers\UserBidController;
use Illuminate\Support\Facades\Route;

Route::post('check', [UserBidController::class, 'userPoints']);
Route::get('users', [AdminController::class, 'userList']);
Route::post('register', [PassportAuthController::class, 'register']);
Route::post('login', [PassportAuthController::class, 'login']);
Route::post('bids', [UserBidController::class, 'getUserBids']);
Route::post('place_bid', [UserBidController::class, 'placeUserBid']);
Route::post('market_data', [MarketController::class, 'provideMarketDigits']);
Route::post('one_market_data', [MarketController::class, 'provideOneMarketData']);
Route::post('bet', [UserBidController::class, 'placeUserBid']);
Route::post('hash_password', [UserBidController::class, 'hashPassword']);
Route::get("check_sp", [CronJobController::class, 'initCheckSP']);
Route::get("check_dp", [CronJobController::class, 'initCheckDP']);
Route::get("check_tp", [CronJobController::class, 'initCheckTP']);
Route::get("check_single", [CronJobController::class, 'initCheckSingle']);
Route::get("check_double", [CronJobController::class, 'initCheckDouble']);
Route::get("check_sangam", [CronJobController::class, 'initCheckHsg']);
Route::get('open', [CronJobController::class, 'initMarketStatusChangeOpen']);
Route::get('close', [CronJobController::class, 'initMarketStatusChangeClose']);
Route::get("reset_market", [CronJobController::class, 'initResetMarket']);
Route::get("current_date", [StarLineController::class, 'provideCurrentDate']);

//StarLineRoutes
Route::get('check_star_panel', [StarLineController::class, 'initCheckUserPanelBets']);
Route::get('check_star_bets', [StarLineController::class, 'initCheckUserSingleBets']);
Route::get('starline_close', [StarLineController::class, 'initStarlineClose']);
Route::get('starline_reset',[StarLineController::class,'initResetStarlineMarkets']);



Route::get("a", [CronJobController::class, 'settleSingleBetsOpen']);

Route::get("gali", [GaliController::class, 'gali']);
Route::get("date", [GaliController::class, 'currentDate']);
Route::get("settle_gali", [GaliController::class, 'settleBid']);
Route::post("place_bid", [GaliController::class, 'bid_place']);
Route::get("close_it", [GaliController::class, 'closeMarket']);
Route::get("reset_it", [GaliController::class, 'resetMarkets']);
Route::post("charts", [GaliController::class, 'chart_url']);


Route::middleware('auth:api')->group(function () {
    Route::get('markets', [MarketController::class, 'provideMarketNames']);
    Route::get('check_server', [MainController::class, 'provideServerStatus']);
    Route::post('user_points', [UserBankController::class, 'userBalance']);
    Route::post('user_profile', [PassportAuthController::class, 'provideProfile']);
    Route::get('banner_image', [MainController::class, 'provideBannerImage']);
    Route::post('bet_history', [UserBankController::class, 'provideBidHistory']);
    Route::post('win_history', [UserBankController::class, 'provideWinHistory']);
    Route::get('time', [CronJobController::class, 'initMarketStatusChange']);
    Route::get('check_win', [CronJobController::class, 'settleSinglePanelClose']);
    Route::post('save_bank', [UserBankController::class, 'saveUserBankInfo']);
    Route::post('get_bank', [UserBankController::class, 'provideUserBankInfo']);
    Route::get('app_config', [MainController::class, 'provideAppConfig']);
    Route::get("double_bets", [CronJobController::class, "settleDoubleBets"]);
    Route::post('set_result', [MarketController::class, 'setMarketResult']);
    Route::get('star_markets', [StarLineController::class, 'provideStarLineMarkets']);
    Route::get('star_rates', [StarLineController::class, 'provideStarLineRates']);
    Route::post('star_bid_place', [StarLineController::class, 'placeStarLineBid']);
Route::post('star_bids', [StarLineController::class, 'provideStarLineBetHistory']);
Route::post('star_wins', [StarLineController::class, 'provideStarWinHistory']);
Route::post("gali_bid_his", [GaliController::class, 'bidHistory']);
Route::post("gali_win_his", [GaliController::class, 'winHistory']);
});




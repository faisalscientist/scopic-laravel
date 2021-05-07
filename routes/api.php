<?php

use App\Http\Controllers\AuctionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('auction-items', [AuctionsController::class, 'index']);
Route::get('auction-items/{id}', [AuctionsController::class, 'getAuctionItem']);
Route::get('maximum-bid/{id}', [AuctionsController::class, 'maximumBid']);
Route::get('auto-bidding/{user}', [AuctionsController::class, 'getAutoBidding']);
Route::post('auto-bidding', [AuctionsController::class, 'createAutoBidding']);
Route::put('auto-bidding/{id}', [AuctionsController::class, 'updateAutoBidding']);
Route::post('bid', [AuctionsController::class, 'bidForItem']);

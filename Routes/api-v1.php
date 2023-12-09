<?php

use Illuminate\Http\Request;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Http\Controllers\MessageController;

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

Route::post('send-message', [MessageController::class, 'store'] )->middleware('throttle:60,1')
    ->name('message.store');
Route::put('update-message/{message}', [MessageController::class, 'update'])
    ->name('message.update');
Route::put('seen-message', [MessageController::class, 'seenMessage'])
    ->name('message.seenMessage');
Route::put('delivered-message', [MessageController::class, 'deliveredMessage'])
    ->name('message.deliveredMessage');
Route::get('message-list', [MessageController::class, 'index'])
    ->name('message.index');
Route::delete('delete-message/{message}',[MessageController::class, 'destroy'])
    ->name('message.destroy');

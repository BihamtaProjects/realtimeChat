<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Modules\Chat\ChatTypes\SupportChat\Http\Controllers\Api\v1\SupportChatController;

Route::prefix('supportchat')->group(function() {
    Route::get('/', [SupportChatController::class,'index']);
});

<?php

use Illuminate\Http\Request;
use Modules\Chat\ChatTypes\SupportChat\Http\Controllers\Api\v1\SupportChatController;

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
Route::resource('support-chat', SupportChatController::class)->except(['create', 'edit','store']);
Route::post('support-chat', [SupportChatController::class ,'store'])->withoutMiddleware(['auth:sanctum','mobile.confirm'])->name('supportChat.store');


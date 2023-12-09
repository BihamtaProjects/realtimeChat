<?php

use Illuminate\Http\Request;
use Modules\Chat\ChatTypes\ConsultingChat\Http\Controllers\Api\v1\ConsultingChatController;
use Modules\Chat\ChatTypes\ConsultingChat\Http\Middleware\IsConsultingChatEditable;
use Modules\Chat\Http\Controllers\SpecialMessageController;
use Modules\Financial\Http\Middleware\PaymentDone;


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

//Route::middleware('auth:api')->get('/consultingChat', function (Request $request) {
//    return $request->user();
//});
Route::resource('consulting-chat', ConsultingChatController::class)->except(['create', 'edit','store','update']);
Route::post('consulting-chat', [ConsultingChatController::class ,'store'])->withoutMiddleware(['auth:sanctum','mobile.confirm'])
    ->name('consultingChat.store');
Route::put('consulting-chat/{consultingChat}', [ConsultingChatController::class ,'update'])
    ->Middleware(IsConsultingChatEditable::class)
    ->name('consultingChat.update');
Route::post('consulting-chat/consulting-responder-history/{consultingChat}', [ConsultingChatController::class ,'ConsultingResponderHistory'])
    ->Middleware(['auth:sanctum','mobile.confirm', PaymentDone::class])->name('consultingChat.consultingResponderHistories');

Route::get('status-specialMessages', [SpecialMessageController::class, 'index'])->name('specialMessage.index');


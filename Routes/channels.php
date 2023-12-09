<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Chat\Models\Chat;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    if (chat::findOrNew($chatId)->users()->where('tbl_base_user.id', $user->id)->exists()) {
        return Arr::only($user->toArray(),['id','email','name','family']);
    }
    return false;
});
Broadcast::channel('chat.deleteMessage.{chatId}', function ($user, $chatId) {
    if (chat::findOrNew($chatId)->users()->where('tbl_base_user.id', $user->id)->exists()) {
        return Arr::only($user->toArray(),['id','email','name','family']);
    }
    return false;
});
//Broadcast::channel('user.status.{chatId}', function ($user, $chatId) {
//    if (chat::findOrNew($chatId)->users()->where('tbl_base_user.id', $user->id)->exists()) {
//        return Arr::only($user->toArray(),['id','email','name','family']);
//    }
//    return false;
//});

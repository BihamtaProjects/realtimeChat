<?php

namespace Modules\Chat\Listeners;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Modules\Chat\Events\SetSupportStatus;

class SupportStatus
{

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(SetSupportStatus $event)
    {
        $supportChat = SupportChat::where('chat_id', $event->chatId)->first();
        $role = $event->user->getRole();
        if ($role == 'patient') {
            $supportChat->status = SupportChat::STATUS_USER_RESPOND;
        } elseif ($role == 'doctor') {
            $supportChat->status = SupportChat::STATUS_SUPPORT_RESPOND;
        }
        $supportChat->save();

    }
}

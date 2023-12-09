<?php

namespace Modules\Chat\Listeners;


use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\Events\SetConsultingStatus;

class ConsultingStatus
{

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(SetConsultingStatus $event)
    {
        $consultingChat = ConsultingChat::where('chat_id', $event->chatId)->first();
        $role = $event->user->getRole();
            if ($role == 'patient') {
                $consultingChat->status = ConsultingChat::STATUS_USER_RESPOND;
            } elseif ($role == 'doctor') {
                $consultingChat->status = ConsultingChat::STATUS_DOCTOR_RESPOND;
            }
            $consultingChat->save();
        }
}

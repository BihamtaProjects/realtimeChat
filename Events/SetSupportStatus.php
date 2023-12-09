<?php

namespace Modules\Chat\Events;

use App\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SetSupportStatus
{
    use Dispatchable, SerializesModels;

    public User $user;
    public int $chatId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($chatId,User $user)
    {
        $this->user = $user;
        $this->chatId = $chatId;
    }


}

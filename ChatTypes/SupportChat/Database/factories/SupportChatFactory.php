<?php

namespace Modules\Chat\ChatTypes\SupportChat\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Str;

class SupportChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SupportChat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => SupportChat::STATUS_USER_RESPOND,
            'support_reason_id' =>rand(1,5),
        ];
    }
}


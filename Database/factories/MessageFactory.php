<?php

namespace Modules\Chat\Database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Chat\Models\Chat;
use Modules\Chat\Models\Message;
use Modules\Chat\Models\SpecialMessage;


class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'chat_id' => Chat::factory()->create()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'text' => $this->faker->text(50),
            'seen' => [],
        ];
    }
}


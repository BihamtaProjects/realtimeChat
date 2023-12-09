<?php

namespace Modules\Chat\Database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Chat\Models\Chat;

class ChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Chat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => 'test title',
            'content' => $this->faker->text,
            'create_user_id'=> User::first()->id,
            'last_respond_id'=> User::first()->id,

        ];
    }

}


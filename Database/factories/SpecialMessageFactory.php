<?php

namespace Modules\Chat\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Chat\Models\SpecialMessage;

class SpecialMessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpecialMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
           'content' => $this->faker->text(50),
            'name' => 'method',
            'controller_method' => '',
        ];
    }
}


<?php

namespace Modules\Chat\ChatTypes\SupportChat\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupportReasonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Chat\ChatTypes\SupportChat\Models\SupportReason::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title'=> $this->faker->name,
        ];
    }
}


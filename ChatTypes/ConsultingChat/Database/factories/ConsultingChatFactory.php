<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Database\factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Contract\Models\Hospital;
use Str;

class ConsultingChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ConsultingChat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'unique_id'=> Str::random(8),
            'status'=> ConsultingChat::STATUS_PENDING,
            'open_time'=> Carbon::yesterday(),
            'private'=> ConsultingChat::PRIVATE_STATUS_PRIVATE,
            'priority'=> '0',
            'visit_number'=> rand(100000,999999),
            'doctor_last_answer_at'  => Carbon::now(),
            'placeable_type' =>  'Modules\Contract\Models\Hospital',
            'placeable_id' => 1
        ];
    }
}


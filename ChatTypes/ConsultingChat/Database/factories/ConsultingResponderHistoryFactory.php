<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingResponderHistory;
use Modules\Doctor\Models\Doctor;

class ConsultingResponderHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ConsultingResponderHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $consultingchat = ConsultingChat::inRandomOrder()->first();
        $doctor = Doctor::inRandomOrder()->first();

        return [
            'consulting_chat_id'=> $consultingchat->id,
            'old_doctor_id'=> $doctor->id,
            'status'=> 1,
            'past_status'=> 2,
        ];
    }
}


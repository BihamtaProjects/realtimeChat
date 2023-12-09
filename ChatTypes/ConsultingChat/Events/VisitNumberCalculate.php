<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Doctor\Models\Doctor;

class VisitNumberCalculate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Doctor $doctor;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Doctor $doctor)
    {
        $this->doctor = $doctor;
    }
}

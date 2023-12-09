<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Listeners;

use Modules\Chat\ChatTypes\ConsultingChat\Events\VisitNumberCalculate;

class CalculateVisitNumber
{
    /**
     * Handle the event.
     *
     * @param VisitNumberCalculate $event
     */
    public function handle(VisitNumberCalculate $event)
    {
        $event->doctor->update([
            'visit_number' => $event->doctor->visit_number + 1,
        ]);
    }
}

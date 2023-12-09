<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Chat\ChatTypes\ConsultingChat\Events\VisitNumberCalculate;
use Modules\Chat\ChatTypes\ConsultingChat\Listeners\CalculateVisitNumber;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        VisitNumberCalculate::class => [
            CalculateVisitNumber::class,
        ],
    ];
}

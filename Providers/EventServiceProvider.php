<?php

namespace Modules\Chat\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Modules\Chat\Events\SetConsultingStatus;
use Modules\Chat\Events\SetSupportStatus;
use Modules\Chat\Listeners\ConsultingStatus;
use Modules\Chat\Listeners\SupportStatus;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SetConsultingStatus::class => [
            ConsultingStatus::class,
        ],
        SetSupportStatus::class => [
            SupportStatus::class,
        ],
    ];
}

<?php

namespace Modules\Chat\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes(['prefix'=>'api/v1/chat','middleware' => ['api', 'auth:sanctum']]);
        }
}

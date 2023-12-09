<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Chat\ChatTypes\ConsultingChat\Console\MigrateYii2AutoPrivateQuestionWord;

class ConsoleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
           MigrateYii2AutoPrivateQuestionWord::class
        ]);
    }
}

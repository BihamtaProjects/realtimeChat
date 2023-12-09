<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Providers;

use App\Models\User;
use App\Providers\Traits\HasSeederServiceProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatDatabaseSeeder;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingResponderHistory;
use Modules\Chat\Models\Chat;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Doctor\Models\Doctor;

class ConsultingChatServiceProvider extends ServiceProvider
{
    use HasSeederServiceProvider;
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Chat';
    protected $basePath = 'ChatTypes/ConsultingChat';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'consultingChat';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerRelations();
        $this->loadMigrationsFrom(module_path($this->moduleName, $this->basePath . '/Database/Migrations'));
        $this->registerSeeder([
            ConsultingChatDatabaseSeeder::class,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(ConsoleServiceProvider::class);

    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, $this->basePath . '/Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadTranslationsFrom(module_path($this->moduleName, $this->basePath . '/Resources/lang'), $this->moduleNameLower);
        $this->loadJsonTranslationsFrom(module_path($this->moduleName, $this->basePath . '/Resources/lang'), $this->moduleNameLower);
    }


    private function registerRelations()
    {
        Chat::resolveRelationUsing('consultingChat', function ($chatModel) {
            return $chatModel->hasOne(ConsultingChat::class,'chat_id', 'id');
        });
        Doctor::resolveRelationUsing('consultingResponderHistories', function ($chatModel) {
            return $chatModel->hasMany(ConsultingResponderHistory::class, 'old_doctor_id','id');
        });

    }
}

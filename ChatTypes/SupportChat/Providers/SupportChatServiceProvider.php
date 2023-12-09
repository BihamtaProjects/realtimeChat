<?php

namespace Modules\Chat\ChatTypes\SupportChat\Providers;

use App\Providers\Traits\HasSeederServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatSeeder;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportChatDatabaseSeeder;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportSeeder;
use Modules\Chat\Models\Chat;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;

class SupportChatServiceProvider extends ServiceProvider
{
    use HasSeederServiceProvider;
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Chat';
    protected $basePath = 'ChatTypes/SupportChat';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'supportchat';

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
            SupportChatDatabaseSeeder::class,
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
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName,$this->basePath . '/Config/config.php'), $this->moduleNameLower
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
        Chat::resolveRelationUsing('supportChat', function ($chatModel) {
            return $chatModel->hasOne(SupportChat::class,'chat_id', 'id');
        });


    }
}

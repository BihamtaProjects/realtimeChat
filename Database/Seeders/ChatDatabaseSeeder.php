<?php

namespace Modules\Chat\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatSeeder;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportSeeder;

class ChatDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            ChatSeeder::class,
            MessageSeeder::class,
            SpecialMessageSeeder::class
        ]);
    }
}

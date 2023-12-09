<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatSeeder;
use Modules\Chat\ChatTypes\ConsultingChat\Models\PrivateWord;

class ConsultingChatDatabaseSeeder extends Seeder
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
            ConsultingChatSeeder::class,
            PrivateWordSeeder::class,
        ]);
    }
}

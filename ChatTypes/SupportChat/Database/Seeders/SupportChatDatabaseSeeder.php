<?php

namespace Modules\Chat\ChatTypes\SupportChat\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatSeeder;

class SupportChatDatabaseSeeder extends Seeder
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
            SupportReasonSeeder::class,
            SupportSeeder::class,
            ConsultingChatSeeder::class,
            ]);
    }
}

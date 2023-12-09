<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingResponderHistory;

class ConsultingResponderHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ConsultingResponderHistory::factory()->count(5)->create();
    }
}

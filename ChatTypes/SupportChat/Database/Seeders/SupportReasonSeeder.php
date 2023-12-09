<?php

namespace Modules\Chat\ChatTypes\SupportChat\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportReason;

class SupportReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SupportReason::factory()->count(5)->create();
    }
}

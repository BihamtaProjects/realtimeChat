<?php

namespace Modules\Chat\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Chat\Models\Message;
use Modules\Location\Models\Location;


class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//media
//        $message = Message::factory()->create();
//        $fileName = md5($message->id . time());
//        $message->addMedia('Modules/Media/Storage/SampleFile/mySuggestion.png')
//            ->setName($fileName)
//            ->setFileName($fileName . '.png')
//            ->toMediaCollection('images');

//location
       Message::factory()
            ->has(Location::factory())
            ->count(5)
            ->create();
    }
}

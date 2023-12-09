<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\Models\Chat;

class ConsultingChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::first();
        $doc = User::inRandomOrder()->first();
        $doctor = $doc->doctor()->create();
        $chatUserItems = [
            $user->id => ['role' => UserRoleEnum::Patient],
            $doctor->user_id => ['role' => UserRoleEnum::Doctor]
        ];
        $chat = Chat::factory()
            ->has(ConsultingChat::factory())
            ->create();
        $chat->users()->attach($chatUserItems);
    }
}

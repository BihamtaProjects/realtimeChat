<?php

namespace Modules\Chat\ChatTypes\SupportChat\Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Chat\Models\Chat;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;

class SupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::firstOrFail();
        $chat = Chat::factory()
            ->has(SupportChat::factory())->create();
        $chat->users()->attach($user, [ 'role' => UserRoleEnum::Patient]);
    }
}

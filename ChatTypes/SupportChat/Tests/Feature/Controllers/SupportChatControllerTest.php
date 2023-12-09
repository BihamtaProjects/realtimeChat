<?php

namespace Modules\Chat\ChatTypes\SupportChat\Tests\Feature\Controllers;
;
use App\Models\Admin;
use App\Models\Patient;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportReasonSeeder;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportSeeder;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Modules\Chat\Database\Seeders\ChatSeeder;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Traits\TestUnauthenticated;
use Tests\Feature\Traits\TestUnConfirmedMobile;
use Tests\TestCase;

class SupportChatControllerTest extends TestCase
{
    use TestUnauthenticated, TestUnConfirmedMobile;
    /**
     * @return array
     */
    public function shouldAuthenticatedRoutes(): array
    {
        return [
            [
                'get',
                'chat.chatTypes.supportChat.support-chat.index',
            ],
            [
                'get',
                'chat.chatTypes.supportChat.support-chat.show',
                ['support_chat' => 2],
                [ChatSeeder::class]
            ],
            [
                'put',
                'chat.chatTypes.supportChat.support-chat.update',
                ['support_chat' => 2],
                [ChatSeeder::class]
            ],
            [
                'delete',
                'chat.chatTypes.supportChat.support-chat.destroy',
                ['support_chat' => 2],
                [ChatSeeder::class]
            ],

        ];
    }
    /**
     * @return array
     */
    public function shouldUserMobileConfirmedRoutes(): array
    {
        return [
            [
                'get',
                'chat.chatTypes.supportChat.support-chat.index',
            ],
            [
                'get',
                'chat.chatTypes.supportChat.support-chat.show',
                ['support_chat' => 1],
                [ChatSeeder::class,SupportReasonSeeder::class,SupportSeeder::class]
            ],
            [
                'put',
                'chat.chatTypes.supportChat.support-chat.update',
                ['support_chat' => 1],
                [ChatSeeder::class,SupportReasonSeeder::class,SupportSeeder::class]
            ],
            [
                'delete',
                'chat.chatTypes.supportChat.support-chat.destroy',
                ['support_chat' => 1],
                [ChatSeeder::class,SupportReasonSeeder::class,SupportSeeder::class]
            ],

        ];
    }

    /**
     * @test
     */
    public function user_support_chat_list()
    {
        /** @var User $user */
        $user = User::factory()->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->get(route('chat.chatTypes.supportChat.support-chat.index'));

        $response
            ->assertOk();
    }
    /**
     * @test
     */
    public function admin_support_chat_list_without_user_id()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->get(route('chat.chatTypes.supportChat.support-chat.index'));

        $response->assertOk();
    }
    /**
     * @test
     */
    public function admin_support_chat_list_with_user_id()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();
        $newUser = User::factory()->has(Patient::factory())->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->get(route('chat.chatTypes.supportChat.support-chat.index',[
            'user_id' => $newUser->id
        ]));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function admin_support_chat_show()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();
        $this->seed([
            ChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $supportChat = SupportChat::firstOrFail();
        $response = $this->get(route('chat.chatTypes.supportChat.support-chat.show', [
            'support_chat' => $supportChat->id,
        ]));
        $response->assertOk();
    }
    /**
     * @test
     */
    public function user_support_chat_show()
    {
        /** @var User $user */
        $user = User::factory()->has(patient::factory())->create();
        $this->seed([
            ChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $supportChat = SupportChat::firstOrFail();
        $chat = $supportChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();


        $response = $this->get(route('chat.chatTypes.supportChat.support-chat.show', [
            'support_chat' => $supportChat->id,
        ]));
        $response->assertOk();
    }

    /**
     * @test
     */
    public function support_chat_store_invalid_data()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->post(route('chat.chatTypes.supportChat.supportChat.store'), [
            'title',
            'content',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'title',
                'content',
            ]);
    }
    /**
     * @test
     */
    public function supportChat_store_successfully_when_user_logged_in_and_has_confirmed_mobile_number()
    {
        /** @var User $user */
        $user = User::factory()->has(patient::factory())->create();
        $this->seed([
            SupportReasonSeeder::class,
        ]);

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->post(route('chat.chatTypes.supportChat.supportChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'name' => $user->name,
            'mobile' => $user->cell,
            'status' => SupportChat::STATUS_USER_RESPOND,
            'support_reason_id' => rand(1,5),
        ]);

        $response->assertStatus(201);

    }
    /**
     * @test
     */
    public function supportChat_store_successfully_when_user_not_logged_in_and_mobile_not_exists()
    {
        $this->seed([
            SupportReasonSeeder::class,
        ]);

        $response = $this->post(route('chat.chatTypes.supportChat.supportChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'name' => 'nick name',
            'mobile' => '+989153514071',
            'status' => SupportChat::STATUS_USER_RESPOND,
            'support_reason_id' => rand(1,5),
        ]);

        $response->assertStatus(423);

    }
    /**
     * @test
     */
    public function supportChat_store_successfully_when_user_not_logged_in_and_mobile_exists()
    {
        $user = User::factory()->has(patient::factory())->create();
        $this->seed([
            SupportReasonSeeder::class,
        ]);
        $response = $this->post(route('chat.chatTypes.supportChat.supportChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'name' => $user->name,
            'mobile' => $user->cell,
            'status' => SupportChat::STATUS_USER_RESPOND,
            'support_reason_id' => rand(1,5),
        ]);

        $response->assertStatus(201);

    }
    /**
     * @test
     */
    public function supportChat_store_successfully_when_user_logged_in_without_mobile()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();
        $user->cell = null;
        $user->save();
        Sanctum::actingAs(
            $user
        );
        $this->seed([
            SupportReasonSeeder::class,
        ]);

        $response = $this->post(route('chat.chatTypes.supportChat.supportChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'name' => 'bita daghestani',
            'mobile' => '+989153514007',
            'status' => SupportChat::STATUS_USER_RESPOND,
            'support_reason_id' => rand(1,5),
        ]);

        $response->assertStatus(423);
    }

    /**
     * @test
     */
    public function supportChat_update_invalid_data()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $this->seed([
            ChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);
        $supportChat= supportChat::firstOrFail();
        $response = $this->put(route('chat.chatTypes.supportChat.support-chat.update', [
            'support_chat' => $supportChat->id,
        ]), [
            'title' => 1,
            'content' => 1,
        ]);
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'content',
            ]);
    }
    /**
     * @test
     */
    public function supportChat_admin_update_successfully()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);
        $supportChat = SupportChat::firstOrFail();
        $user = User::factory()->has(Admin::factory())->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->put(route('chat.chatTypes.supportChat.support-chat.update', [
            'support_chat' => $supportChat->id,
        ]), [
            'title' => $this->faker->text,
            'content' => $this->faker->text,
        ]);
        $response
            ->assertStatus(200);
    }
    /**
     * @test
     */
    public function supportChat_user_update_successfully()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);
        $user = User::factory()->has(Patient::factory())->create();
        $supportChat = SupportChat::firstOrFail();
        $chat = $supportChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->put(route('chat.chatTypes.supportChat.support-chat.update', [
            'support_chat' => $supportChat->id,
        ]), [
            'title' => $this->faker->text,
            'content' => $this->faker->text,
        ]);
        $response
            ->assertStatus(200);
    }
    /**
     * @test
     */
    public function update_supportchat_invalid_User()
    {
        $user = User::factory()->has(Patient::factory())->create();
        Sanctum::actingAs($user);
        $this->seed([
            ChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);
        $newUser = User::factory()->has(Patient::factory())->create();
        $supportChat = SupportChat::firstOrFail();
        $chat = $supportChat->chat;
        $chat->create_user_id = $newUser->id;
        $chat->save();
        $response = $this->put(route('chat.chatTypes.supportChat.support-chat.update', [
            'support_chat' => $supportChat->id,
        ]), [
            'title' => $this->faker->text,
            'content' => $this->faker->text,
        ]);
        $response
            ->assertStatus(403);

    }

    /**
     * @test
     */
    public function delete_supportChat_successfully()
    {
        $user = User::firstOrFail();
        Sanctum::actingAs($user);
        $this->seed([
            ChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);
        $supportChat = SupportChat::firstOrFail();
        $response = $this->delete(route('chat.chatTypes.supportChat.support-chat.destroy', [
            'support_chat' => $supportChat->id,
        ]));
        $response->assertOk();
    }
    /**
     * @test
     */
    public function delete_supportchat_invalid_user()
    {
        $user = User::factory()->has(Patient::factory())->create();
        Sanctum::actingAs($user);
        $this->seed([
            ChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);
        $newUser = User::factory()->has(Patient::factory())->create();
        $supportChat = SupportChat::firstOrFail();

        $response = $this->delete(route('chat.chatTypes.supportChat.support-chat.destroy', [
            'support_chat' => $supportChat->id,
        ]));

        $response
            ->assertStatus(403);

    }
}

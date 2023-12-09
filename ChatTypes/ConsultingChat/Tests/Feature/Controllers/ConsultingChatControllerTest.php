<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Tests\Feature\Controllers;

use App\Enums\UserRoleEnum;
use App\Models\Admin;
use App\Models\Patient;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatSeeder;
use Modules\Chat\ChatTypes\ConsultingChat\Models\consultingChat;
use Modules\Chat\Database\Seeders\ChatSeeder;
use Modules\Chat\Models\Chat;
use Modules\Doctor\Models\Doctor;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Traits\TestUnauthenticated;
use Tests\Feature\Traits\TestUnConfirmedMobile;
use Tests\TestCase;

class ConsultingChatControllerTest extends TestCase
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
                'chat.chatTypes.consultingChat.consulting-chat.index',
            ],
            [
                'get',
                'chat.chatTypes.consultingChat.consulting-chat.show',
                ['consulting_chat' => 2],
                [ChatSeeder::class]
            ],
            [
                'put',
                'chat.chatTypes.consultingChat.consultingChat.update',
                ['consultingChat' => 2],
                [ChatSeeder::class]
            ],
            [
                'delete',
                'chat.chatTypes.consultingChat.consulting-chat.destroy',
                ['consulting_chat' => 2],
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
                'chat.chatTypes.consultingChat.consulting-chat.index',
            ],
            [
                'get',
                'chat.chatTypes.consultingChat.consulting-chat.show',
                ['consulting_chat' => 1],
                [ChatSeeder::class,consultingChatSeeder::class]
            ],
            [
                'put',
                'chat.chatTypes.consultingChat.consultingChat.update',
                ['consultingChat' => 1],
                [ChatSeeder::class,consultingChatSeeder::class]
            ],
            [
                'delete',
                'chat.chatTypes.consultingChat.consulting-chat.destroy',
                ['consulting_chat' => 1],
                [ChatSeeder::class,consultingChatSeeder::class]
            ],

        ];
    }

    /**
     * @test
     */
    public function user_consulting_chat_list()
    {
        /** @var User $user */
        $user = User::factory()->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->get('/api/v1/chat/consulting-chat');

        $response
            ->assertOk();
    }
    /**
     * @test
     */
    public function admin_consulting_chat_list_with_user_id()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();
        $newUser = User::factory()->has(Patient::factory())->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->get('/api/v1/chat/consulting-chat',[
            'user_id' => $newUser->id
        ]);

        $response
            ->assertOk();
    }
    /**
     * @test
     */
    public function admin_consulting_chat_list_without_user_id()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->get('/api/v1/chat/consulting-chat');

        $response
            ->assertOk();
    }

    /**
     * @test
     */
    public function admin_consulting_chat_show()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $consultingChat = consultingChat::firstOrFail();
        $response = $this->get(route('chat.chatTypes.consultingChat.consulting-chat.show', [
            'consulting_chat' => $consultingChat->id,
        ]));
        $response->assertOk();
    }
    /**
     * @test
     */
    public function user_consulting_chat_show()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $consultingChat = consultingChat::firstOrFail();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();
        $response = $this->get(route('chat.chatTypes.consultingChat.consulting-chat.show', [
            'consulting_chat' => $consultingChat->id,
        ]));
        $response->assertOk();
    }

    /**
     * @test
     */
    public function consulting_chat_store_invalid_data()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->post(route('chat.chatTypes.consultingChat.consultingChat.store'), [
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
    public function consultingChat_store_successfully_when_user_logged_in_and_has_mobile_number()
    {
        /** @var User $user */
        $user = User::factory()->has(patient::factory())->create();
        $user->national_code = '1050194098';
        $user->save();
        $newUser = User::factory()->has(Doctor::factory())->create();
        $doctor = Doctor::where('user_id',$newUser->id)->first();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->post(route('chat.chatTypes.consultingChat.consultingChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'name' => $user->name,
            'mobile' => $user->cell,
            'status' => consultingChat::STATUS_PENDING,
            'doctor_id' => $doctor->id,
            'priority' => 1,
            'private' =>consultingChat::PRIVATE_STATUS_PRIVATE,
            'currency' => 1,
        ]);

        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function consultingChat_store_successfully_when_user_not_logged_in_and_mobile_not_exists()
    {
        $newUser = User::factory()->has(Doctor::factory())->create();
        $doctor = Doctor::where('user_id',$newUser->id)->first();
        $response = $this->post(route('chat.chatTypes.consultingChat.consultingChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'status' => consultingChat::STATUS_PENDING,
            'doctor_id' => $doctor->id,
            'priority' => 1,
            'private' =>consultingChat::PRIVATE_STATUS_PRIVATE,
            'currency' => 1,
            'name' => 'nick name',
            'mobile' => '+989153514070',
            'national_code' => '1061442081'
        ]);

        $response->assertStatus(423);

    }
    /**
     * @test
     */
    public function consultingChat_store_successfully_when_user_not_logged_in_and_mobile_exists()
    {
        $user = User::factory()->has(patient::factory())->create();
        $user->national_code = '1050194098';
        $user->save();
        $newUser = User::factory()->has(Doctor::factory())->create();
        $doctor = Doctor::where('user_id',$newUser->id)->first();
        $response = $this->post(route('chat.chatTypes.consultingChat.consultingChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'status' => consultingChat::STATUS_PENDING,
            'doctor_id' => $doctor->id,
            'priority' => 1,
            'private' =>consultingChat::PRIVATE_STATUS_PRIVATE,
            'currency' => 1,
            'name' => $user->name,
            'mobile' => $user->cell,
            'national_code' => $user->national_code
        ]);

        $response->assertStatus(423);

    }
    /**
     * @test
     */
    public function consultingChat_store_successfully_when_user_logged_in_without_mobile()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();
        $user->cell = null;
        $user->mobile_confirm = false;
        $user->national_code = '1050194098';
        $user->save();
        Sanctum::actingAs(
            $user
        );
        $newUser = User::factory()->has(Doctor::factory())->create();
        $doctor = Doctor::where('user_id',$newUser->id)->first();
        $response = $this->post(route('chat.chatTypes.consultingChat.consultingChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'status' => consultingChat::STATUS_PENDING,
            'doctor_id' => $doctor->id,
            'priority' => 1,
            'private' =>consultingChat::PRIVATE_STATUS_PRIVATE,
            'currency' => 1,
            'name' => $user->name,
            'mobile' => '+989153514000',
            'national_code' => $user->national_code
        ]);


        $response->assertStatus(423);
    }
    /**
     * @test
     */
    public function consultingChat_store_successfully_when_user_logged_in_without_national_code()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();
        $user->national_code = null;
        $user->save();
        $newUser = User::factory()->has(Doctor::factory())->create();
        $doctor = Doctor::where('user_id',$newUser->id)->first();
        Sanctum::actingAs(
            $user
        );

        $response = $this->post(route('chat.chatTypes.consultingChat.consultingChat.store'), [
            'title' => 'this title is just for test',
            'content' => 'this content is just for test',
            'name' => $user->name,
            'mobile' => $user->cell,
            'status' => consultingChat::STATUS_PENDING,
            'doctor_id' => $doctor->id,
            'priority' => 1,
            'private' =>consultingChat::PRIVATE_STATUS_PRIVATE,
            'currency' => 1,
            'national_code' => '1061442081'
        ]);

        $response->assertStatus(200);

    }

    /**
     * @test
     */
    public function consultingChat_is_not_editable_when_payment_done_or_when_we_have_active_doctor()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $user = User::factory()->has(Patient::factory())->create();
        $consultingChat = consultingChat::firstOrFail();
        $consultingChat->status = ConsultingChat::STATUS_OPEN;
        $consultingChat->save();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->put(route('chat.chatTypes.consultingChat.consultingChat.update', [
            'consultingChat' => $consultingChat->id,
        ]), [
            'title' => $this->faker->text,
            'content' => $this->faker->text,
        ]);
        $response
            ->assertStatus(423);
    }
    /**
     * @test
     */
    public function consultingChat_update_invalid_data()
    {
        /** @var User $user */
        $user = User::factory()->has(Admin::factory())->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class
        ]);
        $consultingChat= consultingChat::firstOrFail();
//        dd($consultingChat->status);
        $response = $this->put(route('chat.chatTypes.consultingChat.consultingChat.update', [
            'consultingChat' => $consultingChat->id,
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
    public function update_consultingChat_invalid_User()
    {
        $user = User::factory()->has(Patient::factory())->create();
        Sanctum::actingAs($user);
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $newUser = User::factory()->has(Patient::factory())->create();
        $consultingChat = consultingChat::firstOrFail();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $newUser->id;
        $chat->save();

        $response = $this->put(route('chat.chatTypes.consultingChat.consultingChat.update', [
            'consultingChat' => $consultingChat->id,
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
    public function consultingChat_admin_update_successfully()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $consultingChat = consultingChat::firstOrFail();
        $user = User::factory()->has(Admin::factory())->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();
        $response = $this->put(route('chat.chatTypes.consultingChat.consultingChat.update', [
            'consultingChat' => $consultingChat->id,
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
    public function consultingChat_user_update_successfully_when_id_pending()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $user = User::factory()->has(Patient::factory())->create();
        $consultingChat = consultingChat::firstOrFail();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->put(route('chat.chatTypes.consultingChat.consultingChat.update', [
            'consultingChat' => $consultingChat->id,
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
    public function consultingChat_is_editable_when_we_are_doctor_free()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $user = User::factory()->has(Patient::factory())->create();
        $consultingChat = consultingChat::firstOrFail();
        $consultingChat->status = ConsultingChat::STATUS_DOCTOR_FREE;
        $consultingChat->save();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->put(route('chat.chatTypes.consultingChat.consultingChat.update', [
            'consultingChat' => $consultingChat->id,
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
    public function consultingChat_is_editable_after_average_or_guarantee_time_answering()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $user = User::factory()->has(Patient::factory())->create();
        $consultingChat = consultingChat::firstOrFail();
        $consultingChat->status = ConsultingChat::STATUS_OPEN;
        $consultingChat->after_specified_time = ConsultingChat::SEND_TO_ANOTHER_DOCTOR;
        $consultingChat->open_time = $consultingChat->open_time->subHours(10);
        $consultingChat->save();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->put(route('chat.chatTypes.consultingChat.consultingChat.update', [
            'consultingChat' => $consultingChat->id,
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
    public function consultingChat_is_editable_after_72hours()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $user = User::factory()->has(Patient::factory())->create();
        $consultingChat = consultingChat::firstOrFail();
        $consultingChat->status = ConsultingChat::STATUS_OPEN;
        $consultingChat->after_specified_time = ConsultingChat::WAITE_FOR_DOCTOR;
        $consultingChat->open_time = $consultingChat->open_time->subDays(4);
        $consultingChat->doctor_last_answer_at = null;
        $consultingChat->save();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->put(route('chat.chatTypes.consultingChat.consultingChat.update', [
            'consultingChat' => $consultingChat->id,
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
    public function delete_consultingChat_successfully()
    {
        $user = User::firstOrFail();
        Sanctum::actingAs($user);
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $consultingChat = consultingChat::firstOrFail();
        $response = $this->delete(route('chat.chatTypes.consultingChat.consulting-chat.destroy', [
            'consulting_chat' => $consultingChat->id,
        ]));
        $response->assertOk();
    }
    /**
     * @test
     */
    public function delete_consultingChat_invalid_data()
    {
        $user = User::factory()->has(Patient::factory())->create();
        Sanctum::actingAs($user);
        $this->seed([
            ChatSeeder::class,
            consultingChatSeeder::class,
        ]);
        $newUser = User::factory()->has(Patient::factory())->create();
        $consultingChat = consultingChat::firstOrFail();
        $consultingChat->chat->users($newUser);

        $response = $this->delete(route('chat.chatTypes.consultingChat.consulting-chat.destroy', [
            'consulting_chat' => $consultingChat->id,
        ]));

        $response
            ->assertStatus(403);

    }

    /**
     * @test
     */
    public function consulting_responder_history_while_payment_not_done()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            ]);
        $user = User::factory()->has(Patient::factory())->create();
        $doctor = User::factory()->has(Doctor::factory())->create();
        $chatUserItems = [
            $user->id => ['role' => UserRoleEnum::Patient],
            $doctor->id => ['role' => UserRoleEnum::Doctor]
        ];
        $chat = Chat::factory()
            ->has(consultingChat::factory())
            ->create();
        $chat->users()->attach($chatUserItems);
        $consultingChat = consultingChat::where('chat_id', $chat->id)->first();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.chatTypes.consultingChat.consultingChat.consultingResponderHistories', [
            'consultingChat' => $consultingChat->id,
        ]), [
            'after_specified_time' => 1,
        ]);
        $response
            ->assertStatus(423);
    }
    /**
     * @test
     */
    public function successful_consulting_responder_history_payment_done()
    {
        /** @var User $user */
        $this->seed([
            ChatSeeder::class,
            ]);
        $user = User::factory()->has(Patient::factory())->create();
        $doctor = User::factory()->has(Doctor::factory())->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $chatUserItems = [
            $user->id => ['role' => UserRoleEnum::Patient],
            $doctor->id => ['role' => UserRoleEnum::Doctor]
        ];
        $chat = Chat::factory()
            ->has(consultingChat::factory())
            ->create();
        $chat->users()->attach($chatUserItems);
        $consultingChat = consultingChat::where('chat_id', $chat->id)->first();
        $consultingChat->status= ConsultingChat::STATUS_OPEN;
        $consultingChat->save();
        $chat = $consultingChat->chat;
        $chat->create_user_id = $user->id;
        $chat->save();

        $response = $this->post(route('chat.chatTypes.consultingChat.consultingChat.consultingResponderHistories', [
            'consultingChat' => $consultingChat->id,
        ]), [
            'after_specified_time' => 1,
        ]);
        $response
            ->assertStatus(200);
    }


}





<?php

namespace Modules\Chat\Tests\Feature\Controllers;

use App\Models\Admin;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportReasonSeeder;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Modules\Chat\Database\Seeders\ChatSeeder;
use Modules\Chat\Database\Seeders\MessageSeeder;
use Modules\Chat\Database\Seeders\SpecialMessageSeeder;
use Modules\Chat\Models\Chat;
use Modules\Chat\Models\Message;
use Modules\Doctor\Models\Doctor;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Traits\TestUnauthenticated;
use Tests\Feature\Traits\TestUnConfirmedMobile;
use Tests\TestCase;

class MessageControllerTest extends TestCase
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
                'chat.message.index',
            ],
            [
                'put',
                'chat.message.update',
                ['message' => 2],
                [ChatSeeder::class,MessageSeeder::class]
            ],
            [
                'post',
                'chat.message.store',
                ['message' => 2],
                [ChatSeeder::class,MessageSeeder::class]
            ],
            [
                'put',
                'chat.message.seenMessage',
                [ChatSeeder::class,MessageSeeder::class]
            ],
            [
                'put',
                'chat.message.deliveredMessage',
                [ChatSeeder::class,MessageSeeder::class]
            ],
            [
                'delete',
                'chat.message.destroy',
                ['message' => 2],
                [ChatSeeder::class,MessageSeeder::class]
            ],

        ];
    }

    public function shouldUserMobileConfirmedRoutes(): array
    {
        return [
            [
                'get',
                'chat.message.index',
            ],
            [
                'post',
                'chat.message.store',
                ['message' => 2],
                [ChatSeeder::class,MessageSeeder::class]
            ],
            [
                'put',
                'chat.message.update',
                ['message' => 2],
                [ChatSeeder::class,MessageSeeder::class]
            ],
            [
                'put',
                'chat.message.seenMessage',
                [ChatSeeder::class,MessageSeeder::class]
            ],
            [
                'put',
                'chat.message.deliveredMessage',
                [ChatSeeder::class,MessageSeeder::class]
            ],
            [
                'delete',
                'chat.message.destroy',
                ['message' => 1],
                [ChatSeeder::class,MessageSeeder::class]
            ],

        ];
    }
    /**
     * @test
     */
    public function message_list_successfully()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->create_user_id = $user->id;
        $chat->save();
        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->get(route('chat.message.index',['chat_id' => $chat->id]));

        $response
            ->assertOk();
    }
    /**
     * @test
     */
    public function message_list_successfully_unauthorized()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->get(route('chat.message.index',['chat_id' => $chat->id]));

        $response
            ->assertStatus(response::HTTP_FORBIDDEN);
    }
    /**
     * @test
     */
    public function message_send_invalid_data()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->post(route('chat.message.store'), [
            'text',
            'chat_id',
            'special_message_id',
        ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'text',
                'chat_id',
                'special_message_id',
            ]);
    }
    /**
     * @test
     */
    public function message_send_unsuccessfully_when_user_is_not_a_users_chat()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach($user->id);
        $newUser = User::factory()->create();
        Sanctum::actingAs(
            $newUser,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'text' => 'this is a test message',
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_FORBIDDEN);

    }
    /**
     * @test
     */
    public function message_send_to_consultingChat_successfully()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();
        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'text' => 'this is a test message',
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);

    }
    /**
     * @test
     */
    public function special_message_sendInfo_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Doctor::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 1,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_phoneCallNeeded_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Doctor::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 2,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_blockUser_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Doctor::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 3,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_consultingEndedAndArchived_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Admin::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 4,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_consultingArchived_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Admin::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 5,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_consultingEnded_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Admin::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 6,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_rateToDoctor_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Patient::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 7,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_LOCKED);
    }
    /**
     * @test
     */
    public function special_message_thanksToDoctor_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Patient::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 8,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_refundRequest_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Patient::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 9,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_askInConsultingChat_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Admin::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 10,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_notInMyInterests_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Admin::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 11,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function special_message_notMySpecialty_sent_successfully()
    {
        /** @var User $user */

        $this->seed([
            SpecialMessageSeeder::class
        ]);

        $user = User::factory()->has(Admin::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();

        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'special_message_id' => 12,
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);
    }

    /**
     * @test
     */
    public function message_send_successfully_with_media()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();
        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->post(route('chat.message.store'), [
            'text' => 'this is a fucking test message',
            'chat_id' => $chat->id,
            'pictures' => [UploadedFile::fake()->image('test_image.jpg'),]
        ]);
        $response->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function message_send_to_SupportChat_successfully()
    {
        /** @var User $user */
        $user = User::factory()->has(Patient::factory())->create();
        $this->seed([SupportReasonSeeder::class]);
        $chat = Chat::factory()->has(SupportChat::factory())->create();
        $chat->users()->attach($user->id);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->post(route('chat.message.store'), [
            'text' => 'this is a test message',
            'chat_id' => $chat->id,
        ]);

        $response->assertStatus(response::HTTP_CREATED);

    }
    /**
     * @test
     */
    public function message_update_invalid_data()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $message = Message::factory()->create();
        $message->user_id = $user->id;
        $message->save();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->put(route('chat.message.update',['message' => $message->id]), [
            'text',
        ]);

        $response->assertStatus(response::HTTP_UNPROCESSABLE_ENTITY);
    }
    /**
     * @test
     */
    public function unsuccessful_update_message_unauthorized()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $newUser = User::factory()->create();

        $chat->users()->attach($user->id);


        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs(
            $newUser,
            ['*']
        );
        $response = $this->put(route('chat.message.update',['message' => $message->id]), [
            'text' => 'this is a test message',
        ]);

        $response->assertStatus(response::HTTP_FORBIDDEN);

    }
     /**
     * @test
     */
    public function message_update_successfully()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $chat = Chat::factory()->has(ConsultingChat::factory())->create();
        $chat->users()->attach($user->id);


        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->put(route('chat.message.update',['message' => $message->id]), [
            'text' => 'this is a test message',
        ]);

        $response
            ->assertStatus(response::HTTP_CREATED);
    }
    /**
     * @test
     */
    public function delete_message_successfully()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();

        $chat->users()->attach($user->id);

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        $response = $this->delete(route('chat.message.destroy',['message' => $message->id]));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function delete_message_unsuccessfully()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $newUser = User::factory()->create();

        $chat->users()->attach($user->id);

        Sanctum::actingAs(
            $newUser,
            ['*']
        );

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        $response = $this->delete(route('chat.message.destroy',['message' => $message->id]));

        $response->assertStatus(response::HTTP_FORBIDDEN);
    }
    /**
     * @test
     */
    public function delete_message_unsuccessfully_cause_deleted()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();

        $chat->users()->attach($user->id);

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'is_delete' => 1
        ]);
        $response = $this->delete(route('chat.message.destroy',['message' => $message->id]));

        $response->assertStatus(response::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * @test
     */
    public function delete_message_unsuccessfully_cause_seen()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $newUser = User::factory()->create();

        $chat->users()->attach($user->id);
        $chat->users()->attach($newUser->id);

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);
        $seen = $message->seen;
        $seen[] = $newUser->id;
        Message::where('id', $message->id)->update(['seen' => $seen]);
        $updateMessage = Message::where('id', $message->id)->first();
        $response = $this->delete(route('chat.message.destroy',['message' => $updateMessage->id]));

        $response->assertStatus(response::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * @test
     */
    public function seen_message_by_a_user_successfully()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $newUser = User::factory()->create();

        $chat->users()->attach($user->id);

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $newUser->id,
        ]);
        $response = $this->put(route('chat.message.seenMessage'),[
            'messageIds' => [$message->id],
        ]);

        $response->assertOk();

    }
    /**
     * @test
     */
    public function seen_message_by_a_user_unsuccessfully()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $newUser = User::factory()->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $newUser->id,
        ]);

        Message::where('id', $message->id)->update(['is_delete' => 1]);
        $updateMessage = Message::where('id', $message->id)->first();

        $response = $this->put(route('chat.message.seenMessage'),[
            'messageIds' => [$updateMessage->id],
        ]);

        $response->assertStatus(response::HTTP_INTERNAL_SERVER_ERROR);

    }
    /**
     * @test
     */
    public function delivered_message_for_a_user_successfully()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $newUser = User::factory()->create();


        $chat->users()->attach($user->id);

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $newUser->id,
        ]);
        $response = $this->put(route('chat.message.deliveredMessage'),[
            'messageIds' => [$message->id],
        ]);

        $response->assertOk();

    }
    /**
     * @test
     */
    public function delivered_message_for_a_user_unsuccessfully()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $newUser = User::factory()->create();


        $chat->users()->attach($user->id);

        Sanctum::actingAs(
            $newUser,
            ['*']
        );

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'user_id' => $newUser->id,
        ]);
        $delivered = $message->delivered;
        $delivered[] = $newUser->id;
        Message::where('id', $message->id)->update(['delivered' => $delivered]);
        $updateMessage = Message::where('id', $message->id)->first();
        $response = $this->put(route('chat.message.deliveredMessage'),[
            'messageIds' => [$updateMessage->id],
        ]);

        $response->assertStatus(response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


<?php

namespace Modules\Chat\Tests\Feature\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportReasonSeeder;
use Modules\Chat\Database\Seeders\ChatSeeder;
use Modules\Chat\Database\Seeders\MessageSeeder;
use Modules\Chat\Models\Chat;
use Modules\Chat\Models\Message;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatSeeder;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportSeeder;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;
    private Chat|null|Model $firstData;
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ChatSeeder::class,
            MessageSeeder::class,
            ConsultingChatSeeder::class,
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);

        $this->firstData = Chat::firstOrFail();
    }
    /**
     * @test
     */
    public function chats_database_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasColumns('chats', [
                'id','title','content','create_user_id','last_respond_id','admin_read','last_answer_time','created_at','updated_at'
            ]));
    }
    /**
     * @test
     */
    public function relation_with_consultingChat()
    {
        self::assertTrue($this->firstData->consultingChat() instanceof HasOne);
    }
    /**
     * @test
     */
    public function relation_with_supportChat()
    {
        self::assertTrue($this->firstData->supportChat() instanceof HasOne);
    }
    /**
     * @test
     */
    public function relation_with_messages()
    {
        self::assertTrue($this->firstData->messages() instanceof HasMany);
    }
    /**
     * @test
     */
    public function relation_with_users()
    {
        self::assertTrue($this->firstData->users() instanceof BelongsToMany);
    }
    /**
     * @test
     */
    public function user_relation_with_chats()
    {
        $this->NewUser = User::firstOrFail();
        self::assertTrue($this->NewUser->chats() instanceof BelongsToMany);
    }
    /**
     * @test
     */
    public function get_data()
    {
        $getData = Chat::take(1);
        self::assertCount(1, $getData->get()->toArray());
    }
    /**
     * @test
     */
    public function store_data()
    {
        self::assertTrue(
            (new Chat([
                'title' => 'test title',
                'content' => 'fully description about patient problems or description for support chats',
                'create_user_id' => User::first()->id,
                'last_respond_id'=> User::first()->id,
            ]))->save()
        );
    }
    /**
     * @test
     */
    public function update_chat()
    {
        self::assertTrue($this->firstData->update());
    }
    /**
     * @test
     */
    public function delete_chat()
    {
        self::assertTrue($this->firstData->forceDelete());
    }
    /**
     * @test
     */
    public function chat_HasMany_message()
    {
        $message = Message::firstOrFail();

        $this->firstData->messages()->save($message);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $this->firstData->id,
        ]);
    }
    /**
     * @test
     */
    public function user_BelongsToMany_chat()
    {
        $user = User::firstorfail();
        $user->chats()->sync($this->firstData);
        $this->assertDatabaseHas('chat_user', [
            'chat_id' => $this->firstData->id,
            'user_id' => $user->id,
        ]);
    }
    /**
     * @test
     */
    public function chat_BelongsToMany_user()
    {
        $user = User::firstorfail();

        $this->firstData->users()->sync($user);

        $this->assertDatabaseHas('chat_user', [
            'chat_id' => $this->firstData->id,
            'user_id' => $user->id
        ]);
    }
    /**
     * @test
     */
    public function chat_HasOne_consultingChat()
    {
        $consultingChat = ConsultingChat::firstOrFail();

        $this->firstData->consultingChat()->save($consultingChat);

        $this->assertDatabaseHas('consulting_chats', [
            'chat_id' => $this->firstData->id
        ]);
    }
    /**
     * @test
     */
    public function chat_HasOne_supportChat()
    {
        $supportChat = SupportChat::firstOrFail();

        $this->firstData->supportChat()->save($supportChat);

        $this->assertDatabaseHas('support_chats', [
            'chat_id' => $this->firstData->id
        ]);
    }
    /**
     * @test
     */
    public function chat_HasMany_messages()
    {
        $message = Message::firstOrFail();

        $this->firstData->messages()->save($message);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $this->firstData->id
        ]);
    }
}

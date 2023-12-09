<?php

namespace Modules\Chat\ChatTypes\SupportChat\Tests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportReasonSeeder;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportSeeder;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportReason;
use Modules\Chat\Database\Seeders\ChatSeeder;
use Modules\Chat\Models\Chat;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    private SupportChat|null|Model $firstData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SupportReasonSeeder::class,
            ChatSeeder::class,
            SupportSeeder::class,
        ]);

        $this->firstData = SupportChat::firstOrFail();
    }
    /**
     * @test
     */
    public function supportChats_database_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasColumns('support_chats', [
                'id','status','chat_id','support_reason_id','is_login','is_admin_pin', 'created_at','updated_at'
            ]));
    }
    /**
     * @test
     */
    public function relation_with_chat()
    {
        self::assertTrue($this->firstData->chat() instanceof BelongsTo);
    }
    /**
     * @test
     */
    public function relation_with_SupportReason()
    {
        self::assertTrue($this->firstData->supportReason() instanceof BelongsTo);
    }
    /**
     * @test
     */
    public function get_data()
    {
        $getData = SupportChat::take(1);
        self::assertCount(1, $getData->get()->toArray());
    }
    /**
     * @test
     */
    public function store_data()
    {
        self::assertTrue(
            (new SupportChat([
                'chat_id' => Chat::factory()->create()->id,
                'status' => SupportChat::STATUS_USER_RESPOND,
                'support_reason_id' => rand(1,5),
            ]))->save()
        );
    }

    /**
     * @test
     */
    public function update_supportChat()
    {
        self::assertTrue($this->firstData->update([
            'status' => SupportChat::STATUS_USER_RESPOND,
        ]));
    }
    /**
     * @test
     */
    public function delete_supportChat()
    {
        self::assertTrue($this->firstData->forceDelete());
    }
    /**
     * @test
     */
    public function supportChat_BelongsTo_chat()
    {
        $chat = Chat::firstOrFail();
        $this->firstData->chat()->associate($chat)->save();
        $this->assertDatabaseHas('support_chats', [
            'chat_id' => $chat->id
        ]);
    }
    /**
     * @test
     */
    public function supportChat_BelongsTo_supportReason()
    {
        $SupportReason = SupportReason::firstOrFail();
        $this->firstData->supportReason()->associate($SupportReason)->save();
        $this->assertDatabaseHas('support_chats', [
            'support_reason_id' => $SupportReason->id
        ]);
    }
}

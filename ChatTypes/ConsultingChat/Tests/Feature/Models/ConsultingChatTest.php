<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Tests\Feature\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatSeeder;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingResponderHistorySeeder;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingResponderHistory;
use Modules\Chat\Database\Seeders\ChatSeeder;
use Modules\Chat\Models\Chat;
use Modules\Contract\Database\Seeders\HospitalSeeder;
use Modules\Contract\Models\Hospital;
use Tests\TestCase;

class ConsultingChatTest extends TestCase
{
    private ConsultingChat|null|Model $firstData;
    use RefreshDatabase;

    protected function setUp(): void
    {

        parent::setUp();

        $this->seed([
            ChatSeeder::class,
            ConsultingChatSeeder::class,
            ConsultingResponderHistorySeeder::class,
            HospitalSeeder::class
        ]);

        $this->firstData = ConsultingChat::firstOrFail();
        $this->hospital = Hospital::firstOrFail();
    }
    /**
     * @test
     */
    public function consultingChats_database_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasColumns('consulting_chats', [
                'id','unique_id','status','open_time', 'related_patient_id', 'private','priority','visit_number','doctor_last_answer_at','is_payment_notification_sent','view_counter','after_specified_time','is_auto_close','placeable_id', 'placeable_type', 'created_at','updated_at'
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
    public function relation_with_invoice()
    {
        self::assertTrue($this->firstData->invoice() instanceof MorphOne);
    }

    /**
     * @test
     */
    public function relation_with_ConsultingResponderHistory()
    {
        self::assertTrue($this->firstData->consultingResponderHistories() instanceof HasMany);
    }
    /**
     * @test
     */
    public function get_data()
    {
        $getData = ConsultingChat::take(1);
        self::assertCount(1, $getData->get()->toArray());
    }
    /**
     * @test
     */
    public function relation_with_place()
    {
        self::assertTrue($this->firstData->place() instanceof MorphOne);
    }
    /**
     * @test
     */
    public function store_data()
    {
        self::assertTrue(
            (new ConsultingChat([
                'unique_id' => $this->faker->unique()->numerify(),
                'chat_id' => Chat::factory()->create()->id,
                'status' => ConsultingChat::STATUS_PENDING,
                'open_time' => Carbon::yesterday(),
                'private' => ConsultingChat::PRIVATE_STATUS_PRIVATE,
                'priority' => '0',
                'visit_number' => rand(100000,999999),
                'doctor_last_answer_at' => Carbon::now(),
            ]))->save()
        );
    }
    /**
     * @test
     */
    public function update_consultingChat()
    {
        self::assertTrue($this->firstData->update([
            'visit_number' => rand(100000,999999),
            'doctor_last_answer_at' => Carbon::now(),
        ]));
    }
    /**
     * @test
     */
    public function delete_consultingChat()
    {
        self::assertTrue($this->firstData->forceDelete());
    }
    /**
     * @test
     */
    public function consultingChat_BelongsTo_chat()
    {
        $chat = Chat::firstOrFail();
        $this->firstData->chat()->associate($chat)->save();
        $this->assertDatabaseHas('consulting_chats', [
            'chat_id' => $chat->id
        ]);
    }
    /**
     * @test
     */
    public function consultingChat_hasOne_chat()
    {

        $chat = Chat::firstOrFail();
        $this->firstData->chat()->associate($chat)->save();
        $this->assertDatabaseHas('consulting_chats', [
            'chat_id' => $chat->id
        ]);
    }
    /**
     * @test
     */
    public function consultingChat_HasMany_ConsultingResponderHistory()
    {
        $consultingResponderHistory = ConsultingResponderHistory::firstOrFail();
        $this->firstData->consultingResponderHistories()->save($consultingResponderHistory);

        $this->assertDatabaseHas('consulting_responder_histories', [
            'consulting_chat_id' => $this->firstData->id,
        ]);
    }
    /**
     * @test
     */
    public function hospital_MorphTo_placeable()
    {
        $this->assertDatabaseHas('consulting_chats', [
            'placeable_id' => $this->hospital->id
        ]);
    }
}

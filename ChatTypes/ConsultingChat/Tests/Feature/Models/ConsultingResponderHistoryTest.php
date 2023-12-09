<?php

namespace Modules\Chat\ChatTypes\ConsultingChat\Tests\Feature\Models;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingChatSeeder;
use Modules\Chat\ChatTypes\ConsultingChat\Database\Seeders\ConsultingResponderHistorySeeder;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingResponderHistory;
use Modules\Doctor\Models\Doctor;
use Tests\TestCase;

class ConsultingResponderHistoryTest extends TestCase
{
    private ConsultingResponderHistory|null|Model $firstData;
    use RefreshDatabase;

    protected function setUp(): void
    {

        parent::setUp();

        $this->seed([
            ConsultingChatSeeder::class,
            ConsultingResponderHistorySeeder::class,
        ]);

        $this->firstData = ConsultingResponderHistory::firstOrFail();
    }
    /**
     * @test
     */
    public function consultingResponderHistories_database_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasColumns('consulting_responder_histories', [
                'id','consulting_chat_id','old_doctor_id','status','past_status','created_at','updated_at'
            ]));
    }
    /**
     * @test
     */
    public function relation_with_doctor()
    {
        self::assertTrue($this->firstData->doctor() instanceof BelongsTo);
    }
    /**
     * @test
     */
    public function relation_with_consultingChat()
    {
        self::assertTrue($this->firstData->consultingChat() instanceof BelongsTo);
    }
    /**
     * @test
     */
    public function doctor_relation_with_consultingResponderHistory()
    {
        $this->NewUser = doctor::firstOrFail();
        self::assertTrue($this->NewUser->consultingResponderHistories() instanceof hasMany);
    }
    /**
     * @test
     */
    public function consultingResponderHistory_BelongsTo_doctor()
    {
        $doctor = doctor::firstOrFail();
        $this->firstData->doctor()->associate($doctor)->save();
        $this->assertDatabaseHas('consulting_responder_histories', [
            'old_doctor_id' => $doctor->id
        ]);
    }
    /**
     * @test
     */
    public function consultingResponderHistory_BelongsTo_consultingChat()
    {
        $consultingChat = ConsultingChat::firstOrFail();
        $this->firstData->consultingChat()->associate($consultingChat)->saveOrFail();
        $this->assertDatabaseHas('consulting_responder_histories', [
            'consulting_chat_id' => $consultingChat->id
        ]);
    }
    /**
     * @test
     */
    public function user_HasMany_consultingResponderHistory()
    {
        $doctor = doctor::firstOrFail();

        $doctor->consultingResponderHistories()->save($this->firstData);

        $this->assertDatabaseHas('consulting_responder_histories', [
            'old_doctor_id' => $doctor->id,
        ]);
    }
}

<?php

namespace Modules\Chat\ChatTypes\SupportChat\Tests\Feature\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportReasonSeeder;
use Modules\Chat\ChatTypes\SupportChat\Database\Seeders\SupportSeeder;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportChat;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportReason;
use Tests\TestCase;

class SupportReasonTest extends TestCase
{
    use RefreshDatabase;
    private SupportReason|null|Model $firstData;
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SupportReasonSeeder::class,
            SupportSeeder::class,
        ]);

        $this->firstData = SupportReason::firstOrFail();
    }
    /**
     * @test
     */
    public function chats_database_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasColumns('chats', [
                'id','title','created_at','updated_at'
            ]));
    }
    /**
     * @test
     */
    public function get_data()
    {
        $getData = SupportReason::take(1);
        self::assertCount(1, $getData->get()->toArray());
    }
    /**
     * @test
     */
    public function store_data()
    {
        self::assertTrue(
            (new SupportReason([
                'title'=> 'عودت وجه'
            ]))->save()
        );
    }

    /**
     * @test
     */
    public function update_supportReason()
    {
        self::assertTrue($this->firstData->update([
            'title' => 'ارسال پیشنهاد',
        ]));
    }
    /**
     * @test
     */
    public function delete_supportreason()
    {
        self::assertTrue($this->firstData->forceDelete());
    }
    /**
     * @test
     */
    public function relation_with_SupportChat()
    {
        self::assertTrue($this->firstData->supportChats() instanceof HasMany);
    }
    /**
     * @test
     */
    public function SupportReason_HasMany_SupportChat()
    {
        $supportChat = SupportChat::firstOrFail();

        $this->firstData->supportChats()->save($supportChat);

        $this->assertDatabaseHas('support_chats', [
            'support_reason_id' => $this->firstData->id,
        ]);
    }
}

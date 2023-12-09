<?php

namespace Modules\Chat\Tests\Feature\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\Database\Seeders\ChatSeeder;
use Modules\Chat\Database\Seeders\MessageSeeder;
use Modules\Chat\Database\Seeders\SpecialMessageSeeder;
use Modules\Chat\Models\SpecialMessage;
use Modules\Chat\Models\Message;
use Tests\TestCase;

class SpecialMessageTest extends TestCase
{
    private SpecialMessage|null|Model $firstData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ChatSeeder::class,
            MessageSeeder::class,
            SpecialMessageSeeder::class,

        ]);

        $this->firstData = SpecialMessage::firstOrFail();
    }
    /**
     * @test
     */
    public function specialMessages_database_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasColumns('special_messages', [
                'id','name','content', 'controller_method','status', 'created_at','updated_at'
            ]));
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
    public function get_data()
    {
        $getData = SpecialMessage::take(1);
        self::assertCount(1, $getData->get()->toArray());
    }
    /**
     * @test
     */
    public function store_data()
    {
        self::assertTrue(
            (new SpecialMessage([
                'name' => $this->faker->name,
                'content' => $this->faker->text(50),
                'controller_method' => ''
            ]))->save()
        );
    }
    /**
     * @test
     */
    public function update_specialMessage()
    {
        self::assertTrue($this->firstData->update([
            'content' => $this->faker->text(50),
            'controller_method' => ''
        ]));
    }
    /**
     * @test
     */
    public function delete_specialMessage()
    {
        self::assertTrue($this->firstData->forceDelete());
    }
    /**
     * @test
     */
    public function specialMessage_HasMany_message()
    {
        $this->seed([
           MessageSeeder::class,
        ]);

        $message = Message::firstorfail();
        $this->firstData->messages()->save($message);
        $this->assertDatabaseHas('messages', [
            'special_message_id' => $this->firstData->id
        ]);
    }
}

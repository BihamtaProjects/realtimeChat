<?php

namespace Modules\Chat\Tests\Feature\Models;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Schema;
use Modules\Chat\Database\Seeders\ChatSeeder;
use Modules\Chat\Database\Seeders\MessageSeeder;
use Modules\Chat\Database\Seeders\SpecialMessageSeeder;
use Modules\Chat\Models\Chat;
use Modules\Chat\Models\Message;
use Modules\Chat\Models\SpecialMessage;
use Modules\Location\Database\factories\LocationFactory;
use Modules\Location\Models\Location;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessageTest extends TestCase
{
    use RefreshDatabase;
    private Message|null|Model $firstData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ChatSeeder::class,
            MessageSeeder::class,
        ]);

        $this->firstData = Message::firstOrFail();
    }
    /**
     * @test
     */
    public function messages_database_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasColumns('messages', [
                'id','chat_id', 'user_id', 'special_message_id', 'text','respond_to','is_edit','seen','delivered','is_delete','created_at','updated_at'
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
    public function relation_with_user()
    {
        self::assertTrue($this->firstData->user() instanceof BelongsTo);
    }
    /**
     * @test
     */
    public function relation_with_specialMessage()
    {
        self::assertTrue($this->firstData->specialMessage() instanceof BelongsTo);
    }
    /**
     * @test
     */
    public function user_relation_with_messages()
    {
        $this->NewUser = User::firstOrFail();
        self::assertTrue($this->NewUser->messages() instanceof HasMany);
    }
    /**
     * @test
     */
    public function relation_with_location()
    {
        self::assertTrue($this->firstData->location() instanceof MorphOne);
    }
    /**
     * @test
     */
    public function relation_with_media()
    {
        self::assertTrue($this->firstData->media() instanceof MorphMany);
    }
    /**
     * @test
     */
    public function get_data()
    {
        $getData = Message::take(1);
        self::assertCount(1, $getData->get()->toArray());
    }

    /**
     * @test
     */
    public function store_data()
    {
        self::assertTrue(
            (new Message([
                'chat_id' => Chat::inRandomOrder()->first()->id,
                'user_id' => User::inRandomOrder()->first()->id,
                'text' => $this->faker->text(50),
                'is_delete' => '0',
            ]))->save()
        );
    }

    /**
     * @test
     */
    public function update_message()
    {
        self::assertTrue($this->firstData->update([
            'text' => $this->faker->text(50),
        ]));
    }
    /**
     * @test
     */
    public function delete_message()
    {
        self::assertTrue($this->firstData->forceDelete());
    }
    /**
     * @test
     */
    public function user_HasMany_message()
    {
        $user = User::firstorfail();
        $user->messages()->save($this->firstData);
        $this->assertDatabaseHas('messages', [
            'user_id' => $user->id
        ]);
    }
    /**
     * @test
     */
    public function message_BelongsTo_specialMessage()
    {
        $this->seed([
            SpecialMessageSeeder::class,
        ]);

        $specialMessage = SpecialMessage::firstOrFail();

        $this->firstData->specialMessage()->associate($specialMessage)->save();

        $this->assertDatabaseHas('messages', [
            'special_message_id' => $specialMessage->id
        ]);
    }
    /**
     * @test
     */
    public function message_BelongsTo_user()
    {

        $user = User::firstOrFail();

        $this->firstData->user()->associate($user)->save();
        $this->assertDatabaseHas('messages', [
            'user_id' => $user->id
        ]);
    }
    /**
     * @test
     */
    public function message_BelongsTo_chat()
    {
        $this->seed([
            ChatSeeder::class,
        ]);

        $chat = Chat::firstOrFail();

        $this->firstData->chat()->associate($chat)->save();

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id
        ]);
    }
    /**
     * @test
     */
    public function message_MorphOne_location()
    {
       $location = new Location;
       $location->longitude= $this->faker->longitude;
       $location->latitude= $this->faker->latitude;
       $location->marker_longitude = $this->faker->longitude;
       $location->marker_latitude = $this->faker->latitude;
       $location->zoom= rand(1, 9);

       $this->firstData->location()->save($location);
       $this->assertDatabaseHas('locations', [
            'locationable_id' => $this->firstData->id,
            'locationable_type' => 'Modules\Chat\Models\Message',
        ]);
    }

}

<?php

namespace Modules\Chat\Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Modules\Chat\Events\MessageSent;
use Modules\Chat\Models\Chat;
use Modules\Chat\Models\Message;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BroadCastingTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function testBroadcasting()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $message = Message::factory()->create();

        $event = new MessageSent($user, $message, $chat->id);

        // Use EventFake to mock event dispatching
        Event::fake();
        event($event);

        Event::assertDispatched(MessageSent::class, function ($e) use ($message) {
            return $e->message === $message;
        });
    }
}


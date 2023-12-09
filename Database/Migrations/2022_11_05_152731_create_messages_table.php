<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Chat\Models\Chat;
use Modules\Chat\Models\SpecialMessage;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('chat_id');
            $table->foreign('chat_id')->references('id')->on(CHAT::TABLE)->cascadeOnDelete();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on(User::TABLE)->cascadeOnDelete();

            $table->unsignedInteger('special_message_id')->nullable();
            $table->foreign('special_message_id')->references('id')->on(SpecialMessage::TABLE)->nullOnDelete();

            $table->text('text')->nullable()->default(null);
            $table->unsignedInteger('respond_to')->nullable();

            $table->string('delivered')->default("[]");
            $table->boolean('is_edit')->default(0);
            $table->string('seen')->default("[]");
            $table->boolean('is_delete')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
};

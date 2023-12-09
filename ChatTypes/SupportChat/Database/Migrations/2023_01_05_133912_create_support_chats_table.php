<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Chat\ChatTypes\SupportChat\Models\SupportReason;
use Modules\Chat\Models\Chat;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_chats', function (Blueprint $table) {
            $table->integerIncrements('id');

            $table->integer('status')->default(1);
            $table->boolean('is_login')->default(0);
            $table->boolean('is_admin_pin')->default(0);

            $table->unsignedInteger('chat_id');
            $table->foreign('chat_id')->references('id')->on(Chat::TABLE)->cascadeOnDelete();
            $table->unsignedInteger('support_reason_id');
            $table->foreign('support_reason_id')->references('id')->on(SupportReason::TABLE)->cascadeOnDelete();
            $table->unsignedInteger('related_question_id')->default(0);

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
        Schema::dropIfExists('support_chats');
    }
};

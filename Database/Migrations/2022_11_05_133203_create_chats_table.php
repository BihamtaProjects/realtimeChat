<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
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
        Schema::create(Chat::TABLE, function (Blueprint $table) {
            $table->integerIncrements('id');

            $table->string('title');
            $table->text('content');

            $table->unsignedInteger('create_user_id');
            $table->foreign('create_user_id')->references('id')->on(User::TABLE)->cascadeOnDelete();
            $table->unsignedInteger('last_respond_id');
            $table->foreign('last_respond_id')->references('id')->on(User::TABLE)->cascadeOnDelete();
            $table->boolean('admin_read')->default(0);
            $table->timestamp('last_answer_time')->nullable()->default(null);

            $table->timestamps();
        });

        Schema::create('chat_user', function (Blueprint $table) {
            $table->integerIncrements('id');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on(User::TABLE)->cascadeOnDelete();

            $table->unsignedInteger('chat_id');
            $table->foreign('chat_id')->references('id')->on(Chat::TABLE)->cascadeOnDelete();

            $table->string('role')->default('patient');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_user');
        Schema::dropIfExists(Chat::TABLE);
    }
};

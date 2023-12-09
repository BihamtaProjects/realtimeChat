<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Chat\ChatTypes\ConsultingChat\Models\ConsultingChat;
use Modules\Doctor\Models\Doctor;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consulting_responder_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('consulting_chat_id');
            $table->foreign('consulting_chat_id')->references('id')->on(ConsultingChat::TABLE)->cascadeOnDelete();

            $table->unsignedInteger('old_doctor_id');
            $table->foreign('old_doctor_id')->references('id')->on(Doctor::TABLE)->cascadeOnDelete();

//            $table->unsignedInteger('new_responder_id')->nullable();
//            $table->foreign('new_responder_id')->references('id')->on(User::TABLE)->cascadeOnDelete();

            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedTinyInteger('past_status')->default(1);

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
        Schema::dropIfExists('consulting_responder_histories');
    }
};

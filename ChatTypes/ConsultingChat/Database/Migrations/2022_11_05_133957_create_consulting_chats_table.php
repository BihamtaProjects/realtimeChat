<?php

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
        Schema::create('consulting_chats', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->string('unique_id')->unique();

            $table->boolean('status')->default(0)->unsigned();//double check

            $table->unsignedInteger('chat_id');
            $table->foreign('chat_id')->references('id')->on(CHAT::TABLE)->cascadeOnDelete();

            $table->unsignedInteger('related_patient_id')->default(0);
            $table->timestamp('open_time')->nullable()->default(null);
            $table->unsignedInteger('private')->default(0);
            $table->boolean('priority')->default(0);
            $table->integer('visit_number');
            $table->timestamp('doctor_first_answer_at')->nullable()->default(null);
            $table->timestamp('doctor_last_answer_at')->nullable()->default(null);
            $table->boolean('is_payment_notification_sent')->default(0);
            $table->integer('view_counter')->default(0);
            $table->smallInteger('after_specified_time')->default(1);
            $table->boolean('is_auto_close')->default(1);
            $table->nullableMorphs('placeable');
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
        Schema::dropIfExists('consulting_chats');
    }
};

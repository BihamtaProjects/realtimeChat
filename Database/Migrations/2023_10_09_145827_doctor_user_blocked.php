<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_user_blocked', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('doctor_base_user_id');
            $table->foreign('doctor_base_user_id')->references('id')->on(User::TABLE)->cascadeOnDelete();

            $table->unsignedInteger('user_base_user_id');
            $table->foreign('user_base_user_id')->references('id')->on(User::TABLE)->cascadeOnDelete();

            $table->smallInteger('block_type')->nullable();

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
        Schema::dropIfExists('doctor_user_blocked');
    }
};

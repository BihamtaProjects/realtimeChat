<?php

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
        Schema::create('special_messages', function (Blueprint $table) {
            $table->integerIncrements('id');

            $table->string('name');
            $table->string('content');
            $table->string('controller_method')->nullable()->default('null');
            $table->string('status')->default("[]");
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
        Schema::dropIfExists('special_messages');
    }
};

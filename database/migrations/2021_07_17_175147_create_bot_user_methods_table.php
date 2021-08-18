<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotUserMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_user_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bot_user_id');
            $table->string('name', 255);
            $table->smallInteger('type');
            $table->text('signal');
            $table->text('order_pattern');
            $table->integer('step')->nullable();
            $table->float('profit', 13)->nullable();
            $table->float('stop_loss', 13)->nullable();
            $table->float('take_profit', 13)->nullable();
            $table->smallInteger('status');
            $table->string('color', 6);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_user_methods');
    }
}

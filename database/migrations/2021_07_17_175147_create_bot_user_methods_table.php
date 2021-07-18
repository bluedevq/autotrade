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
            $table->integer('bot_method_default_id');
            $table->string('name');
            $table->smallInteger('type');
            $table->text('signal');
            $table->text('order_pattern');
            $table->float('stop_loss', 13)->nullable();
            $table->float('stop_win', 13)->nullable();
            $table->smallInteger('status');
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

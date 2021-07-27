<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotMethodDefaultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_method_defaults', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->smallInteger('type');
            $table->text('signal');
            $table->text('order_pattern');
            $table->float('stop_loss', 13)->nullable();
            $table->float('stop_win', 13)->nullable();
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
        Schema::dropIfExists('bot_method_defaults');
    }
}

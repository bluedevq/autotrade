<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 256);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('reference_name')->nullable();
            $table->smallInteger('rank')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->float('demo_balance', 13)->nullable();
            $table->float('available_balance', 13)->nullable();
            $table->float('usdt_available_balance', 13)->nullable();
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
        Schema::dropIfExists('bot_users');
    }
}

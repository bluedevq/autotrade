<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 256);
            $table->string('password', 64);
            $table->string('name');
            $table->string('phone', 14)->nullable();
            $table->smallInteger('sex')->nullable();
            $table->text('address')->nullable();
            $table->dateTime('expired_date');
            $table->text('forgot_password_token');
            $table->dateTime('forgot_password_expired');
            $table->text('verify_token');
            $table->dateTime('verify_expired');
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
        Schema::dropIfExists('users');
    }
}

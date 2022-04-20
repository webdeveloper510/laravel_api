<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthentication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Auth_table', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('username');
            $table->string('password');
            $table->string('grant_type');
            $table->string('type');
            $table->string('token');
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
        Schema::dropIfExists('Auth_table');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoreFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipment', function (Blueprint $table) {
            //
            $table->string('surname')->default('');
            $table->string('billingAddress')->default('');
            $table->string('telephone')->default('');
            $table->string('bussinessName')->default('');
            $table->string('tradeName')->default('');
            $table->string('ruc')->default('');
            $table->string('turn')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipment', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipment_api', function (Blueprint $table) {
     $table->id();
    $table->string('reference_id');
    $table->string('delivery_time');
    $table->string('items');
    $table->string('waypoint');
    $table->string('user_id');

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
        Schema::table('shipment_api', function (Blueprint $table) {
            //
        });
    }
}

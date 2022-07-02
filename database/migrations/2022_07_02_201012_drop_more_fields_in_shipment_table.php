<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMoreFieldsInShipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipment', function (Blueprint $table) {
             $table->dropColumn('surname');
            $table->dropColumn('billingAddress');
            $table->dropColumn('telephone');
            $table->dropColumn('bussinessName');
            $table->dropColumn('tradeName');
            $table->dropColumn('ruc');
            $table->dropColumn('turn');
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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSrcDestinationAddressToRideHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ride_histories', function ($table) {
            $table->string("pick_point_address")->nullable();
            $table->string("destination_address")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ride_histories', function ($table) {
            $table->dropColumn("pick_point_address");
            $table->dropColumn("destination_address");
        });
    }
}

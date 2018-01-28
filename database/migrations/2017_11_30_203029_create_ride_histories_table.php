<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRideHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ride_histories', function (Blueprint $table) {
            $table->increments('id');
	        $table->integer('client_id');
	        $table->integer('rider_id');
	        $table->datetime('start_time');
	        $table->datetime('end_time');
	        $table->double('pick_point_lat');
	        $table->double('pick_point_lon');
	        $table->double('destination_point_lat');
	        $table->double('destination_point_lon');
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
        Schema::dropIfExists('ride_histories');
    }
}

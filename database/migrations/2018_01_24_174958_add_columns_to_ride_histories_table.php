<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToRideHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ride_histories', function ($table) {
            $table->string("ride_cost")->nullable();
            $table->string("ride_distance")->nullable();
            $table->string("is_ride_started")->nullable();
            $table->string("is_ride_finished")->nullable();
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
            $table->dropColumn("ride_cost");
            $table->dropColumn("ride_distance");
            $table->dropColumn('is_ride_started');
            $table->dropColumn('is_ride_finished');
        });
    }
}

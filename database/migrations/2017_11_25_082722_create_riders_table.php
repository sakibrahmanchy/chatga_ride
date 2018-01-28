<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->increments('id');
	        $table->string('first_name');
	        $table->string('last_name');
	        $table->string('nid');
	        $table->string('device_token');
	        $table->boolean('is_verified');
	        $table->string('driving_license');
	        $table->string('motorbike_registration');
	        $table->string('gender');
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
        Schema::dropIfExists('riders');
    }
}

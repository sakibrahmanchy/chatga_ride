<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->double('discount_percentage')->nullable();
            $table->double('discount_amount')->nullable();
            $table->string('promotion_code');
            $table->integer('max_use_time')->default(1);
            $table->integer('max_discount_amount')->nullable();
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
        Schema::dropIfExists('discounts');
    }
}

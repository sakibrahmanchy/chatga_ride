<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientDiscountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_discount', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->foreign('client_id')
                ->references('id')->on('clients')
                ->onDelete('cascade');
            $table->unsignedInteger('discount_id');
            $table->foreign('discount_id')
                ->references('id')->on('discounts')
                ->onDelete('cascade');
            $table->integer('no_of_usage');
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
        Schema::dropIfExists('client_discount');
    }
}

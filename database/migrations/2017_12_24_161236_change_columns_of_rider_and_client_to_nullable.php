<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsOfRiderAndClientToNullable extends Migration
{
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('name')->nullable()->change();
        });

        Schema::table('clients', function ($table) {
            $table->string('last_name')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->string('device_token')->nullable()->change();
            $table->string('gender')->nullable()->change();
        });

        Schema::table('riders', function ($table) {
            $table->string('last_name')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('nid')->nullable()->change();
            $table->string('device_token')->nullable()->change();
            $table->boolean('is_verified')->nullable()->change();
            $table->string('driving_license')->nullable()->change();
            $table->string('motorbike_registration')->nullable()->change();
            $table->string('gender')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveClientIdFromDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('discounts', 'client_id'))
        {
            Schema::table('discounts', function ($table) {
                $table->dropColumn("client_id");
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('discounts', 'client_id'))
        {
            Schema::table('discounts', function ($table) {
                $table->dropColumn("client_id");
            });
        }
    }
}

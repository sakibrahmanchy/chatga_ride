<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->delete();

        $settings = new \App\Setting();
        $settings->key = "base_fare";
        $settings->value =  "20";
        $settings->save();

        $settings = new \App\Setting();
        $settings->key = "price_per_km";
        $settings->value = "10";
        $settings->save();

        $settings = new \App\Setting();
        $settings->key = "price_per_min";
        $settings->value =  "0.5";
        $settings->save();
    }
}

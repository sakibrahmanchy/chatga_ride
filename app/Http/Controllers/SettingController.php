<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getDateTime(){
        //$server_timezone = date_default_timezone_get();
        date_default_timezone_set('Asia/Dhaka');
        return response()->json(["success"=>true,"message"=>"Date time returned successfully","data"=>time()]);
    }
}

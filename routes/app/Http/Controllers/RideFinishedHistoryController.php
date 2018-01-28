<?php

namespace App\Http\Controllers;

use App\Discount;
use App\RideFinishedHistory;
use Illuminate\Http\Request;

class RideFinishedHistoryController extends Controller
{
    public function newRideFinishedHistory(Request $request){

        $price_per_km = $request->price_per_km;
        $price_per_min = $request->price_per_min;
        $history_id = $request->history_id;
        $base_fare = $request->base_fare;
        $duration_in_minutes = $request->duration_in_minutes;
        $distance = $request->distance;
        $discount_id = $request->discount_id;
//        $destination_lat = $request->destianation_lat;
//        $destination_lon = $request->destination_lon;
//        $destination_name = $request->destination_name;
//        $finish_time = $request->finish_time;

        $total_fare = $base_fare + ($distance * $price_per_km) + ($duration_in_minutes * $price_per_min);

        $currentDateTime = date("Y-m-d h:i:s");

        $discount = Discount::where("id",$discount_id)
            ->whereDate("start_time",'<=',$currentDateTime)
            ->whereDate('end_time','>=',$currentDateTime)
            ->where("max_use_time",">",0)
            ->first();

        if(is_null($discount)){

            $rideFinishedHistory = new RideFinishedHistory();

            $rideFinishedHistory->history_id = $history_id;
            $rideFinishedHistory->is_ride_finished = true;
            $rideFinishedHistory->ride_cost = $total_fare;

            if($rideFinishedHistory->save())
                return response()->json(["success"=>true,"message"=>"Promotion code cannot be applied because it is not valid anymore.","data"=>$rideFinishedHistory]);
            else
                return response()->json(["success"=>false,"message"=>"Error creating fare.","data"=>null]);
        }else{
            if(isset($discount->discount_percentage))
            {
                $discount_amount = $total_fare * $discount->discount_percentage;
                if(isset($discount->max_discount_amount))
                {
                    if($discount_amount>$discount->max_discount_amount)
                        $discount_amount = $discount->max_discount_amount;
                }
                $fareAfterDiscount = $total_fare - $discount_amount;

                $discountHistory  = new DiscountHistoryController();
                $discountHistory->NewDiscountHistory($history_id, $discount_id, $total_fare, $fareAfterDiscount);

            }else{

                $fareAfterDiscount = $total_fare - $discount->discount_amount;
                $discountHistory  = new DiscountHistoryController();
                $discountHistory->NewDiscountHistory($history_id, $discount_id, $total_fare, $fareAfterDiscount);


            }
        }






    }
}

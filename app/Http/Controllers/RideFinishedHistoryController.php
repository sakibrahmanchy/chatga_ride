<?php

namespace App\Http\Controllers;

use App\Discount;
use App\Models\RideHistory;
use App\RideFinishedHistory;
use App\Setting;
use Illuminate\Http\Request;

class RideFinishedHistoryController extends Controller
{
    public function newRideFinishedHistory(Request $request){



        $settings = Setting::all();
        $settingArray = array();
        foreach ($settings as $aSetting){
            $settingArray[$aSetting->key] = $aSetting->value;
        }
        if(!isset($request->history_id)){
            return response()->json(["success"=>false, "message"=>"History id is required", "data"=>null]);
        }
        $price_per_km = $settingArray['price_per_km'];
        $price_per_min = $settingArray['price_per_min'];
        $history_id = $request->history_id;
        $base_fare = $settingArray['base_fare'];
        $duration_in_minutes = isset($request->duration_in_minutes) ? $request->duration_in_minutes : 0;
        $distance = isset($request->distance) ? $request->distance : 0;
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
            
            $rideHistory = RideHistory::where("id",$history_id)->first();
            if(is_null($rideHistory)){
                return response()->json(["success"=>false, "message"=>"No such history found", "data"=>null]);
            }

            $rideHistory->pick_point_address = $request->pick_point_address;
            $rideHistory->destination_address = $request->destination_address;
            $time = new \DateTime();
            $rideHistory->end_time = $time;


            if($rideHistory->save()){
                $rideFinishedHistory = RideFinishedHistory::where("history_id",$history_id)->first();

                $rideFinishedHistory->history_id = $history_id;
                $rideFinishedHistory->is_ride_finished = true;
                $rideFinishedHistory->ride_cost = $total_fare;
                $rideFinishedHistory->ride_distance = $distance;

                if($rideFinishedHistory->save())
                {
                    $data = array(
                        "history_id"=>$history_id,
                        "cost_before_discount"=>$total_fare,
                        "cost_after_discount"=>$total_fare
                    );
                    return response()->json(["success"=>true,"message"=>"Promotion code cannot be applied because it is not valid anymore.","data"=>$data]);
                }
                else
                    return response()->json(["success"=>false,"message"=>"Error creating fare.","data"=>null]);

            }
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

            $rideHistory = RideHistory::where("id",$history_id)->first();
            if(is_null($rideHistory)){
                return response()->json(["success"=>false, "message"=>"No such history found", "data"=>null]);
            }

            $rideHistory->pick_point_address = $request->pick_point_address;
            $rideHistory->destination_address = $request->destination_address;

            $time = new \DateTime();
            $rideHistory->end_time = $time;

            if($rideHistory->save()){

                $rideFinishedHistory = RideFinishedHistory::where("history_id",$history_id)->first();

                $rideFinishedHistory->history_id = $history_id;
                $rideFinishedHistory->is_ride_finished = true;
                $rideFinishedHistory->ride_cost = $total_fare;
                $rideFinishedHistory->ride_distance = $distance;

                if($rideFinishedHistory->save())
                {
                    $data = array(
                        "history_id"=>$history_id,
                        "cost_before_discount"=>$total_fare,
                        "cost_after_discount"=>$fareAfterDiscount
                    );
                    return response()->json(["success"=>true,"message"=>"Promotion code applied.","data"=>$data]);
                }
                else
                    return response()->json(["success"=>false,"message"=>"Error creating fare.","data"=>null]);

            } else
                return response()->json(["success"=>false,"message"=>"Error creating fare.","data"=>null]);



        }






    }
}

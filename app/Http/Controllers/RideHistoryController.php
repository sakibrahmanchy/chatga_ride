<?php

namespace App\Http\Controllers;

use App\Discount;
use App\DiscountHistory;
use App\Models\RideHistory;
use App\RideFinishedHistory;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class RideHistoryController extends Controller
{

    public function userHistory(Request $request){
        if(isset($request->client_id)){
            $client_id = $request->client_id;

            $histories = RideHistory::with("DiscountHistory","Rider")->where("client_id",$client_id)->orderBy('created_at','desc')->get();
            $historyList = array();
            foreach ($histories as $history){
                $currentHistory = new \stdClass();
                $currentHistory->date_time = date_format($history->created_at, 'g:ia \o\n l jS F Y');

                $to_time = strtotime($history->end_time);
                $from_time = strtotime($history->start_time);
                $duration =  round(abs($to_time - $from_time) / 60,2). " minute";
                $currentHistory->distance_time = round($history->ride_distance,2)."KM/".round($duration,2)."min";
                $currentHistory->pick_point_address = $history->pick_point_address;
                $currentHistory->destination_address = $history->destination_address;
                $currentHistory->total_fare = "TK.".round($history->ride_cost,2);
                array_push($historyList,$currentHistory);
            }


            return response()->json(["success"=>true,"message"=>"Success","data"=>$historyList]);
        }else{
            return response()->json(["success"=>false,"message"=>"Client Id is required", "data"=>null]);
        }
    }

    public function userSpecificHistory(Request $request){
        if(!isset($request->history_id))
            return response()->json(["success"=>false,"message"=>"History id is required","data"=>null]);

        $history_id = $request->history_id;
        $history = $histories = RideHistory::with("DiscountHistory","Rider")->where("id",$history_id)->first();

        $currentHistory = new \stdClass();
        $currentHistory->date_time = date_format($history->created_at, 'g:ia \o\n l jS F Y');

        $to_time = strtotime($history->end_time);
        $from_time = strtotime($history->start_time);
        $duration =  round(abs($to_time - $from_time) / 60,2). " minute";
        $currentHistory->distance_time = $history->ride_distance."KM/".$duration;
        $currentHistory->pick_point_address = $history->pick_point_address;
        $currentHistory->destination_address = $history->destination_address;
        $currentHistory->total_fare = "TK.".$history->ride_cost;

        return response()->json(["success"=>true,"message"=>"History successfully returned","data"=>$currentHistory]);

    }

    public function clearAllHistory(){
        RideHistory::truncate();
        DiscountHistory::truncate();
    }

    public function NewRideHistory(Request $request){

        $validate = $this->validations($request);

        if($validate["error"]){
            return $this->prepareResult(false, [], $validate['errors'],"Error while validating history");
        }
        $minutes_to_add = $request->end_time;

        $time = new \DateTime();
        $start_time = $time->format('Y-m-d H:i:s');
        $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
        $end_time = $time->format('Y-m-d H:i:s');

        $history = new RideHistory();
        $history->client_id = $request->client_id;
        $history->rider_id = $request->rider_id;
        $history->start_time = $start_time;
        $history->end_time = $end_time;
        $history->pick_point_lat = $request->pick_point_latitude;
        $history->pick_point_lon = $request->pick_point_longitude;
        $history->destination_point_lat = $request->destination_point_latitude;
        $history->destination_point_lon = $request->destination_point_longitude;
        $history->pick_point_address = $request->pick_point_address;
        $history->destination_address = $request->destination_address;
        $history->is_ride_finished = false;
        $history->is_ride_started = false;
        $history->ride_cost = $request->ride_cost;
        $history->ride_distance = $request->ride_distance;


        if($history->save()){
            return response()->json(["success"=>true,"message"=>"Successfully created ride history","data"=>$history]);
        }
        else
            return response()->json(["success"=>false,"message"=>"Error creating ride history"]);

    }


    public function StartRide(Request $request){

        if(!isset($request->history_id)){
            return response()->json(["success"=>false, "message"=>"History id is required", "data"=>null]);
        }
        $history_id = $request->history_id;
        $rideHistory = RideHistory::where("id",$history_id)->first();
        $rideHistory->is_ride_started = true;
        if($rideHistory->save()){
            return response()->json(["success"=>true,"message"=>"Ride successfully started!","data"=>$history_id]);
        }
        else
            return response()->json(["success"=>false, "message"=>"Failed to start ride!", "data"=>null]);
    }


    public function RideFinishedHistory(Request $request){

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
        $duration_in_minutes = round($duration_in_minutes,2);
        $distance = isset($request->distance) ? $request->distance : 0;
        $distance = round($distance,2);
        $discount_id = $request->discount_id;

        $total_fare = $base_fare + ($distance * $price_per_km) + ($duration_in_minutes * $price_per_min);

        $currentDateTime = date("Y-m-d h:i:s");

        $discount = Discount::where("id",$discount_id)
            ->whereDate("start_time",'<=',$currentDateTime)
            ->whereDate('end_time','>=',$currentDateTime)
            ->first();

        $rideHistory = RideHistory::where("id",$history_id)->first();
        if(is_null($rideHistory)){
            return response()->json(["success"=>false, "message"=>"No such history found", "data"=>null]);
        }

        $discountApplied = DB::table('client_discount')
            ->where("client_id",$rideHistory->client_id)
            ->where("discount_id",$discount_id)
            ->where("no_of_usage",'>',0)
            ->first();

        if(is_null($discount)||is_null($discountApplied)){

            $rideHistory->destination_point_lat = $request->destination_lat;
            $rideHistory->destination_point_lon = $request->destination_lon;
            $rideHistory->destination_address = $request->destination_address;
            $rideHistory->is_ride_finished = true;
            $time = new \DateTime();
            $rideHistory->end_time = $time;

            $rideHistory->ride_cost = $total_fare;
            $rideHistory->ride_distance = $distance;

            if($rideHistory->save()){

                $data = array(
                    "history_id"=>$history_id,
                    "cost_before_discount"=>$total_fare,
                    "cost_after_discount"=>$total_fare
                );
                return response()->json(["success"=>true,"message"=>"Promotion code cannot be applied because it is not valid anymore.","data"=>$data]);


            }
            else
                return response()->json(["success"=>false,"message"=>"Error creating fare.","data"=>null]);

        }else{


            $client_id = $rideHistory->client_id;

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
                $discountStatus =$discountHistory->NewDiscountHistory($history_id, $discount_id, $total_fare, $fareAfterDiscount,$client_id);

            }else{

                $fareAfterDiscount = $total_fare - $discount->discount_amount;
                $discountHistory  = new DiscountHistoryController();
                $discountStatus = $discountHistory->NewDiscountHistory($history_id, $discount_id, $total_fare, $fareAfterDiscount,$client_id);
            }

            if($fareAfterDiscount<$base_fare)
                $fareAfterDiscount = round(doubleval($base_fare),2);

            $rideHistory->destination_point_lat = $request->destination_lat;
            $rideHistory->destination_point_lon = $request->destination_lon;
            $rideHistory->destination_address = $request->destination_address;
            $time = new \DateTime();
            $rideHistory->end_time = $time;

            $rideHistory->is_ride_finished = true;
            $rideHistory->ride_cost = $total_fare;
            $rideHistory->ride_distance = $distance;

            if($rideHistory->save()){
                $data = array(
                    "history_id"=>$history_id,
                    "cost_before_discount"=>$total_fare,
                    "cost_after_discount"=>$fareAfterDiscount
                );
                return response()->json(["success"=>true,"message"=>$discountStatus,"data"=>$data]);

            } else
                return response()->json(["success"=>false,"message"=>"Error creating fare.","data"=>null]);

        }

    }

    public function validations($request){

        $errors = [];

        $error = false;

        $validator = Validator::make($request->all(),[
            'client_id'=>'required',
            'rider_id' =>'required',
            'end_time'=>'required',
            'pick_point_latitude'=>'required',
            'pick_point_longitude'=>'required',
            'destination_point_latitude'=>'required',
            'destination_point_longitude'=>'required'
        ]);

        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }


        return ["error" => $error,"errors"=>$errors];

    }


    private function prepareResult($status, $data, $errors,$msg)
    {
        return ['status' => $status,'data'=> $data,'message' => $msg,'errors' => $errors];
    }
}

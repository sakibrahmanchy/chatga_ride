<?php

namespace App\Http\Controllers;

use App\Models\RideHistory;
use App\RideFinishedHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RideHistoryController extends Controller
{
    public function NewRideHistory(Request $request){

        $validate = $this->validations($request);

        if($validate["error"]){
            return $this->prepareResult(false, [], $validate['errors'],"Error while validating history");
        }
        $minutes_to_add = $request->end_time;
        $time = new \DateTime($request->start_time);
        $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
        $end_time = $time->format('Y-m-d H:i:s');

        $history = new RideHistory();
        $history->client_id = $request->client_id;
        $history->rider_id = $request->rider_id;
        $history->start_time = $request->start_time;
        $history->end_time = $end_time;
        $history->pick_point_lat = $request->pick_point_latitude;
        $history->pick_point_lon = $request->pick_point_longitude;
        $history->destination_point_lat = $request->destination_point_latitude;
        $history->destination_point_lon = $request->destination_point_longitude;
        $history->initial_approx_cost = $request->initial_approx_cost;
        $history->pick_point_address = $request->pick_point_address;
        $history->destination_address = $request->destination_address;

        if($history->save()){
            $rideFinishedHistory = new RideFinishedHistory();
            $rideFinishedHistory->history_id = $history->id;
            $rideFinishedHistory->is_ride_finished = false;
            $rideFinishedHistory->ride_cost = 0.0;
            if($rideFinishedHistory->save())
                return response()->json(["success"=>true,"message"=>"Successfully created ride history","data"=>$history,
                    "rideFinishedHistory"=>$rideFinishedHistory]);
            else
                return response()->json(["success"=>false,"message"=>"Error creating ride finished history"]);
        }
        return response()->json(["success"=>false,"message"=>"Error creating ride history"]);

    }


    public function UpdateRideHistory(Request $request){

        $history_id = $request->history_id;
        $rideHistory = RideHistory::where("id",$history_id)->first();

        $distanceSoFar = $request->ride_distance;



    }

    public function validations($request){

        $errors = [];

        $error = false;

        $validator = Validator::make($request->all(),[
            'client_id'=>'required',
            'rider_id' =>'required',
            'start_time'=>'required',
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

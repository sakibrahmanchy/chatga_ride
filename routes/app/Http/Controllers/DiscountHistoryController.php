<?php

namespace App\Http\Controllers;

use App\Discount;
use App\DiscountHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountHistoryController extends Controller
{

    public function NewDiscountHistory($rideHistoryId, $discount_id, $regular_cost, $cost_paid){

        $request = array(
            "ride_history_id"=>$rideHistoryId,
            "discount_id" => $discount_id,
            "regular_cost"=>$regular_cost,
            "cost_paid"=>$cost_paid
        );
        $validate = $this->validations($request);

        if($validate["error"]){
            return $this->prepareResult(false, [], $validate['errors'],"Error while validating history");
        }

        $discountHistory = new DiscountHistory();
        $discountHistory->ride_history_id = $request->ride_history_id;
        $discountHistory->discount_id = $request->  discount_id;
        $discountHistory->regular_cost = $request->regular_cost;
        $discountHistory->cost_paid = $request->cost_paid;
        if($discountHistory->save()){
            $discount = Discount::where("id",$request->discount_id)->first();
            $discount->max_use_time -= 1;
            if($discount->save())
                return response()->json(["success"=>false,"message"=>"Discount history created","data"=>$discountHistory]);
            else{
                return response()->json(["success"=>false,"message"=>"Failed decreasing discount useable time","data"=>null]);
            }
        }else{
            return response()->json(["success"=>false,"message"=>"Failed creating discount histories","data"=>null]);
        }

    }


    public function validations($request){

        $errors = [];

        $error = false;

        $validator = Validator::make($request,[
            'ride_history_id' =>'required',
            'discount_id'=>'required',
            'regular_cost'=>'required',
            'cost_paid'=>'required'
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

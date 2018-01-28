<?php

namespace App\Http\Controllers;

use App\Discount;
use App\DiscountHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiscountHistoryController extends Controller
{

    public function NewDiscountHistory($rideHistoryId, $discount_id, $regular_cost, $cost_paid,$client_id){

        $request = array(
            "ride_history_id"=>$rideHistoryId,
            "discount_id" => $discount_id,
            "regular_cost"=>$regular_cost,
            "cost_paid"=>$cost_paid
        );
        $validate = $this->validations($request);

        if($validate["error"]){
            return "Error while validating history";
        }

        $discountHistory = new DiscountHistory();
        $discountHistory->ride_history_id = $rideHistoryId;
        $discountHistory->discount_id = $discount_id;
        $discountHistory->regular_cost = $regular_cost;
        $discountHistory->cost_paid = $cost_paid;
        if($discountHistory->save()){

            $discountApplied = DB::table('client_discount')
                ->where("client_id",$client_id)
                ->where("discount_id",$discount_id)
                ->where("no_of_usage",'>',0)
                ->first();

            if(!is_null($discountApplied)){

                $isDiscountValid = DB::table('client_discount')
                    ->where("client_id",$client_id)
                    ->where("discount_id",$discount_id)
                    ->decrement('no_of_usage');
                if($isDiscountValid)
                    return "Discount history created";
                else{
                    return "Failed decreasing discount useable time";
                }

            }else{
                return "Discount is not applied yet.";
            }

        }else{
            return "Failed creating discount histories";
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

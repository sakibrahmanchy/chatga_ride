<?php

namespace App\Http\Controllers;

use App\Discount;
use App\Enumaration\DiscountTypes;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    public function NewDiscount(Request $request){

        $validate = $this->validations($request);

        if($validate["error"]){
            return $this->prepareResult(false, [], $validate['errors'],"Error while validating history");
        }

        if(!isset($request->discount_percentage)&&!isset($request->discount_amount)){
            $error = true;
            return response()->json(["success"=>false,"message"=>"Either discount percentage or discount amount is required","data"=>null]);
        }
        if(isset($request->discount_percentage)&&isset($request->discount_amount)){
            $error = true;
            return response()->json(["success"=>false,"message"=>"Only one of  percentage or discount amount can be applied","data"=>null]);
        }

        $discountType = $request->discount_type;
        if($discountType==DiscountTypes::$APPLICABLE_FOR_ALL){
            $errorInserting = false;
            $clients = Client::all();
            foreach($clients as $aClient){

                $discount = new Discount();
                $discount->client_id = $aClient->user_id;
                $discount->start_time = $request->start_time;
                $discount->end_time = $request->end_time;
                if(isset($request->discount_percentage))
                        $discount->discount_percentage = $request->discount_percentage;
                else
                    $discount->discount_amount = $request->discount_amount;
                $discount->promotion_code = $request->promotion_code;
                if(isset($request->max_use_time))
                    $discount->max_use_time = $request->max_use_time;
                $discount->max_discount_amount = $request->max_discount_amount;
                if(!$discount->save()){
                    $errorInserting = true;
                    break;
                }
            }

            $allDiscounts = Discount::all();

            if(!$errorInserting){
                return response()->json(["success"=>true,"message"=>"Successfully created discount","data"=>$allDiscounts]);
            }else{
                return response()->json(["success"=>false,"message"=>"Failed creating promotion","data"=>null]);
            }

        }else if($discountType==DiscountTypes::$APPLICABLE_FOR_PERSONAL){
            if(!isset($request->client_id)){
                return response()->json(["success"=>false,"message"=>"Client ID is required.","data"=>null]);
            }

            $errorInserting = false;

            $discount = new Discount();
            $discount->client_id = $request->client_id;
            $discount->start_time = $request->start_time;
            $discount->end_time = $request->end_time;
            if(isset($request->discount_percentage))
                $discount->discount_percentage = $request->discount_percentage;
            else
                $discount->discount_amount = $request->discount_amount;
            $discount->promotion_code = $request->promotion_code;
            if(isset($request->max_use_time))
                $discount->max_use_time = $request->max_use_time;
            $discount->max_discount_amount = $request->max_discount_amount;
            if(!$discount->save()){
                $errorInserting = true;

            }

            $allDiscounts = Discount::all();

            if(!$errorInserting){
                return response()->json(["success"=>true,"message"=>"Successfully created discount","data"=>$allDiscounts]);
            }else{
                return response()->json(["success"=>false,"message"=>"Failed creating promotion","data"=>null]);
            }

        }

    }

    public function userDiscounts(Request $request){

        if(!isset($request->user_id)){
            return response()->json(["success"=>false,"message"=>"User id is required","data"=>null]);
        }
        else{

            $user_id = $request->user_id;
            $currentDateTime = date("Y-m-d h:i:s");
            $discounts = DB::table('discounts')
                ->join('client_discount','discounts.id','=','client_discount.discount_id')
                ->where("client_discount.client_id",$user_id)
                ->whereDate("discounts.start_time",'<=',$currentDateTime)
                ->whereDate('discounts.end_time','>=',$currentDateTime)
                ->where("client_discount.no_of_usage",">",0)
                ->get();

            return response()->json(["success"=>true,"message"=>"Success","data"=>$discounts]);
        }



    }

    public function applyClientPromoCode(Request $request){

        $promo_code = $request->promo_code;
        $client_id = $request->client_id;

        $currentDateTime = date("Y-m-d h:i:s");

        $discount = Discount::where("promotion_code",$promo_code)
            ->whereDate("start_time",'<=',$currentDateTime)
            ->whereDate('end_time','>=',$currentDateTime)
            ->first();

        if(!is_null($discount)){

            if(is_null(DB::table("client_discount")->where("client_id",$client_id)->where("discount_id",$discount->id)->first())){
                $max_usage_time = $discount->max_use_time;

                if(DB::table('client_discount')->insert([
                    "client_id"=>$client_id,
                    "discount_id"=>$discount->id,
                    "no_of_usage"=>$max_usage_time
                ]))
                    return response()->json(["success"=>true,"message"=>"Success.","data"=>$discount]);
                else
                    return response()->json(["success"=>false,"message"=>"Failed to apply promo code.","data"=>null]);
            }else{
                return response()->json(["success"=>false,"message"=>"Promo code already applied.","data"=>null]);
            }

        }else{
            return response()->json(["success"=>false,"message"=>"Promo code not valid or expired.","data"=>null]);
        }

    }

    public function validations($request){

        $errors = [];

        $error = false;

        $validator = Validator::make($request->all(),[
            'start_time' =>'required',
            'end_time'=>'required',
            'promotion_code'=>'required',
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

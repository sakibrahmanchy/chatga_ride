<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use JWTAuthException;
use DB;

class ApiController extends Controller
{


	public function accessToken(Request $request)
	{

		$validate = $this->validations($request,"login");

		if($validate["error"]){
			return $this->prepareResult(false, [], $validate['errors'],"Error while validating user");
		}

		$client_id = $request->client_id;
		$client_secret = $request->client_secret;

		if(DB::table('oauth_clients')->where('id',$client_id)->where('secret',$client_secret)){

			$phone_number = "+"+$request->phone_number;
			$user = User::where("phone_number",$phone_number)->first();

			if($user!=null){

				$user_tokens = $user->createToken('Chaatga Ride');

				return $this->prepareResult(true, $user_tokens->accessToken, [],"User found");

			}else{

				return $this->prepareResult(false , null , ["response_code"=>"register_required"],"User should register");    	     

			}

		}
		else{
			return $this->prepareResult(false, [], ["response-code" => "Unauthenticated"]);
		}

	}

	public function validations($request,$type){

		$errors = [];

		$error = false;

		if($type == "login"){

			$validator = Validator::make($request->all(),[
				'phone_number'=>'required'
			]);

			if($validator->fails()){

				$error = true;

				$errors = $validator->errors();

			}

		}
		return ["error" => $error,"errors"=>$errors];

	}


	private function prepareResult($status, $data, $errors,$msg)
	{
		return response()->json(['status' => $status,'access_token'=> $data,'message' => $msg,'errors' => $errors]);
	}

	public function getAccessToken(Request $request){

		$userInfo = array(
			"name" => $request->first_name . " " . $request->last_name,
			"email" => $request->email,
			"phone_number" => $request->phone_number,
			"user_type" => UserTypes::$RIDER,
			"password" => $request->password
		);

		User::create($userInfo);

	}

}

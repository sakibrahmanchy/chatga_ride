<?php

namespace App\Http\Controllers;

use App\Enumaration\UserAuthenticationCodes;
use App\Enumaration\UserTypes;
use App\Models\Client as ClientInformation;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Validator;
use App\Models\LoginStatus;
use App\Models\Rider;


class UserController extends Controller
{
	public function __invoke()
	{
		return User::all();
	}

	public function CheckIfUserExists(Request $request)
	{
		$client = new Client(); //GuzzleHttp\Client
		$phoneNumber = $request->phoneNumber;
		$requestUrl = Config::get('urls.node_api') . "users/+" . $phoneNumber;
		
		try {

			$result = $client->get($requestUrl);
			$message = ($result->getStatusCode());

			
			return response()->json([
					'success' => true,
					'response_code'=> UserAuthenticationCodes::$USER_FOUND
				]);
		
		} catch (RequestException $exception) {

			if (is_null($exception->getResponse())) {
				return response()->json(['success' => false, "response_code" => UserAuthenticationCodes::$NO_RESPONSE], 500);
			} else {
				$message = json_decode($exception->getResponse()->getBody()->getContents());
				if ($message->code == UserAuthenticationCodes::$USER_NOT_FOUND) {
					return response()->json([
						'success' => false,
						"response_code" => UserAuthenticationCodes::$USER_NOT_FOUND
					], 500);
				} else {
					return response()->json([
						'success' => false,
						"response_code" => $message
					], 500);
				}
			}

		}
	}

	public function CheckIfDriverExists(Request $request){


		if(isset($request->phoneNumber))
		{
			$phoneNumber = $request->phoneNumber;
			$user = User::where("phone_number",$phoneNumber)->where("user_type", UserTypes::$RIDER)->first();
			if(!is_null($user)){
				return response()->json([
					'success' => true,
					'response_code'=> UserAuthenticationCodes::$USER_FOUND
				]);
			}else{
				return response()->json([
						'success' => false,
						"response_code" => UserAuthenticationCodes::$USER_NOT_FOUND
					], 500);
			}
		}else{
			return response()->json([
					"success"=>false, 
					"message"=>"Phone number is required"
				]);
		}


	}


	public function deleteAllUsers(Request $request){	

		$verification_code = $request->verification_code;

		if($verification_code == "SECURE_CODE_FOR_DELETE"){

			User::truncate();
			ClientInformation::truncate();	
			
		}else{
			return response()->json(["success"=>false, "message"=>"Unauthenticated"]);
		}

	}

	public function deleteUserByPhoneNumber($phoneNumber, Request $request){	

		$verification_code = $request->verification_code;

		if($verification_code == "SECURE_CODE_FOR_DELETE_9999999999"){

			$user = User::where("phone_number",$phoneNumber)->first();
			
			if(!is_null($user)){

				$user_id = $user->id;
				$user->delete();

				$client = ClientInformation::where("user_id",$user_id)->first();
				$client->delete();

				return response()->json(["success"=>true, "message"=>"Deleted Successfully"]);

			}else
				return response()->json(["success"=>false, "message"=>"User not found"]);	
			
		}else{
			return response()->json(["success"=>false, "message"=>"Unauthenticated"]);
		}

	}

	public function SignUpUser($userInfo)
	{

		$user = User::create($userInfo);

		if ($user) {

			return response()->json([
				"success" => true,
				"response_code" => UserAuthenticationCodes::$USER_CREATED_SUCCESSFULLY,
				"message" => "User Created Successfully",
				"data" => $user,
				"user_id"=>$user->id
			]);
		} else {

			return response()->json([
				"success" => false,
				"response_code" => UserAuthenticationCodes::$USER_CREATE_FAILED,
				"message" => "User create failed"
			], 500);
		}

	}

	public function LoginUser(Request $request){

		$device_token = $request->device_token;
		$phone_number = $request->phone_number;

		$user = User::where("phone_number",$phone_number)->first();
		if($user!=null){

			$user_type = $user->user_type;
			if($user_type==UserTypes::$CLIENT) {

				if(ClientInformation::where("device_token",$device_token)->where("user_id",$user->id)->exists()){

					$loginInfo = array(
						"user_id"=>$user->id,
						"device_id"=>$device_token
					);

					LoginStatus::create($loginInfo);

					$clientInformation = ClientInformation::where("user_id",$user->id)->first();

					return response()->json([
						"success" => true,
						"response_code" => UserAuthenticationCodes::$LOGGED_IN_SUCCESSFULLY,
						"message" => "Logged in successfully",
						"data" => $clientInformation

					], 200);

				}else{
					return response()->json([
						"success" => false,
						"response_code" => UserAuthenticationCodes::$PHONE_VERIFICATION_REQUIRED,
						"message" => "Phone Verification Required"
					], 500);
				}

			}else if($user_type==UserTypes::$RIDER){

				if(Rider::where("device_token",$device_token)->exists()){

					$loginInfo = array(
						"user_id"=>$user->id,
						"device_id"=>$device_token
					);
					LoginStatus::create($loginInfo);
					return response()->json([
						"success" => true,
						"response_code" => UserAuthenticationCodes::$LOGGED_IN_SUCCESSFULLY,
						"message" => "Logged in successfully"
					], 200);

				}

				return response()->json([
						"success" => false,
						"response_code" => UserAuthenticationCodes::$PHONE_VERIFICATION_REQUIRED,
						"message" => "Phone Verification Required"
				], 500);

			}
		}

		return response()->json([
				"success" => false,
				"response_code" => UserAuthenticationCodes::$INVALID_LOGIN_REQUEST,
				"message" => "Invalid login request"
		], 500);

	}

	public function updateUserDeviceToken(Request $request){
		
		$validation = Validator::make($request->all(), [
			'phone_number' => 'required',
			'device_token' => 'required'
		]);


		if ($validation->fails()) {
			return response()->json([
				"success" => false,
				"message" => "Please fill up all the forms.",
				"response_code" => UserAuthenticationCodes::$VALIDATION_ERROR,
				"errors" => $validation->messages()
			], 406);
		}

		$user = User::where("phone_number",$request->phone_number)->first();
		if(!is_null($user)){

			if($user->user_type==UserTypes::$CLIENT){

				$client = ClientInformation::where("user_id",$user->id)->first();
				$client->device_token = $request->device_token;
				if($client->save()){
					return response()->json(["success"=>true, "message"=>"Successfully updated device_token", "data"=>$client->device_token],200);
				}else{
					return response()->json(["success"=>false, "message"=>"Failed to update device_token", "data"=>null],200);
				}
					
			}else if($user->user_type==UserTypes::$RIDER){

				$rider = Rider::where("user_id",$user->id)->first();
				$rider->device_token = $request->device_token;
				if($rider->save()){
					return response()->json(["success"=>true, "message"=>"Successfully updated device_token", "data"=>$rider->device_token],200);
				}else{
					return response()->json(["success"=>false, "message"=>"Failed to update device_token", "data"=>null],200);
				}

			}

		}else{
			return response()->json(["success"=>false, "message"=>"User not found with the phone number", "data"=>null],200);
		}
		
	}


}
	
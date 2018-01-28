<?php

namespace App\Http\Controllers;

use App\Enumaration\UserAuthenticationCodes;
use App\Enumaration\UserTypes;
use Illuminate\Http\Request;
use Validator;
use App\Models\Client;
use Illuminate\Support\Facades\Input;
use App\User;

class ClientController extends Controller
{

	public function getAllClients(){
		$clients = Client::all();

		return response()->json([$clients]);
	}

	public function getClientByPhoneNumber()
	{
		if(!is_null( Input::get('phoneNumber')))
			$phoneNumber = Input::get('phoneNumber');
		else
			return response(["success" => false, "message" => "Invalid query param"]);
		//dd($phoneNumber);
		$phoneNumber = $phoneNumber;
		$user = User::where("phone_number",$phoneNumber)->first();
		if(!is_null($user))
		{
			$client = Client::where("user_id",$user->id)->first();
			return response(["success" => true, "data" => $client]);
		}else {
			return response(["success" => false, "message" => "Client not found"]);
		}
	}

	public function NewClient(Request $request)
	{

		$validation = Validator::make($request->all(), [
			'phone_number' => 'required',
            'first_name' => 'required'
		]);

		if(User::where("phone_number",$request->phone_number)->where("user_type",UserTypes::$CLIENT)->exists()){
            return response()->json([
                "success" => false,
                "message" => "Phone number already exists",
                "response_code" => UserAuthenticationCodes::$VALIDATION_ERROR,
            ], 406);
        }

		if ($validation->fails()) {
			return response()->json([
				"success" => false,
				"message" => "Please fill up all the forms.",
				"response_code" => UserAuthenticationCodes::$VALIDATION_ERROR,
				"errors" => $validation->messages()
			], 406);
		}

		$userInfo = array(
			"name" => $request->first_name . " " . $request->last_name,
			"email" => $request->email,
			"phone_number" => $request->phone_number,
			"user_type" => UserTypes::$CLIENT,
		);

		$userController = new UserController();
		$userResponse = json_decode($userController->SignUpUser($userInfo)->getContent());
		if ($userResponse->success) {

			$clientInfo = array(
				"first_name" => $request->first_name,
				"last_name" => $request->last_name,
				"device_token" => $request->device_token,
				"birth_date" => $request->birth_date,
				"gender" => $request->gender,
				"user_id" => $userResponse->user_id
			);


			if (Client::create($clientInfo)) {

				return response()->json([
					"success" => true,
					"response_code" => UserAuthenticationCodes::$CLIENT_CREATED_SUCCESSFULLY,
					"message" => "Client Created Successfully",
					// "data" => [
					// 	"user_response" => $userResponse,
					// 	"client_info" => $clientInfo
					// ]
				], 201);

			} else {

				return response()->json([
					"success" => true,
					"response_code" => UserAuthenticationCodes::$CLIENT_CREATE_FAILED,
					"message" => "Failed Creating Client",
				], 500);
			}
		}

	}

	
}

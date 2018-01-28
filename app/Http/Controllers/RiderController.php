<?php

namespace App\Http\Controllers;

use App\Enumaration\RiderVerificationStatus;
use App\Enumaration\UserAuthenticationCodes;
use App\Enumaration\UserTypes;
use App\Models\Rider;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Validator;
use Illuminate\Support\Facades\Input;

class RiderController extends Controller
{
	public function allRider(){
		$riders = Rider::with('user')->get();
		return response()->json( $riders);
	}

	public function getRiderByPhoneNumber()
	{
		if(!is_null( Input::get('phoneNumber')))
			$phoneNumber = Input::get('phoneNumber');
		else
			return response(["success" => false, "message" => "Invalid query param"]);
		//dd($phoneNumber);
		$phoneNumber = "+".$phoneNumber;
		$user_id = User::where("phone_number",$phoneNumber)->first()->id;

		if($user_id)
		{
			$rider = Rider::where("user_id",$user_id)->get();
			return response(["success" => true, "data" => $rider]);
		}else {
			return response(["success" => false, "message" => "Rider not found"]);
		}
	}

	public function rideRequest(Request $request){

        $clientId = null;
        if(isset($_GET["clientId"])){
            $clientId = $_GET["clientId"];
        }

        $riderId = null;
        if(isset($_GET["riderId"])){
            $riderId = $_GET["riderId"];
        }

        $sourceLatitude = null;
        if(isset($_GET["sourceLatitude"])){
            $sourceLatitude = $_GET["sourceLatitude"];
        }

        $sourceLongitude = null;
        if(isset($_GET["sourceLongitude"])){
            $sourceLongitude = $_GET["sourceLongitude"];
        }

        $destinationLatitude = null;
        if(isset($_GET["destinationLatitude"])){
            $destinationLatitude = $_GET["destinationLatitude"];
        }

        $destinationLongitude = null;
        if(isset($_GET["destinationLongitude"])){
            $destinationLongitude = $_GET["destinationLongitude"];
        }
        $authKey = Config::get("secret_constants.notification_auth_key");
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key='.$authKey,
            'Content-Type: application/json'
        );


        //$riderDeviceToken = Rider::where("id",$riderId)->first()->device_token;
        $regdata = 'dhrR2WQ57Ng:APA91bHnzmbJ0uePuvm7DxBrLtFE6V3FJ34-A6-f1FWb36uvt_9Phj-t453fVq3EUbk4o5bJotQ0cKUqs7WSmwj9Sam6rAULLzKWz9HK3xu77plewab8v7BPKjiodxIHoEWgUg5SCKH8';
       // $regdata = $riderDeviceToken;
//        dd($regdata);
        $data = array(
            'title' => 'Ride',
            'body' => 'You have a ride',
            'clientId' => $clientId,
            'sourceLatitude' => $sourceLatitude,
            'sourceLongitude' => $sourceLongitude,
            'destinationLatitude' => $destinationLatitude,
            'destinationLongitude' => $destinationLongitude
        );

        $fields = array(
            'to' => $regdata,
            'data' => $data
        );

//        $payload = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if($result == false){
            die('curl_failed'. curl_error($ch));
        }else{
            echo "success";
        }
        curl_close($ch);

//        $client = new Client(["headers"=>$headers]); //GuzzleHttp\Client
//        dd($client);
//        try {
//            $client->post($url,$fields);
//        } catch (RequestException $exception) {
//
//            if (is_null($exception->getResponse())) {
//                return response()->json(['success' => false, "response_code" => UserAuthenticationCodes::$NO_RESPONSE], 500);
//            } else {
//                $message = json_decode($exception->getResponse()->getBody()->getContents());
//                if ($message->code == UserAuthenticationCodes::$USER_NOT_FOUND) {
//                    return response()->json([
//                        'success' => false,
//                        "response_code" => UserAuthenticationCodes::$USER_NOT_FOUND
//                    ], 500);
//                } else {
//                    return response()->json([
//                        'success' => false,
//                        "response_code" => $message
//                    ], 500);
//                }
//            }
//
//        }

    }

	public function NewRider(Request $request)
	{
		$validation = Validator::make($request->all(), [
			'phone_number' => 'required',
		]);

		if ($validation->fails()) {
			return response()->json([
				"success" => false,
				"message" => "Please fill up all the forms.",
				"response_code" => UserAuthenticationCodes::$VALIDATION_ERROR,
				"errors" => $validation->messages()
			], 406);
		}

		if(User::where("phone_number",$request->phone_number)->where("user_type",UserTypes::$RIDER)->exists())
		{
			return response()->json([
				"success" => false,
				"message" => "Rider already exists",
			], 406);
		}

		$userInfo = array(
			"name" => $request->first_name . " " . $request->last_name,
			"email" => $request->email,
			"phone_number" => $request->phone_number,
			"user_type" => UserTypes::$RIDER,
			"password" => bcrypt($request->password)
		);

		$userController = new UserController();
		$userResponse = json_decode($userController->SignUpUser($userInfo)->getContent());

		if ($userResponse->success) {

			$riderInfo = array(
				"first_name" => $request->first_name,
				"last_name" => $request->last_name,
				"device_token" => $request->device_token,
				"birth_date" => $request->birth_date,
				"gender" => $request->gender,
				"user_id" => $userResponse->user_id,
				"nid" => $request->nid,
				"is_verified" => RiderVerificationStatus::$NOT_VERIFIED,
				"driving_license" => $request->driving_license,
				"motorbike_registration" => $request->motorbike_registration,
			);


			if (Rider::create($riderInfo)) {

				return response()->json([
					"success" => true,
					"response_code" => UserAuthenticationCodes::$RIDER_CREATED_SUCCESSFULLY,
					"message" => "Rider Created Successfully",
					"data" => [
						"user_response" => $userResponse,
						"rider_info" => $riderInfo
					]
				], 201);

			} else {

				return response()->json([
					"success" => true,
					"response_code" => UserAuthenticationCodes::$RIDER_CREATE_FAILED,
					"message" => "Failed Creating Rider",
				], 500);
			}
		}

	}
}

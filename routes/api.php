<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('v1/user_exists','UserController@CheckIfUserExists');
Route::get('v1/driver_exists','UserController@CheckIfDriverExists');

Route::post('v1/client','ClientController@NewClient');
Route::post('v1/rider','RiderController@NewRider');
Route::get('/v1/client/all','ClientController@getAllClients');
Route::get('/v1/rider/all','RiderController@allRider');
Route::get('/v1/client/delete_x_y_z_w','UserController@deleteAllUsers');
Route::get('/v1/history/clear_x_y_z_w','RideHistoryController@clearAllHistory');
Route::get("/v1/date_time",'SettingController@getDateTime');


Route::group(['prefix' => 'v1',"middleware"=>"auth:api"], function () {

	Route::post('/login','UserController@LoginUser');
    Route::post('/driver/login','UserController@LoginDriver');
    Route::get('/users','UserController')->middleware('auth:api');
    Route::post('/sign_up_user','UserController@SignUpUser');

	Route::post('/user/device_token','UserController@updateUserDeviceToken');
    Route::post('/driver/device_token','UserController@updateDriverDeviceToken');

	Route::get('/rider','RiderController@getRiderByPhoneNumber');
	Route::get('/client','ClientController@getClientByPhoneNumber');

	Route::get('user/delete/{phoneNumber}','UserController@deleteUserByPhoneNumber');

    Route::get("notify/rider/ride_request",'RiderController@rideRequest');

    Route::get("client/history",'RideHistoryController@userHistory');

    Route::get("client/history/specific","RideHistoryController@userSpecificHistory");

    Route::post("ride/history",'RideHistoryController@NewRideHistory');

    Route::post("ride/start","RideHistoryController@StartRide");

    Route::post("ride/finish","RideHistoryController@RideFinishedHistory");

    Route::post("ride/history/update","RideHistoryController@UpdateRideHistory");

    Route::post("client/promo_code/apply",'DiscountController@applyClientPromoCode');

    Route::post("discount/new","DiscountController@NewDiscount");
    Route::post("discount_history/new",'DiscountHistoryController@NewDiscountHistory');
    Route::get("user_discounts",'DiscountController@userDiscounts');
});



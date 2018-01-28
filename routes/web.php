<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['first', 'second'])->group(function () {
  

});

  Route::get('/users','UserController');

//Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/access_token','ApiController@accessToken');

Route::post('/get_access_token','ApiController@getAccessToken');

Route::get('/oauth/scopes',function($response){
	return $response;
});

Auth::routes();


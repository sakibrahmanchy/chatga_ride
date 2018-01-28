<?php
/**
 * Created by PhpStorm.
 * User: Sakib Rahman
 * Date: 11/23/2017
 * Time: 1:56 AM
 */
namespace App\Enumaration;

class UserAuthenticationCodes
{

	public static $USER_NOT_FOUND = "auth/user-not-found";
	public static $NO_RESPONSE = "auth/no-response-from-server";
	public static $VALIDATION_ERROR = "auth/validation-error";
	public static $USER_CREATED_SUCCESSFULLY = "auth/user-created-successfully";
	public static $USER_CREATE_FAILED = "auth/error-occurred-creating-user";
	public static $CLIENT_CREATED_SUCCESSFULLY = "auth/client-created-successfully";
	public static $CLIENT_CREATE_FAILED = "auth/error-occurred-creating-client";
	public static $RIDER_CREATED_SUCCESSFULLY = "auth/rider-created-successfully";
	public static $RIDER_CREATE_FAILED = "auth/error-occurred-creating-rider";
	public static $USER_FOUND = "user-found";
	public static $LOGGED_IN_SUCCESSFULLY = "auth/logged-in-successfully";
	public static $PHONE_VERIFICATION_REQUIRED = "auth/phone-verification-required";
	public static $INVALID_LOGIN_REQUEST = "auth/invalid-login-request";

}

?>
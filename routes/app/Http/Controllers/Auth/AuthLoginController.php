<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Auth\LoginProxy;
use Illuminate\Http\Request;

class LoginController extends Controller
{
	private $loginProxy;

    public function __construct(LoginProxy $loginProxy)
    {
        $this->loginProxy = $loginProxy;
    }

    public function login(Request $request)
    {

	    $this->validate($request,[
		    "email" => "required|email",
		    "password" => "required"
	    ]);

        $email = $request->get('email');
        $password = $request->get('password');

        return $this->response($this->loginProxy->attemptLogin($email, $password));
    }

    public function refresh(Request $request)
    {
        return $this->response($this->loginProxy->attemptRefresh());
    }

    public function logout()
    {
        $this->loginProxy->logout();

        return $this->response(null, 204);
    }
}

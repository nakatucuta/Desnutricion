<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class LoginController extends Controller
{

     public function username()   //1.coloca esta funcion para cambiar el login
 {
     return 'codigohabilitacion';
  }




// public function login(Request $request)
// {
//     $this->validateLogin($request);

//     // Se usa el campo "codigohabilitacion" en lugar de "email" o "username"
//     $credentials = [$this->username() => $request->codigohabilitacion, 'password' => $request->password];

//     if (Auth::attempt($credentials, $request->filled('remember'))) {
//         $request->session()->regenerate();

//         return redirect()->intended('/');
//     }

//     throw ValidationException::withMessages([
//         $this->username() => [trans('auth.failed')],
//     ]);
// }
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    // }
}

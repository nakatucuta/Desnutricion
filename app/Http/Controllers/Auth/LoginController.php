<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    // Mantén tu campo original. No se usará para validar, pero no estorba.
    public function username()
    {
        return request()->filled('email') ? 'email' : 'codigohabilitacion';
    }

    /**
     * Reglas: debe venir email O codigohabilitacion (al menos uno), más password.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email'              => 'nullable|email|required_without:codigohabilitacion',
            'codigohabilitacion' => 'nullable|string|required_without:email',
            'password'           => 'required|string',
        ], [
            'email.required_without'              => 'Ingresa tu correo o tu código de habilitación.',
            'codigohabilitacion.required_without' => 'Ingresa tu correo o tu código de habilitación.',
        ]);
    }

    /**
     * Arma las credenciales según cuál campo llegó.
     * Si llegan ambos, se intentará primero con email.
     */
    protected function credentials(Request $request)
    {
        if ($request->filled('email')) {
            return [
                'email'    => mb_strtolower($request->input('email')),
                'password' => $request->input('password'),
            ];
        }

        return [
            'codigohabilitacion' => $request->input('codigohabilitacion'),
            'password'           => $request->input('password'),
        ];
    }

    /**
     * Permite ingresar por correo o por usuario/código.
     */
    protected function attemptLogin(Request $request)
    {
        $remember = $request->boolean('remember');

        if ($request->filled('email')) {
            return $this->guard()->attempt($this->credentials($request), $remember);
        }

        $loginValue = trim((string) $request->input('codigohabilitacion'));
        $password = (string) $request->input('password');

        return $this->guard()->attempt([
            'codigohabilitacion' => $loginValue,
            'password' => $password,
        ], $remember) || $this->guard()->attempt([
            'name' => $loginValue,
            'password' => $password,
        ], $remember);
    }

    /**
     * Para que el error aparezca en el campo correcto (email o codigohabilitacion).
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $field = $request->filled('email') ? 'email' : 'codigohabilitacion';

        throw ValidationException::withMessages([
            $field => [trans('auth.failed')],
        ]);
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function username()
    {
        return request()->filled('email') ? 'email' : 'codigohabilitacion';
    }

    /**
     * Reglas: debe venir email O codigohabilitacion (solo uno), mas password.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email:rfc|required_without:codigohabilitacion',
            'codigohabilitacion' => 'nullable|string|max:80|required_without:email',
            'password' => 'required|string',
        ], [
            'email.required_without' => 'Ingresa tu correo o tu codigo de habilitacion.',
            'codigohabilitacion.required_without' => 'Ingresa tu correo o tu codigo de habilitacion.',
        ]);

        if ($request->filled('email') && $request->filled('codigohabilitacion')) {
            throw ValidationException::withMessages([
                'email' => ['Usa solo un metodo de ingreso a la vez.'],
                'codigohabilitacion' => ['Usa solo un metodo de ingreso a la vez.'],
            ]);
        }
    }

    /**
     * Arma credenciales segun el metodo de ingreso.
     */
    protected function credentials(Request $request)
    {
        if ($request->filled('email')) {
            return [
                'email' => mb_strtolower(trim((string) $request->input('email'))),
                'password' => $request->input('password'),
            ];
        }

        return [
            'codigohabilitacion' => trim((string) $request->input('codigohabilitacion')),
            'password' => $request->input('password'),
        ];
    }

    /**
     * Permite ingresar por correo o por usuario/codigo.
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
     * Error generico para no revelar si existe o no la cuenta.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $this->logAuthEvent('login_failed', $request);

        $field = $request->filled('email') ? 'email' : 'codigohabilitacion';

        throw ValidationException::withMessages([
            $field => [trans('auth.failed')],
        ]);
    }

    /**
     * Limite de intentos por identificador + IP.
     */
    protected function maxAttempts()
    {
        return 5;
    }

    /**
     * Minutos de bloqueo tras exceder intentos.
     */
    protected function decayMinutes()
    {
        return 10;
    }

    /**
     * Clave de throttling normalizada para evitar bypass entre metodos.
     */
    protected function throttleKey(Request $request)
    {
        $identifier = $request->filled('email')
            ? mb_strtolower(trim((string) $request->input('email')))
            : mb_strtolower(trim((string) $request->input('codigohabilitacion')));

        return Str::lower($identifier . '|' . $request->ip());
    }

    protected function sendLockoutResponse(Request $request)
    {
        $this->logAuthEvent('login_lockout', $request);

        $seconds = $this->limiter()->availableIn($this->throttleKey($request));
        $minutes = (int) ceil($seconds / 60);

        throw ValidationException::withMessages([
            $this->username() => ["Demasiados intentos fallidos. Intenta de nuevo en {$minutes} minuto(s)."],
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        $this->logAuthEvent('login_success', $request, $user->id ?? null);
    }

    private function logAuthEvent(string $event, Request $request, ?int $userId = null): void
    {
        $identifier = $request->filled('email')
            ? mb_strtolower(trim((string) $request->input('email')))
            : trim((string) $request->input('codigohabilitacion'));

        Log::info('auth_event', [
            'event' => $event,
            'user_id' => $userId,
            'identifier_hash' => $identifier !== '' ? hash('sha256', $identifier) : null,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccessControlService;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function username()
    {
        return request()->filled('email') ? 'email' : 'codigohabilitacion';
    }

    public function showLoginForm()
    {
        return view('auth.login');
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
            return Auth::guard()->attempt($this->credentials($request), $remember);
        }

        $loginValue = trim((string) $request->input('codigohabilitacion'));
        $password = (string) $request->input('password');
        $codeCount = (int) DB::table('users')->where('codigohabilitacion', $loginValue)->count();
        $nameCount = (int) DB::table('users')->where('name', $loginValue)->count();

        // Evita iniciar sesion en una cuenta equivocada cuando el codigo/nombre no es unico.
        if ($codeCount > 1 || $nameCount > 1 || ($codeCount > 0 && $nameCount > 0)) {
            $emails = DB::table('users')
                ->where(function ($q) use ($loginValue) {
                    $q->where('codigohabilitacion', $loginValue)
                      ->orWhere('name', $loginValue);
                })
                ->whereNotNull('email')
                ->pluck('email')
                ->map(fn($e) => mb_strtolower(trim((string) $e)))
                ->filter(fn($e) => $e !== '')
                ->unique()
                ->values();

            $emailsMsg = $emails->isNotEmpty()
                ? ' Correos asociados: ' . $emails->implode(', ') . '.'
                : ' No hay correos asociados visibles para este identificador.';

            $payload = [
                'codigohabilitacion' => ['El codigo/usuario no es unico. Ingresa con correo para acceder a la cuenta correcta.' . $emailsMsg],
            ];
            if ($emails->isNotEmpty()) {
                $payload['login_email_suggestions'] = $emails->all();
            }

            throw ValidationException::withMessages([
                ...$payload,
            ]);
        }

        if ($codeCount === 1) {
            return Auth::guard()->attempt([
                'codigohabilitacion' => $loginValue,
                'password' => $password,
            ], $remember);
        }

        if ($nameCount === 1) {
            return Auth::guard()->attempt([
                'name' => $loginValue,
                'password' => $password,
            ], $remember);
        }

        return false;
    }

    /**
     * Error generico para no revelar si existe o no la cuenta.
     */
    public function login(Request $request): RedirectResponse
    {
        $this->validateLogin($request);

        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts())) {
            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            $request->session()->regenerate();
            RateLimiter::clear($this->throttleKey($request));
            $this->authenticated($request, Auth::user());

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        RateLimiter::hit($this->throttleKey($request), $this->decayMinutes() * 60);

        return $this->sendFailedLoginResponse($request);
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
        app(AccessControlService::class)->recordAuthEvent('login_lockout', $request, null, [
            'source' => 'web_login',
        ]);

        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        $minutes = (int) ceil($seconds / 60);
        $max = (int) $this->maxAttempts();

        throw ValidationException::withMessages([
            $this->username() => ["Demasiados intentos fallidos ({$max}/{$max}). Tu acceso esta bloqueado temporalmente. Intenta de nuevo en {$minutes} minuto(s)."],
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        app(AccessControlService::class)->recordAuthEvent('login_success', $request, $user, [
            'source' => 'web_login',
        ]);
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            app(AccessControlService::class)->recordAuthEvent('logout', $request, $user, [
                'source' => 'web_logout',
            ]);
        }

        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        app(AccessControlService::class)->recordAuthEvent('login_failed', $request, null, [
            'source' => 'web_login',
        ]);

        $field = $request->filled('email') ? 'email' : 'codigohabilitacion';
        $attempts = (int) RateLimiter::attempts($this->throttleKey($request));
        $max = (int) $this->maxAttempts();
        $remaining = max(0, $max - $attempts);

        throw ValidationException::withMessages([
            $field => [trans('auth.failed') . " Intento {$attempts} de {$max}. Te quedan {$remaining} intento(s) antes del bloqueo."],
        ]);
    }
}

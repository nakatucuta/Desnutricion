<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Reglas de contrasena fortalecidas para estandar moderno.
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email:rfc',
            'password' => [
                'required',
                'confirmed',
                Password::min(10)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    protected function sendResetResponse(Request $request, $response)
    {
        Log::info('password_reset_completed', [
            'email_hash' => hash('sha256', mb_strtolower(trim((string) $request->input('email')))),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('login')->with('status', trans($response));
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        Log::warning('password_reset_failed', [
            'email_hash' => hash('sha256', mb_strtolower(trim((string) $request->input('email')))),
            'ip' => $request->ip(),
            'status' => $response,
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => [trans($response)]]);
    }
}

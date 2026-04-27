<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Support\Str;
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

    public function showResetForm(Request $request, ?string $token = null)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate($this->rules());

        $status = PasswordFacade::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === PasswordFacade::PASSWORD_RESET) {
            return $this->sendResetResponse($request, $status);
        }

        return $this->sendResetFailedResponse($request, $status);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Envia el enlace de recuperacion con controles anti-abuso y logging.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email:rfc',
        ]);

        $email = mb_strtolower(trim((string) $request->input('email')));
        $rateKey = 'password-reset|' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);
            $minutes = (int) ceil($seconds / 60);

            throw ValidationException::withMessages([
                'email' => ["Demasiadas solicitudes. Intenta de nuevo en {$minutes} minuto(s)."],
            ]);
        }

        $status = null;
        $lastException = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $status = Password::broker()->sendResetLink(['email' => $email]);
                $lastException = null;
                break;
            } catch (\Throwable $e) {
                $lastException = $e;

                Log::warning('password_reset_mail_attempt_failed', [
                    'email_hash' => hash('sha256', $email),
                    'ip' => $request->ip(),
                    'attempt' => $attempt,
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
            }
        }

        if ($lastException !== null) {
            RateLimiter::hit($rateKey, 900);

            Log::error('password_reset_mail_exception', [
                'email_hash' => hash('sha256', $email),
                'ip' => $request->ip(),
                'message' => $lastException->getMessage(),
                'exception' => get_class($lastException),
            ]);

            throw ValidationException::withMessages([
                'email' => ['No fue posible enviar el correo en este momento. Intenta nuevamente.'],
            ]);
        }

        RateLimiter::hit($rateKey, 900);

        // Evita enumeracion de usuarios: mismo mensaje exista o no el correo.
        Log::info('password_reset_requested', [
            'email_hash' => hash('sha256', $email),
            'ip' => $request->ip(),
            'status' => $status,
        ]);

        return back()->with('status', 'Si el correo existe en el sistema, recibiras un enlace de restablecimiento.');
    }
}

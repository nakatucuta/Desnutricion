<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected string $sessionExpiredMessage = 'Tu sesion expiro por inactividad. Inicia sesion nuevamente.';

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (TokenMismatchException $e, Request $request) {
            return $this->sessionExpiredResponse($request, 419);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 419) {
                return $this->sessionExpiredResponse($request, 419);
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->sessionExpiredResponse($request, 401);
    }

    protected function sessionExpiredResponse(Request $request, int $statusCode)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error' => 'session_expired',
                'message' => $this->sessionExpiredMessage,
                'redirect' => route('login'),
            ], $statusCode);
        }

        return redirect()->guest(route('login'))
            ->with('session_expired', $this->sessionExpiredMessage);
    }
}

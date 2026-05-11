<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !(bool) ($user->force_password_change ?? false)) {
            return $next($request);
        }

        $allowedRouteNames = [
            'profile.edit',
            'profile.password.update',
            'logout',
        ];

        if (in_array((string) $request->route()?->getName(), $allowedRouteNames, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Debes cambiar tu contrasena antes de continuar.',
            ], 423);
        }

        return redirect()
            ->route('profile.edit')
            ->with('warning', 'Por seguridad debes cambiar tu contrasena antes de continuar.');
    }
}

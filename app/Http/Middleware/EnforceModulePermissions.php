<?php

namespace App\Http\Middleware;

use App\Services\AccessControlService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceModulePermissions
{
    public function __construct(private readonly AccessControlService $accessControl)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        // Permitir rutas de gestion de accesos para no generar loops.
        if ($request->routeIs('access.*') || $request->routeIs('access-control.*') || $request->routeIs('access-requests.*')) {
            return $next($request);
        }

        if (
            $this->accessControl->isGesExclusiveUser($user)
            && !$this->accessControl->isAdministratorUsertype($user)
            && !$this->accessControl->gesAllowedRequest($request)
        ) {
            return $this->deny($request, AccessControlService::GESTANTES_ACCESS, 'Tu usuario solo puede operar modulos de gestantes.');
        }

        $permissionCode = $this->accessControl->resolvePermissionForRequest($request);
        if (!$permissionCode) {
            return $next($request);
        }

        if ($this->accessControl->canAccessPermission($user, $permissionCode)) {
            return $next($request);
        }

        return $this->deny($request, $permissionCode);
    }

    private function deny(Request $request, string $permissionCode, ?string $message = null)
    {
        $message = $message ?: 'No tienes permiso para este modulo.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
                'permission' => $permissionCode,
            ], 403);
        }

        return redirect()->route('access.denied', [
            'permission' => $permissionCode,
            'from' => $request->fullUrl(),
        ])->with('error1', $message);
    }
}

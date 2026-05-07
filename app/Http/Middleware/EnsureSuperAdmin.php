<?php

namespace App\Http\Middleware;

use App\Services\AccessControlService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    public function __construct(private readonly AccessControlService $accessControl)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || !$this->accessControl->isSuperAdmin($user)) {
            abort(403, 'Acceso restringido al superadministrador.');
        }

        return $next($request);
    }
}

<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Services\AccessControlService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /** @var AccessControlService $access */
        $access = app(AccessControlService::class);
        try {
            $access->ensureCatalogExists();
        } catch (\Throwable $e) {
            // Evita cortar el arranque si la tabla aun no existe o hay un problema temporal de DB.
        }

        //Esta Gate habilita la estadisticas solo a los usuarios tipo 1 y tipo 3, directiva can:view-statistics en adminlte.php
        Gate::define('view-statistics', function ($user){
            return $user->usertype == 1 || $user->usertype == 3; 
        });

        Gate::define('access-gestantes', function ($user) use ($access) {
            return $access->canAccessPermission($user, AccessControlService::GESTANTES_ACCESS);
        });

        Gate::define('access-pai', function ($user) use ($access) {
            return $access->canAccessPermission($user, AccessControlService::PAI_ACCESS);
        });

        Gate::define('access-nutricional', function ($user) use ($access) {
            return $access->canAccessPermission($user, AccessControlService::NUTRICIONAL_ACCESS);
        });

        Gate::define('access-tamizajes', function ($user) use ($access) {
            return $access->canAccessPermission($user, AccessControlService::TAMIZAJES_ACCESS);
        });

        Gate::define('access-ciclosvida', function ($user) use ($access) {
            return $access->canAccessPermission($user, AccessControlService::CICLOSVIDA_ACCESS);
        });

        Gate::define('access-non-ges', function ($user) use ($access) {
            return !$access->isGesExclusiveUser($user)
                || $access->isSuperAdmin($user)
                || $access->isAdministratorUsertype($user);
        });

        Gate::define('manage-access-control', function ($user) use ($access) {
            return $access->isSuperAdmin($user);
        });
    }
}

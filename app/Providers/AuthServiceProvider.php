<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
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


        //Esta Gate habilita la estadisticas solo a los usuarios tipo 1 y tipo 3, directiva can:view-statistics en adminlte.php
        Gate::define('view-statistics', function ($user){
            return $user->usertype == 1 || $user->usertype == 3; 
        });

        //
    }
}

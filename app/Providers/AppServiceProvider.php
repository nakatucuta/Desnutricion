<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; 
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        // SQL Server: fuerza formato de fecha no ambiguo para evitar errores
        // de conversion nvarchar -> datetime en inserts/framework internals.
        try {
            if (config('database.default') === 'sqlsrv') {
                DB::connection('sqlsrv')->statement("SET DATEFORMAT ymd");
            }
        } catch (\Throwable $e) {
            // Evita romper el arranque si la conexion no esta disponible.
        }
    }
}

<?php

namespace App\Providers;

use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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

        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            if (($event->connection->getDriverName() ?? null) !== 'sqlsrv') {
                return;
            }

            try {
                $event->connection->statement("SET DATEFORMAT ymd");
            } catch (\Throwable $e) {
                // Evita romper el request si no se puede ejecutar.
            }
        });

        // SQL Server: fuerza formato de fecha no ambiguo para evitar errores
        // de conversion nvarchar -> datetime en inserts/framework internals.
        $connections = (array) config('database.connections', []);
        foreach ($connections as $name => $cfg) {
            if (($cfg['driver'] ?? null) !== 'sqlsrv') {
                continue;
            }

            try {
                DB::connection($name)->statement("SET DATEFORMAT ymd");
            } catch (\Throwable $e) {
                // Evita romper el arranque si la conexion no esta disponible.
            }
        }
    }
}

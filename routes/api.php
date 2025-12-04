<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SivigilaController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes (Privadas con Sanctum)
|--------------------------------------------------------------------------
| Base: https://app.epsianaswayuu.com/api
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])
    ->name('api.login');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // ✅ Endpoint actual (NO se toca)
    Route::get('/afiliados', [SivigilaController::class, 'index_api'])
        ->name('api.afiliados');

    // ✅ NUEVO: solo nuevos
    Route::get('/afiliados/nuevos', [SivigilaController::class, 'index_api_nuevos'])
        ->name('api.afiliados.nuevos');

    // ✅ Opcional: reset del cursor
    Route::post('/afiliados/reset', [SivigilaController::class, 'reset_consumo_afiliados'])
        ->name('api.afiliados.reset');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api.user');
});

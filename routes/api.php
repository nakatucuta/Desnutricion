<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SivigilaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ApiludiconController;
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



    // ✅ POR numeroCarnet (código único)
    Route::get('/ludycom/afiliados/carnet/{numeroCarnet}', [ApiludiconController::class, 'show_by_numeroCarnet'])
        ->name('api.ludycom.afiliados.carnet');


     // ✅ NUEVAS rutas LUDYCOM (ApiludiconController)
    Route::get('/ludycom/afiliados', [ApiludiconController::class, 'index_all'])
        ->name('api.ludycom.afiliados.all');

    Route::get('/ludycom/afiliados/nuevos', [ApiludiconController::class, 'index_all_nuevos'])
        ->name('api.ludycom.afiliados.nuevos');

    Route::post('/ludycom/afiliados/reset', [ApiludiconController::class, 'reset_consumo_all'])
        ->name('api.ludycom.afiliados.reset');

    Route::get('/ludycom/afiliados/identificacion/{identificacion}', [ApiludiconController::class, 'show_by_identificacion'])
        ->name('api.ludycom.afiliados.identificacion');
});





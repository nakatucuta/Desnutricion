<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SivigilaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se definen las rutas API que serán accesibles desde el exterior,
| bajo la URL base https://app.epsianaswayuu.com/api
|
*/

// ✔️ Ruta pública para consumo desde apps externas
Route::get('/sivigila/data', [SivigilaController::class, 'data']);

// ✔️ Ruta protegida con token (opcional, si usas Sanctum)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

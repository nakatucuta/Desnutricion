<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SivigilaController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\RevisionController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('sivigila', SivigilaController::class)->middleware('auth');

Route::resource('revision', RevisionController::class);

Route::resource('Seguimiento', SeguimientoController::class)->middleware('auth');
Route::get('/search1', 'App\Http\Controllers\SeguimientoController@search')->name('BUSCADOR1');
Route::get('/sivigila/{num_ide_}/{fec_not}/create', 'App\Http\Controllers\SivigilaController@create')->name('detalle_sivigila');
Route::get('/search', 'App\Http\Controllers\SivigilaController@search')->name('BUSCADOR');
Route::get('/alert', 'App\Http\Controllers\SeguimientoController@alerta')->name('ALERTA');

//rutas para reportes en excel
Route::get('/report', [SeguimientoController::class,'resporte'])->name('export');
Route::get('/report1', [IngresoController::class,'reporte'])->name('export1');
Route::get('/report2', [SivigilaController::class,'reporte1'])->name('export2');
Route::get('/report3', [SeguimientoController::class,'reporte2'])->name('export3');

//RUTAS PDF
Route::get('revision/{id}/reporte','App\Http\Controllers\RevisionController@reportepdf')->name('pdfcertificado');

//RUTAS PARA JALAR DATOS DE SEGUIMIENTOS Y HACER EL VISTO BUENO
Route::get('/revision/{id}/create', 'App\Http\Controllers\RevisionController@create')->name('detalle_revisiones');

//RUTA PARA VER UN DETALLE DE SEGUIMIENTO
Route::get('/Seguimiento/{id}/detail', 'App\Http\Controllers\SeguimientoController@detail')->name('detalleseguimiento');



// Ruta para ver el PDF
Route::get('/seguimiento/view-pdf/{id}', 'App\Http\Controllers\SeguimientoController@viewPDF')->name('seguimiento.view-pdf');

//ruta para las graficas
Route::get('/grafica-barras', [SeguimientoController::class, 'graficaBarras'])->name('grafica.barras');

Route::get('/grafica-torta-clasificacion', [SeguimientoController::class, 'graficaTortaClasificacion'])->name('grafica.torta.clasificacion');


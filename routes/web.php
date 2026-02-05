<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SivigilaController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\SeguimientoOcasionalController;
use App\Http\Controllers\Seguimiento412Controller;
use App\Http\Controllers\Cargue412Controller;
use App\Http\Controllers\AfiliadoController;
use App\Http\Controllers\EmailController;
use App\Exports\vacunaExport;
use Illuminate\Http\Request;
use App\Http\Controllers\TamizajeController;
use App\Http\Controllers\TamizajePdfController;
use App\Http\Controllers\GesTipo1Controller;
use App\Http\Controllers\GesTipo3Controller;
use App\Http\Controllers\MaestroSiv549Controller;
use App\Http\Controllers\AsignacionesMaestrosiv549Controller;
use App\Http\Controllers\SeguimientosHubController;
use App\Http\Controllers\SeguimientMaestrosiv549Controller;
use App\Http\Controllers\CicloVidaController;
use App\Http\Controllers\PiPlaceholderController;
use App\Http\Controllers\GesTipo1SeguimientoController;
use App\Http\Controllers\PreconcepcionalController;
use App\Http\Controllers\FormatosController;
use App\Http\Controllers\GestantesStatsController;
use App\Http\Controllers\AlertasController;

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

//OJO AQUI COMIENZAN LAS RUTAS  DE PAI
Route::get('/afiliado', [AfiliadoController::class, 'index'])->name('afiliado')
->middleware('auth');


// Rutas para el cargue de la información
 Route::get('/import-excel_2', [AfiliadoController::class, 'showImportForm'])->name('import-excel-form_2');//->middleware('auth');
Route::post('/import-excel_2', [AfiliadoController::class, 'importExcel'])
    ->name('import-excel_2')
    ->middleware(['auth', \App\Http\Middleware\IncreaseExecutionTime::class]);
   // ->middleware([\App\Http\Middleware\IncreaseExecutionTime::class]);


// Ruta para obtener vacunas
Route::get('/vacunas/{id}/{numeroCarnet?}', [AfiliadoController::class, 'getVacunas'])
    ->name('getVacunas');


// // Ruta para el reporte Excel
// Route::get('export-vacunas', function () {
//     return Excel::download(new vacunaExport, 'vacunas.xlsx');
// });

// Ruta para el reporte Excel con fechas
// Ruta para el reporte Excel con fechas
Route::get('export-vacunas', [AfiliadoController::class, 'exportVacunas'])
     ->name('exportVacunas');



// Ruta para eliminar registros
Route::delete('/batch_verifications/{id}', [AfiliadoController::class, 'destroy'])->name('batch_verifications.destroy');


//AQUI TERMINAN LAS  RUTAS  DE PAI



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth', \App\Http\Middleware\IncreaseExecutionTime::class])->group(function(){
    // 👉 Endpoint AJAX (ponle /sivigila/data para que no choque con resource)
    Route::get('sivigila/data', [SivigilaController::class, 'data'])
         ->name('sivigila.data');

    // 👉 Resource normal
    Route::resource('sivigila', SivigilaController::class);
});


Route::resource('revision', RevisionController::class);

Route::resource('seguimiento_ocasional', SeguimientoOcasionalController::class)->middleware('auth');
Route::resource('new412', Cargue412Controller::class);
Route::resource('new412_seguimiento', Seguimiento412Controller::class);

// Ruta adicional para DataTables (AJAX)
Route::get('new412_seguimiento-data', [Seguimiento412Controller::class, 'data'])
     ->name('new412_seguimiento.data');
     
Route::get('/new412/{id}/{numero_identificacion}/edit', [Cargue412Controller::class, 'edit'])->name('editvariables');


// Ruta para ver el PDF
Route::get('/seguimiento/view-pdf/{id}', 'App\Http\Controllers\SeguimientoController@viewPDF')->name('seguimiento.view-pdf');


// web.php
Route::get('/Seguimiento/data', [SeguimientoController::class, 'data'])
     ->name('Seguimiento.data')
     ->middleware('auth');

Route::resource('Seguimiento', SeguimientoController::class)
     ->middleware('auth');
     
     // Para que Select2 AJAX busque pacientes
Route::get('Seguimiento/search-pacientes', [SeguimientoController::class, 'searchPacientes'])
->name('seguimiento.searchPacientes')
->middleware('auth');


Route::get('/search1', 'App\Http\Controllers\SeguimientoController@search')->name('BUSCADOR1');
Route::get(
    '/sivigila/{num_ide}/{fec_not}/create',
    [SivigilaController::class, 'create']
)->name('detalle_sivigila');
Route::get('/search', 'App\Http\Controllers\SivigilaController@search')->name('BUSCADOR');
Route::get('/alert', 'App\Http\Controllers\SeguimientoController@alerta')->name('ALERTA');

//rutas para reportes en excel
Route::get('/report', [SeguimientoController::class,'resporte'])->name('export');
Route::get('/report1', [IngresoController::class,'reporte'])->name('export1');
Route::get('/report2', [SivigilaController::class,'reporte1'])->name('export2');
Route::get('/report3', [SeguimientoController::class,'reporte2'])->name('export3');
Route::get('/report4', [SivigilaController::class,'reSporte2'])->name('export4');
Route::get('/report5', [SivigilaController::class,'reSporte_sinseguimiento'])->name('export5');
Route::get('/report6', [Seguimiento412Controller::class,'reporte_seguimiento412'])->name('export6');
Route::get('/report7', [Cargue412Controller::class,'reporte1cargue412'])->name('export7');

//RUTAS PDF
Route::get('revision/{id}/reporte','App\Http\Controllers\RevisionController@reportepdf')->name('pdfcertificado');

//RUTAS PARA JALAR DATOS DE SEGUIMIENTOS Y HACER EL VISTO BUENO
Route::get('/revision/{id}/create', 'App\Http\Controllers\RevisionController@create')->name('detalle_revisiones');

//RUTA PARA VER UN DETALLE DE SEGUIMIENTO
Route::get('/Seguimiento/{id}/detail', 'App\Http\Controllers\SeguimientoController@detail')->name('detalleseguimiento');




// Ruta para ver el PDF seguimiento 412
Route::get('/seguimiento_412/view-pdf/{id}', 'App\Http\Controllers\Seguimiento412Controller@viewPDF')->name('seguimiento.view-pdf_412');

//ruta para las graficas
Route::get('/grafica-barras', [SeguimientoController::class, 'graficaBarras'])->name('grafica.barras');

Route::get('/grafica-torta-clasificacion', [SeguimientoController::class, 'graficaTortaClasificacion'])->name('grafica.torta.clasificacion');

// Route::get('/import', 'App\Http\Controllers\SivigilaController@import');



Route::get('/nuevo', 'App\Http\Controllers\SivigilaController@create1')->name('create11');


// Route::get('seguimiento_ocasional/create/{id}', 'App\Http\Controllers\SeguimientoOcasionalController@create')->name('seguimiento_ocasional.create');


//RUTAS PARA EL CARGUE DE LA  INFORMACION
// Formulario + DataTable
Route::get('/import-excel', [Cargue412Controller::class, 'showImportForm'])
     ->name('import-excel-form')
     ->middleware('auth');

// Endpoint AJAX
Route::get('/import-excel/data', [Cargue412Controller::class, 'getData'])
     ->name('import-excel-data')
     ->middleware('auth');
Route::post('/import-excel', [Cargue412Controller::class, 'importExcel'])->name('import-excel')->middleware('auth');

//RUTA PARA DESCARGAR MANUAL

Route::get('/download-pdf', 'App\Http\Controllers\SivigilaController@downloadPdf')->name('download.pdf');


// RUTA PARA CARGARA LA TABLA POR  JSON
// Route::get('/obtener-datos-tabla', 'SivigilaController@obtenerDatos')->name('obtener.datos.tabla');

//RUTAS PARA LAS TABLAS CON JAVA SCRIPT Y DATATABLE
Route::get('seguimiento/data', [SeguimientoController::class, 'getData'])->name('seguimiento.data');
// Route::get('/obtener-datos-tabla', [SivigilaController::class, 'getData1'])->name('sivigila.data');
Route::post('/sivigila/check-status', [App\Http\Controllers\SivigilaController::class, 'checkStatus'])->name('sivigila.checkStatus');
Route::post('/sivigila/check-ipsprimaria', [App\Http\Controllers\SivigilaController::class, 'checkIpsPrimaria'])->name('sivigila.ipsprimaria');


//RURTA PARA JSON DE ESTADISTICAS   DE LAS TABLAS QUE MUESTRAN PARA LOS INDICADORES
Route::get('/detalle-prestador/{id}', [SeguimientoController::class, 'detallePrestador'])->name('gedetalle_prestador');

Route::get('/detalle-prestador-113/{id}', [SeguimientoController::class, 'detallePrestador_113'])->name('detallePrestador_113');

//RUTA  PARA ENVIO DE CORREO DE  PAI 
Route::post('/send-email', [AfiliadoController::class, 'sendEmail'])->name('send.email');


Route::get('/descargar-formato', [AfiliadoController::class, 'downloadExcel'])->name('download.excel');


//RUTA PARA EL BUSCADOR DINAMICO EN PAI
Route::get('/buscar-afiliados', [AfiliadoController::class, 'buscarAfiliados'])->name('buscar.afiliados');


//rRUTA BUSCADOR DINAMICO EN SEGUIMIENTO
Route::get('/buscar-seguimiento', [SeguimientoController::class, 'buscarSeguimiento'])
     ->name('buscar.seguimiento');

// Ruta para filtrar (puedes usar "index", pero debes retornar JSON si es AJAX)
Route::get('/filtrar-seguimiento', [SeguimientoController::class, 'filtrarSeguimiento'])
     ->name('filtrar.seguimiento');


// RUTAS TAMIZAJE
Route::get('/excel-import', [TamizajeController::class, 'index'])->name('excel.import.index')->middleware('auth');
Route::post('/excel-import', [TamizajeController::class, 'import'])->name('excel.import')->middleware('auth');

// Nueva ruta para la vista de la tabla
// listado con Datatables
Route::get('/excel-import/table', [TamizajeController::class, 'table'])
     ->name('excel.import.table')
     ->middleware('auth');

// detalle de un tamizaje
Route::get('/tamizajes/{tamizaje}', [TamizajeController::class, 'show'])
     ->name('tamizajes.show')
     ->middleware('auth');


// Nueva ruta para generar el Excel
Route::post('/excel-import/generate-excel', [TamizajeController::class, 'generateExcel'])
     ->name('excel.import.generate-excel')->middleware('auth');




     // Formulario para subir el ZIP
// Primero la ruta “fija”
Route::get('/tamizajes/upload-zip', [TamizajePdfController::class, 'showUploadForm'])
    ->name('tamizajes.upload-zip.form')
    ->middleware('auth');

Route::post('/tamizajes/upload-zip', [TamizajePdfController::class, 'handleZipUpload'])
    ->name('tamizajes.upload-zip')
    ->middleware('auth');

// Después la ruta genérica
Route::get('/tamizajes/{tamizaje}', [TamizajeController::class, 'show'])
    ->where('tamizaje', '[0-9]+')   // opcionalmente le ponemos restricción numérica
    ->name('tamizajes.show')
    ->middleware('auth');

    
    Route::get('/tamizajes/{numero}/pdfs', [TamizajePdfController::class, 'showPdfsByPerson'])
    ->name('tamizajes.show-pdfs')
    ->middleware('auth');


//ruta para descargar manual y formato
Route::get('/descargar-formato1', [TamizajeController::class, 'downloadExcel1'])->name('download.excel1');


// RUTAS  GESTANTES


Route::get('gestantes/import', [GesTipo1Controller::class, 'showImportForm'])
     ->name('ges_tipo1.import.form');

Route::post('gestantes/import', [GesTipo1Controller::class, 'import'])
     ->name('ges_tipo1.import');

     Route::get('gestantes', [GesTipo1Controller::class, 'index'])
     ->name('ges_tipo1.index');
   Route::get('gestantes/{ges}', [GesTipo1Controller::class, 'show'])
     ->name('ges_tipo1.show');


     //tipo 3 

     Route::get('gestantes/tipo3/import', [GesTipo3Controller::class,'showImportForm'])
     ->name('ges_tipo3.import.form');

Route::post('gestantes/tipo3/import', [GesTipo3Controller::class,'import'])
     ->name('ges_tipo3.import');

     //RUTAS REPORTE EN EXCEL
Route::get('gestantes/export/tipo1', [GesTipo1Controller::class, 'exportTipo1'])->name('ges_tipo1.export');
Route::get('gestantes/export/tipo3', [GesTipo3Controller::class, 'exportTipo3'])->name('ges_tipo3.export');


//RUTAS PARA LA 549
Route::get('/asignaciones-maestrosiv549/create', [AsignacionesMaestrosiv549Controller::class, 'create'])->name('asignaciones-maestrosiv549.create');

Route::get('/maestrosiv549', [MaestroSiv549Controller::class, 'index'])->name('maestrosiv549.index');
Route::get('/maestrosiv549/data', [MaestroSiv549Controller::class, 'data'])->name('maestrosiv549.data');

//RUTAS PARA ASIGNAR DE LA  549  AMIS TABLAS
// Route::get('/asignaciones-maestrosiv549/create', [\App\Http\Controllers\AsignacionesMaestrosiv549Controller::class, 'create'])->name('asignaciones-maestrosiv549.create');
// Route::post('/asignaciones-maestrosiv549/store', [\App\Http\Controllers\AsignacionesMaestrosiv549Controller::class, 'store'])->name('asignaciones-maestrosiv549.store');

Route::resource('asignaciones_maestrosiv549', AsignacionesMaestrosiv549Controller::class)->only([
    'create', 'store'
]);
//RUTAS PARA EL REPORTE EN EXCEL DE LA  549
Route::get('/reportes/maestro-siv549/export', [MaestroSiv549Controller::class, 'export'])
    ->name('reportes.maestrosiv549.export')
    ->middleware('auth');

// === HUB DE SEGUIMIENTOS ===
Route::prefix('seguimientos1ges')->name('seguimientos.')->group(function () {
    Route::get('/', [SeguimientosHubController::class, 'index'])->name('index');
    Route::get('/asignados/data', [SeguimientosHubController::class, 'dataAsignados'])->name('asignados.data');
    Route::get('/realizados/data', [SeguimientosHubController::class, 'dataRealizados'])->name('realizados.data');
    // NUEVO: alertas
    Route::get('/alertas/data', [SeguimientosHubController::class, 'dataAlertas'])->name('alertas.data');
});

// === CRUD de seguimientos anidado a la asignación (para crear/editar) ===
Route::prefix('asignaciones/{asignacion}')
    ->name('asignaciones.')
    ->group(function () {
        Route::get('seguimientmaestrosiv549/create', [SeguimientMaestrosiv549Controller::class, 'create'])->name('seguimientmaestrosiv549.create');
        Route::post('seguimientmaestrosiv549',        [SeguimientMaestrosiv549Controller::class, 'store'])->name('seguimientmaestrosiv549.store');

        Route::get('seguimientmaestrosiv549/{seguimiento}/edit', [SeguimientMaestrosiv549Controller::class, 'edit'])->name('seguimientmaestrosiv549.edit');
        Route::put('seguimientmaestrosiv549/{seguimiento}',      [SeguimientMaestrosiv549Controller::class, 'update'])->name('seguimientmaestrosiv549.update');
        Route::delete('seguimientmaestrosiv549/{seguimiento}',   [SeguimientMaestrosiv549Controller::class, 'destroy'])->name('seguimientmaestrosiv549.destroy');
    });


    Route::get('seguimientos1ges/export', [SeguimientosHubController::class, 'exportExcel'])
    ->name('seguimientos.export');


    // ciclo de vidas

Route::middleware(['auth'])->group(function () {
    Route::get('/ciclos-vida', [CicloVidaController::class, 'index'])->name('ciclosvida.index');

    Route::get('/ciclos-vida/primera-infancia/opciones', [CicloVidaController::class, 'menuPrimeraInfancia'])->name('ciclosvida.pi.menu');
    Route::get('/ciclos-vida/infancia/opciones',          [CicloVidaController::class, 'menuInfancia'])->name('ciclosvida.infancia.menu');
    Route::get('/ciclos-vida/adolescencia/opciones',      [CicloVidaController::class, 'menuAdolescencia'])->name('ciclosvida.adolescencia.menu');
    Route::get('/ciclos-vida/juventud/opciones',          [CicloVidaController::class, 'menuJuventud'])->name('ciclosvida.juventud.menu');
    Route::get('/ciclos-vida/adultez/opciones',           [CicloVidaController::class, 'menuAdultez'])->name('ciclosvida.adultez.menu');
    Route::get('/ciclos-vida/vejez/opciones',             [CicloVidaController::class, 'menuVejez'])->name('ciclosvida.vejez.menu');

    // === NUEVAS rutas principales de Primera Infancia ===
    Route::get('/pi/medica',       [CicloVidaController::class,'piPlaceholder'])->name('pi.medica.index')->defaults('key','medica');  
    //ATENCIONPOR ENFERMERIA RUTAS 
     Route::get('/ciclos-vida/enfermeria', [CicloVidaController::class, 'enfermeria'])->name('ciclosvida.enfermeria');
     Route::get('/ciclos-vida/enfermeria/data', [CicloVidaController::class, 'enfermeriaData'])->name('ciclosvida.enfermeria.data');

    // === Encabezados seleccionables ===
   // Vista
Route::get('/pi/bucal', [CicloVidaController::class,'pibucal'])
    ->name('pi.bucal.index')
    ->defaults('key','bucal');

// DataTables (server-side)
Route::get('/pi/bucal/data', [CicloVidaController::class,'bucalData'])
    ->name('pi.bucal.data');

   
    Route::get('/pi/nutri',        [CicloVidaController::class,'piPlaceholder'])->name('pi.nutri.index')->defaults('key','nutri');

    // === Opciones Salud Bucal ===
   // Vista:
Route::get('/pi/bucal/fluor/sem1', [CicloVidaController::class,'pifluor'])->name('pi.bucal.fluor.sem1')->defaults('key','bucal_fluor_sem1');

// Endpoint de datos (server-side DataTables):
Route::get('/pi/bucal/fluor/sem1/data', [CicloVidaController::class,'pifluorData'])->name('pi.bucal.fluor.sem1.data');

//     Route::get('/pi/bucal/fluor/sem2', [CicloVidaController::class,'piPlaceholder'])->name('pi.bucal.fluor.sem2')->defaults('key','bucal_fluor_sem2');
    // Vista:
Route::get('/pi/bucal/placa/sem1', [CicloVidaController::class,'piplaca']) ->name('pi.bucal.placa.sem1') ->defaults('key','bucal_placa_sem1');

// Datos (server-side):
Route::get('/pi/bucal/placa/sem1/data', [CicloVidaController::class,'piplacaData'])->name('pi.bucal.placa.sem1.data');

//     Route::get('/pi/bucal/placa/sem2', [CicloVidaController::class,'piPlaceholder'])->name('pi.bucal.placa.sem2')->defaults('key','bucal_placa_sem2');
 // routes/web.php

// Vista (ya la tienes, la dejo aquí por claridad)
Route::get('/pi/bucal/sellantes', [\App\Http\Controllers\CicloVidaController::class,'pisellante'])
    ->name('pi.bucal.sellantes')
    ->defaults('key','bucal_sellantes');

// Endpoint de datos (server-side DataTables)
Route::get('/pi/bucal/sellantes/data', [\App\Http\Controllers\CicloVidaController::class,'pisellanteData'])
    ->name('pi.bucal.sellantes.data');

    // === Opciones Nutrición / Tamizaje ===
  // Index (ya la tienes)
Route::get('/pi/nutri/hemoglobina', [CicloVidaController::class,'piphemoglobina'])
    ->name('pi.nutri.hemoglobina')
    ->defaults('key','nutri_hemoglobina');

// Endpoint DataTables (nuevo)
Route::get('/pi/nutri/hemoglobina/data', [CicloVidaController::class,'piphemoglobinaData'])
    ->name('pi.nutri.hemoglobina.data')
    ->defaults('key','nutri_hemoglobina');



// Vista
Route::get('/pi/nutri/lactancia', [CicloVidaController::class, 'PIlactancia'])
    ->name('pi.nutri.lactancia')
    ->defaults('key','nutri_lactancia');

// DataTables (Yajra) server-side
Route::get('/pi/nutri/lactancia/data', [CicloVidaController::class, 'PIlactanciaData'])
    ->name('pi.nutri.lactancia.data');


// Vista
Route::get('/pi/nutri/vitamina-a',  [CicloVidaController::class,'pivitaminaa'])
    ->name('pi.nutri.vitamina_a')
    ->defaults('key','nutri_vitamina_a');

// Data (Yajra)
Route::get('/pi/datos/data',  [CicloVidaController::class,'pidatogenerales'])
    ->name('pi.nutri.vitamina_a.data');


// ===== HIERRO =====
// routes/web.php
// === HIERRO (0–5 años) ===
// === HIERRO (0–5 años) ===
// === HIERRO (0–5 años) ===
Route::get('/pi/nutri/hierro', [CicloVidaController::class,'PIhierro'])
    ->name('pi.nutri.hierro')
    ->defaults('key','nutri_hierro');

Route::get('/pi/nutri/hierro/data', [CicloVidaController::class,'PIhierroData'])
    ->name('pi.nutri.hierro.data');


// web.php
Route::get('/pi/nutri/data',  [CicloVidaController::class,'pidatogenerales'])
    ->name('pi.datos');

// NUEVO: endpoint JSON para tarjetas
Route::get('/pi/nutri/resumen', [CicloVidaController::class,'piResumenGenerales'])
    ->name('pi.datos.resumen');


// RUTAS PARA LAS ALERTAS


// Página
// ALERTAS PI
// web.php
Route::get('/pi/nutri/alerta', [CicloVidaController::class,'pialerta'])
    ->name('pi.alertas');

Route::get('/pi/nutri/alerta/data', [CicloVidaController::class,'pialertaData'])
    ->name('pi.alertas.data');

Route::post('/pi/nutri/alerta/email', [CicloVidaController::class,'pialertaEmail'])
    ->name('pi.alertas.email');


    // === Rutas existentes (detalle + data) ===
    Route::get('/ciclos-vida/{slug}', [CicloVidaController::class, 'show'])->name('ciclosvida.show');
    Route::get('/ciclos-vida/{slug}/data', [CicloVidaController::class, 'data'])->name('ciclosvida.data');
});




Route::prefix('ges_tipo1/{ges}')->name('ges_tipo1.')->group(function () {
    Route::get('seguimientos/create',     [GesTipo1SeguimientoController::class, 'create'])->name('seguimientos.create');
    Route::post('seguimientos',           [GesTipo1SeguimientoController::class, 'store'])->name('seguimientos.store');

    Route::get('seguimientos/{seg}/edit', [GesTipo1SeguimientoController::class, 'edit'])->name('seguimientos.edit');
    Route::put('seguimientos/{seg}',      [GesTipo1SeguimientoController::class, 'update'])->name('seguimientos.update');
    Route::delete('seguimientos/{seg}',   [GesTipo1SeguimientoController::class, 'destroy'])->name('seguimientos.destroy');
});

// Route::get('/seguimientos/file/{seg}/{field}', [GesTipo1SeguimientoController::class, 'verArchivo'])
//     ->name('seguimientos.file')
//     ->middleware('auth');

    Route::get('seguimientos/{seg}/archivo/{field}', [GesTipo1SeguimientoController::class, 'verArchivo'])
    ->name('ges_tipo1.seguimientos.archivo')
    ->where('field', '[A-Za-z0-9_]+');



// RUTAS PRECONCEPCIONAL
Route::middleware(['auth'])->group(function () {

    Route::get('/preconcepcional', [PreconcepcionalController::class, 'index'])
        ->name('preconcepcional.index');

    Route::get('/preconcepcional/importar', [PreconcepcionalController::class, 'create'])
        ->name('preconcepcional.import');

    Route::post('/preconcepcional/importar', [PreconcepcionalController::class, 'store'])
        ->name('preconcepcional.store');

    // ✅ IMPORTANTÍSIMO: rutas "fijas" ANTES del {preconcepcional}
    Route::get('/preconcepcional/data', [PreconcepcionalController::class, 'data'])
        ->name('preconcepcional.data');

    Route::get('/preconcepcional/export', [PreconcepcionalController::class, 'export'])
        ->name('preconcepcional.export');

    // ✅ al final el wildcard
    Route::get('/preconcepcional/{preconcepcional}', [PreconcepcionalController::class, 'show'])
        ->name('preconcepcional.show');


          // ✅ LOTES
    Route::get('/preconcepcional/lotes', [PreconcepcionalController::class, 'batches'])->name('preconcepcional.batches');
    Route::get('/preconcepcional/lotes/{batch}', [PreconcepcionalController::class, 'batchShow'])->name('preconcepcional.batches.show');
    Route::delete('/preconcepcional/lotes/{batch}', [PreconcepcionalController::class, 'destroyBatch'])->name('preconcepcional.batches.destroy');

    // ✅ SHOW al final + solo numérico
    Route::get('/preconcepcional/{preconcepcional}', [PreconcepcionalController::class, 'show'])
        ->whereNumber('preconcepcional')
        ->name('preconcepcional.show');
});

//ruta para la descarga de los formatos de gestantes 
Route::middleware(['auth'])->group(function () {
    Route::get('/formatos/descargar', [FormatosController::class, 'download'])
        ->name('formatos.download');
});

//  RUTAS PARA ESTIDISTICAS DE GESTANTES 

Route::middleware(['auth'])->group(function () {
    Route::get('/estadisticas/gestantes', [GestantesStatsController::class, 'index'])
        ->name('gestantes.stats.index');

    Route::get('/estadisticas/gestantes/detalle/{modulo}', [GestantesStatsController::class, 'detail'])
        ->name('gestantes.stats.detail');
});


//RUTAS PARA ALERTAS DE GESTANTES  
Route::middleware(['auth'])->group(function () {

    Route::get('/alertas', [AlertasController::class, 'index'])->name('alertas.index');

    Route::post('/alertas/{alerta}/seen', [AlertasController::class, 'markSeen'])->name('alertas.seen');
    Route::post('/alertas/{alerta}/resolve', [AlertasController::class, 'resolve'])->name('alertas.resolve');

    Route::get('/alertas/{alerta}/pdf', [AlertasController::class, 'pdf'])->name('alertas.pdf');
});
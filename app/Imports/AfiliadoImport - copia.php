<?php

namespace App\Imports;

use App\Models\batch_verifications;
use App\Models\afiliado;
use App\Models\vacuna;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Importa la clase Log
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // Asegúrate de importar Session
use Illuminate\Support\Facades\DB;

//Y LE DEBES  AGREGAR EL WithStartRow PARA QUE FUNCIONE LO DE LA FILAO  QUE EMPIEZE POR ESA FILA 
class AfiliadoImport implements ToModel, WithStartRow
{
    protected $errores = []; // Aquí se guardarán los mensajes de advertencia
    protected $guardar = true; // Indicador de si debemos guardar o no
    protected $filasParaGuardar = []; // Aquí almacenamos los afiliados que pasaron la validación

    //ESTA FUNCION  ES LA QUE CREA LA VERIFICACION Y LA ASIGNAS LAS TABLAS FORANEAS
    
    private $batch_verifications_id; // Almacena el ID único para esta importación

    public function __construct()
    {
        // Creando una única instancia de Batch_verification al inicio de la importación
        $verificacion = new batch_verifications([
            'fecha_cargue' => Carbon::now(),
        ]);
        $verificacion->save();

        // Almacenar el ID para su uso posterior
        $this->batch_verifications_id = $verificacion->id;
    }
      /**
     * Specify the row to start reading data from.
     *
     * @return int
     */

    //  OJO CON ESTAFUNCION LE DIGO DESDE QUE FILA INICIA  AGUARADR
    public function startRow(): int
    {
        return 3;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

      


         
    //
    $responsable = isset($row[251]) ? $row[251] : null;
    $fuen_ingresado_paiweb = isset($row[252]) ? $row[252] : null;
    $motivo_noingreso = isset($row[253]) ? $row[253] : null;
    $observaciones = isset($row[254]) ? $row[254] : null;
    $usuario_activo = Auth::id();
 // Verifica si las fechas son válidas y conviértelas
 $fechaatencion = isset($row[0]) ? Date::excelToDateTimeObject($row[0])->format('Y-m-d') : null;
 $fechaNacimiento = isset($row[7]) ? Date::excelToDateTimeObject($row[7])->format('Y-m-d') : null;
 $fechaProbParto = isset($row[46]) ? Date::excelToDateTimeObject($row[46])->format('Y-m-d') : null;
 $fechaAntecedente = isset($row[48]) ? Date::excelToDateTimeObject($row[48])->format('Y-m-d') : null;
 $tipo_identifi = isset($row[1]) ? (string)$row[1] : null; // Convertir a cadena de texto
$numero_identifi = isset($row[2]) ? (string)$row[2] : null; // Convertir a cadena de texto




$afiliado_1 = DB::connection('sqlsrv_1')
//->select('identificacion') // Especifica la conexión 'sqlsrv_1'
->table('maestroIdentificaciones') // Accede a la tabla 'maestroIdentificaciones'
->where('identificacion', $numero_identifi)
->where('tipoIdentificacion', $tipo_identifi) // Filtro por número de identificación
->first(); // Obtener el primer registro que coincida

if ($afiliado_1 ) {

  
$afiliado = afiliado::where('numero_identificacion', $numero_identifi)->first();

 if (!$afiliado) {
     // Si el afiliado no existe, crea uno nuevo
     $this->filasParaGuardar[] = [
      
         'esquema_vacunacion' => $row[74] ?? null,
         'user_id' => $usuario_activo,
         'batch_verifications_id' => $this->batch_verifications_id, // Clave foránea
         'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
         'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
     
   
     // Guarda el modelo afiliado
    // $afiliado->save();
// *** Aquí se agrega la lógica para guardar las vacunas ***
    
 }
}else {
    // Agregar un mensaje de error al array y evitar que se guarde nada
    $this->errores[] = "No se encontró ningún afiliado con la identificación: $numero_identifi y tipo: $tipo_identifi";
    $this->guardar = false; // No debemos guardar nada
  
}
return null; 


 // Verificar si todos los campos en el rango de 75 a 250 están vacíos
$allVacunaFieldsEmpty = true;
for ($i = 75; $i <= 255; $i++) {
    // Comprueba si la celda está establecida, no está vacía, no es nula y no está formada solo por espacios en blanco
    if (isset($row[$i]) && !empty($row[$i]) && !is_null($row[$i]) && trim($row[$i]) !== '') {
        $allVacunaFieldsEmpty = false; // Si encuentra algún campo no vacío, establece la variable en false
        break; // Sale del bucle ya que encontró un campo no vacío
    }
}

if (!$allVacunaFieldsEmpty) { // Si hay al menos un campo no vacío
    // Agregar las vacunas del afiliado
    for ($i = 75; $i <= 255; $i++) {
        $vacunaNombre = $docis = $laboratorio = $lote = $jeringa = $lote_jeringa = $diluyente = 
        $lote_diluyente = $observacion = $gotero = $tipo_neumococo = $num_frascos_utilizados = 
        $fecha_vacuna = $responsable_ = $fuen_ingresado_paiweb_ =  $motivo_noingreso_ = $observaciones_ = $usuario_activo_ = null;

        if (isset($row[$i]) && !empty($row[$i]) && !is_null($row[$i]) && trim($row[$i]) !== '') {
            // Determinar el nombre de la vacuna basado en el rango de columnas
            if ($i >= 75 && $i <= 80) {
                $vacunaNombre = 'Covid 19';
                $docis = isset($row[75]) ? $row[75] : null;
                $laboratorio = isset($row[76]) ? $row[76] : null;
                $lote = isset($row[77]) ? $row[77] : null;
                $jeringa = isset($row[78]) ? $row[78] : null;
                $lote_jeringa = isset($row[79]) ? $row[79] : null;
                $diluyente = isset($row[80]) ? $row[80] : null;
                $fecha_vacuna = $fechaatencion;

                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;

                $i = 80;
            } elseif ($i >= 81 && $i <= 86) {
                $vacunaNombre = 'BCG';
                $docis = isset($row[81]) ? $row[81] : null;
                $lote = isset($row[82]) ? $row[82] : null;
                $jeringa = isset($row[83]) ? $row[83] : null;
                $lote_jeringa = isset($row[84]) ? $row[84] : null;
                $lote_diluyente = isset($row[85]) ? $row[85] : null;
                $observacion = isset($row[86]) ? $row[86] : null;
                $fecha_vacuna = $fechaatencion;

                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 86;
            }
            else {
                continue; // Saltar a la siguiente iteración si no se encuentra una vacuna válida
            }

            // Crear y guardar la vacuna si se ha identificado una
            if (isset($vacunaNombre)) {
                Log::info("Guardando vacuna: $vacunaNombre");

                $vacuna = new vacuna([
                    'afiliado_id' => $afiliado->id,
                    'nombre' => $vacunaNombre,
                    'docis' => $docis ?? null,
                    'laboratorio' => $laboratorio ?? null,
                    'lote' => $lote ?? null,
                    'jeringa' => $jeringa ?? null,
                    'lote_jeringa' => $lote_jeringa ?? null,
                    'diluyente' => $diluyente ?? null,
                    'lote_diluyente' => $lote_diluyente ?? null,
                    'observacion' => $observacion ?? null,
                    'gotero' => $gotero ?? null,
                    'num_frascos_utilizados'=> $num_frascos_utilizados ?? null,
                    'tipo_neumococo' => $tipo_neumococo ?? null,
                    'fecha_vacuna' => $fecha_vacuna ?? null,

                    'responsable' => $responsable_ ?? null,
                    'fuen_ingresado_paiweb' => $fuen_ingresado_paiweb_ ?? null,
                    'motivo_noingreso' => $motivo_noingreso_ ?? null,
                    'observaciones' => $observaciones_ ?? null,
                    'user_id' => $usuario_activo ?? null,
                    'batch_verifications_id' => $this->batch_verifications_id, // Clave foránea
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);

                // Guarda la vacuna
                $vacuna->save();

                // Limpiar las variables
                unset($vacunaNombre, $docis, $laboratorio, $lote, $jeringa, $lote_jeringa, 
                $diluyente, $lote_diluyente, $observacion, $gotero, $tipo_neumococo,
                $num_frascos_utilizados,$fecha_vacuna,
                $responsable_,$fuen_ingresado_paiweb_,$motivo_noingreso_,$observaciones_,$usuario_activo_);
            }
        }
    }
}

// Añadir un mensaje de log para confirmar el guardado
Log::info('Afiliado guardado con éxito:', $afiliado->toArray());

return $afiliado;
}
 // Método para obtener los errores
 public function getErrores()
 {
     return $this->errores;
 }

 // Método para verificar si se debe guardar o no
 public function debeGuardar()
 {
     return $this->guardar;
 }

 // Método para obtener los datos validados y que pueden ser guardados
 public function getFilasParaGuardar()
 {
     return $this->filasParaGuardar;
 }

}
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

//Y LE DEBES  AGREGAR EL WithStartRow PARA QUE FUNCIONE LO DE LA FILAO  QUE EMPIEZE POR ESA FILA 
class AfiliadoImport implements ToModel, WithStartRow
{

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


      


        $data = [
            'fecha_atencion' => $row[0] ?? null,
            'tipo_identificacion' => $row[1] ?? null,
            'numero_identificacion' => isset($row[2]) ? (string)$row[2] : null, // Convertir a cadena de texto
            'primer_nombre' => $row[3] ?? null,
            'segundo_nombre' => $row[4] ?? null,
            'primer_apellido' => $row[5] ?? null,
            'segundo_apellido' => $row[6] ?? null,
            'fecha_nacimiento' => $row[7] ?? null,
            'edad_anos' => $row[8] ?? null,
            'edad_meses' => $row[9] ?? null,
            'edad_dias' => $row[10] ?? null,
            'total_meses' => $row[11] ?? null,
            'esquema_completo' => $row[12] ?? null,
            'sexo' => $row[13] ?? null,
            'genero' => $row[14] ?? null,
            'orientacion_sexual' => $row[15] ?? null,
            'edad_gestacional' => $row[16] ?? null,
            'pais_nacimiento' => $row[17] ?? null,
            'estatus_migratorio' => $row[18] ?? null,
            'lugar_atencion_parto' => $row[19] ?? null,
            'regimen' => $row[20] ?? null,
            'aseguradora' => $row[21] ?? null,
            'pertenencia_etnica' => $row[22] ?? null,
            'desplazado' => $row[23] ?? null,
            'discapacitado' => $row[24] ?? null,
            'fallecido' => $row[25] ?? null,
            'victima_conflicto' => $row[26] ?? null,
            'estudia' => $row[27] ?? null,
            'pais_residencia' => $row[28] ?? null,
            'departamento_residencia' => $row[29] ?? null,
            'municipio_residencia' => $row[30] ?? null,
            'comuna' => $row[31] ?? null,
            'area' => $row[32] ?? null,
            'direccion' => $row[33] ?? null,
            'telefono_fijo' => $row[34] ?? null,
            'celular' => $row[35] ?? null,
            'email' => $row[36] ?? null,
            'autoriza_llamadas' => $row[37] ?? null,
            'autoriza_correos' => $row[38] ?? null,
            'contraindicacion_vacuna' => $row[39] ?? null,
            'enfermedad_contraindicacion' => $row[40] ?? null,
            'reaccion_biologicos' => $row[41] ?? null,
            'sintomas_reaccion' => $row[42] ?? null,
            'condicion_usuaria' => $row[43] ?? null,
            'fecha_ultima_menstruacion' => $row[44] ?? null,
            'semanas_gestacion' => $row[45] ?? null,
            'fecha_prob_parto' => $row[46] ?? null,
            'embarazos_previos' => $row[47] ?? null,
            'fecha_antecedente' => $row[48] ?? null,
            'tipo_antecedente' => $row[49] ?? null,
            'descripcion_antecedente' => $row[50] ?? null,
            'observaciones_especiales' => $row[51] ?? null,
            'madre_tipo_identificacion' => $row[52] ?? null,
            'madre_identificacion' => $row[53] ?? null,
            'madre_primer_nombre' => $row[54] ?? null,
            'madre_segundo_nombre' => $row[55] ?? null,
            'madre_primer_apellido' => $row[56] ?? null,
            'madre_segundo_apellido' => $row[57] ?? null,
            'madre_correo' => $row[58] ?? null,
            'madre_telefono' => $row[59] ?? null,
            'madre_celular' => $row[60] ?? null,
            'madre_regimen' => $row[61] ?? null,
            'madre_pertenencia_etnica' => $row[62] ?? null,
            'madre_desplazada' => $row[63] ?? null,
            'cuidador_tipo_identificacion' => $row[64] ?? null,
            'cuidador_identificacion' => $row[65] ?? null,
            'cuidador_primer_nombre' => $row[66] ?? null,
            'cuidador_segundo_nombre' => $row[67] ?? null,
            'cuidador_primer_apellido' => $row[68] ?? null,
            'cuidador_segundo_apellido' => $row[69] ?? null,
            'cuidador_parentesco' => $row[70] ?? null,
            'cuidador_correo' => $row[71] ?? null,
            'cuidador_telefono' => $row[72] ?? null,
            'cuidador_celular' => $row[73] ?? null,
            'esquema_vacunacion' => $row[74] ?? null,
            'responsable' => $row[251] ?? null,
            'fuen_ingresado_paiweb' => $row[252] ?? null,
            'motivo_noingreso' => $row[253] ?? null,
            'observaciones' => $row[254] ?? null,
            'user_id' => Auth::id(),

        ];
    // Define las reglas de validación
    $rules = [
        'fecha_atencion' => 'nullable',
        'tipo_identificacion' => 'nullable|string',
        'numero_identificacion' => 'nullable',
        'primer_nombre' => 'nullable|string',
        'segundo_nombre' => 'nullable|string',
        'primer_apellido' => 'nullable|string',
        'segundo_apellido' => 'nullable|string',
        'fecha_nacimiento' => 'nullable',
        'edad_anos' => 'nullable|integer',
        'edad_meses' => 'nullable|integer',
        'edad_dias' => 'nullable|integer',
        'total_meses' => 'nullable|integer',
        'esquema_completo' => 'nullable|string',
        'sexo' => 'nullable|string',
        'genero' => 'nullable|string',
        'orientacion_sexual' => 'nullable|string',
        'edad_gestacional' => 'nullable|integer',
        'pais_nacimiento' => 'nullable|string',
        'estatus_migratorio' => 'nullable|string',
        'lugar_atencion_parto' => 'nullable|string',
        'regimen' => 'nullable|string',
        'aseguradora' => 'nullable|string',
        'pertenencia_etnica' => 'nullable|string',
        'desplazado' => 'nullable|string',
        'discapacitado' => 'nullable|string',
        'fallecido' => 'nullable|string',
        'victima_conflicto' => 'nullable|string',
        'estudia' => 'nullable',
        'pais_residencia' => 'nullable|string',
        'departamento_residencia' => 'nullable|string',
        'municipio_residencia' => 'nullable|string',
        'comuna' => 'nullable|string',
        'area' => 'nullable|string',
        'direccion' => 'nullable|string',
        'telefono_fijo' => 'nullable|string',
        'celular' => 'nullable
        ',
        'email' => 'nullable|string',
        'autoriza_llamadas' => 'nullable|string',
        'autoriza_correos' => 'nullable|string',
        'contraindicacion_vacuna' => 'nullable|string',
        'enfermedad_contraindicacion' => 'nullable|string',
        'reaccion_biologicos' => 'nullable|string',
        'sintomas_reaccion' => 'nullable|string',
        'condicion_usuaria' => 'nullable|string',
        'fecha_ultima_menstruacion' => 'nullable',
        'semanas_gestacion' => 'nullable',
        'fecha_prob_parto' => 'nullable',
        'embarazos_previos' => 'nullable|integer',
        'fecha_antecedente' => 'nullable',
        'tipo_antecedente' => 'nullable',
        'descripcion_antecedente' => 'nullable|string',
        'observaciones_especiales' => 'nullable',
        'madre_tipo_identificacion' => 'nullable|string',
        'madre_identificacion' => 'nullable',
        'madre_primer_nombre' => 'nullable|string',
        'madre_segundo_nombre' => 'nullable|string',
        'madre_primer_apellido' => 'nullable|string',
        'madre_segundo_apellido' => 'nullable|string',
        'madre_correo' => 'nullable|string',
        'madre_telefono' => 'nullable|string',
        'madre_celular' => 'nullable',
        'madre_regimen' => 'nullable|string',
        'madre_pertenencia_etnica' => 'nullable|string',
        'madre_desplazada' => 'nullable',
        'cuidador_tipo_identificacion' => 'nullable|string',
        'cuidador_identificacion' => 'nullable',
        'cuidador_primer_nombre' => 'nullable|string',
        'cuidador_segundo_nombre' => 'nullable|string',
        'cuidador_primer_apellido' => 'nullable|string',
        'cuidador_segundo_apellido' => 'nullable|string',
        'cuidador_parentesco' => 'nullable|string',
        'cuidador_correo' => 'nullable|string',
        'cuidador_telefono' => 'nullable|string',
        'cuidador_celular' => 'nullable',
        'esquema_vacunacion' => 'nullable|string',
    ];

    // Define los mensajes de error
    $messages = [
        'date' => 'El campo :attribute debe ser una fecha válida en la columna :attribute_col.',
        'string' => 'El campo :attribute debe ser un texto válido en la columna :attribute_col.',
        'integer' => 'El campo :attribute debe ser un número entero válido en la columna :attribute_col.',
    ];
   // Realiza la validación
   $validator = Validator::make($data, $rules, $messages);

   if ($validator->fails()) {
       // Registra los errores y arroja una excepción de validación
       foreach ($validator->errors()->all() as $error) {
           Log::error($error);
       }
       throw new ValidationException($validator);
   }    

         
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
$numero_identifi = isset($row[2]) ? (string)$row[2] : null; // Convertir a cadena de texto


//PARA GUARDARE N EN MODELO VERIFICACION
// Supongamos que `fechaatencion` es la fecha actual



// $fechaatencion = Carbon::now();
// $verificacion = new Batch_verification([

//     'fecha_cargue' => $fechaatencion,
    
// ]);
// $verificacion->save();
     
   




 // Busca si el afiliado ya existe en la base de datos
 $afiliado = afiliado::where('numero_identificacion', $numero_identifi)->first();

 if (!$afiliado) {
     // Si el afiliado no existe, crea uno nuevo
     $afiliado = new afiliado([
        'fecha_atencion' => $fechaatencion,
         'tipo_identificacion' => $row[1] ?? null,
         'numero_identificacion' => $numero_identifi,
         'primer_nombre' => $row[3] ?? null,
         'segundo_nombre' => $row[4] ?? null,
         'primer_apellido' => $row[5] ?? null,
         'segundo_apellido' => $row[6] ?? null,
         'fecha_nacimiento' => $fechaNacimiento,
         'edad_anos' => $row[8] ?? null,
         'edad_meses' => $row[9] ?? null,
         'edad_dias' => $row[10] ?? null,
         'total_meses' => $row[11] ?? null,
         'esquema_completo' => $row[12] ?? null,
         'sexo' => $row[13] ?? null,
         'genero' => $row[14] ?? null,
         'orientacion_sexual' => $row[15] ?? null,
         'edad_gestacional' => $row[16] ?? null,
         'pais_nacimiento' => $row[17] ?? null,
         'estatus_migratorio' => $row[18] ?? null,
         'lugar_atencion_parto' => $row[19] ?? null,
         'regimen' => $row[20] ?? null,
         'aseguradora' => $row[21] ?? null,
         'pertenencia_etnica' => $row[22] ?? null,
         'desplazado' => $row[23] ?? null,
         'discapacitado' => $row[24] ?? null,
         'fallecido' => $row[25] ?? null,
         'victima_conflicto' => $row[26] ?? null,
         'estudia' => $row[27] ?? null,
         'pais_residencia' => $row[28] ?? null,
         'departamento_residencia' => $row[29] ?? null,
         'municipio_residencia' => $row[30] ?? null,
         'comuna' => $row[31] ?? null,
         'area' => $row[32] ?? null,
         'direccion' => $row[33] ?? null,
         'telefono_fijo' => $row[34] ?? null,
         'celular' => $row[35] ?? null,
         'email' => $row[36] ?? null,
         'autoriza_llamadas' => $row[37] ?? null,
         'autoriza_correos' => $row[38] ?? null,
         'contraindicacion_vacuna' => $row[39] ?? null,
         'enfermedad_contraindicacion' => $row[40] ?? null,
         'reaccion_biologicos' => $row[41] ?? null,
         'sintomas_reaccion' => $row[42] ?? null,
         'condicion_usuaria' => $row[43] ?? null,
         'fecha_ultima_menstruacion' => $row[44] ?? null,
         'semanas_gestacion' => $row[45] ?? null,
         'fecha_prob_parto' => $fechaProbParto,
         'embarazos_previos' => $row[47] ?? null,
         'fecha_antecedente' => $fechaAntecedente,
         'tipo_antecedente' => $row[49] ?? null,
         'descripcion_antecedente' => $row[50] ?? null,
         'observaciones_especiales' => $row[51] ?? null,
         'madre_tipo_identificacion' => $row[52] ?? null,
         'madre_identificacion' => $row[53] ?? null,
         'madre_primer_nombre' => $row[54] ?? null,
         'madre_segundo_nombre' => $row[55] ?? null,
         'madre_primer_apellido' => $row[56] ?? null,
         'madre_segundo_apellido' => $row[57] ?? null,
         'madre_correo' => $row[58] ?? null,
         'madre_telefono' => $row[59] ?? null,
         'madre_celular' => $row[60] ?? null,
         'madre_regimen' => $row[61] ?? null,
         'madre_pertenencia_etnica' => $row[62] ?? null,
         'madre_desplazada' => $row[63] ?? null,
         'cuidador_tipo_identificacion' => $row[64] ?? null,
         'cuidador_identificacion' => $row[65] ?? null,
         'cuidador_primer_nombre' => $row[66] ?? null,
         'cuidador_segundo_nombre' => $row[67] ?? null,
         'cuidador_primer_apellido' => $row[68] ?? null,
         'cuidador_segundo_apellido' => $row[69] ?? null,
         'cuidador_parentesco' => $row[70] ?? null,
         'cuidador_correo' => $row[71] ?? null,
         'cuidador_telefono' => $row[72] ?? null,
         'cuidador_celular' => $row[73] ?? null,
         'esquema_vacunacion' => $row[74] ?? null,
         'user_id' => $usuario_activo,
         'batch_verifications_id' => $this->batch_verifications_id, // Clave foránea
         'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
         'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
         
     ]);
     
   
     // Guarda el modelo afiliado
     $afiliado->save();
 }

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
            } elseif ($i >= 87 && $i <= 91) {
                $vacunaNombre = 'HEPATITIS B';
                $docis = isset($row[87]) ? $row[87] : null;
                $lote = isset($row[88]) ? $row[88] : null;
                $jeringa = isset($row[89]) ? $row[89] : null;
                $lote_jeringa = isset($row[90]) ? $row[90] : null;
                $observacion = isset($row[91]) ? $row[91] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 91;
            } elseif ($i >= 92 && $i <= 96) {
                $vacunaNombre = 'POLIO INACTIVADO(INYECTABLE)';
                $docis = isset($row[92]) ? $row[92] : null;
                $lote = isset($row[93]) ? $row[93] : null;
                $jeringa = isset($row[94]) ? $row[94] : null;
                $lote_jeringa = isset($row[95]) ? $row[95] : null;
                $observacion = isset($row[96]) ? $row[96] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 96;
            } elseif ($i >= 97 && $i <= 99) {
                $vacunaNombre = 'POLIO (VACUNA ORAL)';
                $docis = isset($row[97]) ? $row[97] : null;
                $lote = isset($row[98]) ? $row[98] : null;
                $gotero = isset($row[99]) ? $row[99] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 99;
            } elseif ($i >= 100 && $i <= 104) {
                $vacunaNombre = 'PENTAVALENTE';
                $docis = isset($row[100]) ? $row[100] : null;
                $lote = isset($row[101]) ? $row[101] : null;
                $jeringa = isset($row[102]) ? $row[102] : null;
                $lote_jeringa = isset($row[103]) ? $row[103] : null;
                $observacion = isset($row[104]) ? $row[104] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 104;
            } elseif ($i >= 105 && $i <= 108) {
                $vacunaNombre = 'HEXAVALENTE';
                $docis = isset($row[105]) ? $row[105] : null;
                $lote = isset($row[106]) ? $row[106] : null;
                $jeringa = isset($row[107]) ? $row[107] : null;
                $lote_jeringa = isset($row[108]) ? $row[108] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 108;
            }elseif ($i >= 109 && $i <= 112) {
                $vacunaNombre = 'DIFTERIA, TOS FERINA Y TÉTANOS - DPT';
                $docis = isset($row[109]) ? $row[109] : null;
                $lote = isset($row[110]) ? $row[110] : null;
                $jeringa = isset($row[111]) ? $row[111] : null;
                $lote_jeringa = isset($row[112]) ? $row[112] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 112;
            } 
            elseif ($i >= 113 && $i <= 116) {
                $vacunaNombre = 'DTPA PEDIÁTRICO';
                $docis = isset($row[113]) ? $row[113] : null;
                $lote = isset($row[114]) ? $row[114] : null;
                $jeringa = isset($row[115]) ? $row[115] : null;
                $lote_jeringa = isset($row[116]) ? $row[116] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 116;
            }
            elseif ($i >= 117 && $i <= 120) {
                $vacunaNombre = 'TD PEDIÁTRICO';
                $docis = isset($row[117]) ? $row[117] : null;
                $lote = isset($row[118]) ? $row[118] : null;
                $jeringa = isset($row[119]) ? $row[119] : null;
                $lote_jeringa = isset($row[120]) ? $row[120] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 120;
            }
            elseif ($i >= 121 && $i <= 122) {
                $vacunaNombre = 'ROTAVIRUS (VACUNA ORAL)';
                $docis = isset($row[121]) ? $row[121] : null;
                $lote = isset($row[122]) ? $row[122] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 122;
            }

            elseif ($i >= 123 && $i <= 127) {
                $vacunaNombre = 'NEUMOCOCO';
                $tipo_neumococo = isset($row[123]) ? $row[123] : null;
                $docis = isset($row[124]) ? $row[124] : null;
                $lote = isset($row[125]) ? $row[125] : null;
                $jeringa = isset($row[126]) ? $row[126] : null;
                $lote_jeringa = isset($row[127]) ? $row[127] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 127;
                
                
            }elseif ($i >= 128 && $i <= 132) {
                $vacunaNombre = 'TRIPLE VIRAL - SRP';
                $docis = isset($row[128]) ? $row[128] : null;
                $lote = isset($row[129]) ? $row[129] : null;
                $jeringa = isset($row[130]) ? $row[130] : null;
                $lote_jeringa = isset($row[131]) ? $row[131] : null;
                $lote_diluyente = isset($row[132]) ? $row[132] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 132;
                
                
            }

            elseif ($i >= 133 && $i <= 137) {
                $vacunaNombre = 'SARAMPIÓN - RUBEOLA - SR MULTIDOSIS';
                $docis = isset($row[133]) ? $row[133] : null;
                $lote = isset($row[134]) ? $row[134] : null;
                $jeringa = isset($row[135]) ? $row[135] : null;
                $lote_jeringa = isset($row[136]) ? $row[136] : null;
                $lote_diluyente = isset($row[137]) ? $row[137] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 137;
                
                
            }

            elseif ($i >= 138 && $i <= 142) {
                $vacunaNombre = 'FIEBRE AMARILLA';
                $docis = isset($row[138]) ? $row[138] : null;
                $lote = isset($row[139]) ? $row[139] : null;
                $jeringa = isset($row[140]) ? $row[140] : null;
                $lote_jeringa = isset($row[141]) ? $row[141] : null;
                $lote_diluyente = isset($row[142]) ? $row[142] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 142;
                
                
            }
            elseif ($i >= 143 && $i <= 146) {
                $vacunaNombre = 'HEPATITIS A PEDIÁTRICA';
                $docis = isset($row[143]) ? $row[143] : null;
                $lote = isset($row[144]) ? $row[144] : null;
                $jeringa = isset($row[145]) ? $row[145] : null;
                $lote_jeringa = isset($row[146]) ? $row[146] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
               
                $i = 146;
                
                
            }
                
            elseif ($i >= 147 && $i <= 151) {
                $vacunaNombre = 'VARICELA';
                $docis = isset($row[147]) ? $row[147] : null;
                $lote = isset($row[148]) ? $row[148] : null;
                $jeringa = isset($row[149]) ? $row[149] : null;
                $lote_jeringa = isset($row[150]) ? $row[150] : null;
                $lote_diluyente = isset($row[151]) ? $row[151] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 151;
                
                
            }

            elseif ($i >= 152 && $i <= 155) {
                $vacunaNombre = 'TOXOIDE TETÁNICO Y DIFTÉRICO DE ADULTO';
                $docis = isset($row[152]) ? $row[152] : null;
                $lote = isset($row[153]) ? $row[153] : null;
                $jeringa = isset($row[154]) ? $row[154] : null;
                $lote_jeringa = isset($row[155]) ? $row[155] : null;
               
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 155;
                
                
            }

            elseif ($i >= 156 && $i <= 159) {
                $vacunaNombre = 'DTPA ADULTO';
                $docis = isset($row[156]) ? $row[156] : null;
                $lote = isset($row[157]) ? $row[157] : null;
                $jeringa = isset($row[158]) ? $row[158] : null;
                $lote_jeringa = isset($row[159]) ? $row[159] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
               
                $i = 159;
                
                
            }

            elseif ($i >= 160 && $i <= 164) {
                $vacunaNombre = 'INFLUENZA';
                $docis = isset($row[160]) ? $row[160] : null;
                $lote = isset($row[161]) ? $row[161] : null;
                $jeringa = isset($row[162]) ? $row[162] : null;
                $lote_jeringa = isset($row[163]) ? $row[163] : null;
                $observacion = isset($row[164]) ? $row[164] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 164;
            }

            elseif ($i >= 165 && $i <= 168) {
                $vacunaNombre = 'VPH';
                $docis = isset($row[165]) ? $row[165] : null;
                $lote = isset($row[166]) ? $row[166] : null;
                $jeringa = isset($row[167]) ? $row[167] : null;
                $lote_jeringa = isset($row[168]) ? $row[168] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 168;
            }

            elseif ($i >= 169 && $i <= 174) {
                $vacunaNombre = 'ANTIRRÁBICA  HUMANA (VACUNA)';
                $docis = isset($row[169]) ? $row[169] : null;
                $lote = isset($row[170]) ? $row[170] : null;
                $jeringa = isset($row[171]) ? $row[171] : null;
                $lote_jeringa = isset($row[172]) ? $row[172] : null;
                $lote_diluyente = isset($row[173]) ? $row[173] : null;
                $observacion = isset($row[174]) ? $row[174] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 174;
            }

            elseif ($i >= 175 && $i <= 176) {
                $vacunaNombre = 'ANTIRRÁBICO HUMANO (SUERO)';
                $num_frascos_utilizados = isset($row[175]) ? $row[175] : null;
                $lote = isset($row[176]) ? $row[176] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 176;
            }

            elseif ($i >= 177 && $i <= 181) {
                $vacunaNombre = 'HEPATITIS B (INMUNOGLOBULINA)';
                $num_frascos_utilizados = isset($row[177]) ? $row[177] : null;
                $lote = isset($row[178]) ? $row[178] : null;
                $jeringa = isset($row[179]) ? $row[179] : null;
                $lote_jeringa = isset($row[180]) ? $row[180] : null;
                
                $observacion = isset($row[181]) ? $row[181] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 181;
            }

            elseif ($i >= 182 && $i <= 185) {
                $vacunaNombre = 'INMUNOGLOBULINA ANTI TETANICA (Suero homólogo)';
                $num_frascos_utilizados = isset($row[182]) ? $row[182] : null;
                $lote = isset($row[183]) ? $row[183] : null;
                $jeringa = isset($row[184]) ? $row[184] : null;
                $lote_jeringa = isset($row[185]) ? $row[185] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 185;
            }

            elseif ($i >= 186 && $i <= 189) {
                $vacunaNombre = 'ANTI TOXINA TETANICA (Suero heterólogo)';
                $num_frascos_utilizados = isset($row[186]) ? $row[186] : null;
                $lote = isset($row[187]) ? $row[187] : null;
                $jeringa = isset($row[188]) ? $row[188] : null;
                $lote_jeringa = isset($row[189]) ? $row[189] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 189;
            }

            elseif ($i >= 190 && $i <= 194) {
                $vacunaNombre = 'Meningococo  de los serogrupos A, C, W-135 e Y';
                $docis = isset($row[190]) ? $row[190] : null;
                $lote = isset($row[191]) ? $row[191] : null;
                $jeringa = isset($row[192]) ? $row[192] : null;
                $lote_jeringa = isset($row[193]) ? $row[193] : null;
                $lote_diluyente = isset($row[194]) ? $row[194] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 194;
            }

            elseif ($i >= 195 && $i <= 196) {
                $vacunaNombre = 'HEPATITIS B';
                $docis = isset($row[195]) ? $row[195] : null;
                $lote = isset($row[196]) ? $row[196] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 196;
            }
            elseif ($i >= 197 && $i <= 198) {
                $vacunaNombre = 'PENTAVALENTE (DPaT,HiB,VPI)';
                $docis = isset($row[197]) ? $row[197] : null;
                $lote = isset($row[198]) ? $row[198] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 198;
            }
            elseif ($i >= 199 && $i <= 200) {
                $vacunaNombre = 'HEXAVALENTE (DPaT,HiB,HB,VPI)';
                $docis = isset($row[199]) ? $row[199] : null;
                $lote = isset($row[200]) ? $row[200] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 200;
            }

            elseif ($i >= 201 && $i <= 202) {
                $vacunaNombre = 'TETRAVALENTE (DPaT,VPI)';
                $docis = isset($row[201]) ? $row[201] : null;
                $lote = isset($row[202]) ? $row[202] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 202;
            }

            elseif ($i >= 203 && $i <= 204) {
                $vacunaNombre = 'DPT ACELULAR PEDIATRICO';
                $docis = isset($row[203]) ? $row[203] : null;
                $lote = isset($row[204]) ? $row[204] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 204;
            }
           
            elseif ($i >= 205 && $i <= 206) {
                $vacunaNombre = 'TOXOIDE TETANICO Y DIFTERICO (TD) PEDIATRICO';
                $docis = isset($row[205]) ? $row[205] : null;
                $lote = isset($row[206]) ? $row[206] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 206;
            }
            elseif ($i >= 207 && $i <= 208) {
                $vacunaNombre = 'ROTAVIRUS';
                $docis = isset($row[207]) ? $row[207] : null;
                $lote = isset($row[208]) ? $row[208] : null;
                $fecha_vacuna = $fechaatencion;
                $usuario_activo_ = $usuario_activo;
                $i = 208;
            }
            elseif ($i >= 209 && $i <= 210) {
                $vacunaNombre = 'NEUMOCOCO CONJUGADA';
                $docis = isset($row[209]) ? $row[209] : null;
                $lote = isset($row[210]) ? $row[210] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 210;
            }
            elseif ($i >= 211 && $i <= 212) {
                $vacunaNombre = 'NEUMO POLISACARIDO';
                $docis = isset($row[211]) ? $row[211] : null;
                $lote = isset($row[212]) ? $row[212] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 212;
            }
            elseif ($i >= 213 && $i <= 214) {
                $vacunaNombre = 'TRIPLE VIRAL';
                $docis = isset($row[213]) ? $row[213] : null;
                $lote = isset($row[214]) ? $row[214] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 214;
            }
            elseif ($i >= 215 && $i <= 216) {
                $vacunaNombre = 'VARICELA + TRIPLE VIRAL';
                $docis = isset($row[215]) ? $row[215] : null;
                $lote = isset($row[216]) ? $row[216] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 216;
            }
            elseif ($i >= 217 && $i <= 218) {
                $vacunaNombre = 'FIEBRE AMARILLA';
                $docis = isset($row[217]) ? $row[217] : null;
                $lote = isset($row[218]) ? $row[218] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 218;
            }
            elseif ($i >= 219 && $i <= 220) {
                $vacunaNombre = 'HEPATITIS A';
                $docis = isset($row[219]) ? $row[219] : null;
                $lote = isset($row[220]) ? $row[220] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 220;
            }
            elseif ($i >= 221 && $i <= 222) {
                $vacunaNombre = 'HEPATITIS A, HEPATITIS B';
                $docis = isset($row[221]) ? $row[221] : null;
                $lote = isset($row[222]) ? $row[222] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 222;
            }
            elseif ($i >= 223 && $i <= 224) {
                $vacunaNombre = 'VARICELA';
                $docis = isset($row[223]) ? $row[223] : null;
                $lote = isset($row[224]) ? $row[224] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 224;
            }
            elseif ($i >= 225 && $i <= 226) {
                $vacunaNombre = 'TOXOIDE TETÁNICO/DIFTERICO ADULTOS';
                $docis = isset($row[225]) ? $row[225] : null;
                $lote = isset($row[226]) ? $row[226] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 226;
            }
            elseif ($i >= 227 && $i <= 228) {
                $vacunaNombre = 'DPT ACELULAR ADULTO';
                $docis = isset($row[227]) ? $row[227] : null;
                $lote = isset($row[228]) ? $row[228] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 228;
            }
            elseif ($i >= 229 && $i <= 230) {
                $vacunaNombre = 'INFLUENZA';
                $docis = isset($row[229]) ? $row[229] : null;
                $lote = isset($row[230]) ? $row[230] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 230;
            }
            elseif ($i >= 231 && $i <= 232) {
                $vacunaNombre = 'VPH';
                $docis = isset($row[231]) ? $row[231] : null;
                $lote = isset($row[232]) ? $row[232] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 232;
            }

            elseif ($i >= 233 && $i <= 235) {
                $vacunaNombre = 'ANTIRRÁBICA PROFILÁCTICA';
                $docis = isset($row[233]) ? $row[233] : null;
                $lote = isset($row[234]) ? $row[234] : null;
                $observacion = isset($row[235]) ? $row[235] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 235;
            }

            elseif ($i >= 236 && $i <= 237) {
                $vacunaNombre = 'INMUNOGLOBULINA ANTI TETANICA (Suero homólogo)';
                $num_frascos_utilizados = isset($row[236]) ? $row[236] : null;
                $lote = isset($row[237]) ? $row[237] : null; 
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 237;
            }
            elseif ($i >= 238 && $i <= 240) {
                $vacunaNombre = 'INMUNOGLOBULINA ANTI HEPATITIS B';
                $num_frascos_utilizados = isset($row[238]) ? $row[238] : null;
                $lote = isset($row[239]) ? $row[239] : null;
                $observacion = isset($row[240]) ? $row[240] : null; 
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 240;
            }
            elseif ($i >= 241 && $i <= 242) {
                $vacunaNombre = 'INMUNOGLOBULINA ANTI TETANICA (Suero homólogo)';
                $num_frascos_utilizados = isset($row[241]) ? $row[241] : null;
                $lote = isset($row[242]) ? $row[242] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 242;
            }
            elseif ($i >= 243 && $i <= 244) {
                $vacunaNombre = 'ANTI TOXINA TETANICA (Suero heterólogo)';
                $num_frascos_utilizados = isset($row[243]) ? $row[243] : null;
                $lote = isset($row[244]) ? $row[244] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 244;
            }
            elseif ($i >= 245 && $i <= 246) {
                $vacunaNombre = 'MENINGOCOCO CONJUGADO';
                $docis = isset($row[245]) ? $row[245] : null;
                $lote = isset($row[246]) ? $row[246] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 246;
            }
            elseif ($i >= 247 && $i <= 248) {
                $vacunaNombre = 'FIEBRE TIFOIDEA';
                $docis = isset($row[247]) ? $row[247] : null;
                $lote = isset($row[248]) ? $row[248] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 248;
            }
            elseif ($i >= 249 && $i <= 250) {
                $vacunaNombre = 'HERPES ZOSTER';
                $docis = isset($row[249]) ? $row[249] : null;
                $lote = isset($row[250]) ? $row[250] : null;
                $fecha_vacuna = $fechaatencion;
                $responsable_ = $responsable;
                $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                $motivo_noingreso_ = $motivo_noingreso;
                $observaciones_ = $observaciones;
                $usuario_activo_ = $usuario_activo;
                $i = 250;
            }
            else {
                continue; // Saltar a la siguiente iteración si no se encuentra una vacuna válida
            }

//         // Validar tipos de datos
// if (!is_string($vacunaNombre) ||
// (!is_null($docis) && !is_string($docis)) ||
// (!is_null($laboratorio) && !is_string($laboratorio)) ||
// (!is_null($lote) && !is_string($lote)) ||
// (!is_null($jeringa) && !is_string($jeringa)) ||
// (!is_null($lote_jeringa) && !is_string($lote_jeringa)) ||
// (!is_null($diluyente) && !is_string($diluyente)) ||
// (!is_null($lote_diluyente) && !is_string($lote_diluyente)) ||
// (!is_null($observacion) && !is_string($observacion)) ||
// (!is_null($gotero) && !is_string($gotero)) ||
// (!is_null($tipo_neumococo) && !is_string($tipo_neumococo)) ||
// (!is_null($num_frascos_utilizados) && !is_numeric($num_frascos_utilizados)) ||
// (!is_null($fecha_vacuna) && !is_string($fecha_vacuna)) ||
// (!is_null($responsable_) && !is_string($responsable_)) ||
// (!is_null($fuen_ingresado_paiweb_) && !is_string($fuen_ingresado_paiweb_)) ||
// (!is_null($motivo_noingreso_) && !is_string($motivo_noingreso_)) ||
// (!is_null($observaciones_) && !is_string($observaciones_))
// ) {
// Log::error("Error de tipo de dato en la columna $i para la vacuna $vacunaNombre");
// continue; // Saltar a la siguiente iteración si hay un error de tipo de dato
// }


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



}
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
      
        // validacion previa de  datos 

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
            'tipo_identificacion' => 'nullable',
            'numero_identificacion' => 'nullable',
            'primer_nombre' => 'nullable',
            'segundo_nombre' => 'nullable',
            'primer_apellido' => 'nullable',
            'segundo_apellido' => 'nullable',
            'fecha_nacimiento' => 'nullable',
            'edad_anos' => 'nullable|integer',
            'edad_meses' => 'nullable|integer',
            'edad_dias' => 'nullable|integer',
            'total_meses' => 'nullable|integer',
            'esquema_completo' => 'nullable',
            'sexo' => 'nullable',
            'genero' => 'nullable',
            'orientacion_sexual' => 'nullable',
            'edad_gestacional' => 'nullable|integer',
            'pais_nacimiento' => 'nullable',
            'estatus_migratorio' => 'nullable',
            'lugar_atencion_parto' => 'nullable',
            'regimen' => 'nullable',
            'aseguradora' => 'nullable',
            'pertenencia_etnica' => 'nullable',
            'desplazado' => 'nullable',
            'discapacitado' => 'nullable',
            'fallecido' => 'nullable',
            'victima_conflicto' => 'nullable',
            'estudia' => 'nullable',
            'pais_residencia' => 'nullable',
            'departamento_residencia' => 'nullable',
            'municipio_residencia' => 'nullable',
            'comuna' => 'nullable',
            'area' => 'nullable',
            'direccion' => 'nullable',
            'telefono_fijo' => 'nullable',
            'celular' => 'nullable
            ',
            'email' => 'nullable',
            'autoriza_llamadas' => 'nullable',
            'autoriza_correos' => 'nullable',
            'contraindicacion_vacuna' => 'nullable',
            'enfermedad_contraindicacion' => 'nullable',
            'reaccion_biologicos' => 'nullable',
            'sintomas_reaccion' => 'nullable',
            'condicion_usuaria' => 'nullable',
            'fecha_ultima_menstruacion' => 'nullable',
            'semanas_gestacion' => 'nullable',
            'fecha_prob_parto' => 'nullable',
            'embarazos_previos' => 'nullable|integer',
            'fecha_antecedente' => 'nullable',
            'tipo_antecedente' => 'nullable',
            'descripcion_antecedente' => 'nullable',
            'observaciones_especiales' => 'nullable',
            'madre_tipo_identificacion' => 'nullable',
            'madre_identificacion' => 'nullable',
            'madre_primer_nombre' => 'nullable',
            'madre_segundo_nombre' => 'nullable',
            'madre_primer_apellido' => 'nullable',
            'madre_segundo_apellido' => 'nullable',
            'madre_correo' => 'nullable',
            'madre_telefono' => 'nullable',
            'madre_celular' => 'nullable',
            'madre_regimen' => 'nullable',
            'madre_pertenencia_etnica' => 'nullable',
            'madre_desplazada' => 'nullable',
            'cuidador_tipo_identificacion' => 'nullable',
            'cuidador_identificacion' => 'nullable',
            'cuidador_primer_nombre' => 'nullable',
            'cuidador_segundo_nombre' => 'nullable',
            'cuidador_primer_apellido' => 'nullable',
            'cuidador_segundo_apellido' => 'nullable',
            'cuidador_parentesco' => 'nullable',
            'cuidador_correo' => 'nullable',
            'cuidador_telefono' => 'nullable',
            'cuidador_celular' => 'nullable',
            'esquema_vacunacion' => 'nullable',
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
        $tipo_identifi = isset($row[1]) ? (string)$row[1] : null; // Convertir a cadena de texto
        $numero_identifi = isset($row[2]) ? (string)$row[2] : null; // Convertir a cadena de texto

        //PARA GUARDARE N EN MODELO VERIFICACION
        // Supongamos que `fechaatencion` es la fecha actual

        // $fechaatencion = Carbon::now();
        // $verificacion = new Batch_verification([

        //     'fecha_cargue' => $fechaatencion,
            
        // ]);
        // $verificacion->save();


        // Consulta el afiliado en la base de datos externa
        
        // Verificar si el afiliado existe en la base de datos externa
        // Consulta en la base de datos externa para validar si el afiliado existe
        
        $afiliado_1 = DB::connection('sqlsrv_1')
            ->table('maestroIdentificaciones')
            ->where('identificacion', $numero_identifi)
            ->where('tipoIdentificacion', $tipo_identifi)
            ->first();

        // Si no se encuentra el afiliado en la base externa
        if (!$afiliado_1) {
            $this->errores[] = "No se encontró ningún afiliado con la identificación: $numero_identifi y tipo: $tipo_identifi";
            $this->guardar = false; // No se debe guardar nada
            return null;
        }

        $numero_carnet = DB::connection('sqlsrv_1')
            ->table('maestroIdentificaciones')
            ->where('identificacion', $numero_identifi)
            ->where('tipoIdentificacion', $tipo_identifi)
            ->value('numeroCarnet');  // Obtener el valor de numeroCarnet


        if (is_null($numero_carnet)) {
            Log::info("El número de carnet es nulo para identificación: $numero_identifi");
        } //else {
            //Log::info("Número de carnet obtenido: $numero_carnet para identificación: $numero_identifi");
        //}

        // Verificar si el afiliado ya existe en la base de datos local
        // Se cambio el parametro a nunmero de carnet, el numero de identificacion generaba duplicados
        $afiliado = Afiliado::where('numero_carnet', $numero_carnet)->first();

        // Si el afiliado no existe, creamos un nuevo registro
        if (!$afiliado) {
            $afiliadoData = [
                'fecha_atencion' => $fechaatencion,
                'tipo_identificacion' => $tipo_identifi,
                'numero_identificacion' => $numero_identifi,
                'numero_carnet' => $numero_carnet, // Asignar el número de carnet correctamente
                'primer_nombre' => (!empty($row[3]) && $row[3] !== 'NONE') ? $row[3] : null, // Evita que se guarde 'NONE'
                'segundo_nombre' => (!empty($row[4]) && $row[4] !== 'NONE') ? $row[4] : null,
                'primer_apellido' => (!empty($row[5]) && $row[5] !== 'NONE') ? $row[5] : null,
                'segundo_apellido' => (!empty($row[6]) && $row[6] !== 'NONE') ? $row[6] : null,
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
                'email' => (!empty($row[36]) && $row[36] !== 'NONE') ? $row[36] : null, // Verificar también para otros campos
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
                'madre_primer_nombre' => (!empty($row[54]) && $row[54] !== 'NONE') ? $row[54] : null,
                'madre_segundo_nombre' => (!empty($row[55]) && $row[55] !== 'NONE') ? $row[55] : null,
                'madre_primer_apellido' => (!empty($row[56]) && $row[56] !== 'NONE') ? $row[56] : null,
                'madre_segundo_apellido' => (!empty($row[57]) && $row[57] !== 'NONE') ? $row[57] : null,
                'madre_correo' => $row[58] ?? null,
                'madre_telefono' => $row[59] ?? null,
                'madre_celular' => $row[60] ?? null,
                'madre_regimen' => $row[61] ?? null,
                'madre_pertenencia_etnica' => $row[62] ?? null,
                'madre_desplazada' => $row[63] ?? null,
                'cuidador_tipo_identificacion' => $row[64] ?? null,
                'cuidador_identificacion' => $row[65] ?? null,
                'cuidador_primer_nombre' => (!empty($row[66]) && $row[66] !== 'NONE') ? $row[66] : null,
                'cuidador_segundo_nombre' => (!empty($row[67]) && $row[67] !== 'NONE') ? $row[67] : null,
                'cuidador_primer_apellido' => (!empty($row[68]) && $row[68] !== 'NONE') ? $row[68] : null,
                'cuidador_segundo_apellido' => (!empty($row[69]) && $row[69] !== 'NONE') ? $row[69] : null,
                'cuidador_parentesco' => $row[70] ?? null,
                'cuidador_correo' => $row[71] ?? null,
                'cuidador_telefono' => $row[72] ?? null,
                'cuidador_celular' => $row[73] ?? null,
                'esquema_vacunacion' => $row[74] ?? null,
                'user_id' => $usuario_activo,
                'batch_verifications_id' => $this->batch_verifications_id, // Clave foránea
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
  
            // Añadir afiliado y vacunas a filasParaGuardar
            $vacunasData = $this->extraerVacunas($row, $fechaatencion, $responsable, $fuen_ingresado_paiweb, $motivo_noingreso, $observaciones, $usuario_activo);
            $this->filasParaGuardar[] = [
                'afiliado' => $afiliadoData,
                'vacunas' => $vacunasData,
                'existe' => false,
            ];

        } 
        else {

            $afiliadoData = ['numero_carnet' => $numero_carnet]; //enviar el numero de carnet para referenciar

            // Si el afiliado ya existe, procesamos solo las vacunas nuevas
            $vacunasData = $this->extraerVacunas($row, $fechaatencion, $responsable, $fuen_ingresado_paiweb, $motivo_noingreso, $observaciones, $usuario_activo);

            //Delaga el guardado de Vacunas al controlador 
            $this->filasParaGuardar[] = [
                'afiliado' => $afiliadoData,
                'vacunas' => $vacunasData,
                'existe' => true,
            ];
    
           /* foreach ($vacunasData as $vacuna) {
                // Verificar si la vacuna con la misma dosis ya está registrada para evitar duplicados solo en dosis
                $existeVacuna = Vacuna::where('afiliado_id', $afiliado->id)
                    ->whereRaw("docis COLLATE Latin1_General_CI_AI =?", [$vacuna['docis']])  //Case Insensitive y Accent insensitive con COLLATE
                    ->where('vacunas_id', $vacuna['vacunas_id'])  // Verifica también el nombre de la vacuna
                    ->first();
                //Log::debug("Ya existe vacuna[".$vacuna['vacunas_id']."] ".$vacuna['docis']." -> Afiliado:".$afiliado->id);
                if (!$existeVacuna) {
                    // Guardamos la nueva vacuna si no existe la misma dosis
                    $vacuna['afiliado_id'] = $afiliado->id;
                    Vacuna::create($vacuna);  // Guardar la vacuna asociada
                }
            } */
        }
    
        return null;
    }

    // Retornar null para no guardar un modelo inválido
    // $responsable = isset($row[251]) ? $row[251] : null;
    // $fuen_ingresado_paiweb = isset($row[252]) ? $row[252] : null;
    // $motivo_noingreso = isset($row[253]) ? $row[253] : null;
    // $observaciones = isset($row[254]) ? $row[254] : null;
    // $usuario_activo = Auth::id();
    private function extraerVacunas($row, $fechaatencion, $responsable, $fuen_ingresado_paiweb, $motivo_noingreso, $observaciones, $usuario_activo)
    {

        $vacunas = [];

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
                        $vacunaNombre = 1 ;
                        $docis = isset($row[75]) ? trim($row[75]) : null;
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
                        $vacunaNombre = 2 ;
                        $docis = isset($row[81]) ? trim($row[81]) : null;
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
                        $vacunaNombre = 3 ;
                        $docis = isset($row[87]) ? trim($row[87]) : null;
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
                        $vacunaNombre = 4 ;
                        $docis = isset($row[92]) ? trim($row[92]) : null;
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
                        $vacunaNombre = 5 ;
                        $docis = isset($row[97]) ? trim($row[97]) : null;
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
                        $vacunaNombre = 6 ;
                        $docis = isset($row[100]) ? trim($row[100]) : null;
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
                        $vacunaNombre = 7 ;
                        $docis = isset($row[105]) ? trim($row[105]) : null;
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
                    } elseif ($i >= 109 && $i <= 112) {
                        $vacunaNombre = 8 ;
                        $docis = isset($row[109]) ? trim($row[109]) : null;
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
                    } elseif ($i >= 113 && $i <= 116) {
                        $vacunaNombre = 9 ;
                        $docis = isset($row[113]) ? trim($row[113]) : null;
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
                    } elseif ($i >= 117 && $i <= 120) {
                        $vacunaNombre = 10 ;
                        $docis = isset($row[117]) ? trim($row[117]) : null;
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
                    } elseif ($i >= 121 && $i <= 122) {
                        $vacunaNombre = 11 ;
                        $docis = isset($row[121]) ? trim($row[121]) : null;
                        $lote = isset($row[122]) ? $row[122] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 122;
                    } elseif ($i >= 123 && $i <= 127) {
                        $vacunaNombre = 12 ;
                        $tipo_neumococo = isset($row[123]) ? trim($row[123]) : null;
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
                    } elseif ($i >= 128 && $i <= 132) {
                        $vacunaNombre = 13 ;
                        $docis = isset($row[128]) ? trim($row[128]) : null;
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
                    } elseif ($i >= 133 && $i <= 137) {
                        $vacunaNombre =  14 ;
                        $docis = isset($row[133]) ? trim($row[133]) : null;
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
                    } elseif ($i >= 138 && $i <= 142) {
                        $vacunaNombre = 15 ;
                        $docis = isset($row[138]) ? trim($row[138]) : null;
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
                    } elseif ($i >= 143 && $i <= 146) {
                        $vacunaNombre = 16 ;
                        $docis = isset($row[143]) ? trim($row[143]) : null;
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
                    } elseif ($i >= 147 && $i <= 151) {
                        $vacunaNombre = 17 ;
                        $docis = isset($row[147]) ? trim($row[147]) : null;
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
                    } elseif ($i >= 152 && $i <= 155) {
                        $vacunaNombre = 18 ;
                        $docis = isset($row[152]) ? trim($row[152]) : null;
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
                    } elseif ($i >= 156 && $i <= 159) {
                        $vacunaNombre = 19 ;
                        $docis = isset($row[156]) ? trim($row[156]) : null;
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
                    } elseif ($i >= 160 && $i <= 164) {
                        $vacunaNombre = 20 ;
                        $docis = isset($row[160]) ? trim($row[160]) : null;
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
                    } elseif ($i >= 165 && $i <= 168) {
                        $vacunaNombre = 21 ;
                        $docis = isset($row[165]) ? trim($row[165]) : null;
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
                    } elseif ($i >= 169 && $i <= 174) {
                        $vacunaNombre = 22 ;
                        $docis = isset($row[169]) ? trim($row[169]) : null;
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
                    }elseif ($i >= 175 && $i <= 176) {
                        $vacunaNombre = 23 ;
                        $num_frascos_utilizados = isset($row[175]) ? $row[175] : null;
                        $lote = isset($row[176]) ? $row[176] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 176;
                    } elseif ($i >= 177 && $i <= 181) {
                        $vacunaNombre = 24 ;
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
                    } elseif ($i >= 182 && $i <= 185) {
                        $vacunaNombre = 25 ;
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
                    } elseif ($i >= 186 && $i <= 189) {
                        $vacunaNombre = 26 ;
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
                    } elseif ($i >= 190 && $i <= 194) {
                        $vacunaNombre = 27 ;
                        $docis = isset($row[190]) ? trim($row[190]) : null;
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
                    } elseif ($i >= 195 && $i <= 196) {
                        $vacunaNombre = 28 ;
                        $docis = isset($row[195]) ? trim($row[195]) : null;
                        $lote = isset($row[196]) ? $row[196] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 196;
                    } elseif ($i >= 197 && $i <= 198) {
                        $vacunaNombre = 29 ;
                        $docis = isset($row[197]) ? trim($row[197]) : null;
                        $lote = isset($row[198]) ? $row[198] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 198;
                    } elseif ($i >= 199 && $i <= 200) {
                        $vacunaNombre = 30 ;
                        $docis = isset($row[199]) ? trim($row[199]) : null;
                        $lote = isset($row[200]) ? $row[200] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 200;
                    } elseif ($i >= 201 && $i <= 202) {
                        $vacunaNombre = 31 ;
                        $docis = isset($row[201]) ? trim($row[201]) : null;
                        $lote = isset($row[202]) ? $row[202] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 202;
                    } elseif ($i >= 203 && $i <= 204) {
                        $vacunaNombre = 32 ;
                        $docis = isset($row[203]) ? trim($row[203]) : null;
                        $lote = isset($row[204]) ? $row[204] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 204;
                    } elseif ($i >= 205 && $i <= 206) {
                        $vacunaNombre = 33 ;
                        $docis = isset($row[205]) ? trim($row[205]) : null;
                        $lote = isset($row[206]) ? $row[206] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 206;
                    } elseif ($i >= 207 && $i <= 208) {
                        $vacunaNombre = 34 ;
                        $docis = isset($row[207]) ? trim($row[207]) : null;
                        $lote = isset($row[208]) ? $row[208] : null;
                        $fecha_vacuna = $fechaatencion;
                        $usuario_activo_ = $usuario_activo;
                        $i = 208;
                    } elseif ($i >= 209 && $i <= 210) {
                        $vacunaNombre = 35 ;
                        $docis = isset($row[209]) ? trim($row[209]) : null;
                        $lote = isset($row[210]) ? $row[210] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 210;
                    } elseif ($i >= 211 && $i <= 212) {
                        $vacunaNombre = 36 ;
                        $docis = isset($row[211]) ? trim($row[211]) : null;
                        $lote = isset($row[212]) ? $row[212] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 212;
                    } elseif ($i >= 213 && $i <= 214) {
                        $vacunaNombre = 37 ;
                        $docis = isset($row[213]) ? trim($row[213]) : null;
                        $lote = isset($row[214]) ? $row[214] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 214;
                    } elseif ($i >= 215 && $i <= 216) {
                        $vacunaNombre = 38 ;
                        $docis = isset($row[215]) ? trim($row[215]) : null;
                        $lote = isset($row[216]) ? $row[216] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 216;
                    } elseif ($i >= 217 && $i <= 218) {
                        $vacunaNombre = 39 ;
                        $docis = isset($row[217]) ? trim($row[217]) : null;
                        $lote = isset($row[218]) ? $row[218] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 218;
                    } elseif ($i >= 219 && $i <= 220) {
                        $vacunaNombre = 40 ;
                        $docis = isset($row[219]) ? trim($row[219]) : null;
                        $lote = isset($row[220]) ? $row[220] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 220;
                    } elseif ($i >= 221 && $i <= 222) {
                        $vacunaNombre = 41 ;
                        $docis = isset($row[221]) ? trim($row[221]) : null;
                        $lote = isset($row[222]) ? $row[222] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 222;
                    } elseif ($i >= 223 && $i <= 224) {
                        $vacunaNombre = 42 ;
                        $docis = isset($row[223]) ? trim($row[223]) : null;
                        $lote = isset($row[224]) ? $row[224] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 224;
                    } elseif ($i >= 225 && $i <= 226) {
                        $vacunaNombre = 43 ;
                        $docis = isset($row[225]) ? trim($row[225]) : null;
                        $lote = isset($row[226]) ? $row[226] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 226;
                    } elseif ($i >= 227 && $i <= 228) {
                        $vacunaNombre = 44 ;
                        $docis = isset($row[227]) ? trim($row[227]) : null;
                        $lote = isset($row[228]) ? $row[228] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 228;
                    } elseif ($i >= 229 && $i <= 230) {
                        $vacunaNombre = 45 ;
                        $docis = isset($row[229]) ? trim($row[229]) : null;
                        $lote = isset($row[230]) ? $row[230] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 230;
                    } elseif ($i >= 231 && $i <= 232) {
                        $vacunaNombre = 46 ;
                        $docis = isset($row[231]) ? trim($row[231]) : null;
                        $lote = isset($row[232]) ? $row[232] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 232;
                    } elseif ($i >= 233 && $i <= 235) {
                        $vacunaNombre = 47 ;
                        $docis = isset($row[233]) ? trim($row[233]) : null;
                        $lote = isset($row[234]) ? $row[234] : null;
                        $observacion = isset($row[235]) ? $row[235] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 235;
                    } elseif ($i >= 236 && $i <= 237) {
                        $vacunaNombre = 48 ;
                        $num_frascos_utilizados = isset($row[236]) ? $row[236] : null;
                        $lote = isset($row[237]) ? $row[237] : null; 
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 237;
                    } elseif ($i >= 238 && $i <= 240) {
                        $vacunaNombre = 49 ;
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
                    } elseif ($i >= 241 && $i <= 242) {
                        $vacunaNombre = 50 ;
                        $num_frascos_utilizados = isset($row[241]) ? $row[241] : null;
                        $lote = isset($row[242]) ? $row[242] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 242;
                    } elseif ($i >= 243 && $i <= 244) {
                        $vacunaNombre = 51 ;
                        $num_frascos_utilizados = isset($row[243]) ? $row[243] : null;
                        $lote = isset($row[244]) ? $row[244] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 244;
                    } elseif ($i >= 245 && $i <= 246) {
                        $vacunaNombre = 52 ;
                        $docis = isset($row[245]) ? trim($row[245]) : null;
                        $lote = isset($row[246]) ? $row[246] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 246;
                    } elseif ($i >= 247 && $i <= 248) {
                        $vacunaNombre = 53 ;
                        $docis = isset($row[247]) ? trim($row[247]) : null;
                        $lote = isset($row[248]) ? $row[248] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 248;
                    } elseif ($i >= 249 && $i <= 250) {
                        $vacunaNombre = 54 ;
                        $docis = isset($row[249]) ? trim($row[249]) : null;
                        $lote = isset($row[250]) ? $row[250] : null;
                        $fecha_vacuna = $fechaatencion;
                        $responsable_ = $responsable;
                        $fuen_ingresado_paiweb_ = $fuen_ingresado_paiweb;
                        $motivo_noingreso_ = $motivo_noingreso;
                        $observaciones_ = $observaciones;
                        $usuario_activo_ = $usuario_activo;
                        $i = 250;
                    } else {
                        continue; // Saltar a la siguiente iteración si no se encuentra una vacuna válida
                    }

                    if ($vacunaNombre) {
                        $vacunas[] = [
                    
                            // 'nombre' => $vacunaNombre,
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
                            'vacunas_id' => $vacunaNombre,
                            'user_id' => $usuario_activo ?? null,
                            
                            'batch_verifications_id' => $this->batch_verifications_id, // Clave foránea
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        ];
                    }
                }
            }
        }
        return $vacunas;
    }

    public function getErrores()
    {
        return $this->errores;
    }

    public function debeGuardar()
    {
        return $this->guardar;
    }

    public function getFilasParaGuardar()
    {
        return $this->filasParaGuardar;
    }

    public function getBatchVerificationsID()
    {
        return $this->batch_verifications_id;
    }


}

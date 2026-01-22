<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preconcepcional extends Model
{
    protected $table = 'preconcepcionales';

    // ✅ FORZAR SQL SERVER SIN MILISEGUNDOS
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'no',
        'tipo_documento',
        'numero_identificacion',
        'apellido_1','apellido_2','nombre_1','nombre_2',
        'fecha_nacimiento','edad','sexo',
        'regimen_afiliacion','pertenencia_etnica','grupo_poblacional',
        'departamento_residencia','municipio_residencia','zona','etnia',
        'asentamiento','telefono','direccion','nivel_educativo','discapacidad',
        'mujer_cabeza_hogar','ocupacion','estado_civil','control_tradicional',
        'gestante_renuente','inasistente','nombre_ips_primaria',

        'hipertension_personal','diabetes_mellitus','enfermedad_renal','cardiopatias',
        'epilepsia','enfermedades_autoinmunes','trastornos_mentales','cancer',
        'enfermedades_infecciosas_cronicas','uso_permanente_medicamentos','alergias',

        'edad_menarquia','fecha_ultimo_periodo_mestrual','numero_gestaciones_previas',
        'partos_vaginales','cesareas','abortos','complicaciones_obstetricas_previas',

        'hipertension_familiar','diabetes_familiar','malformaciones_congenitas','enfermedades_geneticas',
        'enfermedades_mentales_familia','muerte_materna_familia',

        'inicio_vida_sexual','numero_parejas_sexuales','uso_actual_metodos_anticonceptivos',
        'antecedentes_its','deseo_reproductivo',

        'consumo_tabaco','consumo_alcohol','consumo_sustancias_psicoactivas','actividad_fisica',
        'alimentacion_saludable','violencias',

        'peso','talla','imc','riesgo_nutricional','suplementacion_acido_folico',

        'tetanos','influenza','covid_19',

        'fecha_tamizaje_sifilis','resultado_sifilis',
        'fecha_tamizaje_vih','resultado_vih',
        'fecha_tamizaje_hepatitis_b','resultado_hepatitis_b',
        'citologia','tamizaje_salud_mental',

        'riesgo_preconcepcional',

        'consejeria_preconcepcional','educacion_planificacion_familiar',
        'recomendaciones_nutricionales','ordenes_medicas',

        // ✅ NUEVO: para manejar lotes
        'created_batch_id',
        'last_batch_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
        'fecha_ultimo_periodo_mestrual' => 'date:Y-m-d',
        'fecha_tamizaje_sifilis' => 'date:Y-m-d',
        'fecha_tamizaje_vih' => 'date:Y-m-d',
        'fecha_tamizaje_hepatitis_b' => 'date:Y-m-d',
        'peso' => 'decimal:2',
        'talla' => 'decimal:2',
        'imc' => 'decimal:2',

        'created_batch_id' => 'integer',
        'last_batch_id' => 'integer',


        // (Opcional) si quieres mostrar así en vistas/export
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    // ✅ RELACIONES (recomendado)
    public function createdBatch()
    {
        return $this->belongsTo(\App\Models\PreconcepcionalImportBatch::class, 'created_batch_id');
    }

    public function lastBatch()
    {
        return $this->belongsTo(\App\Models\PreconcepcionalImportBatch::class, 'last_batch_id');
    }
}

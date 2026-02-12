<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientMaestrosiv549 extends Model
{
    protected $table = 'seguimient_maestrosiv549';

    protected $fillable = [
        'asignacion_id',

        // Hospitalización y egreso
        'fecha_hospitalizacion',
        'gestion_hospitalizacion',
        'fecha_egreso',
        'institucion_egreso_paciente',

        // Criterios / complicaciones
        'eclampsia',
        'preeclampsia_severa',
        'sepsis_infeccion_sistemica_severa',
        'hemorragia_obstetrica_severa',
        'ruptura_uterina',
        'falla_cardiovascular',
        'falla_renal',
        'falla_hepatica',
        'falla_cerebral',
        'falla_respiratoria',
        'falla_coagulacion',
        'cirugia_adicional',
        'ttl_criter',
        'diagnostico_cie10',
        'causa_agrupada',

        // Seguimiento inmediato
        'descripcion_seguimiento_inmediato',
        'fecha_control_rn_inmediato',
        'seguimiento_efectivo_inmediato',

        // Seguimiento 1
        'fecha_seguimiento_1',
        'tipo_seguimiento_1',
        'paciente_sigue_embarazo_1',
        'fecha_control_1',
        'metodo_anticonceptivo',
        'fecha_consulta_rn_1',
        'entrega_medicamentos_labs_1',
        'gestion_posegreso_1',

        // Seguimiento 2 (7 días)
        'fecha_seguimiento_2',
        'paciente_sigue_embarazo_2',
        'fecha_control_2',
        'fecha_consulta_rn_2',
        'entrega_medicamentos_labs_2',
        'gestion_primera_semana',
        'seguimiento_efectivo_2',

        // Seguimiento 3 (14 días)
        'fecha_seguimiento_3',
        'tipo_seguimiento_3',
        'paciente_sigue_embarazo_3',
        'fecha_control_3',
        'fecha_consulta_rn_3',
        'entrega_medicamentos_labs_3',
        'gestion_segunda_semana',
        'seguimiento_efectivo_3',

        // Seguimiento 4 (21 días)
        'fecha_seguimiento_4',
        'tipo_seguimiento_4',
        'paciente_sigue_embarazo_4',
        'fecha_control_4',
        'fecha_consulta_rn_4',
        'entrega_medicamentos_labs_4',
        'gestion_tercera_semana',
        'seguimiento_efectivo_4',

        // Seguimiento 5 (28 días)
        'fecha_seguimiento_5',
        'tipo_seguimiento_5',
        'paciente_sigue_embarazo_5',
        'fecha_control_5',
        'fecha_consulta_rn_5',
        'entrega_medicamentos_labs_5',
        'fecha_consulta_lactancia',
        'fecha_control_metodo',
        'gestion_despues_mes',
        'seguimiento_efectivo_5',

        // Largo plazo
        'fecha_consulta_6_meses',
        'fecha_consulta_1_ano',
    ];

    protected $casts = [
        // fechas
        'fecha_hospitalizacion' => 'date',
        'fecha_egreso' => 'date',
        'fecha_control_rn_inmediato' => 'date',
        'fecha_seguimiento_1' => 'date',
        'fecha_control_1' => 'date',
        'fecha_consulta_rn_1' => 'date',
        'fecha_seguimiento_2' => 'date',
        'fecha_control_2' => 'date',
        'fecha_consulta_rn_2' => 'date',
        'fecha_seguimiento_3' => 'date',
        'fecha_control_3' => 'date',
        'fecha_consulta_rn_3' => 'date',
        'fecha_seguimiento_4' => 'date',
        'fecha_control_4' => 'date',
        'fecha_consulta_rn_4' => 'date',
        'fecha_seguimiento_5' => 'date',
        'fecha_control_5' => 'date',
        'fecha_consulta_rn_5' => 'date',
        'fecha_consulta_lactancia' => 'date',
        'fecha_control_metodo' => 'date',
        'fecha_consulta_6_meses' => 'date',
        'fecha_consulta_1_ano' => 'date',

        // boolean
        'paciente_sigue_embarazo_1' => 'boolean',
        'paciente_sigue_embarazo_2' => 'boolean',
        'paciente_sigue_embarazo_3' => 'boolean',
        'paciente_sigue_embarazo_4' => 'boolean',
        'paciente_sigue_embarazo_5' => 'boolean',

        'eclampsia' => 'boolean',
        'preeclampsia_severa' => 'boolean',
        'sepsis_infeccion_sistemica_severa' => 'boolean',
        'hemorragia_obstetrica_severa' => 'boolean',
        'ruptura_uterina' => 'boolean',
        'falla_cardiovascular' => 'boolean',
        'falla_renal' => 'boolean',
        'falla_hepatica' => 'boolean',
        'falla_cerebral' => 'boolean',
        'falla_respiratoria' => 'boolean',
        'falla_coagulacion' => 'boolean',
        'cirugia_adicional' => 'boolean',

        'seguimiento_efectivo_inmediato' => 'boolean',
        'seguimiento_efectivo_2' => 'boolean',
        'seguimiento_efectivo_3' => 'boolean',
        'seguimiento_efectivo_4' => 'boolean',
        'seguimiento_efectivo_5' => 'boolean',
    ];

    public function asignacion()
    {
        return $this->belongsTo(\App\Models\AsignacionesMaestrosiv549::class, 'asignacion_id');
    }

    public function alertasEnviadas()
    {
        return $this->hasMany(\App\Models\SeguimientoAlerta::class, 'seguimiento_id');
    }

    public function scopeCompletos($q)
    {
        return $q->whereNotNull('fecha_consulta_1_ano');
    }
}

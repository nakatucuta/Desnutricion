<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientMaestrosiv549 extends Model
{
     public function getDateFormat(){
        return 'Y-d-m h:m:s';
        }
    protected $table = 'seguimient_maestrosiv549';

    protected $fillable = [
        'asignacion_id',
        'fecha_hospitalizacion',
        'gestion_hospitalizacion',
        'fecha_egreso',
        'descripcion_seguimiento_inmediato',
        'fecha_seguimiento_1',
        'tipo_seguimiento_1',
        'paciente_sigue_embarazo_1',
        'fecha_control_1',
        'metodo_anticonceptivo',
        'fecha_consulta_rn_1',
        'entrega_medicamentos_labs_1',
        'gestion_posegreso_1',
        'fecha_seguimiento_2',
        'paciente_sigue_embarazo_2',
        'fecha_control_2',
        'fecha_consulta_rn_2',
        'entrega_medicamentos_labs_2',
        'gestion_primera_semana',
        'fecha_seguimiento_3',
        'tipo_seguimiento_3',
        'paciente_sigue_embarazo_3',
        'fecha_control_3',
        'fecha_consulta_rn_3',
        'entrega_medicamentos_labs_3',
        'gestion_segunda_semana',
        'fecha_seguimiento_4',
        'tipo_seguimiento_4',
        'paciente_sigue_embarazo_4',
        'fecha_control_4',
        'fecha_consulta_rn_4',
        'entrega_medicamentos_labs_4',
        'gestion_tercera_semana',
        'fecha_seguimiento_5',
        'tipo_seguimiento_5',
        'paciente_sigue_embarazo_5',
        'fecha_control_5',
        'fecha_consulta_rn_5',
        'entrega_medicamentos_labs_5',
        'fecha_consulta_lactancia',
        'fecha_control_metodo',
        'gestion_despues_mes',
        'fecha_consulta_6_meses',
        'fecha_consulta_1_ano',
    ];

    public function asignacion()
    {
        return $this->belongsTo(\App\Models\AsignacionesMaestrosiv549::class, 'asignacion_id');
    }

     public function scopeCompletos($q)
    {
        // Usa esta si el “último dato” es solo 1:
        return $q->whereNotNull('fecha_consulta_1_ano');

        // O si quieres exigir TODO el bloque de largo plazo, cambia por:
        /*
        return $q->whereNotNull('fecha_consulta_lactancia')
                 ->whereNotNull('fecha_control_metodo')
                 ->whereNotNull('fecha_consulta_6_meses')
                 ->whereNotNull('fecha_consulta_1_ano');
        */
    }
}
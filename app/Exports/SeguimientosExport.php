<?php

namespace App\Exports;

use App\Models\SeguimientMaestrosiv549;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SeguimientosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;
    protected bool $canSeeAll;
    protected int $userId;

    public function __construct(array $filters = [], bool $canSeeAll = false, int $userId = 0)
    {
        $this->filters  = $filters;
        $this->canSeeAll = $canSeeAll;
        $this->userId   = $userId;
    }

    public function query()
    {
        $q = SeguimientMaestrosiv549::query()
            ->with(['asignacion.user']) // para mapear fácilmente
            ->orderByDesc('id');

        // Permisos: si NO es admin (1/2), solo ve sus asignaciones
        if (!$this->canSeeAll) {
            $q->whereHas('asignacion', function (Builder $qa) {
                $qa->where('user_id', $this->userId);
            });
        }

        // Filtros por paciente (sobre asignación)
        if (!empty($this->filters['tip_ide_'])) {
            $tip = trim($this->filters['tip_ide_']);
            $q->whereHas('asignacion', fn($qa) => $qa->where('tip_ide_', 'LIKE', "%{$tip}%"));
        }
        if (!empty($this->filters['num_ide_'])) {
            $num = trim($this->filters['num_ide_']);
            $q->whereHas('asignacion', fn($qa) => $qa->where('num_ide_', 'LIKE', "%{$num}%"));
        }

        // Filtro de fechas por created_at del seguimiento
        $desde = $this->filters['fec_desde'] ?? null;
        $hasta = $this->filters['fec_hasta'] ?? null;
        if ($desde && $hasta) {
            $q->whereBetween('created_at', [
                "{$desde} 00:00:00",
                "{$hasta} 23:59:59",
            ]);
        } elseif ($desde) {
            $q->where('created_at', '>=', "{$desde} 00:00:00");
        } elseif ($hasta) {
            $q->where('created_at', '<=', "{$hasta} 23:59:59");
        }

        return $q;
    }

    public function headings(): array
    {
        return [
            // Paciente primero
            'ID Seguimiento',
            'ID Asignación',
            'Tipo ID',
            'Número ID',
            'Primer Nombre',
            'Segundo Nombre',
            'Primer Apellido',
            'Segundo Apellido',
            'Paciente',
            'Evento',
            'Fecha Notificación',
            'Prestador',

            // Seguimiento
            'Fecha Hospitalización',
            'Gestión Hospitalización',
            'Fecha Egreso',
            'Descripción (48–72h)',

            'Fecha Seg 1',
            'Tipo Seg 1 (1=Telf,2=Dom,3=Otro)',
            '¿Sigue Embarazo 1?',
            'Fecha Control 1',
            'Método Anticonceptivo',
            'Fecha Consulta RN 1',
            'Entrega Meds/Labs 1',
            'Gestión Posegreso 1',

            'Fecha Seg 2',
            '¿Sigue Embarazo 2?',
            'Fecha Control 2',
            'Fecha Consulta RN 2',
            'Entrega Meds/Labs 2',
            'Gestión Primera Semana',

            'Fecha Seg 3',
            'Tipo Seg 3 (1=Telf,2=Dom)',
            '¿Sigue Embarazo 3?',
            'Fecha Control 3',
            'Fecha Consulta RN 3',
            'Entrega Meds/Labs 3',
            'Gestión Segunda Semana',

            'Fecha Seg 4',
            'Tipo Seg 4 (1=Telf,2=Dom)',
            '¿Sigue Embarazo 4?',
            'Fecha Control 4',
            'Fecha Consulta RN 4',
            'Entrega Meds/Labs 4',
            'Gestión Tercera Semana',

            'Fecha Seg 5',
            'Tipo Seg 5 (1=Telf,2=Dom)',
            '¿Sigue Embarazo 5?',
            'Fecha Control 5',
            'Fecha Consulta RN 5',
            'Entrega Meds/Labs 5',

            'Fecha Consulta Lactancia',
            'Fecha Control Método',
            'Gestión Después del Mes',
            'Fecha Consulta 6 Meses',
            'Fecha Consulta 1 Año',

            'Creado',
            'Actualizado',
        ];
    }

    public function map($s): array
    {
        $a = $s->asignacion; // puede ser null si hubo inconsistencia, lo manejamos
        $paciente = $a
            ? trim("{$a->pri_nom_} {$a->seg_nom_} {$a->pri_ape_} {$a->seg_ape_}")
            : '';

        return [
            // Paciente primero
            $s->id,
            $s->asignacion_id,
            $a->tip_ide_   ?? '',
            $a->num_ide_   ?? '',
            $a->pri_nom_   ?? '',
            $a->seg_nom_   ?? '',
            $a->pri_ape_   ?? '',
            $a->seg_ape_   ?? '',
            $paciente,
            $a->nom_eve    ?? '',
            $a->fec_not    ?? '',
            optional($a->user)->name ?? '',

            // Seguimiento
            $s->fecha_hospitalizacion,
            $s->gestion_hospitalizacion,
            $s->fecha_egreso,
            $s->descripcion_seguimiento_inmediato,

            $s->fecha_seguimiento_1,
            $s->tipo_seguimiento_1,
            $this->yn($s->paciente_sigue_embarazo_1),
            $s->fecha_control_1,
            $s->metodo_anticonceptivo,
            $s->fecha_consulta_rn_1,
            $s->entrega_medicamentos_labs_1,
            $s->gestion_posegreso_1,

            $s->fecha_seguimiento_2,
            $this->yn($s->paciente_sigue_embarazo_2),
            $s->fecha_control_2,
            $s->fecha_consulta_rn_2,
            $s->entrega_medicamentos_labs_2,
            $s->gestion_primera_semana,

            $s->fecha_seguimiento_3,
            $s->tipo_seguimiento_3,
            $this->yn($s->paciente_sigue_embarazo_3),
            $s->fecha_control_3,
            $s->fecha_consulta_rn_3,
            $s->entrega_medicamentos_labs_3,
            $s->gestion_segunda_semana,

            $s->fecha_seguimiento_4,
            $s->tipo_seguimiento_4,
            $this->yn($s->paciente_sigue_embarazo_4),
            $s->fecha_control_4,
            $s->fecha_consulta_rn_4,
            $s->entrega_medicamentos_labs_4,
            $s->gestion_tercera_semana,

            $s->fecha_seguimiento_5,
            $s->tipo_seguimiento_5,
            $this->yn($s->paciente_sigue_embarazo_5),
            $s->fecha_control_5,
            $s->fecha_consulta_rn_5,
            $s->entrega_medicamentos_labs_5,

            $s->fecha_consulta_lactancia,
            $s->fecha_control_metodo,
            $s->gestion_despues_mes,
            $s->fecha_consulta_6_meses,
            $s->fecha_consulta_1_ano,

            optional($s->created_at)->format('Y-m-d H:i'),
            optional($s->updated_at)->format('Y-m-d H:i'),
        ];
    }

    private function yn($val): string
    {
        if ($val === null || $val === '') return '';
        return (string)$val === '1' ? 'Sí' : 'No';
    }
}

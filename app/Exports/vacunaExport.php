<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class VacunaExport implements FromQuery, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable;

    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '8G');
    }

    public function query()
    {
        $query = DB::table('DESNUTRICION.dbo.vacunas as a')
            ->join('afiliados as b', 'b.id', '=', 'a.afiliado_id')
            ->join('referencia_vacunas as d', 'a.vacunas_id', '=', 'd.id')
            ->join('users', 'users.id', '=', 'a.user_id')
            ->select(
                'users.name',
                'b.fecha_atencion',
                'b.tipo_identificacion',
                'b.numero_identificacion',
                'b.primer_nombre',
                'b.segundo_nombre',
                'b.primer_apellido',
                'b.segundo_apellido',
                'b.fecha_nacimiento',
                
                DB::raw('DATEDIFF(YEAR, b.fecha_nacimiento, a.fecha_vacuna) as edad_anos'),
                DB::raw('DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna) % 12 as edad_meses'),
                DB::raw('
                CASE 
                    WHEN DATEDIFF(DAY, DATEADD(MONTH, DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna), b.fecha_nacimiento), a.fecha_vacuna) < 0 
                    THEN 0 
                    ELSE DATEDIFF(DAY, DATEADD(MONTH, DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna), b.fecha_nacimiento), a.fecha_vacuna) 
                END as edad_dias
            '),
                'b.total_meses',
                'b.esquema_completo',
                'b.sexo',
                'b.genero',
                'b.orientacion_sexual',
                'b.edad_gestacional',
                'b.pais_nacimiento',
                'b.estatus_migratorio',
                'b.lugar_atencion_parto',
                'b.regimen',
                'b.aseguradora',
                'b.pertenencia_etnica',
                'b.desplazado',
                'b.discapacitado',
                'b.fallecido',
                'b.victima_conflicto',
                'b.estudia',
                'b.pais_residencia',
                'b.departamento_residencia',
                'b.municipio_residencia',
                'b.comuna',
                'b.area',
                'b.direccion',
                'b.telefono_fijo',
                'b.celular',
                'b.email',
                'b.autoriza_llamadas',
                'b.autoriza_correos',
                'b.contraindicacion_vacuna',
                'b.enfermedad_contraindicacion',
                'b.reaccion_biologicos',
                'b.sintomas_reaccion',
                'b.condicion_usuaria',
                'b.fecha_ultima_menstruacion',
                'b.semanas_gestacion',
                'b.fecha_prob_parto',
                'b.embarazos_previos',
                'b.fecha_antecedente',
                'b.tipo_antecedente',
                'b.descripcion_antecedente',
                'b.observaciones_especiales',
                'b.madre_tipo_identificacion',
                'b.madre_identificacion',
                'b.madre_primer_nombre',
                'b.madre_segundo_nombre',
                'b.madre_primer_apellido',
                'b.madre_segundo_apellido',
                'b.madre_correo',
                'b.madre_telefono',
                'b.madre_celular',
                'b.madre_regimen',
                'b.madre_pertenencia_etnica',
                'b.madre_desplazada',
                'b.cuidador_tipo_identificacion',
                'b.cuidador_identificacion',
                'b.cuidador_primer_nombre',
                'b.cuidador_segundo_nombre',
                'b.cuidador_primer_apellido',
                'b.cuidador_segundo_apellido',
                'b.cuidador_parentesco',
                'b.cuidador_correo',
                'b.cuidador_telefono',
                'b.cuidador_celular',
                'b.esquema_vacunacion',
                'd.nombre as vacuna_nombre',
                'a.docis',
                'a.laboratorio',
                'a.lote',
                'a.jeringa',
                'a.lote_jeringa',
                'a.diluyente',
                'a.lote_diluyente',
                'a.observacion',
                'a.gotero',
                'a.tipo_neumococo',
                'a.num_frascos_utilizados',
                'a.fecha_vacuna',
                'a.responsable',
                'a.fuen_ingresado_paiweb',
                'a.motivo_noingreso',
                'a.observaciones',
                'a.created_at'
            )
            ->orderBy('a.created_at', 'desc');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('a.fecha_vacuna', [$this->startDate, $this->endDate]);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Prestador', 'Fecha de Atención', 'Tipo de Identificación', 'Número de Identificación',
            'Primer Nombre', 'Segundo Nombre', 'Primer Apellido', 'Segundo Apellido',
            'Fecha de Nacimiento', 'Edad (Años)', 'Edad (Meses)', 'Edad (Días)',
            'Total de Meses', 'Esquema Completo', 'Sexo', 'Género', 'Orientación Sexual', 'Edad Gestacional',
            'País de Nacimiento', 'Estatus Migratorio', 'Lugar de Atención del Parto', 'Régimen', 'Aseguradora',
            'Pertenencia Étnica', 'Desplazado', 'Discapacitado', 'Fallecido', 'Víctima de Conflicto', 'Estudia',
            'País de Residencia', 'Departamento de Residencia', 'Municipio de Residencia', 'Comuna', 'Área',
            'Dirección', 'Teléfono Fijo', 'Celular', 'Email', 'Autoriza Llamadas', 'Autoriza Correos',
            'Contraindicación Vacuna', 'Enfermedad Contraindicación', 'Reacción a Biológicos', 'Síntomas Reacción',
            'Condición Usuaria', 'Fecha Última Menstruación', 'Semanas de Gestación', 'Fecha Probable de Parto',
            'Embarazos Previos', 'Fecha Antecedente', 'Tipo de Antecedente', 'Descripción del Antecedente',
            'Observaciones Especiales', 'Madre Tipo de Identificación', 'Madre Identificación', 'Madre Primer Nombre',
            'Madre Segundo Nombre', 'Madre Primer Apellido', 'Madre Segundo Apellido', 'Madre Correo',
            'Madre Teléfono', 'Madre Celular', 'Madre Régimen', 'Madre Pertenencia Étnica', 'Madre Desplazada',
            'Cuidador Tipo de Identificación', 'Cuidador Identificación', 'Cuidador Primer Nombre',
            'Cuidador Segundo Nombre', 'Cuidador Primer Apellido', 'Cuidador Segundo Apellido',
            'Cuidador Parentesco', 'Cuidador Correo', 'Cuidador Teléfono', 'Cuidador Celular', 'Esquema de Vacunación',
            'Nombre de la Vacuna', 'Dosis', 'Laboratorio', 'Lote', 'Jeringa', 'Lote de Jeringa', 'Diluyente',
            'Lote de Diluyente', 'Observación', 'Gotero', 'Tipo Neumococo', 'Número de Frascos Utilizados',
            'Fecha de Vacunación', 'Responsable', 'Fuente Ingresado en PAIWEB', 'Motivo No Ingreso',
            'Observaciones', 'Fecha de Creación'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $cellRange = 'A1:CP1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('e6ffe6');
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal('center');
                $event->sheet->setAutoFilter($cellRange);
                $event->sheet->getDelegate()->getStyle('B2:CP2000')->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ]
                ]);
            },
        ];
    }
}
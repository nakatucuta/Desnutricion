<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
        $selects = array_map(fn ($column) => $column['select'], self::columns());

        return DB::table('vacunas as a')
            ->join('afiliados as b', 'b.id', '=', 'a.afiliado_id')
            ->join('referencia_vacunas as d', 'd.id', '=', 'a.vacunas_id')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->leftJoin('SGA.dbo.maestroIps as j', 'b.numero_Carnet', '=', 'j.numeroCarnet')
            ->leftJoin('SGA.dbo.maestroIpsGru as k', 'j.idGrupoIps', '=', 'k.id')
            ->select($selects)
            ->when($this->startDate && $this->endDate, function ($q) {
                $q->whereBetween('a.fecha_vacuna', [$this->startDate, $this->endDate]);
            })
            ->orderBy('a.created_at', 'desc');
    }

    public function headings(): array
    {
        return self::headingsStatic();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count(self::headingsStatic()));
                $headerRange = "A1:{$lastColumn}1";
                $dataRange = "A2:{$lastColumn}2000";

                $sheet->getStyle($headerRange)->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle($headerRange)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('E6FFE6');
                $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->setAutoFilter($headerRange);

                $sheet->getStyle($dataRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                ]);
            },
        ];
    }

    public static function headingsStatic(): array
    {
        return array_map(fn ($column) => $column['heading'], self::columns());
    }

    public static function selectsStatic(): array
    {
        return array_map(fn ($column) => $column['select'], self::columns());
    }

    private static function columns(): array
    {
        return [
            ['select' => 'u.name', 'heading' => 'Prestador'],
            ['select' => 'k.descrip as ips_primaria', 'heading' => 'IPS PRIMARIA'],
            ['select' => 'b.fecha_atencion', 'heading' => 'Fecha de Atencion'],
            ['select' => 'b.tipo_identificacion', 'heading' => 'Tipo de Identificacion'],
            ['select' => 'b.numero_identificacion', 'heading' => 'Numero de Identificacion'],
            ['select' => 'b.numero_carnet', 'heading' => 'Numero de Carnet'],
            ['select' => 'b.primer_nombre', 'heading' => 'Primer Nombre'],
            ['select' => 'b.segundo_nombre', 'heading' => 'Segundo Nombre'],
            ['select' => 'b.primer_apellido', 'heading' => 'Primer Apellido'],
            ['select' => 'b.segundo_apellido', 'heading' => 'Segundo Apellido'],
            ['select' => 'b.fecha_nacimiento', 'heading' => 'Fecha de Nacimiento'],
            ['select' => DB::raw('DATEDIFF(YEAR, b.fecha_nacimiento, a.fecha_vacuna) AS edad_anos'), 'heading' => 'Edad (Anos)'],
            ['select' => DB::raw('DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna) % 12 AS edad_meses'), 'heading' => 'Edad (Meses)'],
            ['select' => DB::raw("
                CASE
                    WHEN DATEDIFF(
                           DAY,
                           DATEADD(
                               MONTH,
                               DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna),
                               b.fecha_nacimiento
                           ),
                           a.fecha_vacuna
                         ) < 0
                    THEN 0
                    ELSE DATEDIFF(
                           DAY,
                           DATEADD(
                               MONTH,
                               DATEDIFF(MONTH, b.fecha_nacimiento, a.fecha_vacuna),
                               b.fecha_nacimiento
                           ),
                           a.fecha_vacuna
                         )
                END AS edad_dias
            "), 'heading' => 'Edad (Dias)'],
            ['select' => 'b.total_meses', 'heading' => 'Total de Meses'],
            ['select' => 'b.esquema_completo', 'heading' => 'Esquema Completo'],
            ['select' => 'b.sexo', 'heading' => 'Sexo'],
            ['select' => 'b.genero', 'heading' => 'Genero'],
            ['select' => 'b.orientacion_sexual', 'heading' => 'Orientacion Sexual'],
            ['select' => 'b.edad_gestacional', 'heading' => 'Edad Gestacional'],
            ['select' => 'b.pais_nacimiento', 'heading' => 'Pais de Nacimiento'],
            ['select' => 'b.estatus_migratorio', 'heading' => 'Estatus Migratorio'],
            ['select' => 'b.lugar_atencion_parto', 'heading' => 'Lugar de Atencion del Parto'],
            ['select' => 'b.regimen', 'heading' => 'Regimen'],
            ['select' => 'b.aseguradora', 'heading' => 'Aseguradora'],
            ['select' => 'b.pertenencia_etnica', 'heading' => 'Pertenencia Etnica'],
            ['select' => 'b.desplazado', 'heading' => 'Desplazado'],
            ['select' => 'b.discapacitado', 'heading' => 'Discapacitado'],
            ['select' => 'b.fallecido', 'heading' => 'Fallecido'],
            ['select' => 'b.victima_conflicto', 'heading' => 'Victima de Conflicto'],
            ['select' => 'b.estudia', 'heading' => 'Estudia'],
            ['select' => 'b.pais_residencia', 'heading' => 'Pais de Residencia'],
            ['select' => 'b.departamento_residencia', 'heading' => 'Departamento de Residencia'],
            ['select' => 'b.municipio_residencia', 'heading' => 'Municipio de Residencia'],
            ['select' => 'b.comuna', 'heading' => 'Comuna'],
            ['select' => 'b.area', 'heading' => 'Area'],
            ['select' => 'b.direccion', 'heading' => 'Direccion'],
            ['select' => 'b.telefono_fijo', 'heading' => 'Telefono Fijo'],
            ['select' => 'b.celular', 'heading' => 'Celular'],
            ['select' => 'b.email', 'heading' => 'Email'],
            ['select' => 'b.autoriza_llamadas', 'heading' => 'Autoriza Llamadas'],
            ['select' => 'b.autoriza_correos', 'heading' => 'Autoriza Correos'],
            ['select' => 'b.contraindicacion_vacuna', 'heading' => 'Contraindicacion Vacuna'],
            ['select' => 'b.enfermedad_contraindicacion', 'heading' => 'Enfermedad Contraindicacion'],
            ['select' => 'b.reaccion_biologicos', 'heading' => 'Reaccion a Biologicos'],
            ['select' => 'b.sintomas_reaccion', 'heading' => 'Sintomas Reaccion'],
            ['select' => 'b.condicion_usuaria', 'heading' => 'Condicion Usuaria'],
            ['select' => 'b.fecha_ultima_menstruacion', 'heading' => 'Fecha Ultima Menstruacion'],
            ['select' => 'b.semanas_gestacion', 'heading' => 'Semanas de Gestacion'],
            ['select' => 'b.fecha_prob_parto', 'heading' => 'Fecha Probable de Parto'],
            ['select' => 'b.embarazos_previos', 'heading' => 'Embarazos Previos'],
            ['select' => 'b.fecha_antecedente', 'heading' => 'Fecha Antecedente'],
            ['select' => 'b.tipo_antecedente', 'heading' => 'Tipo de Antecedente'],
            ['select' => 'b.descripcion_antecedente', 'heading' => 'Descripcion del Antecedente'],
            ['select' => 'b.observaciones_especiales', 'heading' => 'Observaciones Especiales'],
            ['select' => 'b.madre_tipo_identificacion', 'heading' => 'Madre Tipo de Identificacion'],
            ['select' => 'b.madre_identificacion', 'heading' => 'Madre Identificacion'],
            ['select' => 'b.madre_primer_nombre', 'heading' => 'Madre Primer Nombre'],
            ['select' => 'b.madre_segundo_nombre', 'heading' => 'Madre Segundo Nombre'],
            ['select' => 'b.madre_primer_apellido', 'heading' => 'Madre Primer Apellido'],
            ['select' => 'b.madre_segundo_apellido', 'heading' => 'Madre Segundo Apellido'],
            ['select' => 'b.madre_correo', 'heading' => 'Madre Correo'],
            ['select' => 'b.madre_telefono', 'heading' => 'Madre Telefono'],
            ['select' => 'b.madre_celular', 'heading' => 'Madre Celular'],
            ['select' => 'b.madre_regimen', 'heading' => 'Madre Regimen'],
            ['select' => 'b.madre_pertenencia_etnica', 'heading' => 'Madre Pertenencia Etnica'],
            ['select' => 'b.madre_desplazada', 'heading' => 'Madre Desplazada'],
            ['select' => 'b.cuidador_tipo_identificacion', 'heading' => 'Cuidador Tipo de Identificacion'],
            ['select' => 'b.cuidador_identificacion', 'heading' => 'Cuidador Identificacion'],
            ['select' => 'b.cuidador_primer_nombre', 'heading' => 'Cuidador Primer Nombre'],
            ['select' => 'b.cuidador_segundo_nombre', 'heading' => 'Cuidador Segundo Nombre'],
            ['select' => 'b.cuidador_primer_apellido', 'heading' => 'Cuidador Primer Apellido'],
            ['select' => 'b.cuidador_segundo_apellido', 'heading' => 'Cuidador Segundo Apellido'],
            ['select' => 'b.cuidador_parentesco', 'heading' => 'Cuidador Parentesco'],
            ['select' => 'b.cuidador_correo', 'heading' => 'Cuidador Correo'],
            ['select' => 'b.cuidador_telefono', 'heading' => 'Cuidador Telefono'],
            ['select' => 'b.cuidador_celular', 'heading' => 'Cuidador Celular'],
            ['select' => 'b.esquema_vacunacion', 'heading' => 'Esquema de Vacunacion'],
            ['select' => 'd.nombre as vacuna_nombre', 'heading' => 'Nombre de la Vacuna'],
            ['select' => 'a.docis', 'heading' => 'Dosis'],
            ['select' => 'a.laboratorio', 'heading' => 'Laboratorio'],
            ['select' => 'a.lote', 'heading' => 'Lote'],
            ['select' => 'a.jeringa', 'heading' => 'Jeringa'],
            ['select' => 'a.lote_jeringa', 'heading' => 'Lote de Jeringa'],
            ['select' => 'a.diluyente', 'heading' => 'Diluyente'],
            ['select' => 'a.lote_diluyente', 'heading' => 'Lote de Diluyente'],
            ['select' => 'a.observacion', 'heading' => 'Observacion'],
            ['select' => 'a.gotero', 'heading' => 'Gotero'],
            ['select' => 'a.tipo_neumococo', 'heading' => 'Tipo Neumococo'],
            ['select' => 'a.num_frascos_utilizados', 'heading' => 'Numero de Frascos Utilizados'],
            ['select' => 'a.fecha_vacuna', 'heading' => 'Fecha de Vacunacion'],
            ['select' => 'a.responsable', 'heading' => 'Responsable'],
            ['select' => 'a.fuen_ingresado_paiweb', 'heading' => 'Fuente Ingresado en PAIWEB'],
            ['select' => 'a.motivo_noingreso', 'heading' => 'Motivo No Ingreso'],
            ['select' => 'a.observaciones', 'heading' => 'Observaciones'],
            ['select' => 'a.regimen as regimen_vacuna', 'heading' => 'Regimen de la Vacuna'],
            ['select' => 'a.created_at', 'heading' => 'Fecha de Creacion'],
        ];
    }
}

<?php

namespace App\Exports;

use App\Models\Cargue412;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithChunkReading,
    ShouldAutoSize,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;

class Cargue412Export implements 
    FromQuery,
    WithHeadings,
    WithMapping,
    WithChunkReading,
    ShouldAutoSize,
    WithEvents
{
    /**
     * Prepara el query (streaming por chunks).
     */
    public function query()
    {
        return Cargue412::from('cargue412s as a')
            ->select(
                'u.name', 
                'd.descrip as ips_primaria', 
                'a.id',
                'a.numero_orden',
                'a.nombre_coperante',
                'a.fecha_captacion',
                'a.municipio',
                'a.nombre_rancheria',
                'a.ubicacion_casa',
                'a.nombre_cuidador',
                'a.identioficacion_cuidador',
                'a.telefono_cuidador',
                'a.nombre_eapb_cuidador',
                'a.nombre_autoridad_trad_ansestral',
                'a.datos_contacto_autoridad',
                'a.primer_nombre',
                'a.segundo_nombre',
                'a.primer_apellido',
                'a.segundo_apellido',
                'a.tipo_identificacion',
                'a.numero_identificacion',
                'a.sexo',
                'a.fecha_nacimieto_nino',
                'a.edad_meses',
                'a.regimen_afiliacion',
                'a.nombre_eapb_menor',
                'a.peso_kg',
                'a.logitud_talla_cm',
                'a.perimetro_braqueal',
                'a.signos_peligro_infeccion_respiratoria',
                'a.sexosignos_desnutricion',
                'a.puntaje_z',
                'a.calsificacion_antropometrica',
                'a.estado',
                'a.user_id',
                'a.created_at',
                'a.updated_at',
                'a.numero_profesional',
                'a.uds'
            )
            ->leftJoin(
                DB::connection('sqlsrv_1')
                  ->raw('[sga].[dbo].[maestroidentificaciones] AS b'),
                function($join) {
                    $join->on('a.tipo_identificacion', '=', 'b.tipoIdentificacion')
                         ->on('a.numero_identificacion', '=', 'b.identificacion');
                }
            )
            ->leftJoin(
                DB::connection('sqlsrv_1')
                  ->raw('[sga].[dbo].[maestroips] AS c'),
                'b.numeroCarnet', '=', 'c.numeroCarnet'
            )
            ->leftJoin(
                DB::connection('sqlsrv_1')
                  ->raw('[sga].[dbo].[maestroIpsGru] AS d'),
                'c.idGrupoIps', '=', 'd.id'
            )
            ->leftJoin('users AS u', 'u.id', '=', 'a.user_id')
            ->orderBy('a.id', 'asc');  // evita el error de "cargue412s.id"
    }

    /**
     * Cuántas filas por chunk.
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Mapea cada fila **en el orden exacto** de tu reporte original.
     */
    public function map($row): array
    {
        return [
            // coinciden con select(...) y con tu headings()
            $row->name,
            $row->ips_primaria,
            $row->id,
            $row->numero_orden,
            $row->nombre_coperante,
            $row->fecha_captacion,
            $row->municipio,
            $row->nombre_rancheria,
            $row->ubicacion_casa,
            $row->nombre_cuidador,
            $row->identioficacion_cuidador,
            $row->telefono_cuidador,
            $row->nombre_eapb_cuidador,
            $row->nombre_autoridad_trad_ansestral,
            $row->datos_contacto_autoridad,
            $row->primer_nombre,
            $row->segundo_nombre,
            $row->primer_apellido,
            $row->segundo_apellido,
            $row->tipo_identificacion,
            $row->numero_identificacion,
            $row->sexo,
            $row->fecha_nacimieto_nino,
            $row->edad_meses,
            $row->regimen_afiliacion,
            $row->nombre_eapb_menor,
            $row->peso_kg,
            $row->logitud_talla_cm,
            $row->perimetro_braqueal,
            $row->signos_peligro_infeccion_respiratoria,
            $row->sexosignos_desnutricion,
            $row->puntaje_z,
            $row->calsificacion_antropometrica,
            $row->estado,
            $row->user_id,
            $row->created_at,
            $row->updated_at,
            $row->numero_profesional,
            $row->uds,
        ];
    }

    /**
     * Tus encabezados **en el mismo orden**.
     */
    public function headings(): array
    {
        return [
            'IPS ASIGNADA',
            'PRESTADOR PRIMARIO',
            'id',
            'numero_orden',
            'nombre_coperante',
            'fecha_captacion',
            'municipio',
            'nombre_rancheria',
            'ubicacion_casa',
            'nombre_cuidador',
            'identioficacion_cuidador',
            'telefono_cuidador',
            'nombre_eapb_cuidador',
            'nombre_autoridad_trad_ansestral',
            'datos_contacto_autoridad',
            'primer_nombre',
            'segundo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'tipo_identificacion',
            'numero_identificacion',
            'sexo',
            'fecha_nacimieto_nino',
            'edad_meses',
            'regimen_afiliacion',
            'nombre_eapb_menor',
            'peso_kg',
            'logitud_talla_cm',
            'perimetro_braqueal',
            'signos_peligro_infeccion_respiratoria',
            'sexosignos_desnutricion',
            'puntaje_z',
            'calsificacion_antropometrica',
            'estado',
            'user_id',
            'created_at',
            'updated_at',
            'numero_profesional',
            'uds',
        ];
    }

    /**
     * Estilos AfterSheet (sin tocar).
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $cellRange = 'A1:AN1';
                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getFont()->setSize(14)->setBold(true);
                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('e6ffe6');
                $event->sheet->getDelegate()->getStyle($cellRange)
                    ->getAlignment()->setHorizontal('center');
                $event->sheet->setAutoFilter($cellRange);
            },
        ];
    }
}

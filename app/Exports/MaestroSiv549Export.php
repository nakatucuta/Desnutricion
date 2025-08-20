<?php

namespace App\Exports;

use App\Models\MaestroSiv549;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaestroSiv549Export implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        // === Tu consulta base ===
        $q = MaestroSiv549::query()->select([
            'tip_ide_',
            'num_ide_',
            'pri_nom_',
            'seg_nom_',
            'pri_ape_',
            'seg_ape_',
            'edad_',
            'sexo_',
            'fec_not',
            'semana',
            'year',
            'ocupacion_',
            'telefono_',
            'dir_res_',
            'nom_eve'
        ]);

        // (Opcional) pequeños filtros si los pasas por querystring
        if (!empty($this->filters['tip_ide_'])) {
            $q->where('tip_ide_', $this->filters['tip_ide_']);
        }
        if (!empty($this->filters['num_ide_'])) {
            $q->where('num_ide_', trim($this->filters['num_ide_']));
        }
        if (!empty($this->filters['nom_eve'])) {
            $q->where('nom_eve', 'like', '%'.$this->filters['nom_eve'].'%');
        }
        if (!empty($this->filters['fec_desde'])) {
            $q->whereDate('fec_not', '>=', $this->filters['fec_desde']);
        }
        if (!empty($this->filters['fec_hasta'])) {
            $q->whereDate('fec_not', '<=', $this->filters['fec_hasta']);
        }

        return $q;
    }

    public function headings(): array
    {
        return [
            'Tipo ID',
            'Número ID',
            'Primer nombre',
            'Segundo nombre',
            'Primer apellido',
            'Segundo apellido',
            'Edad',
            'Sexo',
            'Fecha Notificación',
            'Semana',
            'Año',
            'Ocupación',
            'Teléfono',
            'Dirección',
            'Evento',
        ];
    }

    public function map($row): array
    {
        // Limpieza básica/trim; fecha como string segura
        return [
            trim((string) $row->tip_ide_),
            trim((string) $row->num_ide_),
            trim((string) $row->pri_nom_),
            trim((string) $row->seg_nom_),
            trim((string) $row->pri_ape_),
            trim((string) $row->seg_ape_),
            $row->edad_,
            trim((string) $row->sexo_),
            (string) $row->fec_not,
            $row->semana,
            $row->year,
            trim((string) $row->ocupacion_),
            trim((string) $row->telefono_),
            trim((string) $row->dir_res_),
            trim((string) $row->nom_eve),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Encabezado en negrita
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

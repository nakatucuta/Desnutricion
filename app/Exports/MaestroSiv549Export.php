<?php

namespace App\Exports;

use App\Models\MaestroSiv549;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaestroSiv549Export implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
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
            'nom_eve',
        ]);

        foreach (['year', 'semana', 'tip_ide_', 'sexo_', 'nom_eve', 'area_', 'tip_cas_', 'tip_ss_', 'nom_grupo_', 'nmun_resi', 'nmun_notif', 'nom_upgd', 'cod_eve'] as $field) {
            $value = trim((string) ($this->filters[$field] ?? ''));
            if ($value !== '') {
                $q->where($field, $value);
            }
        }

        if ($this->filled('fec_desde')) {
            $q->whereDate('fec_not', '>=', $this->filters['fec_desde']);
        }
        if ($this->filled('fec_hasta')) {
            $q->whereDate('fec_not', '<=', $this->filters['fec_hasta']);
        }
        if ($this->filled('edad_desde')) {
            $q->where('edad_', '>=', $this->filters['edad_desde']);
        }
        if ($this->filled('edad_hasta')) {
            $q->where('edad_', '<=', $this->filters['edad_hasta']);
        }
        if ($this->filled('sem_ges_desde')) {
            $q->where('sem_ges', '>=', $this->filters['sem_ges_desde']);
        }
        if ($this->filled('sem_ges_hasta')) {
            $q->where('sem_ges', '<=', $this->filters['sem_ges_hasta']);
        }

        foreach (['num_ide_', 'telefono_', 'ocupacion_', 'dir_res_', 'pri_nom_', 'pri_ape_'] as $field) {
            $value = trim((string) ($this->filters[$field] ?? ''));
            if ($value !== '') {
                $q->where($field, 'like', '%' . $value . '%');
            }
        }

        $quick = trim((string) ($this->filters['q'] ?? ''));
        if ($quick !== '') {
            $q->where(function ($subQuery) use ($quick) {
                $like = '%' . $quick . '%';
                $subQuery->where('num_ide_', 'like', $like)
                    ->orWhere('tip_ide_', 'like', $like)
                    ->orWhere('pri_nom_', 'like', $like)
                    ->orWhere('seg_nom_', 'like', $like)
                    ->orWhere('pri_ape_', 'like', $like)
                    ->orWhere('seg_ape_', 'like', $like)
                    ->orWhere('telefono_', 'like', $like)
                    ->orWhere('nom_eve', 'like', $like)
                    ->orWhere('dir_res_', 'like', $like);
            });
        }

        return $q;
    }

    public function headings(): array
    {
        return [
            'Tipo ID',
            'Numero ID',
            'Primer nombre',
            'Segundo nombre',
            'Primer apellido',
            'Segundo apellido',
            'Edad',
            'Sexo',
            'Fecha Notificacion',
            'Semana',
            'Anio',
            'Ocupacion',
            'Telefono',
            'Direccion',
            'Evento',
        ];
    }

    public function map($row): array
    {
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
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function filled(string $field): bool
    {
        return isset($this->filters[$field]) && trim((string) $this->filters[$field]) !== '';
    }
}

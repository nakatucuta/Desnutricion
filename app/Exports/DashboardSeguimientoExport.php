<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DashboardSeguimientoExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private array $data) {}

    public function headings(): array
    {
        return [
            'Fecha Generacion',
            'Anio',
            'Filtro Evento',
            'Filtro Estado',
            'Filtro Prestador',
            'Busqueda',
            'Prestador ID',
            'Prestador',
            'Evento',
            'Casos Asignados',
            'Casos Con Seguimiento',
            'Casos Sin Seguimiento',
            'Cobertura (%)',
            'Nivel Riesgo',
        ];
    }

    public function collection(): Collection
    {
        $filters = $this->data['filters'] ?? [];
        $rows = $this->data['rows'] ?? collect();
        $generatedAt = now()->format('Y-m-d H:i:s');

        $prestadorFiltro = 'Todos';
        if (!empty($filters['prestador_id'])) {
            $prestador = collect($this->data['prestadores'] ?? [])->firstWhere('id', (int)$filters['prestador_id']);
            $prestadorFiltro = $prestador ? ($prestador->name ?? 'Prestador') : 'Prestador';
        }

        return $rows->map(function ($row) use ($generatedAt, $filters, $prestadorFiltro) {
            return [
                $generatedAt,
                $filters['anio'] ?? '',
                $filters['evento'] ?? 'todos',
                $filters['estado'] ?? 'con_sin',
                $prestadorFiltro,
                $filters['q'] ?? '',
                $row->id ?? '',
                $row->name ?? '',
                $row->evento ?? '',
                (int)($row->cant_casos_asignados ?? 0),
                (int)($row->casos_con_seguimiento ?? 0),
                (int)($row->total_sin_seguimientos ?? 0),
                (float)($row->cobertura_pct ?? 0),
                $row->nivel_riesgo ?? '',
            ];
        });
    }
}

<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaestroSiv549DesignerExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private Builder $query,
        private array $headings,
        private array $columns
    ) {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function map($row): array
    {
        $fullName = trim(implode(' ', array_filter([
            $row->pri_nom_ ?? '',
            $row->seg_nom_ ?? '',
            $row->pri_ape_ ?? '',
            $row->seg_ape_ ?? '',
        ])));

        $line = [];
        foreach ($this->columns as $column) {
            $line[] = $column === 'nombre_completo'
                ? $fullName
                : ($row->{$column} ?? '');
        }

        return $line;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

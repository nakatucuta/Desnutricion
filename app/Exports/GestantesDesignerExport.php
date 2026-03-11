<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GestantesDesignerExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private $query,
        private array $headings,
        private array $columns
    ) {
    }

    public function query()
    {
        return $this->query;
    }

    public function map($row): array
    {
        $line = [];
        foreach ($this->columns as $column) {
            $line[] = $row->{$column} ?? '';
        }

        return $line;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

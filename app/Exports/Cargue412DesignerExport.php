<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class Cargue412DesignerExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private array $rows,
        private array $headings,
        private array $columns
    ) {
    }

    public function collection(): Collection
    {
        return collect($this->rows)->map(function ($row) {
            $line = [];
            foreach ($this->columns as $col) {
                $line[] = $row[$col] ?? '';
            }
            return $line;
        });
    }

    public function headings(): array
    {
        return $this->headings;
    }
}


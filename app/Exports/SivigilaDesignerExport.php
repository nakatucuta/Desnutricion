<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SivigilaDesignerExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private Collection $rows,
        private array $headings,
        private array $columns
    ) {
    }

    public function collection(): Collection
    {
        return $this->rows->map(function ($row) {
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


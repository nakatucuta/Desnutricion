<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CicloVidaDesignerSheet implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected string $title,
        protected array $fields,
        protected array $labels,
        protected array $rows
    ) {
    }

    public function array(): array
    {
        return collect($this->rows)
            ->map(fn (array $row) => collect($this->fields)->map(fn ($field) => $row[$field] ?? null)->all())
            ->all();
    }

    public function headings(): array
    {
        return array_map(fn ($field) => $this->labels[$field] ?? $field, $this->fields);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '1D4ED8'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}

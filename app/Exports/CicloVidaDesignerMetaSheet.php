<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CicloVidaDesignerMetaSheet implements FromArray, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(protected array $meta)
    {
    }

    public function array(): array
    {
        $filters = $this->meta['filters'] ?? [];

        $rows = [
            ['Generado por', $this->meta['generated_by'] ?? ''],
            ['Fecha de generacion', $this->meta['generated_at'] ?? ''],
            ['Plantilla', $this->meta['template'] ?? 'Diseno libre'],
            ['Desde', $this->meta['from'] ?? ''],
            ['Hasta', $this->meta['to'] ?? ''],
            ['Total de registros', $this->meta['total_records'] ?? 0],
            [''],
            ['Filtro', 'Valor'],
        ];

        foreach ($filters as $key => $value) {
            $rows[] = [$key, $value];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            8 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '0F766E'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Metadata';
    }
}

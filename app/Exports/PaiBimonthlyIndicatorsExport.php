<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class PaiBimonthlyIndicatorsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    public function __construct(private array $payload)
    {
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->payload['groups'] ?? [] as $group) {
            foreach ($group['rows'] ?? [] as $row) {
                $rows->push($this->mapRow($group, $row['vaccine'] ?? '', $row));
            }
            if (!empty($group['totals'])) {
                $rows->push($this->mapRow($group, 'TOTAL IPS', $group['totals']));
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        $months = $this->payload['period']['labels'] ?? ['Mes 1', 'Mes 2'];

        return [
            'IPS vacunadora', 'Código IPS', 'Municipio', 'Vacuna', $months[0].' - Programadas', $months[0].' - Realizadas', $months[0].' - Cobertura', $months[1].' - Programadas', $months[1].' - Realizadas', $months[1].' - Cobertura',
        ];
    }

    private function mapRow(array $group, string $label, array $row): array
    {
        return [
            'ips' => $group['ips']['name'] ?? '',
            'codigo' => $group['ips']['code'] ?? '',
            'municipio' => $group['ips']['municipio'] ?? '',
            'vacuna' => $label,
            'm1_programado' => $row['month_1']['programmed'] ?? 0,
            'm1_aplicado' => $row['month_1']['applied'] ?? 0,
            'm1_porcentaje' => ($row['month_1']['percentage'] ?? null) === null ? 'N/A' : (($row['month_1']['percentage'] ?? 0) / 100),
            'm2_programado' => $row['month_2']['programmed'] ?? 0,
            'm2_aplicado' => $row['month_2']['applied'] ?? 0,
            'm2_porcentaje' => ($row['month_2']['percentage'] ?? null) === null ? 'N/A' : (($row['month_2']['percentage'] ?? 0) / 100),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:J1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8FAFC');
                $sheet->getStyle('E1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('EFF6FF');
                $sheet->getStyle('H1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F0FDF4');
                $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode('@');
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:J1');
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 2) {
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $cell = $sheet->getCell('B'.$row);
                        $cell->setValueExplicit((string) $cell->getValue(), DataType::TYPE_STRING);
                    }
                    $sheet->getStyle('G2:G'.$highestRow)->getNumberFormat()->setFormatCode('0.00%');
                    $sheet->getStyle('J2:J'.$highestRow)->getNumberFormat()->setFormatCode('0.00%');
                    $sheet->getStyle('H1:H'.$highestRow)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('CBD5E1');
                }
            },
        ];
    }
}

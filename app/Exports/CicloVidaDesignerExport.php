<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CicloVidaDesignerExport implements WithMultipleSheets
{
    public function __construct(
        protected array $meta,
        protected array $fields,
        protected array $labels,
        protected array $rows
    ) {
    }

    public function sheets(): array
    {
        return [
            new CicloVidaDesignerSheet('Reporte', $this->fields, $this->labels, $this->rows),
            new CicloVidaDesignerMetaSheet($this->meta),
        ];
    }
}

<?php

// app/Services/ImportReportService.php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class ImportReportService
{
    public function generarArchivoVacunasDesdeDB(int $batchId, string $filePath): void
    {
        $rows = DB::table('vacunas')
            ->select('afiliado_id','vacunas_id','docis','lote','fecha_vacuna','created_at')
            ->where('batch_verifications_id', $batchId)
            ->orderBy('afiliado_id')
            ->get();

        $fh = fopen($filePath, 'w');
        fwrite($fh, "batch_verifications_id={$batchId}\n");
        fwrite($fh, "total=" . $rows->count() . "\n\n");

        foreach ($rows as $r) {
            $line = implode("\t", [
                $r->afiliado_id,
                $r->vacunas_id,
                (string)($r->docis ?? ''),
                (string)($r->lote ?? ''),
                (string)($r->fecha_vacuna ?? ''),
                (string)($r->created_at ?? ''),
            ]);
            fwrite($fh, $line . "\n");
        }
        fclose($fh);
    }
}

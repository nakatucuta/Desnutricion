<?php

namespace App\Console\Commands;

use App\Services\PaiDoseNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NormalizePaiVacunasDocis extends Command
{
    protected $signature = 'pai:normalize-vacunas-docis {--chunk=500 : Rows to process per batch} {--write-report=1 : Write a TXT summary to storage/app} {--only-pending=1 : Process only rows without docis_original yet} {--repair-quotes=0 : Process rows whose docis still contains quote artifacts} {--repair-legacy=0 : Process rows with legacy accented or legacy wording variants}';
    protected $description = 'Normalize vacuna dose labels (docis) and backfill docis_original.';

    public function handle(PaiDoseNormalizer $normalizer): int
    {
        $chunkSize = max(50, (int) $this->option('chunk'));
        $writeReport = (int) $this->option('write-report') === 1;
        $onlyPending = (int) $this->option('only-pending') === 1;
        $repairQuotes = (int) $this->option('repair-quotes') === 1;
        $repairLegacy = (int) $this->option('repair-legacy') === 1;

        $stats = [
            'rows' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'nullified' => 0,
            'canonicalized' => 0,
        ];

        $unmapped = [];

        $query = DB::table('vacunas')
            ->select(['id', 'docis', 'docis_original', 'vacunas_id', 'num_frascos_utilizados'])
            ->orderBy('id');

        if ($onlyPending) {
            $query->whereNull('docis_original');
        }

        if ($repairQuotes) {
            $query->where(function ($q) {
                $q->whereRaw("docis LIKE '%''%'")
                    ->orWhereRaw('docis LIKE \'%"%\'');
            });
        }

        if ($repairLegacy) {
            $query->where(function ($q) {
                $q->whereRaw("docis LIKE '%Ú%'")
                    ->orWhereRaw("docis LIKE '%É%'")
                    ->orWhereRaw("docis LIKE '%DOSIS UNICA%'")
                    ->orWhereRaw("docis LIKE '%ÚNICA%'")
                    ->orWhereRaw("docis LIKE '%RECIÉN NACIDO%'")
                    ->orWhereRaw("docis LIKE '%RECIEN NACIDO%'");
            });
        }

        $query->chunkById($chunkSize, function ($rows) use (&$stats, &$unmapped, $normalizer) {
                $payload = [];

                foreach ($rows as $row) {
                    $stats['rows']++;

                    $raw = $row->docis;
                    $original = $normalizer->normalizeOriginal($raw);
                    $normalized = $normalizer->normalizeDocis(
                        $raw,
                        isset($row->vacunas_id) ? (int) $row->vacunas_id : null,
                        ['num_frascos_utilizados' => $row->num_frascos_utilizados ?? null]
                    );

                    $currentDocis = $row->docis;
                    $currentOriginal = $row->docis_original;

                    $changed = false;

                    if ($currentOriginal !== $original) {
                        $changed = true;
                    }

                    if ($currentDocis !== $normalized) {
                        $changed = true;
                    }

                    if ($changed) {
                        $stats['updated']++;
                    } else {
                        $stats['unchanged']++;
                    }

                    if ($raw !== null && trim((string) $raw) !== '' && $normalized === null) {
                        $stats['nullified']++;
                        $key = trim((string) $raw);
                        $unmapped[$key] = ($unmapped[$key] ?? 0) + 1;
                    } elseif ($normalized !== null && $normalized !== $currentDocis) {
                        $stats['canonicalized']++;
                    }

                    if ($changed) {
                        $payload[] = [
                            'id' => (int) $row->id,
                            'docis_original' => $original,
                            'docis' => $normalized,
                        ];
                    }
                }

                if (!empty($payload)) {
                    $this->applyBatchUpdate($payload);
                }
                if (($stats['rows'] % 5000) === 0) {
                    $this->line('Processed ' . $stats['rows'] . ' rows...');
                }
            }, 'id');

        if ($writeReport) {
            $report = $this->buildReport($stats, $unmapped);
            Storage::disk('local')->put('pai_docis_normalization_report.txt', $report);
            $this->line('Reporte escrito en storage/app/pai_docis_normalization_report.txt');
        }

        $this->info('Normalizacion de docis finalizada.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Rows processed', $stats['rows']],
                ['Rows updated', $stats['updated']],
                ['Rows unchanged', $stats['unchanged']],
                ['Rows nullified', $stats['nullified']],
                ['Rows canonicalized', $stats['canonicalized']],
            ]
        );

        return self::SUCCESS;
    }

    private function applyBatchUpdate(array $payload): void
    {
        if (empty($payload)) {
            return;
        }

        $ids = [];
        $caseDocis = [];
        $caseOriginal = [];

        foreach ($payload as $row) {
            $id = (int) $row['id'];
            $ids[] = $id;

            $caseDocis[] = 'WHEN ' . $id . ' THEN ' . $this->sqlLiteral($row['docis']);
            $caseOriginal[] = 'WHEN ' . $id . ' THEN ' . $this->sqlLiteral($row['docis_original']);
        }

        $idsSql = implode(',', array_unique($ids));

        $sql = "
            UPDATE v
            SET
                docis_original = COALESCE(v.docis_original, CASE v.id
                    " . implode(PHP_EOL . '                    ', $caseOriginal) . "
                    ELSE v.docis_original
                END),
                docis = CASE v.id
                    " . implode(PHP_EOL . '                    ', $caseDocis) . "
                    ELSE v.docis
                END
            FROM dbo.vacunas AS v
            WHERE v.id IN ($idsSql)
        ";

        DB::unprepared($sql);
    }

    private function sqlLiteral($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $value = (string) $value;
        $value = str_replace("'", "''", $value);
        return "N'{$value}'";
    }

    private function buildReport(array $stats, array $unmapped): string
    {
        arsort($unmapped);
        $unmapped = array_slice($unmapped, 0, 50, true);

        $lines = [];
        $lines[] = 'PAI DOCIS NORMALIZATION REPORT';
        $lines[] = 'Generated at: ' . now()->format('Y-m-d H:i:s');
        $lines[] = 'Rows processed: ' . ($stats['rows'] ?? 0);
        $lines[] = 'Rows updated: ' . ($stats['updated'] ?? 0);
        $lines[] = 'Rows unchanged: ' . ($stats['unchanged'] ?? 0);
        $lines[] = 'Rows nullified: ' . ($stats['nullified'] ?? 0);
        $lines[] = 'Rows canonicalized: ' . ($stats['canonicalized'] ?? 0);
        $lines[] = '';
        $lines[] = 'Top unmapped values:';

        if (empty($unmapped)) {
            $lines[] = '- none';
        } else {
            foreach ($unmapped as $raw => $count) {
                $lines[] = '- ' . $raw . ' | ' . $count;
            }
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}

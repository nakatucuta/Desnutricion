<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaiBimonthlyIndicatorsService
{
    private const VACCINES = [
        ['key' => 'polio', 'label' => 'POLIO', 'aliases' => ['POLIO', 'OPV', 'IPV'] ],
        ['key' => 'pentavalente', 'label' => 'PENTAVALENTE', 'aliases' => ['PENTAVALENTE', 'PENTA'] ],
        ['key' => 'hexavalente', 'label' => 'HEXAVALENTE', 'aliases' => ['HEXAVALENTE'] ],
        ['key' => 'rotavirus', 'label' => 'ROTAVIRUS', 'aliases' => ['ROTAVIRUS'] ],
        ['key' => 'neumococo', 'label' => 'NEUMOCOCO', 'aliases' => ['NEUMOCOCO', 'NEUMOCOCCICA'] ],
        ['key' => 'influenza', 'label' => 'INFLUENZA', 'aliases' => ['INFLUENZA', 'INFLUENZA ESTACIONAL'] ],
        ['key' => 'triple_viral', 'label' => 'TRIPLE VIRAL', 'aliases' => ['TRIPLE VIRAL', 'SRP'] ],
        ['key' => 'varicela', 'label' => 'VARICELA', 'aliases' => ['VARICELA'] ],
        ['key' => 'hepatitis_a', 'label' => 'HEPATITIS A', 'aliases' => ['HEPATITIS A'] ],
        ['key' => 'fiebre_amarilla', 'label' => 'FIEBRE AMARILLA', 'aliases' => ['FIEBRE AMARILLA'] ],
        ['key' => 'dpt', 'label' => 'DPT', 'aliases' => ['DPT', 'DIFTERIA TOSFERINA TETANOS'] ],
        ['key' => 'vph', 'label' => 'VPH', 'aliases' => ['VPH', 'PAPILOMA'] ],
        ['key' => 'toxoide_tetanico', 'label' => 'TOXOIDE TETANICO', 'aliases' => ['TOXOIDE TETANICO', 'TOXOIDE TETANICO ADSORBIDO', 'TD'] ],
        ['key' => 'dpt_acelular', 'label' => 'DPT ACELULAR', 'aliases' => ['DPT ACELULAR', 'TDAP', 'DPTA'] ],
        ['key' => 'covid_19', 'label' => 'COVID 19', 'aliases' => ['COVID 19', 'COVID-19', 'COVID'] ],
        ['key' => 'vsr', 'label' => 'VSR', 'aliases' => ['VSR', 'VIRUS SINCITIAL RESPIRATORIO'] ],
    ];

    public function build(array $filters): array
    {
        $year = $this->validYear($filters['year'] ?? null);
        $bimester = $this->validBimester($filters['bimester'] ?? null);
        $months = [$bimester * 2 - 1, $bimester * 2];
        $start = Carbon::create($year, $months[0], 1)->startOfMonth();
        $end = Carbon::create($year, $months[1], 1)->endOfMonth();
        $municipio = $this->normalize($filters['municipio'] ?? '');
        $ipsCode = $this->normalize($filters['ips_code'] ?? '');
        $regimen = $this->normalize($filters['regimen'] ?? '');

        $referenceIps = $this->referenceIps($year, $municipio, $ipsCode);
        $referenceCatalog = $this->referenceIps($year, '', '');
        $metaRows = $this->metaRows($year, $municipio, $ipsCode, $regimen);
        $vaccineMap = $this->vaccineMap();
        $programmed = $this->programmedByIpsAndVaccine($metaRows, $vaccineMap);
        $applied = $this->appliedByIpsVaccineAndMonth($year, $start, $end, $municipio, $ipsCode, $regimen, $vaccineMap);
        $programmed = array_intersect_key($programmed, $referenceIps);

        $ipsKeys = collect(array_keys($programmed))
            ->merge(array_keys($applied))
            ->merge(array_keys($referenceIps))
            ->unique()
            ->values();

        $ipsNames = $this->ipsNames($ipsKeys->all());
        $groups = [];
        foreach ($ipsKeys as $code) {
            $code = (string) $code;
            $ipsMeta = $referenceIps[$code] ?? [];
            $rows = [];
            foreach (self::VACCINES as $vaccine) {
                $key = $vaccine['key'];
                $programmedAnnual = (int) ($programmed[$code][$key] ?? 0);
                $monthProgrammed = $this->splitAnnualTarget($programmedAnnual, $months);
                $monthOneApplied = (int) ($applied[$code][$key][$months[0]] ?? 0);
                $monthTwoApplied = (int) ($applied[$code][$key][$months[1]] ?? 0);
                $rows[] = [
                    'vaccine_key' => $key,
                    'vaccine' => $vaccine['label'],
                    'month_1' => [
                        'programmed' => $monthProgrammed[0],
                        'applied' => $monthOneApplied,
                        'percentage' => $this->percentage($monthOneApplied, $monthProgrammed[0]),
                    ],
                    'month_2' => [
                        'programmed' => $monthProgrammed[1],
                        'applied' => $monthTwoApplied,
                        'percentage' => $this->percentage($monthTwoApplied, $monthProgrammed[1]),
                    ],
                ];
            }

            $totals = [
                'month_1' => [
                    'programmed' => (int) collect($rows)->sum('month_1.programmed'),
                    'applied' => (int) collect($rows)->sum('month_1.applied'),
                ],
                'month_2' => [
                    'programmed' => (int) collect($rows)->sum('month_2.programmed'),
                    'applied' => (int) collect($rows)->sum('month_2.applied'),
                ],
            ];
            $totals['month_1']['percentage'] = $this->percentage($totals['month_1']['applied'], $totals['month_1']['programmed']);
            $totals['month_2']['percentage'] = $this->percentage($totals['month_2']['applied'], $totals['month_2']['programmed']);

            $groups[] = [
                'ips' => [
                    'code' => $code,
                    'name' => (string) (($ipsMeta['name'] ?? '') ?: ($ipsNames[$code]['name'] ?? ('IPS '.$code))),
                    'municipio' => (string) (($ipsMeta['municipio'] ?? '') ?: ($ipsNames[$code]['municipio'] ?? '') ?: $municipio),
                    'referenced_primary_count' => (int) ($ipsMeta['referenced_primary_count'] ?? 0),
                ],
                'rows' => $rows,
                'totals' => $totals,
            ];
        }

        usort($groups, fn ($a, $b) => strcasecmp($a['ips']['name'], $b['ips']['name']));

        return [
            'ok' => true,
            'filters' => [
                'year' => $year,
                'bimester' => $bimester,
                'municipio' => $municipio,
                'ips_code' => $ipsCode,
                'regimen' => $regimen,
            ],
            'period' => [
                'months' => $months,
                'labels' => [$this->monthName($months[0]), $this->monthName($months[1])],
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
            'vaccines' => array_map(fn ($v) => ['key' => $v['key'], 'label' => $v['label']], self::VACCINES),
            'ips' => array_values($ipsNames),
            'catalogs' => [
                'municipalities' => collect($referenceCatalog)->pluck('municipio')->filter()->unique()->sort()->values()->all(),
                'ips' => collect($referenceCatalog)->map(fn ($item, $code) => [
                    'code' => (string) $code,
                    'name' => (string) ($item['name'] ?? ''),
                    'municipio' => (string) ($item['municipio'] ?? ''),
                ])->values()->all(),
                'regimens' => ['', 'SUBSIDIADO', 'CONTRIBUTIVO'],
            ],
            'groups' => $groups,
            'meta_source' => 'metas_vacunacion.poblacion / 12, distribuida por mes',
            'numerator_source' => 'vacunas + pai_ips_referenciadas + vacunas.ips_primaria_codigo',
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function metaRows(int $year, string $municipio, string $ipsCode, string $regimen)
    {
        return DB::table('metas_vacunacion as m')
            ->leftJoin('referencia_vacunas as rv', 'rv.id', '=', 'm.id_vacuna')
            ->where('m.vigencia', $year)
            ->when($municipio !== '', fn ($q) => $q->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(m.municipio, '')))) = ?", [$municipio]))
            ->when($ipsCode !== '', fn ($q) => $q->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(m.codigo_habilitacion, '')))) = ?", [$ipsCode]))
            ->when($regimen !== '', fn ($q) => $q->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(m.regimen, '')))) = ?", [$regimen]))
            ->get([
                'm.codigo_habilitacion',
                'm.municipio',
                'm.poblacion',
                'm.biologico',
                'm.id_vacuna',
                'rv.nombre as referencia_nombre',
            ]);
    }

    private function programmedByIpsAndVaccine($rows, array $vaccineMap): array
    {
        $result = [];
        foreach ($rows as $row) {
            $vaccineName = trim((string) ($row->referencia_nombre ?? '')) ?: (string) ($row->biologico ?? '');
            $key = $this->vaccineKey((int) ($row->id_vacuna ?? 0), $vaccineName, $vaccineMap);
            if ($key === null) {
                continue;
            }
            $ips = $this->normalize($row->codigo_habilitacion ?? '');
            if ($ips === '') {
                continue;
            }
            $result[$ips][$key] = ($result[$ips][$key] ?? 0) + max(0, (int) ($row->poblacion ?? 0));
        }
        return $result;
    }

    private function appliedByIpsVaccineAndMonth(int $year, Carbon $start, Carbon $end, string $municipio, string $ipsCode, string $regimen, array $vaccineMap): array
    {
        $query = DB::table('vacunas as v')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->whereNotNull('v.fecha_vacuna')
            ->whereBetween('v.fecha_vacuna', [$start->toDateString(), $end->toDateString()])
            ->whereRaw("LTRIM(RTRIM(ISNULL(v.ips_primaria_codigo, ''))) <> ''")
            ->when($municipio !== '', fn ($q) => $q->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(u.municipio, '')))) = ?", [$municipio]))
            ->when($ipsCode !== '', fn ($q) => $q->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(u.codigohabilitacion, '')))) = ?", [$ipsCode]))
            ->when($regimen !== '', fn ($q) => $q->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(v.regimen, '')))) = ?", [$regimen]))
            ->whereExists(function ($reference) use ($year) {
                $reference->selectRaw('1')
                    ->from('pai_ips_referenciadas as pir')
                    ->where('pir.vigencia', $year)
                    ->where('pir.activo', true)
                    ->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(pir.ips_vacunadora_codigo, '')))) = UPPER(LTRIM(RTRIM(ISNULL(u.codigohabilitacion, ''))))")
                    ->where(function ($municipality) {
                        $municipality->whereNull('pir.municipio')
                            ->orWhereRaw("LTRIM(RTRIM(ISNULL(pir.municipio, ''))) = ''")
                            ->orWhereRaw("UPPER(LTRIM(RTRIM(ISNULL(pir.municipio, '')))) = UPPER(LTRIM(RTRIM(ISNULL(u.municipio, ''))))");
                    })
                    ->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(pir.ips_primaria_codigo, '')))) = UPPER(LTRIM(RTRIM(ISNULL(v.ips_primaria_codigo, ''))))");
            })
            ->selectRaw("UPPER(LTRIM(RTRIM(ISNULL(u.codigohabilitacion, '')))) as ips_code")
            ->selectRaw('v.vacunas_id')
            ->selectRaw('MONTH(v.fecha_vacuna) as month_number')
            ->selectRaw('COUNT_BIG(*) as total')
            ->groupByRaw("UPPER(LTRIM(RTRIM(ISNULL(u.codigohabilitacion, ''))))")
            ->groupBy('v.vacunas_id')
            ->groupByRaw('MONTH(v.fecha_vacuna)')
            ->get();

        $referenceNames = DB::table('referencia_vacunas')->pluck('nombre', 'id');
        $result = [];
        foreach ($query as $row) {
            $key = $this->vaccineKey((int) $row->vacunas_id, (string) ($referenceNames[$row->vacunas_id] ?? ''), $vaccineMap);
            if ($key === null) {
                continue;
            }
            $ips = $this->normalize($row->ips_code ?? '');
            $month = (int) $row->month_number;
            $result[$ips][$key][$month] = ($result[$ips][$key][$month] ?? 0) + (int) $row->total;
        }
        return $result;
    }

    private function referenceIps(int $year, string $municipio, string $ipsCode): array
    {
        return DB::table('pai_ips_referenciadas')
            ->where('vigencia', $year)
            ->where('activo', true)
            ->when($municipio !== '', fn ($q) => $q->where(function ($w) use ($municipio) {
                $w->whereNull('municipio')->orWhereRaw("LTRIM(RTRIM(ISNULL(municipio, ''))) = ''")
                    ->orWhereRaw("UPPER(LTRIM(RTRIM(ISNULL(municipio, '')))) = ?", [$municipio]);
            }))
            ->when($ipsCode !== '', fn ($q) => $q->whereRaw("UPPER(LTRIM(RTRIM(ISNULL(ips_vacunadora_codigo, '')))) = ?", [$ipsCode]))
            ->get(['ips_vacunadora_codigo', 'ips_vacunadora_nombre', 'municipio', 'ips_primaria_codigo'])
            ->groupBy(fn ($row) => $this->normalize($row->ips_vacunadora_codigo))
            ->map(function ($rows) {
                $first = $rows->first();
                return [
                    'name' => (string) ($first->ips_vacunadora_nombre ?? ''),
                    'municipio' => (string) ($first->municipio ?? ''),
                    'referenced_primary_count' => $rows->pluck('ips_primaria_codigo')->filter()->unique()->count(),
                ];
            })->all();
    }

    private function ipsNames(array $codes): array
    {
        if (empty($codes)) {
            return [];
        }
        // SQL Server puede inferir los bindings numericos como BIGINT y tratar
        // de convertir toda la columna (que es NVARCHAR). Forzamos cada
        // parametro a NVARCHAR para soportar codigos numericos y alfanumericos.
        $placeholders = implode(',', array_fill(0, count($codes), 'CAST(? AS NVARCHAR(60))'));
        $query = DB::table('users')
            ->whereRaw(
                "UPPER(LTRIM(RTRIM(ISNULL(codigohabilitacion, '')))) IN ({$placeholders})",
                array_map(fn ($code) => (string) $code, $codes)
            )
            ->selectRaw("UPPER(LTRIM(RTRIM(ISNULL(codigohabilitacion, '')))) as code")
            ->selectRaw("MIN(COALESCE(NULLIF(LTRIM(RTRIM(name)), ''), 'IPS SIN NOMBRE')) as name")
            ->selectRaw("MIN(COALESCE(NULLIF(LTRIM(RTRIM(municipio)), ''), '')) as municipio")
            ->groupByRaw("UPPER(LTRIM(RTRIM(ISNULL(codigohabilitacion, ''))))")
            ->get();

        return $query
            ->mapWithKeys(fn ($row) => [(string) $row->code => ['code' => (string) $row->code, 'name' => (string) $row->name, 'municipio' => (string) $row->municipio]])
            ->all();
    }

    private function vaccineMap(): array
    {
        $map = [];
        foreach (DB::table('referencia_vacunas')->select('id', 'nombre')->get() as $row) {
            $map[(int) $row->id] = $this->vaccineKey((int) $row->id, (string) $row->nombre, $map, false);
        }
        return $map;
    }

    private function vaccineKey(int $id, string $name, array $map, bool $allowIdMap = true): ?string
    {
        if ($allowIdMap && isset($map[$id]) && $map[$id] !== null) {
            return $map[$id];
        }
        $normalized = $this->normalize($name);
        if ($normalized === '') {
            return null;
        }
        $aliases = [];
        foreach (self::VACCINES as $vaccine) {
            foreach ($vaccine['aliases'] as $alias) {
                $aliases[] = ['key' => $vaccine['key'], 'alias' => $this->normalize($alias)];
            }
        }
        foreach ($aliases as $candidate) {
            if ($normalized === $candidate['alias']) {
                return $candidate['key'];
            }
        }
        usort($aliases, fn ($a, $b) => strlen($b['alias']) <=> strlen($a['alias']));
        foreach ($aliases as $candidate) {
            if (str_contains($normalized, $candidate['alias']) || str_contains($candidate['alias'], $normalized)) {
                return $candidate['key'];
            }
        }
        return null;
    }

    private function splitAnnualTarget(int $annual, array $months): array
    {
        $base = intdiv(max(0, $annual), 12);
        $remainder = max(0, $annual) % 12;
        return [
            $base + ($months[0] <= $remainder ? 1 : 0),
            $base + ($months[1] <= $remainder ? 1 : 0),
        ];
    }

    private function percentage(int $applied, int $programmed): ?float
    {
        return $programmed > 0 ? round(($applied / $programmed) * 100, 2) : null;
    }

    private function validYear($value): int
    {
        $year = (int) ($value ?: now()->year);
        return $year >= 2000 && $year <= 2100 ? $year : (int) now()->year;
    }

    private function validBimester($value): int
    {
        $bimester = (int) ($value ?: 1);
        return $bimester >= 1 && $bimester <= 6 ? $bimester : 1;
    }

    private function normalize($value): string
    {
        $text = mb_strtoupper(trim((string) $value), 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        return trim((string) preg_replace('/[^A-Z0-9]+/', ' ', $text));
    }

    private function monthName(int $month): string
    {
        return [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'][$month] ?? '';
    }
}

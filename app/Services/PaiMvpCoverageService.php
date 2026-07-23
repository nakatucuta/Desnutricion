<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaiMvpCoverageService
{
    private const MODE_NORMATIVE = 'normative';

    public function __construct(
        private readonly PaiCurrentSchemeEvaluator $evaluator,
        private readonly PaiGestationClassifier $gestationClassifier,
    ) {}

    public function evaluateForAfiliado(int $afiliadoId, string $mode = self::MODE_NORMATIVE): array
    {
        $afiliado = DB::table('afiliados as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->where('a.id', $afiliadoId)
            ->select($this->afiliadoColumns())
            ->first();

        if (! $afiliado) {
            return ['ok' => false, 'message' => 'Afiliado no encontrado.'];
        }

        $applications = $this->buildApplicationsForAfiliadoIds(collect([$afiliadoId]));
        $summary = $this->evaluateRow($afiliado, $applications[$afiliadoId] ?? []);

        return array_merge([
            'ok' => true,
            'mode' => $mode,
            'as_of' => now()->format('Y-m-d H:i:s'),
            'version' => (string) config('pai_esquemas.version', 'CURSO-VIDA'),
        ], $summary);
    }

    public function evaluateForCollection(Collection $afiliados, string $mode = self::MODE_NORMATIVE): array
    {
        $ids = $afiliados->pluck('id')
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $applications = $this->buildApplicationsForAfiliadoIds($ids);
        $result = [];

        foreach ($afiliados as $row) {
            $id = (int) ($row->id ?? 0);
            if ($id > 0) {
                $result[$id] = $this->evaluateRow($row, $applications[$id] ?? []);
            }
        }

        return $result;
    }

    private function evaluateRow(object $row, array $applications): array
    {
        $birthDate = $this->safeParseDate($row->fecha_nacimiento ?? null);
        $gestationWeeks = $this->toIntOrNull($row->semanas_gestacion ?? null);
        $isGestante = $this->isGestante($row);
        $asOf = now();

        $person = (object) [
            'birth_date' => $birthDate,
            'sex' => $row->sexo ?? null,
            'is_gestante' => $isGestante,
            'gestation_weeks' => $gestationWeeks,
            'has_contraindication' => $this->isYes($row->contraindicacion_vacuna ?? null),
        ];

        $evaluation = $this->evaluator->evaluate($person, $applications, $asOf);
        $course = $evaluation['curso'] ?? null;
        $fullName = trim(implode(' ', array_filter([
            $row->primer_nombre ?? '',
            $row->segundo_nombre ?? '',
            $row->primer_apellido ?? '',
            $row->segundo_apellido ?? '',
        ])));

        $evaluation['afiliado'] = [
            'id' => (int) ($row->id ?? 0),
            'nombre' => $fullName,
            'tipo_identificacion' => $row->tipo_identificacion ?? null,
            'numero_identificacion' => $row->numero_identificacion ?? null,
            'numero_carnet' => $row->numero_carnet ?? null,
            'fecha_nacimiento' => $birthDate?->format('Y-m-d'),
            'edad_dias' => $course['age_days'] ?? null,
            'edad_meses' => $course['age_months'] ?? null,
            'edad_anios' => $course['age_years'] ?? null,
            'curso_vida' => $course['key'] ?? null,
            'curso_vida_label' => $course['label'] ?? null,
            'prestador' => $row->prestador ?? null,
            'sexo' => $row->sexo ?? null,
            'es_gestante' => $isGestante,
            'semanas_gestacion' => $gestationWeeks,
            'municipio_residencia' => $row->municipio_residencia ?? null,
            'contraindicacion_vacuna' => $row->contraindicacion_vacuna ?? null,
            'enfermedad_contraindicacion' => $row->enfermedad_contraindicacion ?? null,
        ];

        foreach ($evaluation['faltantes'] as &$missing) {
            $missing['edad_actual'] = $this->formatAge(
                $evaluation['afiliado']['edad_anios'],
                $evaluation['afiliado']['edad_meses']
            );
            $missing['edad_meses_actual'] = $evaluation['afiliado']['edad_meses'];
            $missing['motivo'] = $missing['motivo'] ?? 'Vacuna o dosis exigible para el curso y la edad actual sin registro válido.';
        }
        unset($missing);

        return $evaluation;
    }

    private function buildApplicationsForAfiliadoIds(Collection $ids): array
    {
        $rows = DB::table('vacunas')
            ->whereIn('afiliado_id', $ids->all())
            ->whereNotNull('vacunas_id')
            ->get(['afiliado_id', 'vacunas_id', 'docis', 'fecha_vacuna', 'condicion_usuaria']);

        $result = [];
        foreach ($rows as $row) {
            $id = (int) $row->afiliado_id;
            $result[$id][] = [
                'vacunas_id' => (int) $row->vacunas_id,
                'dose' => $row->docis,
                'date' => $this->safeParseDate($row->fecha_vacuna),
                'condition' => $row->condicion_usuaria,
            ];
        }

        return $result;
    }

    private function afiliadoColumns(): array
    {
        return [
            'a.id',
            'a.tipo_identificacion',
            'a.numero_identificacion',
            'a.numero_carnet',
            'a.primer_nombre',
            'a.segundo_nombre',
            'a.primer_apellido',
            'a.segundo_apellido',
            'a.fecha_nacimiento',
            'a.sexo',
            'a.condicion_usuaria',
            'a.semanas_gestacion',
            'a.municipio_residencia',
            'a.contraindicacion_vacuna',
            'a.enfermedad_contraindicacion',
            'u.name as prestador',
        ];
    }

    private function isGestante(object $row): bool
    {
        return $this->gestationClassifier->isGestante(
            $row->condicion_usuaria ?? null,
            $row->semanas_gestacion ?? null,
        );
    }

    private function safeParseDate($value): ?Carbon
    {
        try {
            return $value ? Carbon::parse($value) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function toIntOrNull($value): ?int
    {
        $clean = trim((string) ($value ?? ''));

        return $clean !== '' && preg_match('/^-?\d+$/', $clean) ? (int) $clean : null;
    }

    private function isYes($value): bool
    {
        return in_array($this->normalize((string) $value), ['SI', 'S', 'YES', 'Y', '1', 'TRUE'], true);
    }

    private function normalize(string $value): string
    {
        return mb_strtoupper(trim($value), 'UTF-8');
    }

    private function formatAge(?int $years, ?int $months): string
    {
        if ($years === null && $months === null) {
            return 'Sin fecha de nacimiento';
        }

        return (int) ($years ?? 0).' años ('.(int) ($months ?? 0).' meses)';
    }
}

<?php

namespace App\Services\CicloVida;

use App\Exports\CicloVidaDesignerExport;
use App\Support\CicloVidaCatalog;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CicloVidaReportDesigner
{
    public function pageData(): array
    {
        return [
            'filters' => $this->filterOptions(),
            'fieldGroups' => $this->fieldGroups(),
            'templates' => $this->templates(),
        ];
    }

    public function preview(Request $request): array
    {
        [$fields, $labels] = $this->resolveFields($request);
        [$from, $to] = $this->resolveRange($request);

        $query = $this->baseQuery($request, $from, $to);
        $total = (clone $query)->count();
        $limit = min(max((int) $request->input('limit', 100), 10), 250);

        $rows = $this->fetchRows(
            (clone $query)->orderBy('r.event_date', 'desc')->limit($limit),
            $fields
        );

        return [
            'ok' => true,
            'meta' => $this->reportMeta($request, $from, $to, $total, $fields, $labels),
            'columns' => collect($fields)->map(fn (string $field) => [
                'key' => $field,
                'label' => $labels[$field] ?? $field,
            ])->values(),
            'rows' => $rows,
        ];
    }

    public function export(Request $request, string $format)
    {
        $format = strtolower($format);
        [$fields, $labels] = $this->resolveFields($request);
        [$from, $to] = $this->resolveRange($request);

        $query = $this->baseQuery($request, $from, $to);
        $total = (clone $query)->count();
        $rows = $this->fetchRows((clone $query)->orderBy('r.event_date', 'desc'), $fields);
        $meta = $this->reportMeta($request, $from, $to, $total, $fields, $labels);
        $filename = 'ciclos_vida_reporte_'.now()->format('Ymd_His');

        return match ($format) {
            'csv' => $this->streamCsv("{$filename}.csv", $fields, $labels, $rows),
            'json' => $this->streamJson("{$filename}.json", $meta, $fields, $rows),
            'xlsx', 'xls' => Excel::download(
                new CicloVidaDesignerExport($meta, $fields, $labels, $rows),
                "{$filename}.xlsx"
            ),
            default => abort(404),
        };
    }

    protected function filterOptions(): array
    {
        $courseOptions = collect(CicloVidaCatalog::courses())
            ->map(function (array $course, string $key) {
                return [
                    'value' => $key,
                    'label' => $course['label'] ?? $key,
                ];
            })
            ->values();

        $moduleOptions = collect(CicloVidaCatalog::courses())
            ->flatMap(function (array $course) {
                return collect($course['modules'] ?? [])->map(function (array $module, string $key) {
                    return [
                        'value' => $key,
                        'label' => $module['label'] ?? $key,
                    ];
                });
            })
            ->unique('value')
            ->sortBy('label')
            ->values();

        $distinct = DB::table('ciclo_vida_cache_records as r')
            ->leftJoinSub($this->demographicLookupSubquery(), 'd', function ($join): void {
                $join->on('r.tipo_identificacion', '=', 'd.tipo_identificacion')
                    ->on('r.identificacion', '=', 'd.identificacion');
            })
            ->whereNotNull('r.id');

        return [
            'courses' => $courseOptions,
            'modules' => $moduleOptions,
            'recordTypes' => collect([
                ['value' => 'all', 'label' => 'Atenciones y alertas'],
                ['value' => 'event', 'label' => 'Solo atenciones'],
                ['value' => 'alert', 'label' => 'Solo alertas'],
            ]),
            'genders' => (clone $distinct)
                ->select(DB::raw("COALESCE(NULLIF(d.genero,''), 'Sin genero') as label"))
                ->distinct()
                ->orderBy('label')
                ->get()
                ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
                ->values(),
            'departments' => (clone $distinct)
                ->select(DB::raw("COALESCE(NULLIF(d.departamento,''), 'Sin departamento') as label"))
                ->distinct()
                ->orderBy('label')
                ->get()
                ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
                ->values(),
            'municipalities' => (clone $distinct)
                ->select(DB::raw("COALESCE(NULLIF(d.municipio,''), 'Sin municipio') as label"))
                ->distinct()
                ->orderBy('label')
                ->get()
                ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
                ->values(),
            'ips' => (clone $distinct)
                ->select(DB::raw("COALESCE(NULLIF(r.ips_primaria,''), 'Sin IPS') as label"))
                ->distinct()
                ->orderBy('label')
                ->get()
                ->map(fn ($row) => ['value' => $row->label, 'label' => $row->label])
                ->values(),
        ];
    }

    protected function fieldGroups(): array
    {
        $groups = [
            'control' => [
                'label' => 'Control del reporte',
                'fields' => ['course_label', 'course_key', 'module_label', 'module_key', 'record_type', 'range_start', 'range_end'],
            ],
            'identificacion' => [
                'label' => 'Identificacion del usuario',
                'fields' => ['numero_carnet', 'tipo_identificacion', 'identificacion', 'primer_apellido', 'segundo_apellido', 'primer_nombre', 'segundo_nombre', 'nombre_completo'],
            ],
            'demografia' => [
                'label' => 'Demografia y afiliacion',
                'fields' => ['fecha_nacimiento', 'genero', 'edad', 'edad_meses', 'rango_edad', 'estado_actual', 'fecha_cambio_estado', 'fecha_afiliacion_sistema', 'fecha_afiliacion_eps'],
            ],
            'territorio' => [
                'label' => 'Ubicacion y territorial',
                'fields' => ['codigo_departamento', 'departamento', 'codigo_municipio', 'municipio', 'zona'],
            ],
            'atencion' => [
                'label' => 'Variables de atencion',
                'fields' => ['event_date', 'codigo_ips', 'ips_primaria', 'codigo_servicio', 'descripcion_servicio', 'diagnostico_principal', 'finalidad'],
            ],
            'tecnico' => [
                'label' => 'Detalle tecnico',
                'fields' => ['record_hash', 'payload_json', 'usuario_generador', 'fecha_generacion'],
            ],
        ];

        $catalog = $this->fieldDefinitions();

        return collect($groups)->map(function (array $group) use ($catalog) {
            $group['fields'] = collect($group['fields'])
                ->map(fn (string $field) => $catalog[$field] ?? null)
                ->filter()
                ->values()
                ->all();

            return $group;
        })->values()->all();
    }

    protected function templates(): array
    {
        return [
            [
                'key' => 'vejez_junio',
                'label' => 'Plantilla Vejez Junio',
                'description' => 'Replica una salida nominal tipo operativo con foco en identificacion del usuario, afiliacion y detalle de la atencion.',
                'icon' => 'fas fa-file-excel',
                'color' => 'grad-emerald',
                'filters' => [
                    'course_key' => 'vejez',
                    'record_type' => 'event',
                ],
                'fields' => [
                    'fecha_cambio_estado',
                    'estado_actual',
                    'numero_carnet',
                    'tipo_identificacion',
                    'identificacion',
                    'primer_apellido',
                    'segundo_apellido',
                    'primer_nombre',
                    'segundo_nombre',
                    'fecha_nacimiento',
                    'genero',
                    'edad',
                    'fecha_afiliacion_sistema',
                    'event_date',
                    'ips_primaria',
                    'descripcion_servicio',
                    'course_label',
                    'module_label',
                ],
            ],
            [
                'key' => 'nominal_operativo',
                'label' => 'Nominal operativo',
                'description' => 'Plantilla transversal para listar todas las atenciones o alertas con datos completos del usuario y servicio.',
                'icon' => 'fas fa-notes-medical',
                'color' => 'grad-indigo',
                'filters' => [
                    'record_type' => 'all',
                ],
                'fields' => [
                    'course_label',
                    'module_label',
                    'event_date',
                    'tipo_identificacion',
                    'identificacion',
                    'nombre_completo',
                    'fecha_nacimiento',
                    'edad',
                    'rango_edad',
                    'ips_primaria',
                    'descripcion_servicio',
                    'diagnostico_principal',
                    'finalidad',
                ],
            ],
            [
                'key' => 'territorial_ips',
                'label' => 'Territorial por IPS',
                'description' => 'Pensado para gestion territorial y seguimiento institucional por curso, municipio, departamento e IPS.',
                'icon' => 'fas fa-map-marked-alt',
                'color' => 'grad-cyan',
                'filters' => [
                    'record_type' => 'event',
                ],
                'fields' => [
                    'course_label',
                    'module_label',
                    'departamento',
                    'municipio',
                    'zona',
                    'codigo_ips',
                    'ips_primaria',
                    'event_date',
                    'identificacion',
                    'nombre_completo',
                    'descripcion_servicio',
                ],
            ],
            [
                'key' => 'alertas_prestador',
                'label' => 'Brechas por prestador',
                'description' => 'Resume pacientes y pendientes para operacion con alertas, prestadores y demanda inducida.',
                'icon' => 'fas fa-bell',
                'color' => 'grad-rose',
                'filters' => [
                    'record_type' => 'alert',
                ],
                'fields' => [
                    'course_label',
                    'module_label',
                    'tipo_identificacion',
                    'identificacion',
                    'nombre_completo',
                    'edad',
                    'rango_edad',
                    'ips_primaria',
                    'descripcion_servicio',
                    'municipio',
                    'departamento',
                ],
            ],
        ];
    }

    protected function resolveFields(Request $request): array
    {
        $catalog = $this->fieldDefinitions();
        $default = ['course_label', 'module_label', 'event_date', 'tipo_identificacion', 'identificacion', 'nombre_completo', 'edad', 'ips_primaria', 'descripcion_servicio'];
        $requested = collect((array) $request->input('fields', $default))
            ->map(fn ($field) => (string) $field)
            ->filter(fn ($field) => isset($catalog[$field]))
            ->values();

        if ($requested->isEmpty()) {
            $requested = collect($default);
        }

        $labels = $requested
            ->mapWithKeys(fn ($field) => [$field => $catalog[$field]['label'] ?? $field])
            ->all();

        return [$requested->all(), $labels];
    }

    protected function resolveRange(Request $request): array
    {
        try {
            $from = Carbon::parse((string) $request->input('desde', now()->subDays(120)->toDateString()))->startOfDay();
            $to = Carbon::parse((string) $request->input('hasta', now()->toDateString()))->startOfDay();
        } catch (\Throwable $e) {
            $from = now()->subDays(120)->startOfDay();
            $to = now()->startOfDay();
        }

        if ($to->lessThan($from)) {
            $to = $from->copy();
        }

        return [$from, $to];
    }

    protected function baseQuery(Request $request, Carbon $from, Carbon $to): Builder
    {
        $query = DB::table('ciclo_vida_cache_records as r')
            ->leftJoinSub($this->demographicLookupSubquery(), 'd', function ($join): void {
                $join->on('r.tipo_identificacion', '=', 'd.tipo_identificacion')
                    ->on('r.identificacion', '=', 'd.identificacion');
            })
            ->whereBetween('r.event_date', [$from->toDateString(), $to->toDateString()]);

        $courseKey = trim((string) $request->input('course_key', ''));
        if ($courseKey !== '') {
            $query->where('r.course_key', $courseKey);
        }

        $moduleKey = trim((string) $request->input('module_key', ''));
        if ($moduleKey !== '') {
            $query->where('r.module_key', $moduleKey);
        }

        $recordType = trim((string) $request->input('record_type', 'all'));
        if (in_array($recordType, ['event', 'alert'], true)) {
            $query->where('r.record_type', $recordType);
        }

        $department = trim((string) $request->input('departamento', ''));
        if ($department !== '') {
            $query->whereRaw("COALESCE(NULLIF(d.departamento,''), 'Sin departamento') = ?", [$department]);
        }

        $municipality = trim((string) $request->input('municipio', ''));
        if ($municipality !== '') {
            $query->whereRaw("COALESCE(NULLIF(d.municipio,''), 'Sin municipio') = ?", [$municipality]);
        }

        $ips = trim((string) $request->input('ips', ''));
        if ($ips !== '') {
            if ($ips === 'Sin IPS') {
                $query->where(function (Builder $builder): void {
                    $builder->whereNull('r.ips_primaria')->orWhere('r.ips_primaria', '');
                });
            } else {
                $query->where('r.ips_primaria', $ips);
            }
        }

        $gender = trim((string) $request->input('genero', ''));
        if ($gender !== '') {
            $query->whereRaw("COALESCE(NULLIF(d.genero,''), 'Sin genero') = ?", [$gender]);
        }

        $edadMin = $request->filled('edad_min') ? (int) $request->input('edad_min') : null;
        $edadMax = $request->filled('edad_max') ? (int) $request->input('edad_max') : null;
        if ($edadMin !== null) {
            $query->where('r.edad', '>=', $edadMin);
        }
        if ($edadMax !== null) {
            $query->where('r.edad', '<=', $edadMax);
        }

        return $query;
    }

    protected function fetchRows(Builder $query, array $fields): array
    {
        $definitions = $this->fieldDefinitions();
        $selects = [];

        foreach ($fields as $field) {
            $definition = $definitions[$field] ?? null;
            if ($definition === null || empty($definition['select'])) {
                continue;
            }

            $selects[] = DB::raw($definition['select'].' as '.$field);
        }

        $rows = $query->get($selects);

        return $rows->map(function ($row) use ($fields, $definitions) {
            $item = (array) $row;

            foreach ($fields as $field) {
                $definition = $definitions[$field] ?? null;
                if ($definition === null) {
                    continue;
                }

                if (($definition['type'] ?? null) === 'course_label') {
                    $item[$field] = data_get(CicloVidaCatalog::course($item[$field] ?? ''), 'label', $item[$field] ?? '');
                }

                if (($definition['type'] ?? null) === 'date' && !empty($item[$field])) {
                    try {
                        $item[$field] = Carbon::parse((string) $item[$field])->toDateString();
                    } catch (\Throwable $e) {
                    }
                }
            }

            return collect($fields)->mapWithKeys(fn ($field) => [$field => $item[$field] ?? null])->all();
        })->values()->all();
    }

    protected function reportMeta(Request $request, Carbon $from, Carbon $to, int $total, array $fields, array $labels): array
    {
        $templateKey = trim((string) $request->input('template', ''));
        $template = collect($this->templates())->firstWhere('key', $templateKey);
        $user = $request->user();

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => $user?->name ?? $user?->email ?? 'Usuario del sistema',
            'template' => $template['label'] ?? 'Diseño libre',
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_records' => $total,
            'selected_fields' => $fields,
            'field_labels' => $labels,
            'filters' => [
                'course_key' => (string) $request->input('course_key', ''),
                'module_key' => (string) $request->input('module_key', ''),
                'record_type' => (string) $request->input('record_type', 'all'),
                'departamento' => (string) $request->input('departamento', ''),
                'municipio' => (string) $request->input('municipio', ''),
                'ips' => (string) $request->input('ips', ''),
                'genero' => (string) $request->input('genero', ''),
                'edad_min' => (string) $request->input('edad_min', ''),
                'edad_max' => (string) $request->input('edad_max', ''),
            ],
        ];
    }

    protected function fieldDefinitions(): array
    {
        return [
            'course_key' => ['key' => 'course_key', 'label' => 'Curso clave', 'select' => 'r.course_key'],
            'course_label' => ['key' => 'course_label', 'label' => 'Curso de vida', 'select' => 'r.course_key', 'type' => 'course_label'],
            'module_key' => ['key' => 'module_key', 'label' => 'Modulo clave', 'select' => 'r.module_key'],
            'module_label' => ['key' => 'module_label', 'label' => 'Modulo', 'select' => 'r.module_label'],
            'record_type' => ['key' => 'record_type', 'label' => 'Tipo de registro', 'select' => 'r.record_type'],
            'range_start' => ['key' => 'range_start', 'label' => 'Inicio de corte', 'select' => 'r.range_start', 'type' => 'date'],
            'range_end' => ['key' => 'range_end', 'label' => 'Fin de corte', 'select' => 'r.range_end', 'type' => 'date'],
            'numero_carnet' => ['key' => 'numero_carnet', 'label' => 'Numero carnet', 'select' => 'd.numero_carnet'],
            'tipo_identificacion' => ['key' => 'tipo_identificacion', 'label' => 'Tipo ID', 'select' => 'r.tipo_identificacion'],
            'identificacion' => ['key' => 'identificacion', 'label' => 'Numero identificacion', 'select' => 'r.identificacion'],
            'primer_apellido' => ['key' => 'primer_apellido', 'label' => 'Primer apellido', 'select' => 'r.primer_apellido'],
            'segundo_apellido' => ['key' => 'segundo_apellido', 'label' => 'Segundo apellido', 'select' => 'r.segundo_apellido'],
            'primer_nombre' => ['key' => 'primer_nombre', 'label' => 'Primer nombre', 'select' => 'r.primer_nombre'],
            'segundo_nombre' => ['key' => 'segundo_nombre', 'label' => 'Segundo nombre', 'select' => 'r.segundo_nombre'],
            'nombre_completo' => ['key' => 'nombre_completo', 'label' => 'Nombre completo', 'select' => "LTRIM(RTRIM(COALESCE(r.primer_apellido,'') + ' ' + COALESCE(r.segundo_apellido,'') + ' ' + COALESCE(r.primer_nombre,'') + ' ' + COALESCE(r.segundo_nombre,'')))"],
            'fecha_nacimiento' => ['key' => 'fecha_nacimiento', 'label' => 'Fecha nacimiento', 'select' => 'r.fecha_nacimiento', 'type' => 'date'],
            'genero' => ['key' => 'genero', 'label' => 'Genero', 'select' => 'd.genero'],
            'edad' => ['key' => 'edad', 'label' => 'Edad', 'select' => 'r.edad'],
            'edad_meses' => ['key' => 'edad_meses', 'label' => 'Edad meses', 'select' => 'r.edad_meses'],
            'rango_edad' => ['key' => 'rango_edad', 'label' => 'Rango de edad', 'select' => 'r.rango_edad'],
            'estado_actual' => ['key' => 'estado_actual', 'label' => 'Estado actual', 'select' => 'd.estado_actual'],
            'fecha_cambio_estado' => ['key' => 'fecha_cambio_estado', 'label' => 'Fecha cambio estado', 'select' => 'd.fecha_cambio_estado', 'type' => 'date'],
            'fecha_afiliacion_sistema' => ['key' => 'fecha_afiliacion_sistema', 'label' => 'Fecha afiliacion sistema', 'select' => 'd.fecha_afiliacion_sistema', 'type' => 'date'],
            'fecha_afiliacion_eps' => ['key' => 'fecha_afiliacion_eps', 'label' => 'Fecha afiliacion EPS', 'select' => 'd.fecha_afiliacion_eps', 'type' => 'date'],
            'codigo_departamento' => ['key' => 'codigo_departamento', 'label' => 'Codigo departamento', 'select' => 'd.codigo_departamento'],
            'departamento' => ['key' => 'departamento', 'label' => 'Departamento', 'select' => "COALESCE(NULLIF(d.departamento,''), 'Sin departamento')"],
            'codigo_municipio' => ['key' => 'codigo_municipio', 'label' => 'Codigo municipio', 'select' => 'd.codigo_municipio'],
            'municipio' => ['key' => 'municipio', 'label' => 'Municipio', 'select' => "COALESCE(NULLIF(d.municipio,''), 'Sin municipio')"],
            'zona' => ['key' => 'zona', 'label' => 'Zona', 'select' => 'd.zona'],
            'event_date' => ['key' => 'event_date', 'label' => 'Fecha atencion', 'select' => 'r.event_date', 'type' => 'date'],
            'codigo_ips' => ['key' => 'codigo_ips', 'label' => 'Codigo IPS', 'select' => 'r.codigo_ips'],
            'ips_primaria' => ['key' => 'ips_primaria', 'label' => 'IPS / Grupo', 'select' => 'r.ips_primaria'],
            'codigo_servicio' => ['key' => 'codigo_servicio', 'label' => 'Codigo servicio', 'select' => 'r.codigo_servicio'],
            'descripcion_servicio' => ['key' => 'descripcion_servicio', 'label' => 'Descripcion servicio', 'select' => 'r.descripcion_servicio'],
            'diagnostico_principal' => ['key' => 'diagnostico_principal', 'label' => 'Diagnostico principal', 'select' => 'r.diagnostico_principal'],
            'finalidad' => ['key' => 'finalidad', 'label' => 'Finalidad', 'select' => 'r.finalidad'],
            'record_hash' => ['key' => 'record_hash', 'label' => 'Hash tecnico', 'select' => 'r.record_hash'],
            'payload_json' => ['key' => 'payload_json', 'label' => 'Payload JSON', 'select' => 'r.payload'],
            'usuario_generador' => ['key' => 'usuario_generador', 'label' => 'Usuario generador', 'select' => "'".str_replace("'", "''", auth()->user()?->name ?? auth()->user()?->email ?? 'Usuario del sistema')."'"],
            'fecha_generacion' => ['key' => 'fecha_generacion', 'label' => 'Fecha generacion', 'select' => "'".now()->format('Y-m-d H:i:s')."'"],
        ];
    }

    protected function demographicLookupSubquery(): Builder
    {
        return DB::query()
            ->from(DB::raw('sga..maestroidentificaciones as x'))
            ->join(DB::raw('sga..maestroafiliados as afi'), 'x.numeroCarnet', '=', 'afi.numeroCarnet')
            ->leftJoin(DB::raw('sga..municipios as m'), function ($join): void {
                $join->on('afi.codigoDepartamento', '=', 'm.codigoDepartamento')
                    ->on('afi.codigoMunicipio', '=', 'm.codigoMunicipio');
            })
            ->leftJoin(DB::raw('sga..departamentos as d'), 'afi.codigoDepartamento', '=', 'd.codigo')
            ->select([
                'x.tipoIdentificacion as tipo_identificacion',
                'x.identificacion',
                DB::raw('MAX(x.numeroCarnet) as numero_carnet'),
                DB::raw('MAX(afi.genero) as genero'),
                DB::raw('MAX(afi.estadoActual) as estado_actual'),
                DB::raw('MAX(afi.fechaCambioEstado) as fecha_cambio_estado'),
                DB::raw('MAX(afi.fechaAfiliacionSistema) as fecha_afiliacion_sistema'),
                DB::raw('MAX(afi.fechaAfiliacionArs) as fecha_afiliacion_eps'),
                DB::raw('MAX(afi.codigoDepartamento) as codigo_departamento'),
                DB::raw("MAX(COALESCE(NULLIF(d.descrip,''), 'Sin departamento')) as departamento"),
                DB::raw('MAX(afi.codigoMunicipio) as codigo_municipio'),
                DB::raw("MAX(COALESCE(NULLIF(m.descrip,''), 'Sin municipio')) as municipio"),
                DB::raw('MAX(afi.zona) as zona'),
            ])
            ->groupBy('x.tipoIdentificacion', 'x.identificacion');
    }

    protected function streamCsv(string $filename, array $fields, array $labels, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($fields, $labels, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_map(fn ($field) => $labels[$field] ?? $field, $fields));
            foreach ($rows as $row) {
                fputcsv($handle, collect($fields)->map(fn ($field) => $row[$field] ?? null)->all());
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function streamJson(string $filename, array $meta, array $fields, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($meta, $fields, $rows): void {
            echo json_encode([
                'meta' => $meta,
                'fields' => $fields,
                'rows' => $rows,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }
}

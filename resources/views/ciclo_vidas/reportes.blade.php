@extends('adminlte::page')

@section('title', 'Reportes de ciclos de vida')

@section('content_header')
    <div class="cv-report-hero">
        <div>
            <span class="cv-report-chip">Reportes y exportacion</span>
            <h1 class="mb-2">Diseñador de reportes de ciclos de vida</h1>
            <p class="mb-0">
                Construye reportes personalizados con todos los cursos de vida, todas las atenciones y alertas,
                vista previa nominal y exportacion en Excel, CSV o JSON.
            </p>
        </div>
        <div class="cv-report-hero-side">
            <div class="cv-report-brand">
                <img src="{{ $companyLogo }}" alt="Escudo institucional">
                <div>
                    <small>Analitica institucional</small>
                    <strong>INOVA</strong>
                </div>
            </div>
            <div class="cv-report-side-card">
                <small>Plantilla sugerida</small>
                <strong>Vejez Junio · nominal operativo</strong>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div id="cvReportLoading" class="cv-report-loading is-visible" aria-live="polite" aria-busy="true">
        <div class="cv-report-loading__backdrop"></div>
        <div class="cv-report-loading__panel">
            <div class="cv-report-loading__grid"></div>
            <div class="cv-report-loading__orb">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="cv-report-loading__brand">
                <img src="{{ $companyLogo }}" alt="Escudo institucional">
            </div>
            <h3>Preparando diseñador de reportes</h3>
            <p>Estamos organizando plantillas, variables y vista previa para construir tu reporte.</p>
            <div class="cv-report-loading__status">
                <span class="cv-report-loading__dot"></span>
                <span id="cvReportLoadingText">Sincronizando diseñador...</span>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        @foreach ($templates as $template)
            <div class="col-12 col-lg-6 col-xl-3 mb-3">
                <button
                    type="button"
                    class="cv-template-card {{ $template['color'] }}"
                    data-template-card="{{ $template['key'] }}">
                    <div class="cv-template-card__icon">
                        <i class="{{ $template['icon'] }}"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="mb-1">{{ $template['label'] }}</h4>
                        <p class="mb-0">{{ $template['description'] }}</p>
                    </div>
                </button>
            </div>
        @endforeach
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header border-0 bg-white">
            <h3 class="card-title mb-1">Diseña tu reporte</h3>
            <small class="text-muted">Elige el corte, define filtros, selecciona variables y genera una vista previa antes de exportar.</small>
        </div>
        <div class="card-body">
            <div class="mb-3">
                @include('ciclo_vidas.partials.date_range_toolbar', [
                    'pickerId' => 'reportRange',
                    'applyButtonId' => 'btnPreviewReport',
                    'applyLabel' => 'Vista previa',
                    'applyIcon' => 'fas fa-eye',
                    'noteClass' => 'cv-report-note',
                    'note' => '<span><i class="fas fa-info-circle"></i> Diseña un reporte libre o parte de una plantilla prediseñada.</span>',
                ])
            </div>

            <div class="cv-report-filter-grid mb-4">
                <div>
                    <label class="mb-1 font-weight-bold">Curso de vida</label>
                    <select id="reportCourse" class="form-control">
                        <option value="">Todos los cursos</option>
                        @foreach ($filters['courses'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Modulo</label>
                    <select id="reportModule" class="form-control">
                        <option value="">Todos los modulos</option>
                        @foreach ($filters['modules'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Tipo de registro</label>
                    <select id="reportType" class="form-control">
                        @foreach ($filters['recordTypes'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Genero</label>
                    <select id="reportGender" class="form-control">
                        <option value="">Todos</option>
                        @foreach ($filters['genders'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Departamento</label>
                    <select id="reportDepartment" class="form-control">
                        <option value="">Todos</option>
                        @foreach ($filters['departments'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">Municipio</label>
                    <select id="reportMunicipality" class="form-control">
                        <option value="">Todos</option>
                        @foreach ($filters['municipalities'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 font-weight-bold">IPS / Grupo</label>
                    <select id="reportIps" class="form-control">
                        <option value="">Todas</option>
                        @foreach ($filters['ips'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="cv-report-age-grid">
                    <div>
                        <label class="mb-1 font-weight-bold">Edad min</label>
                        <input type="number" id="reportAgeMin" class="form-control" min="0" placeholder="0">
                    </div>
                    <div>
                        <label class="mb-1 font-weight-bold">Edad max</label>
                        <input type="number" id="reportAgeMax" class="form-control" min="0" placeholder="120">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-xl-7 mb-3">
                    <div class="cv-report-panel h-100">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h4 class="mb-1">Variables disponibles</h4>
                                <p class="mb-0 text-muted">Marca las variables que quieres incluir. Puedes combinar datos nominales, de atencion, territorio y control.</p>
                            </div>
                            <div class="cv-report-actions-inline">
                                <button type="button" id="btnSelectAllFields" class="btn btn-outline-primary btn-sm">Seleccionar todo</button>
                                <button type="button" id="btnClearAllFields" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                            </div>
                        </div>
                        <div class="cv-field-groups">
                            @foreach ($fieldGroups as $group)
                                <div class="cv-field-group">
                                    <div class="cv-field-group__header">
                                        <h5 class="mb-0">{{ $group['label'] }}</h5>
                                    </div>
                                    <div class="cv-field-group__body">
                                        <div class="row">
                                            @foreach ($group['fields'] as $field)
                                                <div class="col-12 col-md-6 mb-2">
                                                    <label class="cv-field-option">
                                                        <input type="checkbox" class="report-field-checkbox" value="{{ $field['key'] }}">
                                                        <div>
                                                            <strong>{{ $field['label'] }}</strong>
                                                            <small>{{ $field['key'] }}</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-5 mb-3">
                    <div class="cv-report-panel h-100">
                        <div class="mb-3">
                            <h4 class="mb-1">Lienzo del reporte</h4>
                            <p class="mb-0 text-muted">Reordena las columnas como quieras. Este orden se respetará en la vista previa y la exportación.</p>
                        </div>
                        <div id="selectedFieldsBoard" class="cv-selected-fields"></div>
                        <div class="cv-report-user-card">
                            <small>Generado por</small>
                            <strong>{{ auth()->user()->name ?? auth()->user()->email ?? 'Usuario del sistema' }}</strong>
                            <span id="selectedTemplateLabel">Diseño libre</span>
                        </div>
                        <div class="cv-report-actions-stack">
                            <button type="button" id="btnPreviewReport" class="btn btn-primary btn-lg">
                                <i class="fas fa-eye mr-2"></i>Generar vista previa
                            </button>
                            <div class="btn-group w-100">
                                <button type="button" id="btnSaveLocalDesign" class="btn btn-outline-secondary">
                                    <i class="fas fa-save mr-2"></i>Guardar diseño
                                </button>
                                <button type="button" id="btnLoadLocalDesign" class="btn btn-outline-secondary">
                                    <i class="fas fa-folder-open mr-2"></i>Cargar diseño
                                </button>
                            </div>
                            <div class="btn-group w-100">
                                <button type="button" class="btn btn-success export-report" data-format="xlsx">
                                    <i class="fas fa-file-excel mr-2"></i>Excel
                                </button>
                                <button type="button" class="btn btn-outline-success export-report" data-format="csv">
                                    <i class="fas fa-file-csv mr-2"></i>CSV
                                </button>
                                <button type="button" class="btn btn-outline-info export-report" data-format="json">
                                    <i class="fas fa-file-code mr-2"></i>JSON
                                </button>
                            </div>
                            <button type="button" id="btnResetDesign" class="btn btn-link text-muted">Restablecer diseño</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 col-lg-4 mb-3">
            <div class="cv-report-guide">
                <i class="fas fa-magic"></i>
                <h5>1. Elige una plantilla</h5>
                <p class="mb-0">Puedes partir del prediseñado “Vejez Junio” o construir un diseño libre según el objetivo del cliente.</p>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            <div class="cv-report-guide">
                <i class="fas fa-filter"></i>
                <h5>2. Focaliza la informacion</h5>
                <p class="mb-0">Combina rango de fechas, curso de vida, modulo, genero, territorio, IPS y edades para llegar al reporte exacto.</p>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            <div class="cv-report-guide">
                <i class="fas fa-file-export"></i>
                <h5>3. Previsualiza y exporta</h5>
                <p class="mb-0">Antes de descargar, valida la estructura nominal y después exporta a Excel, CSV o JSON.</p>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header border-0 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h3 class="card-title mb-1">Vista previa del reporte</h3>
                    <small class="text-muted">Se muestran hasta 100 filas en pantalla para validar el diseño antes de exportar.</small>
                </div>
                <span class="badge badge-light" id="previewSummary">Sin vista previa generada</span>
            </div>
        </div>
        <div class="card-body">
            <div class="cv-preview-sheet mb-3">
                <div class="cv-preview-sheet__meta">
                    <div>
                        <small>Plantilla</small>
                        <strong id="previewTemplate">Diseño libre</strong>
                    </div>
                    <div>
                        <small>Generado por</small>
                        <strong id="previewGeneratedBy">-</strong>
                    </div>
                    <div>
                        <small>Fecha</small>
                        <strong id="previewGeneratedAt">-</strong>
                    </div>
                    <div>
                        <small>Columnas</small>
                        <strong id="previewColumnsCount">0</strong>
                    </div>
                </div>
                <div class="cv-preview-sheet__filters" id="previewFilters">Sin filtros destacados.</div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="reportPreviewTable">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td class="text-muted">Aun no has generado una vista previa.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    @include('ciclo_vidas.partials.report_designer_styles')
@stop

@section('js')
    @include('ciclo_vidas.partials.report_designer_script')
@stop

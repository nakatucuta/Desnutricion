@extends('adminlte::page')

@section('title', 'PAI - Indicadores 2026')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">Administrar Indicadores PAI 2026</h1>
        <div class="text-muted">Fuente administrable para metas del reporte de coberturas</div>
    </div>
    <div class="d-flex align-items-center">
        <button type="button" class="btn btn-outline-info mr-2" data-toggle="modal" data-target="#paiCalcHelpModal">
            <i class="fas fa-calculator mr-1"></i> Como Se Calcula
        </button>
        <a href="{{ route('afiliado.stats.view') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Estadisticas
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    <div class="card">
        <div class="card-header"><strong>Nuevo / Editar registro</strong></div>
        <div class="card-body">
            <form id="paiIndicForm">
                <input type="hidden" id="id" value="">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Vigencia</label>
                        <input type="number" class="form-control form-control-sm" id="vigencia" value="2026" min="2000" max="2100" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted mb-1">Municipio</label>
                        <input list="munList" class="form-control form-control-sm" id="municipio" required>
                        <datalist id="munList">
                            @foreach($municipios as $m)
                                <option value="{{ $m }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted mb-1">IPS (users)</label>
                        <select class="form-control form-control-sm" id="ips_user_id">
                            <option value="">Sin asociar</option>
                            @foreach($ips as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Regimen</label>
                        <input list="regList" class="form-control form-control-sm" id="regimen" required>
                        <datalist id="regList">
                            @foreach($regimenes as $r)
                                <option value="{{ $r }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Poblacion anual</label>
                        <input type="number" class="form-control form-control-sm" id="poblacion_programada_anual" min="0" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Indicador</label>
                        <select class="form-control form-control-sm" id="indicador" required>
                            <option value="">Seleccione...</option>
                            @foreach($indicadoresCatalog as $c)
                                <option value="{{ $c['indicador'] }}">{{ $c['indicador'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted mb-1">Biologico</label>
                        <select class="form-control form-control-sm" id="biologico" required>
                            <option value="">Seleccione indicador primero...</option>
                        </select>
                        <small class="text-muted">Se llena segun el indicador seleccionado (criterio del formato PAI).</small>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Fuente</label>
                        <input type="text" class="form-control form-control-sm" id="fuente" placeholder="Manual, Excel...">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted mb-1">Nombre IPS (Excel)</label>
                        <input type="text" class="form-control form-control-sm" id="ips_nombre_excel" placeholder="Opcional">
                    </div>
                    <div class="col-md-1 mb-2">
                        <label class="small text-muted mb-1">Activo</label>
                        <select class="form-control form-control-sm" id="activo">
                            <option value="1" selected>Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10 mb-2">
                        <label class="small text-muted mb-1">Observaciones</label>
                        <input type="text" class="form-control form-control-sm" id="observaciones">
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end justify-content-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm mr-2" id="btnLimpiar">Limpiar</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnGuardar">Guardar</button>
                    </div>
                </div>
            </form>
            <small class="text-muted" id="paiIndicMsg"></small>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Registros cargados</strong>
            <div class="d-flex align-items-center">
                <label class="small text-muted mb-0 mr-2">Vigencia</label>
                <input type="number" id="filterYear" class="form-control form-control-sm" style="width:100px;" value="2026" min="2000" max="2100">
                <button class="btn btn-sm btn-outline-primary ml-2" id="btnRefrescar">Actualizar</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Vigencia</th>
                            <th>Municipio</th>
                            <th>IPS</th>
                            <th>Regimen</th>
                            <th>Indicador</th>
                            <th>Biologico</th>
                            <th class="text-right">Poblacion</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="rowsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paiCalcHelpModal" tabindex="-1" role="dialog" aria-labelledby="paiCalcHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paiCalcHelpModalLabel">Detalle del calculo de Estadisticas PAI</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>Objetivo:</strong> explicar de donde salen <strong>Meta</strong>, <strong>Dosis aplicadas</strong>, <strong>Susceptibles</strong>, <strong>Cobertura</strong> y <strong>Estado</strong>.</p>

                <h6 class="mt-3">1) Fuentes de datos</h6>
                <p class="mb-1"><strong>Meta:</strong> sale primero de esta tabla administrable <code>pai_indicadores_2026</code> (la que alimentas en esta pantalla).</p>
                <p class="mb-1"><strong>Dosis aplicadas:</strong> se cuentan desde <code>vacunas</code> (join con <code>afiliados</code>) filtrando por año, periodo, municipio, IPS y regimen.</p>
                <p class="mb-1"><strong>Catalogo de biológicos:</strong> se usa <code>referencia_vacunas</code> para mapear IDs de vacuna por indicador.</p>

                <h6 class="mt-3">2) Filtros que afectan el resultado</h6>
                <p class="mb-1">Año + Escala (Mensual/Bimensual/Trimestral/Semestral) + Periodo + Municipio + IPS + Regimen.</p>
                <p class="mb-1">El periodo define los meses que se cuentan: por ejemplo <code>III Trimestre = 7,8,9</code>.</p>

                <h6 class="mt-3">3) Calculo de META por indicador</h6>
                <p class="mb-1">Para cada fila (Indicador + Biologico):</p>
                <pre class="bg-light p-2 rounded"><code>PoblacionProgramadaAnual = valor en pai_indicadores_2026
MetaMes = round(PoblacionProgramadaAnual / 12)
MetaPeriodo = MetaMes * cantidadMesesDelPeriodo</code></pre>
                <p class="mb-1">Si no hay registro en tabla administrable, el sistema usa fallback (Excel de indicadores / estimacion) segun configuracion.</p>

                <h6 class="mt-3">4) Calculo de DOSIS APLICADAS</h6>
                <p class="mb-1">Se hace <code>COUNT(v.id)</code> sobre <code>vacunas v</code> con estos filtros:</p>
                <pre class="bg-light p-2 rounded"><code>where year(v.fecha_vacuna) = anio
and month(v.fecha_vacuna) in (meses del periodo)
and municipio afiliado = municipio seleccionado
and v.user_id = IPS seleccionada
and v.regimen = regimen seleccionado
and v.vacunas_id in (IDs del biologico del indicador)
and regla de dosis (ej: 3ra, refuerzo, dosis unica)</code></pre>

                <h6 class="mt-3">5) Susceptibles y Cobertura</h6>
                <pre class="bg-light p-2 rounded"><code>Susceptibles = max(MetaPeriodo - DosisAplicadas, 0)
Cobertura = (MetaPeriodo &gt; 0) ? (DosisAplicadas / MetaPeriodo) : 0</code></pre>

                <h6 class="mt-3">6) Estado de cobertura</h6>
                <p class="mb-1">Se clasifica con esta escala:</p>
                <pre class="bg-light p-2 rounded"><code>Cobertura = 0            -> SIN REPORTE
0   &lt;= Cobertura &lt;= 0.50 -> Cobertura muy critica
0.50<= Cobertura &lt;= 0.799 -> Cobertura Critica
0.80<= Cobertura &lt;= 0.899 -> Cobertura no util
0.90<= Cobertura &lt;= 0.949 -> Cobertura bajo riesgo
0.95<= Cobertura &lt;= 1.00  -> Cobertura util
Cobertura &gt; 1.00         -> Cobertura Optima</code></pre>

                <h6 class="mt-3">7) Recomendacion de calidad de datos</h6>
                <p class="mb-0">Para resultados estables, mantén completa la tabla <code>pai_indicadores_2026</code> por cada combinacion de Municipio + IPS + Regimen + Indicador + Biologico de la vigencia.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
(function(){
    const indicadorPairs = @json($indicadoresCatalog);
    const routes = {
        data: @json(route('afiliado.stats.indicadores.data')),
        store: @json(route('afiliado.stats.indicadores.store')),
        updateBase: @json(url('/afiliado/estadisticas/indicadores')),
        deleteBase: @json(url('/afiliado/estadisticas/indicadores'))
    };

    const body = document.getElementById('rowsBody');
    const msg = document.getElementById('paiIndicMsg');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentRows = [];

    function v(id){ return (document.getElementById(id).value || '').trim(); }
    function set(id,val){ document.getElementById(id).value = val ?? ''; }
    function esc(s){
        return String(s ?? '').replace(/[&<>"']/g, function(m){
            const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'};
            return map[m] || m;
        });
    }

    function refreshBiologicoOptions(selectedBiologico = ''){
        const indicador = v('indicador');
        const el = document.getElementById('biologico');
        if (!el) return;

        const list = (indicadorPairs || [])
            .filter(p => String(p.indicador || '') === indicador)
            .map(p => String(p.biologico || ''))
            .filter((x, i, arr) => x !== '' && arr.indexOf(x) === i);

        el.innerHTML = '';
        const first = document.createElement('option');
        first.value = '';
        first.textContent = list.length ? 'Seleccione...' : 'Sin biologicos para este indicador';
        el.appendChild(first);

        list.forEach(function(bio){
            const op = document.createElement('option');
            op.value = bio;
            op.textContent = bio;
            if (selectedBiologico && selectedBiologico === bio) op.selected = true;
            el.appendChild(op);
        });
    }

    function clearForm(){
        ['id','municipio','ips_user_id','ips_nombre_excel','regimen','indicador','biologico','poblacion_programada_anual','fuente','observaciones'].forEach(id => set(id,''));
        set('vigencia', v('filterYear') || '2026');
        set('activo','1');
        refreshBiologicoOptions('');
    }

    function fillForm(row){
        set('id', row.id);
        set('vigencia', row.vigencia);
        set('municipio', row.municipio);
        set('ips_user_id', row.ips_user_id ?? '');
        set('ips_nombre_excel', row.ips_nombre_excel ?? '');
        set('regimen', row.regimen);
        set('indicador', row.indicador);
        refreshBiologicoOptions(String(row.biologico || ''));
        set('biologico', row.biologico);
        set('poblacion_programada_anual', row.poblacion_programada_anual);
        set('fuente', row.fuente ?? '');
        set('observaciones', row.observaciones ?? '');
        set('activo', row.activo ? '1' : '0');
    }

    function draw(rows){
        currentRows = rows || [];
        body.innerHTML = '';
        currentRows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td>'+esc(r.vigencia)+'</td>'+
                '<td>'+esc(r.municipio)+'</td>'+
                '<td>'+esc(r.ips_name || '(sin asociar)')+'</td>'+
                '<td>'+esc(r.regimen)+'</td>'+
                '<td>'+esc(r.indicador)+'</td>'+
                '<td>'+esc(r.biologico)+'</td>'+
                '<td class="text-right">'+Number(r.poblacion_programada_anual || 0).toLocaleString('es-CO')+'</td>'+
                '<td>'+(r.activo ? 'Si' : 'No')+'</td>'+
                '<td>'+
                    '<button class="btn btn-xs btn-outline-primary mr-1 btn-edit" data-id="'+r.id+'">Editar</button>'+
                    '<button class="btn btn-xs btn-outline-danger btn-del" data-id="'+r.id+'">Eliminar</button>'+
                '</td>';
            body.appendChild(tr);
        });
    }

    function load(){
        const url = routes.data + '?year=' + encodeURIComponent(v('filterYear') || '2026');
        msg.textContent = 'Cargando...';
        fetch(url, { headers:{ 'Accept':'application/json' }})
            .then(r => r.json())
            .then(data => {
                draw(data.rows || []);
                msg.textContent = 'Registros: ' + (data.rows || []).length;
            })
            .catch(() => { msg.textContent = 'Error cargando datos.'; });
    }

    function payload(){
        return {
            vigencia: Number(v('vigencia') || 2026),
            municipio: v('municipio'),
            ips_user_id: v('ips_user_id') || null,
            ips_nombre_excel: v('ips_nombre_excel') || null,
            regimen: v('regimen'),
            indicador: v('indicador'),
            biologico: v('biologico'),
            poblacion_programada_anual: Number(v('poblacion_programada_anual') || 0),
            fuente: v('fuente') || null,
            observaciones: v('observaciones') || null,
            activo: v('activo') === '1'
        };
    }

    document.getElementById('paiIndicForm').addEventListener('submit', function(e){
        e.preventDefault();
        const id = v('id');
        const isEdit = id !== '';
        const url = isEdit ? (routes.updateBase + '/' + encodeURIComponent(id)) : routes.store;
        const method = isEdit ? 'PUT' : 'POST';

        msg.textContent = 'Guardando...';
        fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload())
        })
        .then(async r => {
            const data = await r.json();
            if (!r.ok || !data.ok) {
                throw new Error(data.message || 'No se pudo guardar');
            }
            msg.textContent = data.message || 'Guardado';
            clearForm();
            load();
        })
        .catch(err => {
            msg.textContent = err.message || 'Error guardando.';
        });
    });

    body.addEventListener('click', function(e){
        const btnEdit = e.target.closest('.btn-edit');
        const btnDel = e.target.closest('.btn-del');
        if (btnEdit) {
            const row = currentRows.find(x => String(x.id) === String(btnEdit.dataset.id));
            if (row) {
                fillForm(row);
                msg.textContent = 'Editando ID #' + row.id;
            }
        }
        if (btnDel) {
            const id = btnDel.dataset.id;
            if (!confirm('¿Eliminar este registro?')) return;
            fetch(routes.deleteBase + '/' + encodeURIComponent(id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            })
            .then(async r => {
                const data = await r.json();
                if (!r.ok || !data.ok) throw new Error(data.message || 'No se pudo eliminar');
                msg.textContent = data.message || 'Eliminado';
                if (v('id') === String(id)) clearForm();
                load();
            })
            .catch(err => { msg.textContent = err.message || 'Error eliminando.'; });
        }
    });

    document.getElementById('indicador').addEventListener('change', function(){
        refreshBiologicoOptions('');
    });

    document.getElementById('btnLimpiar').addEventListener('click', function(){ clearForm(); msg.textContent=''; });
    document.getElementById('btnRefrescar').addEventListener('click', function(){ load(); });
    clearForm();
    load();
})();
</script>
@stop

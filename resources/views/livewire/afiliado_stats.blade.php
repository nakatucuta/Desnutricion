@extends('adminlte::page')

@section('title', 'PAI - Estadisticas')

@section('content_header')
<div class="pai-topbar">
    <div class="pai-brand">
        <div class="pai-brand__logo">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo EPS IANAS WAYUU" class="pai-brand__logo-img">
        </div>
        <div class="pai-brand__text">
            <div class="pai-brand__title">Estadisticas PAI</div>
            <div class="pai-brand__subtitle">Analitica detallada para gestion operativa y gerencial</div>
        </div>
    </div>
    <div>
        <a href="{{ route('afiliado') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Cargue
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    @include('livewire.pai_stats_dashboard')
</div>
@stop

@section('css')
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
.pai-topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 4px;margin-bottom:10px;}
.pai-brand{display:flex;align-items:center;gap:12px;}
.pai-brand__logo{width:52px;height:52px;border-radius:12px;background:#fff;border:1px solid rgba(15,23,42,.08);display:flex;align-items:center;justify-content:center;}
.pai-brand__logo-img{width:40px;height:40px;object-fit:contain;}
.pai-brand__title{font-weight:900;color:#0f172a;line-height:1.1;}
.pai-brand__subtitle{color:#64748b;font-size:.9rem;}

.pai-card{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:16px;box-shadow:0 10px 24px rgba(2,6,23,.08);}
.btn-pai{border-radius:12px;padding:8px 12px;font-weight:700;border:1px solid rgba(15,23,42,.1);}
.btn-pai-pastel-primary{background:#eaf2ff;border-color:#cfe0ff;color:#1e40af;}

.pai-analytics{border:1px solid rgba(15,23,42,.08);}
.pai-analytics__head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid rgba(15,23,42,.08);background:linear-gradient(180deg, rgba(234,242,255,.75), #fff);}
.pai-analytics__brand{display:flex;align-items:center;gap:12px;}
.pai-analytics__logo{width:44px;height:44px;border-radius:10px;object-fit:contain;background:#fff;border:1px solid rgba(15,23,42,.12);padding:4px;}
.pai-analytics__title{font-weight:900;color:#0f172a;line-height:1.1;}
.pai-analytics__subtitle{color:#64748b;font-size:.86rem;}
.pai-analytics__filters{padding:12px 16px 8px;border-bottom:1px solid rgba(15,23,42,.08);background:#fbfdff;}
.pai-stat-box{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:12px;padding:10px 12px;min-height:78px;}
.pai-stat-box .label{font-size:.74rem;text-transform:uppercase;letter-spacing:.35px;color:#64748b;font-weight:800;}
.pai-stat-box .value{font-size:1.25rem;font-weight:900;color:#0f172a;}
.pai-stat-box--ok{background:#ecfdf3;border-color:#c8f1d9;}
.pai-stat-box--bad{background:#fff1f2;border-color:#ffd3d8;}
.pai-chart-card{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:12px 12px 8px;height:100%;overflow:hidden;}
.pai-chart-card__title{font-weight:800;color:#0f172a;font-size:.9rem;margin-bottom:8px;}
.pai-chart-wrap{position:relative;height:260px;min-height:260px;max-height:260px;}
.pai-chart-wrap canvas{display:block !important;width:100% !important;height:100% !important;}
@media (max-width: 992px){
  .pai-chart-wrap{height:220px;min-height:220px;max-height:220px;}
}
</style>
@stop

@section('js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
window.PAI_STATS_DASHBOARD_URL = @json(route('afiliado.stats.dashboard'));
</script>
<script>
(function(){
  const url = window.PAI_STATS_DASHBOARD_URL;
  if (!url || typeof Chart === 'undefined') return;

  const numFmt = new Intl.NumberFormat('es-CO');
  const charts = { municipio:null, prestador:null, biologico:null, trend:null };

  function colorPalette(n){
      const base=['#2563eb','#0ea5e9','#14b8a6','#10b981','#22c55e','#84cc16','#f59e0b','#f97316','#ef4444','#ec4899','#8b5cf6','#6366f1'];
      const out=[]; for(let i=0;i<n;i++) out.push(base[i%base.length]); return out;
  }

  function getFilters(){
      return {
          start_date: ($('#statsStartDate').val() || '').trim(),
          end_date: ($('#statsEndDate').val() || '').trim(),
          prestador_id: ($('#statsPrestador').val() || '').trim(),
          municipio: ($('#statsMunicipio').val() || '').trim(),
          vacuna_id: ($('#statsVacuna').val() || '').trim(),
          regimen: ($('#statsRegimen').val() || '').trim(),
          mode: 'normative'
      };
  }

  function setKpis(k){
      $('#kpiTotalVacunas').text(numFmt.format(Number(k.total_vacunas || 0)));
      $('#kpiTotalAfiliados').text(numFmt.format(Number(k.total_afiliados || 0)));
      $('#kpiTotalPrestadores').text(numFmt.format(Number(k.total_prestadores || 0)));
      $('#kpiTotalMunicipios').text(numFmt.format(Number(k.total_municipios || 0)));
      $('#kpiCompleto').text(numFmt.format(Number(k.esquema_completo || 0)));
      $('#kpiIncompleto').text(numFmt.format(Number(k.esquema_incompleto || 0)));
  }

  function setMeta(resp){
      const source = (resp && resp.kpis && resp.kpis.evaluation_source) ? resp.kpis.evaluation_source : 'normative';
      const generated = (resp && resp.generated_at) ? resp.generated_at : '';
      const sourceText = source === 'normative' ? 'Evaluación normativa exacta' : 'Evaluación mixta (fallback esquema_completo)';
      const r = (resp && resp.range_info) ? resp.range_info : {};
      const reqStart = r.requested_start || '';
      const reqEnd = r.requested_end || '';
      const hasRangeData = !!r.has_data_for_requested_range;
      const availableMin = r.available_min || '';
      const availableMax = r.available_max || '';

      let msg = sourceText + (generated ? ' | Generado: ' + generated : '');
      if (reqStart && reqEnd && !hasRangeData) {
          msg += ' | Sin datos en el rango solicitado. Disponible desde ' + (availableMin || 'N/D') + ' hasta ' + (availableMax || 'N/D') + '.';
      }
      $('#paiStatsMeta').text(msg);
  }

  function fillCatalogs(resp){
      const c = (resp && resp.catalogs) ? resp.catalogs : {};
      function fill($el, items, valueKey, textKey, placeholder){
          const current = $el.val() || '';
          $el.empty().append('<option value="">'+placeholder+'</option>');
          (items || []).forEach(function(it){
              const val = (it && it[valueKey] !== undefined) ? String(it[valueKey]) : '';
              const txt = (it && it[textKey] !== undefined) ? String(it[textKey]) : val;
              $el.append('<option value="'+$('<div>').text(val).html()+'">'+$('<div>').text(txt).html()+'</option>');
          });
          if (current) $el.val(current);
      }
      fill($('#statsPrestador'), c.prestadores || [], 'id', 'name', 'Todos');
      fill($('#statsVacuna'), c.vacunas || [], 'id', 'nombre', 'Todos');
      fill($('#statsMunicipio'), (c.municipios || []).map(m => ({value:m,text:m})), 'value', 'text', 'Todos');
      fill($('#statsRegimen'), (c.regimenes || []).map(m => ({value:m,text:m})), 'value', 'text', 'Todos');
  }

  function rows(arr){ return (arr||[]).map(r => ({label:String(r.label ?? 'N/D'), total:Number(r.total ?? 0)})); }

  function upsert(key, type, canvasId, labels, data, extra){
      const el = document.getElementById(canvasId); if(!el) return;
      if (charts[key]) { charts[key].data.labels=labels; charts[key].data.datasets[0].data=data; charts[key].update(); return; }
      charts[key] = new Chart(el, {
          type: type,
          data: { labels, datasets:[{ data, backgroundColor: colorPalette(Math.max(data.length,4)), borderColor:'rgba(255,255,255,.8)', borderWidth:1.5, fill:type==='line' }]},
          options: Object.assign({
              responsive:true, maintainAspectRatio:false,
              animation: false,
              normalized: true,
              resizeDelay: 120,
              plugins:{ legend:{display:type==='doughnut'}, tooltip:{callbacks:{label:(ctx)=>' '+numFmt.format(Number(ctx.parsed?.y ?? ctx.parsed ?? 0))}}},
              scales: type==='doughnut' ? {} : {
                y:{beginAtZero:true,ticks:{callback:(v)=>numFmt.format(v), maxTicksLimit:6}},
                x:{ticks:{maxRotation:30,minRotation:0,autoSkip:true,maxTicksLimit:8}}
              }
          }, extra || {})
      });
  }

  function render(ch){
      const m=rows(ch.municipios), p=rows(ch.prestadores), b=rows(ch.biologicos), t=rows(ch.trend);
      upsert('municipio','bar','paiChartMunicipio',m.map(x=>x.label),m.map(x=>x.total));
      upsert('prestador','bar','paiChartPrestador',p.map(x=>x.label),p.map(x=>x.total));
      upsert('biologico','bar','paiChartBiologico',b.map(x=>x.label),b.map(x=>x.total));
      upsert('trend','line','paiChartTrend',t.map(x=>x.label),t.map(x=>x.total),{elements:{line:{tension:.28},point:{radius:3}}});
  }

  function load(){
      $('#paiStatsApplyBtn, #paiStatsRefreshBtn').prop('disabled', true);
      $('#paiStatsMeta').text('Cargando estadísticas...');
      $.ajax({
          url, method:'GET', dataType:'json', timeout:20000, data:getFilters(),
          success:function(resp){
              if (!resp || !resp.ok) { $('#paiStatsMeta').text('No se pudieron cargar las estadísticas.'); return; }
              setKpis(resp.kpis || {}); setMeta(resp); fillCatalogs(resp); render(resp.charts || {});
          },
          error:function(){ $('#paiStatsMeta').text('Error consultando estadísticas. Intenta de nuevo.'); },
          complete:function(){ $('#paiStatsApplyBtn, #paiStatsRefreshBtn').prop('disabled', false); }
      });
  }

  $('#paiStatsApplyBtn, #paiStatsRefreshBtn').on('click', function(e){ e.preventDefault(); load(); });
  $('#paiStatsResetBtn').on('click', function(e){
      e.preventDefault();
      $('#statsStartDate,#statsEndDate,#statsPrestador,#statsMunicipio,#statsVacuna,#statsRegimen').val('');
      load();
  });

  $(document).ready(load);

  // Evita confusión por autocompletado del navegador con fechas sin datos.
  // El usuario puede elegir fechas manualmente y luego aplicar filtros.
  $('#statsStartDate, #statsEndDate').attr('autocomplete', 'off');
})();
</script>
@stop

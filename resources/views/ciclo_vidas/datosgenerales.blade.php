{{-- resources/views/ciclo_vidas/datosgenerales.blade.php --}}
@extends('adminlte::page')

@section('title', 'Ciclos de vida - Datos generales')

@section('content_header')
    <h1 class="mb-3">Datos generales</h1>
@stop

@section('content')

<div class="card">
  <div class="card-body">
    <form id="filtros" class="row g-2">
      <div class="col-md-3">
        <label class="form-label mb-0">Desde</label>
        <input type="date" class="form-control" id="desde" name="desde"
               value="{{ now()->startOfYear()->format('Y-m-d') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label mb-0">Hasta (exclusivo)</label>
        <input type="date" class="form-control" id="hasta" name="hasta"
               value="{{ now()->addDay()->format('Y-m-d') }}">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="button" id="btn-aplicar" class="btn btn-primary">
          <i class="fas fa-sync"></i> Aplicar filtros
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Tarjeta de Totales --}}
<div class="row" id="row-totales">
  <div class="col-md-12">
    <div class="info-box bg-gradient-navy">
      <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">TOTAL ATENCIONES (Todas las áreas)</span>
        <div class="row">
          <div class="col-3"><span class="info-box-number" id="tot-total">0</span><small>Total</small></div>
          <div class="col-3"><span class="info-box-number" id="tot-pac">0</span><small>Pacientes únicos</small></div>
          <div class="col-3"><span class="info-box-number" id="tot-ips">0</span><small>IPS</small></div>
          <div class="col-3"><span class="info-box-number" id="tot-fechas">0</span><small>Fechas distintas</small></div>
        </div>
        <span class="progress-description" id="tot-rango"></span>
      </div>
    </div>
  </div>
</div>

{{-- Tarjetas por área --}}
<div class="row" id="row-areas">
  {{-- Se llena por JS --}}
</div>

@stop

@section('css')
<style>
  .kpi-card .card-body { padding: 0.75rem 1rem; }
  .kpi-badge { font-size: .85rem; }
  .info-box-number { font-size: 1.6rem; }
</style>
@stop

@section('js')
<script>
(function() {
  const routeResumen = "{{ route('pi.datos.resumen') }}";

  function fmt(n){ return new Intl.NumberFormat().format(n||0); }

  function renderAreas(areas) {
    const row = document.getElementById('row-areas');
    row.innerHTML = '';
    if (!areas || !areas.length) { return; }

    areas.forEach(a => {
      const col = document.createElement('div');
      col.className = 'col-lg-4 col-md-6 mb-3';

      col.innerHTML = `
        <div class="card kpi-card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-clinic-medical mr-2"></i> ${a.area}
            </h3>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6 mb-2">
                <div class="small-box bg-gradient-primary mb-0">
                  <div class="inner">
                    <h3 class="mb-0">${fmt(a.total)}</h3>
                    <p class="mb-0">Atenciones</p>
                  </div>
                  <div class="icon"><i class="fas fa-notes-medical"></i></div>
                </div>
              </div>
              <div class="col-6 mb-2">
                <div class="small-box bg-gradient-teal mb-0">
                  <div class="inner">
                    <h3 class="mb-0">${fmt(a.pacientes)}</h3>
                    <p class="mb-0">Pacientes únicos</p>
                  </div>
                  <div class="icon"><i class="fas fa-user-friends"></i></div>
                </div>
              </div>
              <div class="col-6">
                <div class="small-box bg-gradient-indigo mb-0">
                  <div class="inner">
                    <h3 class="mb-0">${fmt(a.ips)}</h3>
                    <p class="mb-0">IPS</p>
                  </div>
                  <div class="icon"><i class="fas fa-hospital"></i></div>
                </div>
              </div>
              <div class="col-6">
                <div class="small-box bg-gradient-orange mb-0">
                  <div class="inner">
                    <h3 class="mb-0">${fmt(a.fechas)}</h3>
                    <p class="mb-0">Fechas distintas</p>
                  </div>
                  <div class="icon"><i class="fas fa-calendar-day"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>`;
      row.appendChild(col);
    });
  }

  function renderTotales(tot, desde, hasta) {
    document.getElementById('tot-total').textContent = fmt(tot.total);
    document.getElementById('tot-pac'  ).textContent = fmt(tot.pacientes);
    document.getElementById('tot-ips'  ).textContent = fmt(tot.ips);
    document.getElementById('tot-fechas').textContent = fmt(tot.fechas);
    document.getElementById('tot-rango').textContent = `Rango: ${desde} → ${hasta} (exclusivo)`;
  }

  async function cargar() {
    const desde = document.getElementById('desde').value;
    const hasta = document.getElementById('hasta').value;

    const url = new URL(routeResumen, window.location.origin);
    url.searchParams.set('desde', desde);
    url.searchParams.set('hasta', hasta);

    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    if (!data.ok) {
      console.error('Error backend', data);
      toastr && toastr.error('No se pudo cargar el resumen.');
      return;
    }

    renderAreas(data.areas || []);
    renderTotales(data.totales || {total:0,pacientes:0,ips:0,fechas:0}, data.desde, data.hasta);
  }

  document.getElementById('btn-aplicar').addEventListener('click', cargar);
  // carga inicial
  cargar();
})();
</script>
@stop

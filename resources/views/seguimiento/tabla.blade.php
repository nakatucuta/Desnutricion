@extends('adminlte::page')

@section('title','Seguimientos')

@section('content')
<div class="content">

  {{-- Contadores --}}
  <div class="row mb-4">
    <div class="col-md-4">
          <div class="stat-box stat-abiertos" id="filter-abiertos">
            <div class="icon"><i class="fas fa-user-check"></i></div> {{-- Ícono de caso activo --}}
            <div class="content">
                <h5>Abiertos</h5>
                <h2>{{ $conteo }}</h2>
            </div>
    
    
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-box stat-proximos" id="filter-proximos">
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
            <div class="content">
                <h5>Próximos</h5>
                <h2>{{ $otro->count() }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-box stat-cerrados" id="filter-cerrados">
            <div class="icon"><i class="fas fa-lock"></i></div>
            <div class="content">
                <h5>Cerrados</h5>
                <h2>{{ $cerrados }}</h2>
            </div>
        </div>
    </div>
</div>


  {{-- Encabezado de controles --}}
  <div class="row mb-3 align-items-center">
    <div class="col-md-6 d-flex align-items-center gap-2">
      {{-- Botón exportar --}}
    

      {{-- Filtro por año --}}
      <label for="filtroAnio" class="mb-0 mr-2 text-dark font-weight-bold">Año:</label>
      <select id="filtroAnio" class="form-control form-control-sm shadow-sm border rounded-pill" style="width: 120px;">
        <option value="">Todos</option>
        @for($año = now()->year; $año >= 2022; $año--)
          <option value="{{ $año }}">{{ $año }}</option>
        @endfor
      </select>
    </div>
  </div>

  {{-- Tabla --}}
  <table id="seguimiento" class="table table-striped table-bordered w-100">
    <thead class="bg-info">
      <tr>
        <th>ID</th>
        <th>Fecha asignación</th>
        <th>Identificación</th>
        <th>Semana Epid</th>
        <th>Nombre</th>
        <th>Estado</th>
        <th>IPS</th>
        <th>Próximo Control</th>
        <th>Acciones</th>
      </tr>
    </thead>
  </table>
</div>

{{-- Spinner de carga --}}
<div id="overlay-spinner">
  <div class="spinner-container">
    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
      <span class="sr-only">Cargando...</span>
    </div>
    <strong class="text-dark mt-3 d-block">Cargando datos, por favor espere...</strong>
  </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap4.min.css') }}">
@include('seguimiento.css')   

@stop

@section('js')
@include('seguimiento.javascript')   
@stop

@extends('adminlte::page')

@section('title', 'Lotes de Importación')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="text-primary mb-0">
            <i class="fas fa-layer-group mr-2"></i>Lotes de Importación
        </h1>
        <small class="text-muted">Control total de cargues masivos: auditoría + borrado por lote</small>
    </div>

    <a href="{{ route('preconcepcional.index') }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success shadow-sm">
        <i class="fas fa-check mr-1"></i>{{ session('success') }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <span class="font-weight-bold text-secondary">
            <i class="fas fa-history mr-1"></i> Historial de cargues
        </span>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="thead-light">
                <tr>
                    <th style="width:70px;">Lote</th>
                    <th>Usuario</th>
                    <th>Archivo</th>
                    <th style="width:110px;">Tamaño</th>
                    <th style="width:120px;">Hash</th>
                    <th style="width:100px;">Tiempo</th>
                    <th style="width:90px;">Creados</th>
                    <th style="width:110px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($batches as $b)
                <tr>
                    <td class="text-center font-weight-bold">#{{ $b->id }}</td>

                    <td>
                        <span class="badge badge-light border p-2">
                            <i class="fas fa-user mr-1"></i>
                            {{ $b->user->name ?? '—' }}
                        </span>
                    </td>

                    <td>{{ $b->original_filename ?? '—' }}</td>

                    <td class="text-center">
                        @php
                            $bytes = $b->file_size_bytes ?? 0;
                            $kb = $bytes ? round($bytes / 1024, 1) : 0;
                        @endphp
                        {{ $kb }} KB
                    </td>

                    <td style="font-size:12px;">
                        <span class="text-monospace">
                            {{ $b->file_hash ? substr($b->file_hash, 0, 12).'…' : '—' }}
                        </span>
                    </td>

                    <td class="text-center">
                        <span class="badge badge-info p-2">{{ $b->duration_ms }} ms</span>
                    </td>

                    <td class="text-center">
                        <span class="badge badge-success p-2">{{ $b->created_rows }}</span>
                    </td>

                    <td class="text-center">
                      <a href="{{ route('preconcepcional.batches.show', $b) }}" class="btn btn-sm btn-primary">
    <i class="fas fa-eye"></i>
</a>

<form action="{{ route('preconcepcional.batches.destroy', $b) }}" method="POST" class="d-inline"
      onsubmit="return confirm('¿Seguro que deseas eliminar este lote y sus registros?')">
    @csrf
    @method('DELETE')
    <button class="btn btn-sm btn-danger">
        <i class="fas fa-trash"></i>
    </button>
</form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">Sin lotes registrados</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $batches->links() }}
        </div>
    </div>
</div>

{{-- ✅ Modal confirmación --}}
<div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">
          <i class="fas fa-exclamation-triangle mr-1"></i> Eliminar lote
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p class="mb-1"><b>Lote:</b> <span id="m_id"></span></p>
        <p class="mb-1"><b>Archivo:</b> <span id="m_file"></span></p>
        <p class="mb-0 text-danger">
            Esto eliminará <b id="m_created"></b> registros creados en este cargue.
        </p>
        <small class="text-muted d-block mt-2">
            Los que existían antes y solo se actualizaron NO se borran.
        </small>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>

        <form id="formDelete" method="POST" action="">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash mr-1"></i> Sí, eliminar
            </button>
        </form>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$('#modalDelete').on('show.bs.modal', function (event) {
    var button  = $(event.relatedTarget);
    var id      = button.data('id');
    var file    = button.data('file');
    var created = button.data('created');

    $('#m_id').text('#' + id);
    $('#m_file').text(file ?? '—');
    $('#m_created').text(created ?? 0);

    var url = "{{ route('preconcepcional.batches.destroy', 'ID_REPLACE') }}";
    url = url.replace('ID_REPLACE', id);
    $('#formDelete').attr('action', url);
});
</script>
@stop

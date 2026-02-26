@extends('adminlte::page')

@section('title', 'Gestion de Lotes PAI')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
    <div>
        <h4 class="mb-0 font-weight-bold">Gestion de Lotes PAI</h4>
        <small class="text-muted">Eliminacion optimizada por lote para administradores</small>
    </div>
    <a href="{{ route('afiliado') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    @if(session('success'))
    <div class="bc-alert bc-alert--success" data-flash-type="success" data-flash-message="{{ session('success') }}">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bc-alert bc-alert--danger" data-flash-type="error" data-flash-message="{{ session('error') }}">
        <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-4 mb-2 mb-md-0">
            <div class="bc-kpi"><div class="bc-kpi__label">Total lotes</div><div class="bc-kpi__value">{{ number_format($stats['total_lotes'] ?? 0) }}</div></div>
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
            <div class="bc-kpi"><div class="bc-kpi__label">Afiliados asociados</div><div class="bc-kpi__value">{{ number_format($stats['total_afiliados'] ?? 0) }}</div></div>
        </div>
        <div class="col-md-4">
            <div class="bc-kpi"><div class="bc-kpi__label">Vacunas asociadas</div><div class="bc-kpi__value">{{ number_format($stats['total_vacunas'] ?? 0) }}</div></div>
        </div>
    </div>

    <div class="card bc-card">
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control mr-2 mb-2 mb-md-0" placeholder="Buscar por ID o fecha">
                <button class="btn btn-primary mr-2" type="submit"><i class="fas fa-search mr-1"></i> Buscar</button>
                <a href="{{ route('batch.cleanup.index') }}" class="btn btn-light">Limpiar</a>
            </form>

            <form id="bulkDeleteForm" action="{{ route('batch.cleanup.bulkDestroy') }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:8px;">
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllRows">Seleccionar pagina</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllRows">Limpiar seleccion</button>
                        <span class="ml-2 text-muted" id="selectedCounter">0 lote(s) seleccionado(s)</span>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash mr-1"></i> Eliminar seleccionados
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm bc-table">
                        <thead>
                            <tr>
                                <th style="width:42px;"><input type="checkbox" id="checkAll"></th>
                                <th style="width:90px;">Lote</th>
                                <th style="width:190px;">Fecha cargue</th>
                                <th style="width:140px;">Afiliados</th>
                                <th style="width:140px;">Vacunas</th>
                                <th class="text-right" style="width:180px;">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotes as $lote)
                            <tr>
                                <td><input type="checkbox" class="row-check" name="batch_ids[]" value="{{ $lote->id }}"></td>
                                <td><span class="badge badge-light">#{{ $lote->id }}</span></td>
                                <td>{{ $lote->fecha_cargue ?? '---' }}</td>
                                <td>{{ number_format($lote->afiliados_count ?? 0) }}</td>
                                <td>{{ number_format($lote->vacunas_count ?? 0) }}</td>
                                <td class="text-right">
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm js-single-delete-btn"
                                            data-batch-id="{{ $lote->id }}"
                                            data-delete-url="{{ route('batch_verifications.destroy', $lote->id) }}">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No hay lotes para mostrar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-2">
                {{ $lotes->links() }}
            </div>
        </div>
    </div>

    <div class="card bc-card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap:8px;">
                <h6 class="mb-0 font-weight-bold">Auditoria reciente de eliminaciones</h6>
                <small class="text-muted">Usuario, cantidades eliminadas y fecha/hora</small>
            </div>
            <div class="table-responsive">
                <table class="table table-sm bc-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:170px;">Fecha / Hora</th>
                            <th style="width:170px;">Usuario</th>
                            <th style="width:120px;">Accion</th>
                            <th style="width:110px;">Lotes</th>
                            <th style="width:120px;">Afiliados</th>
                            <th style="width:110px;">Vacunas</th>
                            <th>IDs lote</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($audits ?? collect()) as $audit)
                        <tr>
                            <td>{{ $audit->created_at ?? '---' }}</td>
                            <td>{{ $audit->user_name ?? 'Sistema' }}</td>
                            <td>
                                @if(($audit->action ?? '') === 'single_delete')
                                <span class="badge badge-warning">Individual</span>
                                @else
                                <span class="badge badge-primary">Masivo</span>
                                @endif
                            </td>
                            <td>{{ number_format($audit->batches_count ?? 0) }}</td>
                            <td>{{ number_format($audit->afiliados_count ?? 0) }}</td>
                            <td>{{ number_format($audit->vacunas_count ?? 0) }}</td>
                            <td class="text-muted">{{ $audit->batch_ids ?? '---' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Aun no hay eventos de auditoria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.bc-kpi{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:12px 14px;box-shadow:0 10px 20px rgba(2,6,23,.06)}
.bc-kpi__label{font-size:.8rem;text-transform:uppercase;letter-spacing:.4px;color:#64748b;font-weight:800}
.bc-kpi__value{font-size:1.2rem;font-weight:900;color:#0f172a}
.bc-card{border:1px solid rgba(15,23,42,.08);border-radius:16px;box-shadow:0 12px 24px rgba(2,6,23,.08)}
.bc-table thead th{background:linear-gradient(180deg,#eef4ff,#fff);font-weight:900;color:#0f172a;border-top:0}
.bc-table td,.bc-table th{vertical-align:middle}
.bc-table tbody tr:hover td{background:#f8fbff}
.bc-alert{border-radius:12px;padding:11px 14px;margin-bottom:12px;border:1px solid;font-weight:700}
.bc-alert--success{background:#ecfdf3;border-color:#b8e9cc;color:#155b36}
.bc-alert--danger{background:#fff1f2;border-color:#ffc9d1;color:#9f1239}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>
<script>
(function(){
  const csrfToken = @json(csrf_token());
  const checkAll = document.getElementById('checkAll');
  const selectAllRows = document.getElementById('selectAllRows');
  const clearAllRows = document.getElementById('clearAllRows');
  const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
  const selectedCounter = document.getElementById('selectedCounter');
  const form = document.getElementById('bulkDeleteForm');
  const flashEl = document.querySelector('[data-flash-type][data-flash-message]');

  function rowChecks(){ return Array.from(document.querySelectorAll('.row-check')); }
  function refreshSelectionUI(){
    const checked = rowChecks().filter(el => el.checked).length;
    selectedCounter.textContent = checked + ' lote(s) seleccionado(s)';
    bulkDeleteBtn.disabled = checked === 0;
    checkAll.checked = checked > 0 && checked === rowChecks().length;
  }

  document.addEventListener('change', function(e){
    if(e.target.classList.contains('row-check')) refreshSelectionUI();
  });

  checkAll?.addEventListener('change', function(){
    rowChecks().forEach(el => { el.checked = checkAll.checked; });
    refreshSelectionUI();
  });

  selectAllRows?.addEventListener('click', function(){
    rowChecks().forEach(el => { el.checked = true; });
    refreshSelectionUI();
  });

  clearAllRows?.addEventListener('click', function(){
    rowChecks().forEach(el => { el.checked = false; });
    refreshSelectionUI();
  });

  form?.addEventListener('submit', function(e){
    const checked = rowChecks().filter(el => el.checked).length;
    if (checked === 0) {
      e.preventDefault();
      return;
    }
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Confirmar eliminacion masiva',
      html: 'Se eliminaran <b>' + checked + ' lote(s)</b> con todos sus afiliados y vacunas asociados.<br>Esta accion no se puede deshacer.',
      showCancelButton: true,
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#dc3545',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) form.submit();
    });
  });

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-single-delete-btn');
    if(!btn) return;
    e.preventDefault();
    const batchId = btn.getAttribute('data-batch-id') || '';
    const deleteUrl = btn.getAttribute('data-delete-url') || '';

    Swal.fire({
      icon: 'warning',
      title: 'Eliminar lote #' + batchId,
      html: 'Se eliminaran todos los datos asociados a este lote.<br>Esta accion no se puede deshacer.',
      showCancelButton: true,
      confirmButtonText: 'Si, eliminar lote',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#dc3545',
      reverseButtons: true
    }).then((result) => {
      if (!result.isConfirmed || !deleteUrl) return;

      const tmpForm = document.createElement('form');
      tmpForm.method = 'POST';
      tmpForm.action = deleteUrl;
      tmpForm.style.display = 'none';

      const tokenInput = document.createElement('input');
      tokenInput.type = 'hidden';
      tokenInput.name = '_token';
      tokenInput.value = csrfToken;
      tmpForm.appendChild(tokenInput);

      const methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      methodInput.value = 'DELETE';
      tmpForm.appendChild(methodInput);

      document.body.appendChild(tmpForm);
      tmpForm.submit();
    });
  });

  if (flashEl && flashEl.dataset.flashMessage) {
    const type = flashEl.dataset.flashType === 'success' ? 'success' : 'error';
    Swal.fire({
      icon: type,
      title: type === 'success' ? 'Operacion exitosa' : 'Atencion',
      text: flashEl.dataset.flashMessage,
      timer: 3200,
      showConfirmButton: false,
      toast: true,
      position: 'top-end'
    });
  }

  refreshSelectionUI();
})();
</script>
@stop

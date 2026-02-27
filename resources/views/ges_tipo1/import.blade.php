@extends('adminlte::page')

@section('title', 'Importar Gestantes Tipo')

@section('content_header')
    <h1 class="text-primary mb-0">
        <i class="fas fa-upload mr-2"></i>Importar Excel Gestantes Tipo
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <strong>Validacion de formato:</strong>
                el archivo debe tener encabezados en la fila 1 y datos desde la fila 2.
                Debe conservar las <strong>31 columnas</strong> en su posicion original.
                <br>
                <strong>Modo estricto:</strong> si se detecta cualquier inconsistencia en alguna columna,
                el cargue se rechaza y se muestra consolidado de errores por fila/campo.
            </div>

            <form id="ges-tipo1-import-form" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="form-group">
                    <label for="ges-tipo1-file"><strong>Archivo</strong></label>
                    <input
                        type="file"
                        id="ges-tipo1-file"
                        name="file"
                        class="form-control"
                        required
                        accept=".xlsx,.xls,.csv">
                    <small class="text-muted d-block mt-1">
                        Formatos permitidos: XLSX, XLS, CSV. Maximo recomendado: 20MB.
                    </small>
                </div>

                <div class="d-flex flex-wrap" style="gap:.5rem;">
                    <button id="ges-tipo1-submit" type="submit" class="btn btn-success">
                        <i class="fas fa-play mr-1"></i> Iniciar cargue en cola
                    </button>
                    <a href="{{ route('ges_tipo1.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-table mr-1"></i> Ver tabla
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" id="ges-tipo1-progress-card" style="display:none;">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-tasks mr-1"></i>Estado del proceso</h3>
        </div>
        <div class="card-body">
            <div class="mb-2">
                <strong id="ges-tipo1-step">En cola...</strong>
                <div class="text-muted" id="ges-tipo1-message">Preparando proceso...</div>
            </div>
            <div class="progress" style="height:20px;">
                <div
                    id="ges-tipo1-bar"
                    class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar"
                    style="width:0%;">
                    0%
                </div>
            </div>
            <div class="mt-3 text-muted small" id="ges-tipo1-meta"></div>
        </div>
    </div>

    <div class="card border-danger" id="ges-tipo1-errors-card" style="display:none;">
        <div class="card-header bg-danger text-white">
            <h3 class="card-title mb-0"><i class="fas fa-exclamation-triangle mr-1"></i>Errores de cargue</h3>
        </div>
        <div class="card-body">
            <ul class="mb-0" id="ges-tipo1-errors-list"></ul>
        </div>
    </div>
@stop

@section('adminlte_js')
    @parent
    <script>
        (function () {
            const START_URL = @json(route('ges_tipo1.import.start'));
            const STATUS_BASE_URL = @json(route('ges_tipo1.import.status', ['token' => '__TOKEN__']));

            const form = document.getElementById('ges-tipo1-import-form');
            const fileInput = document.getElementById('ges-tipo1-file');
            const submitBtn = document.getElementById('ges-tipo1-submit');

            const progressCard = document.getElementById('ges-tipo1-progress-card');
            const progressBar = document.getElementById('ges-tipo1-bar');
            const stepText = document.getElementById('ges-tipo1-step');
            const messageText = document.getElementById('ges-tipo1-message');
            const metaText = document.getElementById('ges-tipo1-meta');

            const errorsCard = document.getElementById('ges-tipo1-errors-card');
            const errorsList = document.getElementById('ges-tipo1-errors-list');

            let pollTimer = null;
            let currentToken = null;
            let isPolling = false;

            function setLoadingState(isLoading) {
                submitBtn.disabled = isLoading;
                submitBtn.innerHTML = isLoading
                    ? '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...'
                    : '<i class="fas fa-play mr-1"></i> Iniciar cargue en cola';
            }

            function setProgress(percent, step, message, status) {
                const pct = Math.max(0, Math.min(100, parseInt(percent || 0, 10)));

                progressBar.style.width = pct + '%';
                progressBar.textContent = pct + '%';
                stepText.textContent = step || 'Procesando...';
                messageText.textContent = message || '';

                progressBar.classList.remove('bg-danger', 'bg-success');
                if (status === 'failed') progressBar.classList.add('bg-danger');
                if (status === 'done') progressBar.classList.add('bg-success');
            }

            function showErrors(errors) {
                errorsList.innerHTML = '';
                (errors || []).forEach(function (error) {
                    const li = document.createElement('li');
                    li.textContent = error;
                    errorsList.appendChild(li);
                });
                errorsCard.style.display = 'block';
            }

            function hideErrors() {
                errorsCard.style.display = 'none';
                errorsList.innerHTML = '';
            }

            function stopPolling() {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
                isPolling = false;
            }

            async function pollStatus() {
                if (!currentToken || isPolling) return;
                isPolling = true;

                try {
                    const url = STATUS_BASE_URL.replace('__TOKEN__', encodeURIComponent(currentToken)) + '?t=' + Date.now();
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        isPolling = false;
                        return;
                    }

                    const data = await response.json();
                    setProgress(data.percent, data.step, data.message, data.status);

                    if (data.batch_id) {
                        metaText.innerHTML = 'Lote generado: <strong>#' + data.batch_id + '</strong>';
                    }

                    const status = String(data.status || '').toLowerCase();
                    if (status === 'failed') {
                        stopPolling();
                        setLoadingState(false);
                        showErrors(data.errors || ['La importacion fallo. Revisa el archivo.']);
                        if (data.errors_count) {
                            metaText.innerHTML = 'Errores detectados: <strong>' + data.errors_count + '</strong>';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Importacion fallida',
                            text: data.message || 'Se detectaron errores durante el proceso.',
                        });
                    } else if (status === 'done') {
                        stopPolling();
                        setLoadingState(false);
                        hideErrors();
                        Swal.fire({
                            icon: 'success',
                            title: 'Importacion completada',
                            text: data.message || 'El cargue termino correctamente.',
                        });
                    }
                } catch (error) {
                    // mantener polling en errores transitorios
                } finally {
                    isPolling = false;
                }
            }

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                hideErrors();

                if (!fileInput.files || !fileInput.files[0]) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Archivo requerido',
                        text: 'Selecciona un archivo para iniciar el cargue.',
                    });
                    return;
                }

                setLoadingState(true);
                progressCard.style.display = 'block';
                setProgress(2, 'subida', 'Subiendo archivo al servidor...', 'running');
                metaText.textContent = '';

                stopPolling();
                currentToken = null;

                const formData = new FormData();
                formData.append('file', fileInput.files[0]);

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(START_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok || !data || !data.ok || !data.token) {
                        setLoadingState(false);
                        showErrors([data.message || 'No se pudo iniciar la importacion.']);
                        setProgress(0, 'error', data.message || 'Error al iniciar el proceso.', 'failed');
                        return;
                    }

                    currentToken = data.token;
                    setProgress(5, 'cola', 'Importacion en cola...', 'running');

                    pollTimer = setInterval(pollStatus, 1400);
                    await pollStatus();
                } catch (error) {
                    setLoadingState(false);
                    showErrors(['Error de red al enviar archivo. Intenta nuevamente.']);
                    setProgress(0, 'error', 'No fue posible iniciar el cargue.', 'failed');
                }
            });
        })();
    </script>
@stop

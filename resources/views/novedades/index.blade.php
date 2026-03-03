@extends('adminlte::page')

@section('title', 'Novedades')

@section('content_header')
    <h1>Novedades Globales</h1>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-12 mb-3">
            <div class="card">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
                    <div class="nav nav-pills" id="novedades-tabs" role="tablist">
                        <a class="nav-link active" id="tab-unread-link" data-toggle="tab" href="#tab-unread" role="tab" aria-controls="tab-unread" aria-selected="true">
                            No leidas ({{ $unreadCount }})
                        </a>
                        <a class="nav-link" id="tab-read-link" data-toggle="tab" href="#tab-read" role="tab" aria-controls="tab-read" aria-selected="false">
                            Leidas ({{ $readCount }})
                        </a>
                        <a class="nav-link" id="tab-archived-link" data-toggle="tab" href="#tab-archived" role="tab" aria-controls="tab-archived" aria-selected="false">
                            Archivadas ({{ $archivedCount }})
                        </a>
                        <a class="nav-link" id="tab-all-link" data-toggle="tab" href="#tab-all" role="tab" aria-controls="tab-all" aria-selected="false">
                            Todas ({{ $unreadCount + $readCount + $archivedCount }})
                        </a>
                    </div>
                    @if($unreadCount > 0)
                        <form id="mark-all-form" method="POST" action="{{ route('novedades.readAll') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">Marcar todas como leidas</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($canPublish)
        <div class="row">
            <div class="col-lg-12 mb-3">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Publicar Nueva Novedad (Solo Admin)</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('novedades.store') }}">
                            @csrf
                            <div class="form-group">
                                <label for="title">Titulo</label>
                                <input id="title" name="title" type="text" maxlength="160" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                                @error('title') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="message">Mensaje</label>
                                <textarea id="message" name="message" rows="4" maxlength="5000" class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                                @error('message') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group form-check">
                                <input
                                    id="is_mandatory"
                                    name="is_mandatory"
                                    type="checkbox"
                                    value="1"
                                    class="form-check-input"
                                    {{ old('is_mandatory') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_mandatory">
                                    Novedad obligatoria (mostrar modal forzosa al iniciar sesion hasta marcar como leida)
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">Publicar para Todos</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="tab-content" id="novedades-tab-content">
                <div class="tab-pane fade show active" id="tab-unread" role="tabpanel" aria-labelledby="tab-unread-link">
                    @include('novedades.partials.list', [
                        'items' => $unreadNovedades,
                        'canPublish' => $canPublish,
                        'showReadDate' => false,
                        'allowArchive' => false,
                        'allowUnarchive' => false,
                        'emptyMessage' => 'No tienes novedades pendientes por leer.',
                    ])
                </div>
                <div class="tab-pane fade" id="tab-read" role="tabpanel" aria-labelledby="tab-read-link">
                    @include('novedades.partials.list', [
                        'items' => $readNovedades,
                        'canPublish' => $canPublish,
                        'showReadDate' => true,
                        'allowArchive' => true,
                        'allowUnarchive' => false,
                        'emptyMessage' => 'Aun no tienes novedades leidas.',
                    ])
                </div>
                <div class="tab-pane fade" id="tab-archived" role="tabpanel" aria-labelledby="tab-archived-link">
                    @include('novedades.partials.list', [
                        'items' => $archivedNovedades,
                        'canPublish' => $canPublish,
                        'showReadDate' => true,
                        'allowArchive' => false,
                        'allowUnarchive' => true,
                        'emptyMessage' => 'No tienes novedades archivadas.',
                    ])
                </div>
                <div class="tab-pane fade" id="tab-all" role="tabpanel" aria-labelledby="tab-all-link">
                    @include('novedades.partials.list', [
                        'items' => $allNovedades,
                        'canPublish' => $canPublish,
                        'showReadDate' => true,
                        'allowArchive' => true,
                        'allowUnarchive' => true,
                        'emptyMessage' => 'No hay novedades publicadas.',
                    ])
                </div>
            </div>
        </div>
    </div>
@stop

@section('adminlte_js')
    @parent
    <script>
        $(function () {
            const tabStorageKey = 'novedades_tab_activa';
            const $tabs = $('#novedades-tabs a[data-toggle="tab"]');
            const $markAll = $('#mark-all-form');
            const hasUnread = {{ $unreadCount > 0 ? 'true' : 'false' }};

            function syncActions() {
                if (!$markAll.length) return;
                const activeHref = $('#novedades-tabs .nav-link.active').attr('href');
                $markAll.toggle(hasUnread && activeHref === '#tab-unread');
            }

            const savedTab = localStorage.getItem(tabStorageKey);
            if (savedTab && $tabs.filter('[href="' + savedTab + '"]').length) {
                $tabs.filter('[href="' + savedTab + '"]').tab('show');
            }

            syncActions();

            $tabs.on('shown.bs.tab', function (event) {
                const href = $(event.target).attr('href');
                localStorage.setItem(tabStorageKey, href);
                syncActions();
            });
        });
    </script>
@stop

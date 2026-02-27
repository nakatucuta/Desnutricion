@forelse($items as $novedad)
    @php($isReadItem = $novedad->reads->isNotEmpty())
    @php($read = $novedad->reads->first())
    @php($readAt = optional($read)->read_at)
    @php($isArchivedItem = !is_null(optional($read)->archived_at))
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ $novedad->title }}</h3>
            <div class="d-flex align-items-center" style="gap:.45rem;">
                <span class="badge {{ $isArchivedItem ? 'badge-secondary' : ($isReadItem ? 'badge-success' : 'badge-warning') }}">
                    {{ $isArchivedItem ? 'Archivada' : ($isReadItem ? 'Leida' : 'No leida') }}
                </span>
                @if($canPublish)
                    <a href="{{ route('novedades.audit', $novedad->id) }}" class="btn btn-sm btn-outline-primary">
                        Auditoria
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <p class="mb-2">{{ $novedad->message }}</p>
            <small class="text-muted d-block mb-3">
                Publicada: {{ optional($novedad->created_at)->format('Y-m-d H:i') }}
                por {{ optional($novedad->creator)->name ?? 'Administrador' }}
            </small>
            @if($showReadDate && $isReadItem && $readAt)
                <small class="text-success d-block mb-2">
                    Leida por ti el {{ optional($readAt)->format('Y-m-d H:i') }}
                </small>
            @endif
            @if($isArchivedItem)
                <small class="text-muted d-block mb-2">
                    Archivada el {{ optional(optional($read)->archived_at)->format('Y-m-d H:i') }}
                </small>
            @endif
            @if(!$isReadItem)
                <form method="POST" action="{{ route('novedades.read', $novedad->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success">Marcar como leida</button>
                </form>
            @else
                <div class="d-flex flex-wrap" style="gap:.45rem;">
                    @if($allowArchive && !$isArchivedItem)
                        <form method="POST" action="{{ route('novedades.archive', $novedad->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Archivar</button>
                        </form>
                    @endif
                    @if($allowUnarchive && $isArchivedItem)
                        <form method="POST" action="{{ route('novedades.unarchive', $novedad->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-info">Desarchivar</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info">{{ $emptyMessage }}</div>
@endforelse

@extends('adminlte::page')

@section('title', 'PDF no disponible')

@section('content')
<div class="container-fluid" style="padding-top:1.2rem; padding-bottom:1.2rem;">
    <div style="max-width:850px; margin:0 auto; border:1px solid #d9eaee; border-radius:20px; overflow:hidden; box-shadow:0 14px 30px rgba(18,66,79,.12); background:#fff;">
        <div style="display:flex; align-items:center; gap:.9rem; padding:1rem 1.2rem; color:#fff; background:linear-gradient(130deg,#0f6f7e,#11939a 52%,#17a85f);">
            <div style="width:58px; height:58px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.22);">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" style="width:36px; height:auto; object-fit:contain;">
            </div>
            <div>
                <h1 style="margin:0; font-size:1.2rem; font-weight:800;">No se encontro el PDF</h1>
                <p style="margin:.2rem 0 0; opacity:.92;">{{ $moduleName ?? 'Seguimiento' }} | Registro #{{ $recordId ?? '-' }}</p>
            </div>
        </div>

        <div style="padding:1.1rem 1.2rem;">
            <div style="border:1px solid #f5d2d7; background:#fff4f6; color:#8b1e2d; border-radius:12px; padding:.75rem .85rem; margin-bottom:.8rem;">
                El archivo PDF asociado no esta disponible en las rutas esperadas.
            </div>

            <div style="display:grid; grid-template-columns:180px 1fr; gap:.55rem; border:1px solid #e2edf0; border-radius:12px; padding:.8rem; background:#fbfeff;">
                <strong style="color:#4b6f76;">Ruta registrada:</strong>
                <span style="color:#1e4851; word-break:break-word;">{{ $rawPath ?: 'Sin valor en base de datos' }}</span>
            </div>

            <div style="display:flex; gap:.55rem; flex-wrap:wrap; margin-top:1rem;">
                <a href="{{ $backUrl ?? url()->previous() }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Volver</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.location.reload();"><i class="fas fa-sync-alt"></i> Reintentar</button>
            </div>
        </div>
    </div>
</div>
@stop

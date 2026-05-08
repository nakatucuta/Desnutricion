@extends('adminlte::page')

@section('title', 'Permisos de Usuarios')
@section('plugins.Datatables', true)
@section('css')
<style>
    :root{
        --ac-primary:#0f766e;
        --ac-cyan:#14b8a6;
        --ac-bg:#f2f8f7;
        --ac-panel:#ffffff;
        --ac-text:#163b3a;
        --ac-muted:#5c7f7b;
        --ac-border:#d2e9e6;
        --ac-shadow:0 12px 30px rgba(9, 53, 50, .10);
    }
    .content-wrapper{
        background: radial-gradient(circle at 8% 5%, rgba(0,180,216,.13), transparent 36%), var(--ac-bg);
    }
    .ac-hero{
        background:
            radial-gradient(circle at 88% 12%, rgba(91,224,221,.22), transparent 34%),
            linear-gradient(110deg, #031a44 0%, #153b66 43%, #192f66 66%, #0d6d75 100%);
        border-radius: 30px;
        color: #fff;
        padding: 1.65rem 1.85rem;
        box-shadow: 0 24px 50px rgba(4, 18, 45, .35);
        border: 1px solid rgba(155, 210, 255, .22);
        min-height: 188px;
        position: relative;
        overflow: hidden;
        color:#fff !important;
    }
    .ac-hero::after{
        content:"";
        position:absolute;
        inset:0;
        background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.05) 48%, transparent 76%);
        pointer-events:none;
    }
    .ac-hero__brand{
        display:flex;
        align-items:center;
        gap:1rem;
        z-index:2;
        position:relative;
    }
    .ac-hero__logo-wrap{
        width:68px;
        height:68px;
        border-radius:20px;
        background: rgba(203, 227, 255, .14);
        border:1px solid rgba(226, 243, 255, .30);
        display:flex;
        align-items:center;
        justify-content:center;
        backdrop-filter: blur(6px);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.08);
    }
    .ac-hero__logo{
        width:44px;
        height:auto;
        object-fit:contain;
    }
    .ac-hero__title{
        margin:0;
        font-size:3.15rem;
        line-height:1.03;
        font-weight:900;
        letter-spacing:.25px;
        max-width:760px;
        color:#ffffff !important;
        -webkit-text-fill-color:#ffffff !important;
        text-shadow:0 2px 10px rgba(0,0,0,.18);
    }
    .ac-hero__subtitle{
        margin:1.05rem 0 0;
        color:rgba(245,251,255,.96) !important;
        font-size:1.02rem;
    }
    .ac-hero__badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:.36rem .95rem;
        border-radius:999px;
        background: rgba(146, 175, 214, .24);
        border:1px solid rgba(187, 217, 255, .42);
        color:#ffffff !important;
        font-size:.78rem;
        font-weight:800;
        letter-spacing:.12em;
        text-transform:uppercase;
        margin-bottom:.95rem;
    }
    .ac-pill{
        border-radius:999px;
        padding:.34rem .8rem;
        font-weight:700;
        font-size:.79rem;
        border:1px solid rgba(255,255,255,.24);
        background: rgba(244,63,94,.92);
        color:#fff !important;
        z-index:2;
        position:relative;
    }
    .ac-hero *{
        color:inherit;
    }
    .ac-hero .ac-hero__title,
    .ac-hero .ac-hero__subtitle,
    .ac-hero .ac-hero__badge,
    .ac-hero .ac-pill{
        color:#fff !important;
    }
    .ac-hero__orbital{
        position:relative;
        z-index:2;
        min-width:340px;
        max-width:420px;
        width:36%;
        min-height:138px;
        border-radius:30px;
        border:1px solid rgba(170, 223, 225, .26);
        background: linear-gradient(135deg, rgba(104,152,208,.24), rgba(79,205,186,.22));
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.06);
        margin-left:1rem;
    }
    .ac-hero__halo{
        width:214px;
        height:214px;
        border-radius:50%;
        border:1px dashed rgba(201, 237, 255, .38);
        background: radial-gradient(circle at center, rgba(123,218,219,.28) 0%, rgba(61,102,159,.18) 48%, rgba(28,53,97,.05) 74%, transparent 100%);
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow: 0 0 0 16px rgba(153, 223, 240, .06);
    }
    .ac-hero__crest{
        width:124px;
        height:152px;
        border-radius:30px;
        background:#edf4ff;
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow:0 12px 30px rgba(1, 16, 43, .35);
        border:1px solid rgba(255,255,255,.92);
    }
    .ac-hero__crest img{
        width:86px;
        height:auto;
        object-fit:contain;
    }
    .ac-card{
        border:1px solid var(--ac-border);
        border-radius:16px;
        overflow:hidden;
        box-shadow: 0 10px 24px rgba(13,42,79,.06);
    }
    .ac-card .card-header{
        background: linear-gradient(180deg, #f8fffe 0%, #eef9f7 100%);
        border-bottom:1px solid var(--ac-border);
        color:var(--ac-text);
        font-weight:800;
    }
    #users-permissions-table_wrapper{
        padding: .75rem;
    }
    #users-permissions-table{
        border-collapse:separate!important;
        border-spacing:0;
        table-layout: fixed;
    }
    #users-permissions-table thead th{
        background:#e8f6f3;
        color:#1f4d49;
        font-weight:800;
        border-bottom:1px solid var(--ac-border)!important;
    }
    #users-permissions-table tbody tr{
        transition: background-color .16s ease;
    }
    #users-permissions-table tbody tr:hover{
        background:#f2fbf9;
    }
    #users-permissions-table td{
        vertical-align:top!important;
        border-color:#dcebe8!important;
    }
    #users-permissions-table th.col-id,
    #users-permissions-table td.col-id{width:64px;max-width:64px;}
    #users-permissions-table th.col-user,
    #users-permissions-table td.col-user{width:220px;max-width:220px;}
    #users-permissions-table th.col-email,
    #users-permissions-table td.col-email{width:300px;max-width:300px;}
    #users-permissions-table th.col-code,
    #users-permissions-table td.col-code{width:126px;max-width:126px;}
    #users-permissions-table th.col-type,
    #users-permissions-table td.col-type{width:62px;max-width:62px;}
    #users-permissions-table th.col-action,
    #users-permissions-table td.col-action{width:138px;max-width:138px;}

    .ac-cell-compact{
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        max-width: 100%;
    }
    .ac-user{
        font-weight:700;
        color:#1d3f3d;
        letter-spacing:.1px;
    }
    .ac-email,.ac-code{
        font-size:.95rem;
        color:#355d5a;
    }
    .ac-tech-grid{
        position:relative;
    }
    .ac-tech-grid::before{
        content:"";
        position:absolute;
        inset:0;
        background-image: linear-gradient(to right, rgba(22,110,102,.06) 1px, transparent 1px),
                          linear-gradient(to bottom, rgba(22,110,102,.05) 1px, transparent 1px);
        background-size: 24px 24px;
        pointer-events:none;
        opacity:.45;
    }
    .ac-tech-grid > *{
        position:relative;
        z-index:1;
    }
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select{
        border-radius:10px!important;
        border:1px solid #b7d8d4!important;
        background:#fff!important;
        color:#184645!important;
        min-height:36px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current{
        border-radius:8px!important;
        background:linear-gradient(180deg,#14b8a6,#0f766e)!important;
        color:#fff!important;
        border:1px solid #0f766e!important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button{
        border-radius:8px!important;
        border:1px solid #c8e2de!important;
        background:#fff!important;
        color:#1f4d49!important;
    }
    #users-permissions-table .form-check{
        background:#edf9f7;
        border:1px solid #cfe8e4;
        border-radius:10px;
        padding:.2rem .5rem .2rem 1.35rem;
        margin-bottom:.32rem;
    }
    #users-permissions-table .form-check-input{
        margin-top:.33rem;
    }
    #users-permissions-table .btn-success{
        border-radius:10px;
        font-weight:700;
        background:linear-gradient(180deg,#18b15a,#0c9848);
        border-color:#0e9347;
        box-shadow:0 8px 16px rgba(12,152,72,.22);
    }
    .ac-requests .table thead th{
        background:#e8f6f3;
        color:#1f4d49;
        border-color:#d1e9e5!important;
    }
    .ac-requests .form-control{
        border-radius:9px;
        border:1px solid #c0dfdb;
    }
    @media (max-width: 991.98px){
        .ac-hero{
            border-radius:20px;
            padding:1.1rem 1rem;
            min-height:auto;
        }
        .ac-hero__title{font-size:1.9rem;}
        .ac-hero__subtitle{font-size:.9rem;}
        .ac-hero__orbital{
            width:100%;
            min-width:0;
            margin-left:0;
            margin-top:1rem;
            min-height:120px;
            border-radius:20px;
        }
        .ac-hero__halo{
            width:150px;
            height:150px;
        }
        .ac-hero__crest{
            width:92px;
            height:114px;
            border-radius:24px;
        }
        .ac-hero__crest img{
            width:64px;
        }
        #users-permissions-table{
            table-layout:auto;
        }
        #users-permissions-table th.col-user,
        #users-permissions-table td.col-user,
        #users-permissions-table th.col-email,
        #users-permissions-table td.col-email,
        #users-permissions-table th.col-code,
        #users-permissions-table td.col-code{
            width:auto;
            max-width:none;
        }
    }
</style>
@stop

@section('content_header')
    <div class="ac-hero d-flex justify-content-between align-items-center flex-wrap">
        <div class="ac-hero__brand">
            <div>
                <span class="ac-hero__badge">Control de Acceso</span>
                <h1 class="ac-hero__title">Gestiona aqui toda la informacion.</h1>
                <p class="ac-hero__subtitle mb-0">Administra usuarios, permisos y solicitudes de acceso con una vista centralizada y segura.</p>
            </div>
        </div>
        <div class="ac-hero__orbital">
            <div class="ac-hero__halo">
                <div class="ac-hero__crest">
                    <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="ac-hero__logo">
                </div>
            </div>
        </div>
        <span class="ac-pill mt-2 mt-md-0">Solo superadministrador</span>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card ac-card mb-4 ac-tech-grid">
        <div class="card-header"><strong>Asignar o quitar permisos por usuario</strong></div>
        <div class="card-body table-responsive p-0">
            <table id="users-permissions-table" class="table table-sm table-bordered mb-0" style="width:100%;">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Codigo</th>
                        <th>Tipo</th>
                        <th>Permisos</th>
                        <th style="width: 130px;">Accion</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card ac-card ac-requests">
        <div class="card-header"><strong>Solicitudes pendientes de acceso</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Modulo</th>
                        <th>Motivo</th>
                        <th style="width: 280px;">Gestion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingRequests as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>{{ optional($req->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $req->user->name ?? 'N/A' }}<br><small>{{ $req->user->email ?? '' }}</small></td>
                            <td>{{ $req->modulePermission->name ?? $req->modulePermission->code ?? 'N/A' }}</td>
                            <td>{{ $req->requested_reason ?: 'Sin motivo' }}</td>
                            <td>
                                <form method="POST" action="{{ route('access-control.requests.resolve', $req) }}" class="form-inline mb-1">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <input type="text" name="admin_response" class="form-control form-control-sm mr-2"
                                        placeholder="Respuesta opcional">
                                    <button type="submit" class="btn btn-sm btn-success">Aprobar</button>
                                </form>
                                <form method="POST" action="{{ route('access-control.requests.resolve', $req) }}" class="form-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <input type="text" name="admin_response" class="form-control form-control-sm mr-2"
                                        placeholder="Motivo de rechazo">
                                    <button type="submit" class="btn btn-sm btn-danger">Rechazar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay solicitudes pendientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
    (function () {
        $('#users-permissions-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            searchDelay: 250,
            ajax: '{{ route('access-control.data') }}',
            order: [[0, 'desc']],
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    className: 'col-id'
                },
                {
                    data: 'user_label',
                    name: 'user_label',
                    orderable: false,
                    searchable: true,
                    className: 'col-user',
                    render: function (data, type) {
                        if (type !== 'display') return data;
                        const raw = $('<div>').html(data).text();
                        return '<span class="ac-cell-compact ac-user" title="' + $('<div>').text(raw).html() + '">' + data + '</span>';
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    className: 'col-email',
                    render: function (data, type) {
                        if (type !== 'display') return data;
                        const safe = data || '';
                        return '<span class="ac-cell-compact ac-email" title="' + $('<div>').text(safe).html() + '">' + $('<div>').text(safe).html() + '</span>';
                    }
                },
                {
                    data: 'codigohabilitacion',
                    name: 'codigohabilitacion',
                    className: 'col-code',
                    render: function (data, type) {
                        if (type !== 'display') return data;
                        const safe = data || '';
                        return '<span class="ac-cell-compact ac-code" title="' + $('<div>').text(safe).html() + '">' + $('<div>').text(safe).html() + '</span>';
                    }
                },
                {
                    data: 'usertype',
                    name: 'usertype',
                    className: 'col-type'
                },
                { data: 'permissions_html', name: 'permissions_html', orderable: false, searchable: false },
                { data: 'action_html', name: 'action_html', orderable: false, searchable: false, className: 'col-action' }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
        });

        const wrapper = $('#users-permissions-table_wrapper');
        wrapper.find('.dataTables_filter input')
            .attr('placeholder', 'Buscar por nombre, correo o codigo...')
            .addClass('form-control form-control-sm');
        wrapper.find('.dataTables_length select').addClass('form-control form-control-sm');
        $('#users-permissions-table thead th').eq(0).addClass('col-id');
        $('#users-permissions-table thead th').eq(1).addClass('col-user');
        $('#users-permissions-table thead th').eq(2).addClass('col-email');
        $('#users-permissions-table thead th').eq(3).addClass('col-code');
        $('#users-permissions-table thead th').eq(4).addClass('col-type');
        $('#users-permissions-table thead th').eq(6).addClass('col-action');
    })();
</script>
@stop

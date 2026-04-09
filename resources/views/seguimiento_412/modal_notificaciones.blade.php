@php
    $novedades = $notificacionesPendientes ?? collect();
    $totalNovedades = (int) ($conteo ?? $novedades->count());
    $currentYear = (int) now()->year;
    $aniosDisponibles = collect([$currentYear])->merge($novedades
        ->map(function ($seguimiento) {
            $baseFecha = $seguimiento->seguimiento_created_at ?? $seguimiento->fecha_proximo_control ?? null;
            if (!$baseFecha) {
                return null;
            }

            try {
                return Carbon\Carbon::parse($baseFecha)->year;
            } catch (\Throwable $e) {
                return null;
            }
        })
        ->filter())
        ->unique()
        ->sortDesc()
        ->values();
@endphp

<div class="seg-notify412-bell-wrap">
    <button type="button"
        class="btn {{ $totalNovedades > 0 ? 'btn-danger' : 'btn-primary' }} btn-sm btn-pulse rounded-circle p-0 seg-notify412-bell"
        data-toggle="modal"
        data-target="#modalNovedades412"
        title="Notificaciones de seguimiento">
        <i class="fas fa-bell fa-lg text-white"></i>
        <span class="badge badge-light position-absolute seg-notify412-badge">{{ $totalNovedades }}</span>
    </button>
</div>

<div class="modal fade" id="modalNovedades412" tabindex="-1" aria-labelledby="modalNovedades412Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content seg-notify412-modal">
            <div class="modal-header border-0">
                <div class="seg-notify412-modal__brand">
                    <img src="{{ asset('img/logo.png') }}" alt="Escudo empresa" class="seg-notify412-modal__logo">
                    <div>
                        <h5 class="modal-title mb-1" id="modalNovedades412Label">
                            Novedades pendientes evento 412
                        </h5>
                        <small class="seg-notify412-modal__subtitle">
                            Panel de cumplimiento: estas alertas continuaran hasta cerrar el caso o completar el seguimiento.
                        </small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body pt-2">
                <div class="seg-notify412-summary mb-3">
                    <div class="seg-notify412-summary__item">
                        <span class="seg-notify412-summary__label">Novedades activas</span>
                        <strong class="seg-notify412-summary__value">{{ $novedades->count() }}</strong>
                    </div>
                    <div class="seg-notify412-summary__item">
                        <span class="seg-notify412-summary__label">Alcance</span>
                        <strong class="seg-notify412-summary__value">
                            {{ (int) Auth::user()->usertype === 2 ? 'Solo tus casos' : 'Todos los casos' }}
                        </strong>
                    </div>
                    <div class="seg-notify412-summary__item">
                        <span class="seg-notify412-summary__label">Normativa</span>
                        <strong class="seg-notify412-summary__value">Vencidos y proximos 0-2 dias</strong>
                    </div>
                    <div class="seg-notify412-summary__item">
                        <span class="seg-notify412-summary__label">Filtro por ano</span>
                        <select id="filtroAnioNovedades412" class="form-control form-control-sm seg-notify412-summary__select">
                            <option value="">Todos</option>
                            @foreach($aniosDisponibles as $anio)
                                <option value="{{ $anio }}" {{ (int) $anio === $currentYear ? 'selected' : '' }}>{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seg-notify412-results mb-2">
                    Mostrando <strong id="novedades412VisibleCount">{{ $novedades->count() }}</strong> de {{ $novedades->count() }} novedades.
                </div>

                @if($novedades->count() > 0)
                    @foreach($novedades as $seguimiento)
                        @include('seguimiento_412.notificacion_individual', ['seguimiento' => $seguimiento])
                    @endforeach
                @else
                    <div class="alert alert-success mb-0">
                        No tienes novedades pendientes en el ano vigente.
                    </div>
                @endif
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@if($totalNovedades > 0)
    <div class="seg-notify412-toast" id="toastNovedades412">
        <div class="seg-notify412-toast__icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="seg-notify412-toast__content">
            <strong>Revisame, tienes novedades pendientes</strong>
            <small>{{ $totalNovedades }} seguimiento(s) requieren gestion segun la normativa.</small>
        </div>
        <button type="button" class="btn btn-light btn-sm seg-notify412-toast__btn" data-toggle="modal" data-target="#modalNovedades412">
            Ver
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const autoCloseMs = 8000;
            setTimeout(function () {
                if (typeof $ !== 'undefined') {
                    $('#modalNovedades412').modal('show');
                }
            }, 500);

            setTimeout(function () {
                const toast = document.getElementById('toastNovedades412');
                if (toast) {
                    toast.style.display = 'none';
                }
                if (typeof $ !== 'undefined') {
                    $('#modalNovedades412').modal('hide');
                }
            }, autoCloseMs);
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selector = document.getElementById('filtroAnioNovedades412');
        if (!selector) {
            return;
        }

        const cards = Array.from(document.querySelectorAll('#modalNovedades412 .seg-notify-card412'));
        const countNode = document.getElementById('novedades412VisibleCount');

        const applyYearFilter = function () {
            const selectedYear = selector.value;
            let visible = 0;

            cards.forEach(function (card) {
                const cardYear = card.getAttribute('data-year') || '';
                const show = !selectedYear || cardYear === selectedYear;
                card.style.display = show ? '' : 'none';
                if (show) {
                    visible++;
                }
            });

            if (countNode) {
                countNode.textContent = visible;
            }
        };

        selector.addEventListener('change', applyYearFilter);
        applyYearFilter();
    });
</script>

<style>
    .seg-notify412-bell-wrap{
        display:inline-flex;
        align-items:center;
        margin-left:.1rem;
    }
    .seg-notify412-bell{
        width:46px;
        height:46px;
        position:relative;
        border-radius:14px !important;
        border:1px solid rgba(255,255,255,.35);
        background:linear-gradient(135deg, rgba(220,53,69,.95), rgba(179,35,54,.95)) !important;
        box-shadow:0 10px 18px rgba(86, 12, 21, .35);
    }
    .seg-notify412-badge{
        top:-8px;
        right:-8px;
        font-size:.72rem;
        min-width:24px;
        border:1px solid #fff;
        box-shadow:0 4px 10px rgba(0,0,0,.2);
    }
    .seg-notify412-modal{
        border:none;
        border-radius:20px;
        overflow:hidden;
        background:linear-gradient(180deg, #ffffff, #f8fcfd);
        box-shadow:0 22px 38px rgba(18, 57, 73, .22);
    }
    .seg-notify412-modal .modal-header{
        background:linear-gradient(135deg, #39335f, #4f3f77);
        color:#fff;
        padding:1rem 1.15rem;
    }
    .seg-notify412-modal__brand{
        display:flex;
        align-items:center;
        gap:.75rem;
    }
    .seg-notify412-modal__logo{
        width:38px;
        height:38px;
        object-fit:contain;
        border-radius:10px;
        background:rgba(255,255,255,.2);
        padding:4px;
    }
    .seg-notify412-modal__subtitle{
        color:rgba(255,255,255,.9);
    }
    .seg-notify412-summary{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:.8rem;
    }
    .seg-notify412-summary__item{
        border:1px solid #dbe2f0;
        border-radius:14px;
        padding:.75rem .85rem;
        background:#fff;
    }
    .seg-notify412-summary__label{
        display:block;
        font-size:.76rem;
        color:#6e7392;
        text-transform:uppercase;
        letter-spacing:.05em;
        font-weight:700;
    }
    .seg-notify412-summary__value{
        color:#312f59;
        font-weight:800;
        font-size:.95rem;
    }
    .seg-notify412-summary__select{
        border-radius:10px;
        border:1px solid #d8d9ee;
        font-weight:700;
        color:#353363;
        margin-top:.25rem;
    }
    .seg-notify412-results{
        color:#69688b;
        font-size:.9rem;
        font-weight:600;
    }
    .seg-notify-card412{
        border:1px solid #e4e4f3;
        border-radius:16px;
        background:#fff;
        padding:.95rem;
        margin-bottom:.8rem;
        box-shadow:0 8px 18px rgba(44, 34, 91, .08);
    }
    .seg-notify-card412__head{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:.7rem;
        margin-bottom:.75rem;
    }
    .seg-notify-card412__title-wrap{
        display:flex;
        align-items:flex-start;
        gap:.6rem;
    }
    .seg-notify-card412__icon{
        width:34px;
        height:34px;
        border-radius:10px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        background:#5548a5;
        flex-shrink:0;
    }
    .seg-notify-card412__title{
        font-size:1rem;
        font-weight:800;
        color:#2f2d5a;
    }
    .seg-notify-card412__subtitle{
        color:#6c7090;
    }
    .seg-notify-card412__badge{
        border-radius:999px;
        padding:.28rem .65rem;
        font-size:.72rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.05em;
    }
    .seg-notify-card412__grid{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:.65rem;
    }
    .seg-notify-card412__label{
        display:block;
        font-size:.73rem;
        color:#777da1;
        text-transform:uppercase;
        letter-spacing:.04em;
        font-weight:700;
    }
    .seg-notify-card412__value{
        display:block;
        color:#33325e;
        font-size:.9rem;
        line-height:1.25;
    }
    .seg-notify-card412__timeline{
        margin-top:.7rem;
        padding:.55rem .7rem;
        border-radius:12px;
        background:#f4f3fb;
        color:#47447a;
        font-weight:600;
    }
    .seg-notify-card412__actions{
        margin-top:.75rem;
        display:flex;
        justify-content:flex-end;
    }
    .seg-notify-card412__btn{
        border-radius:999px;
        font-weight:700;
        background:linear-gradient(135deg, #5548a5, #6a59bf);
        border:none;
        color:#fff;
        box-shadow:0 8px 16px rgba(84, 72, 165, .24);
    }
    .seg-notify-card412__btn:hover{
        color:#fff;
        filter:brightness(.98);
    }
    .seg-notify-card412.is-danger{
        border-color:#f2d3d8;
        background:linear-gradient(180deg, #fff9fa, #ffffff);
    }
    .seg-notify-card412.is-danger .seg-notify-card412__icon{
        background:#d94b5f;
    }
    .seg-notify-card412.is-danger .seg-notify-card412__badge{
        color:#a53347;
        background:#ffe3e8;
        border:1px solid #f8c9d2;
    }
    .seg-notify-card412.is-warning{
        border-color:#f2e2bd;
        background:linear-gradient(180deg, #fffdf7, #ffffff);
    }
    .seg-notify-card412.is-warning .seg-notify-card412__icon{
        background:#d09a1f;
    }
    .seg-notify-card412.is-warning .seg-notify-card412__badge{
        color:#8a6411;
        background:#fff3d5;
        border:1px solid #f7e2ab;
    }
    .seg-notify-card412.is-success{
        border-color:#cde9d7;
        background:linear-gradient(180deg, #f8fffb, #ffffff);
    }
    .seg-notify-card412.is-success .seg-notify-card412__icon{
        background:#1c8f63;
    }
    .seg-notify-card412.is-success .seg-notify-card412__badge{
        color:#156847;
        background:#dbf4e6;
        border:1px solid #bfe8d1;
    }
    .seg-notify412-toast{
        position:fixed;
        right:22px;
        bottom:24px;
        z-index:2050;
        display:flex;
        align-items:center;
        gap:.75rem;
        max-width:430px;
        padding:.85rem .95rem;
        border-radius:16px;
        background:linear-gradient(135deg, #2f2f4d, #41335f);
        color:#fff;
        box-shadow:0 20px 34px rgba(36, 25, 63, .35);
        animation:segNotifyPulse412 2.2s ease-in-out infinite;
    }
    .seg-notify412-toast__icon{
        width:42px;
        height:42px;
        border-radius:12px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:rgba(255,255,255,.16);
        font-size:1.05rem;
        flex-shrink:0;
    }
    .seg-notify412-toast__content{
        display:flex;
        flex-direction:column;
        line-height:1.25;
    }
    .seg-notify412-toast__content small{
        opacity:.88;
    }
    .seg-notify412-toast__btn{
        border-radius:999px;
        padding:.3rem .8rem;
        font-weight:700;
        flex-shrink:0;
    }
    @keyframes segNotifyPulse412{
        0% { transform: translateY(0); box-shadow:0 20px 34px rgba(36, 25, 63, .35); }
        50% { transform: translateY(-2px); box-shadow:0 26px 38px rgba(36, 25, 63, .46); }
        100% { transform: translateY(0); box-shadow:0 20px 34px rgba(36, 25, 63, .35); }
    }
    @media (max-width: 991px){
        .seg-notify412-summary{
            grid-template-columns:1fr;
        }
        .seg-notify-card412__grid{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 575px){
        .seg-notify-card412__grid{
            grid-template-columns:1fr;
        }
    }
</style>

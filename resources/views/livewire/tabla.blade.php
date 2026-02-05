{{-- livewire/tabla.blade.php --}}

{{-- Barra superior: Export + Buscador --}}
<div class="pai-card mb-3">
    <div class="pai-card__head">
        <div class="pai-card__head-left">
            <div class="pai-card__title">
                <i class="fas fa-table mr-2"></i> Registros
            </div>
            <div class="pai-card__subtitle">Resultados del cargue y consultas</div>
        </div>

        <div class="pai-card__head-right">
            {{-- ✅ Botón pastel (suave) --}}
            <a href="#"
               class="btn btn-pai btn-pai-pastel-success"
               data-toggle="modal"
               data-target="#exportModal">
                <i class="fas fa-file-excel mr-2"></i> Exportar a Excel
            </a>

            {{-- Buscador --}}
            <div class="pai-search">
                <i class="fas fa-search pai-search__icon"></i>
                <input type="text"
                       id="search"
                       class="pai-search__input"
                       placeholder="Buscar por Número de Identificación"
                       autocomplete="off">

                <div id="search-results" class="pai-search__results" style="display:none;">
                    <div id="loading-spinner" class="pai-search__loading" style="display:none;">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Cargando...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Fecha Report --}}
@include('livewire.modal_fecha_report')

{{-- Tabla --}}
<div class="pai-card">
    <div class="pai-table-wrap">

        @if(auth()->user()->usertype == 1)

            <table class="pai-table" id="sivigila">
                <thead>
                    <tr>
                        <th style="width:90px;">ID</th>
                        <th style="width:240px;">Documento</th>
                        <th>Paciente</th>
                        <th style="width:170px;">Lote</th>
                        <th style="width:240px;" class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sivigilas as $student2)
                        @php
                            $fullName = trim(($student2->primer_nombre ?? '').' '.($student2->segundo_nombre ?? '').' '.($student2->primer_apellido ?? '').' '.($student2->segundo_apellido ?? ''));
                            $doc = $student2->numero_identificacion ?? '';
                            $initials = strtoupper(substr($student2->primer_nombre ?? 'P',0,1).substr($student2->primer_apellido ?? 'A',0,1));
                        @endphp
                        <tr>
                            <td>
                                <span class="pai-badge-id">#{{ $student2->id }}</span>
                            </td>

                            <td>
                                <a href="#"
                                   class="numero-identificacion pai-doclink"
                                   data-id="{{ $student2->id }}"
                                   data-carnet="{{ $student2->numero_carnet ?? '' }}">
                                    <span class="pai-dot"></span>
                                    <span class="pai-doclink__text">{{ $doc }}</span>
                                </a>
                                <div class="pai-muted">Clic para ver vacunas</div>
                            </td>

                            <td>
                                <div class="pai-person">
                                    <div class="pai-avatar">{{ $initials }}</div>
                                    <div class="pai-person__meta">
                                        <div class="pai-person__name">{{ $fullName }}</div>
                                        <div class="pai-muted">Usuario: {{ auth()->user()->name ?? '' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="pai-pill">{{ $student2->batch_verifications_id }}</span>
                            </td>

                            <td class="text-right">
                                <div class="pai-actions">
                                    <a href="#" class="btn btn-pai btn-pai-pastel-warning btn-sm">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>

                                    <form action="{{ route('batch_verifications.destroy', $student2->batch_verifications_id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-pai btn-pai-pastel-danger btn-sm"
                                                onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?')">
                                            <i class="fas fa-trash mr-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pai-pagination">
                {{ $sivigilas->links() }}
            </div>

        @else

            <table class="pai-table" id="sivigila">
                <thead>
                    <tr>
                        <th style="width:90px;">ID</th>
                        <th style="width:240px;">Documento</th>
                        <th>Paciente</th>
                        <th style="width:170px;">Carnet</th>
                        <th style="width:240px;" class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sivigilas_usernormal as $student21)
                        @php
                            $correoEnviado = App\Models\CorreoEnviado::where('user_id', auth()->id())
                                ->where('patient_id', $student21->id)
                                ->exists();

                            $fullName = trim(($student21->primer_nombre ?? '').' '.($student21->segundo_nombre ?? '').' '.($student21->primer_apellido ?? '').' '.($student21->segundo_apellido ?? ''));
                            $doc = $student21->numero_identificacion ?? '';
                            $carnet = $student21->numero_carnet ?? '';
                            $initials = strtoupper(substr($student21->primer_nombre ?? 'P',0,1).substr($student21->primer_apellido ?? 'A',0,1));
                        @endphp

                        <tr>
                            <td>
                                <span class="pai-badge-id">#{{ $student21->id }}</span>
                            </td>

                            <td>
                                <a href="#"
                                   class="numero-identificacion pai-doclink"
                                   data-id="{{ $student21->id }}"
                                   data-carnet="{{ $student21->numero_carnet }}">
                                    <span class="pai-dot"></span>
                                    <span class="pai-doclink__text">{{ $doc }}</span>
                                </a>
                                <div class="pai-muted">Clic para ver vacunas</div>
                            </td>

                            <td>
                                <div class="pai-person">
                                    <div class="pai-avatar">{{ $initials }}</div>
                                    <div class="pai-person__meta">
                                        <div class="pai-person__name">{{ $fullName }}</div>
                                        <div class="pai-muted">Estado: {{ $correoEnviado ? 'Solicitud enviada' : 'Sin solicitud' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="pai-pill">{{ $carnet }}</span>
                            </td>

                            <td class="text-right">
                                <div class="pai-actions">
                                    @if($correoEnviado)
                                        <button class="btn btn-pai btn-pai-pastel-neutral btn-sm" disabled>
                                            <i class="fas fa-envelope mr-1"></i> Correo enviado
                                        </button>
                                    @else
                                        <a href="#"
                                           class="btn btn-pai btn-pai-pastel-primary btn-sm send-email"
                                           data-toggle="modal"
                                           data-target="#emailModal"
                                           data-id="{{ $student21->id }}"
                                           data-name="{{ $fullName }}">
                                            <i class="fas fa-envelope mr-1"></i> Solicitud
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pai-pagination">
                {{ $sivigilas_usernormal->links() }}
            </div>

        @endif

    </div>
</div>

<style>
/* ===== Paleta pastel + legibilidad ===== */
:root{
  --pai-text:#0f172a;
  --pai-muted:#475569;
  --pai-border:rgba(15,23,42,.10);
  --pai-shadow:0 14px 30px rgba(2,6,23,.08);

  /* pasteles */
  --p-blue-bg:#eaf2ff;
  --p-blue-bd:#cfe0ff;
  --p-blue-tx:#1e40af;

  --p-green-bg:#ecfdf3;
  --p-green-bd:#c8f1d9;
  --p-green-tx:#166534;

  --p-amber-bg:#fff7e6;
  --p-amber-bd:#ffe2b8;
  --p-amber-tx:#92400e;

  --p-red-bg:#fff1f2;
  --p-red-bd:#ffd3d8;
  --p-red-tx:#9f1239;

  --p-gray-bg:#f1f5f9;
  --p-gray-bd:#dbe3ee;
  --p-gray-tx:#334155;
}

/* Card */
.pai-card{
  background:#fff;
  border-radius:18px;
  border:1px solid rgba(15,23,42,.08);
  box-shadow:var(--pai-shadow);
  overflow:hidden;
}

/* Header pastel suave */
.pai-card__head{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:16px;
  padding:16px 18px;
  border-bottom:1px solid rgba(2,6,23,.08);
  background: linear-gradient(180deg, rgba(234,242,255,.75), #ffffff);
}
.pai-card__head-left{display:flex; flex-direction:column;}
.pai-card__title{
  font-weight:900;
  color:var(--pai-text);
  font-size:1.15rem;
  display:flex;
  align-items:center;
}
.pai-card__subtitle{
  color:var(--pai-muted);
  font-size:.98rem;
  margin-top:4px;
  line-height:1.35;
}
.pai-card__head-right{
  display:flex;
  gap:12px;
  flex-wrap:wrap;
  align-items:center;
  justify-content:flex-end;
}

/* ===== Botones pastel ===== */
.btn-pai{
  border-radius:12px;
  padding:10px 14px;
  font-weight:900;
  font-size:.97rem;
  border:1px solid var(--pai-border);
  background:#fff;
  color:var(--pai-text);
  transition:transform .12s ease, box-shadow .12s ease, filter .12s ease;
  box-shadow:0 10px 22px rgba(2,6,23,.07);
}
.btn-pai:hover{
  transform:translateY(-1px);
  box-shadow:0 14px 28px rgba(2,6,23,.10);
  filter:brightness(1.01);
}
.btn-sm.btn-pai{padding:9px 12px; border-radius:12px; font-size:.95rem;}

/* Pastel variants */
.btn-pai-pastel-success{
  background:var(--p-green-bg);
  border-color:var(--p-green-bd);
  color:var(--p-green-tx);
  box-shadow:none;
}
.btn-pai-pastel-success:hover{box-shadow:0 12px 24px rgba(2,6,23,.10);}

.btn-pai-pastel-primary{
  background:var(--p-blue-bg);
  border-color:var(--p-blue-bd);
  color:var(--p-blue-tx);
  box-shadow:none;
}
.btn-pai-pastel-warning{
  background:var(--p-amber-bg);
  border-color:var(--p-amber-bd);
  color:var(--p-amber-tx);
  box-shadow:none;
}
.btn-pai-pastel-danger{
  background:var(--p-red-bg);
  border-color:var(--p-red-bd);
  color:var(--p-red-tx);
  box-shadow:none;
}
.btn-pai-pastel-neutral{
  background:var(--p-gray-bg);
  border-color:var(--p-gray-bd);
  color:var(--p-gray-tx);
  box-shadow:none;
}

/* ===== Search pastel ===== */
.pai-search{position:relative; width:min(540px, 94vw);}
.pai-search__input{
  width:100%;
  border-radius:14px;
  padding:13px 48px 13px 46px;
  border:1px solid rgba(2,6,23,.14);
  background:#fff;
  box-shadow:0 10px 22px rgba(2,6,23,.06);
  outline:none;
  font-size:1rem;
  color:var(--pai-text);
}
.pai-search__input:focus{
  border-color:rgba(30,64,175,.35);
  box-shadow:0 14px 28px rgba(30,64,175,.12);
}
.pai-search__icon{
  position:absolute;
  left:15px;
  top:50%;
  transform:translateY(-50%);
  color:#64748b;
  font-size:1.05rem;
}
.pai-search__results{
  position:absolute;
  top:112%;
  left:0;
  right:0;
  max-height:320px;
  overflow:auto;
  border-radius:14px;
  border:1px solid rgba(2,6,23,.10);
  background:#fff;
  box-shadow:0 18px 35px rgba(2,6,23,.14);
  z-index:1200;
}
.pai-search__loading{padding:12px; text-align:center; color:#0f172a; font-weight:800;}

/* ===== Tabla (legible) ===== */
.pai-table-wrap{width:100%; overflow:auto;}
.pai-table{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  min-width:940px;
  font-size:1rem;
  color:var(--pai-text);
}
.pai-table thead th{
  position:sticky;
  top:0;
  z-index:2;
  background: linear-gradient(180deg, rgba(234,242,255,.95), rgba(255,255,255,1));
  color:#0b1220;
  font-weight:900;
  font-size:.95rem;
  padding:16px 14px;
  border-bottom:1px solid rgba(2,6,23,.12);
  text-transform:none;
}
.pai-table tbody td{
  padding:16px 14px;
  border-bottom:1px solid rgba(2,6,23,.08);
  vertical-align:middle;
  background:#fff;
}
.pai-table tbody tr:hover td{
  background:rgba(234,242,255,.55);
}

/* ID badge */
.pai-badge-id{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:7px 12px;
  border-radius:999px;
  background:rgba(15,23,42,.05);
  font-weight:900;
  font-size:.95rem;
  color:var(--pai-text);
}

/* Doc link */
.pai-doclink{
  display:inline-flex;
  align-items:center;
  gap:10px;
  font-weight:900;
  color:var(--p-blue-tx);
  text-decoration:none;
  font-size:1.02rem;
}
.pai-doclink:hover{text-decoration:underline;}
.pai-dot{
  width:10px;
  height:10px;
  border-radius:999px;
  background:#34d399;
  box-shadow:0 0 0 4px rgba(52,211,153,.18);
}

/* Person */
.pai-person{display:flex; gap:12px; align-items:center;}
.pai-avatar{
  width:46px; height:46px;
  border-radius:16px;
  display:flex; align-items:center; justify-content:center;
  background:rgba(234,242,255,.9);
  border:1px solid rgba(207,224,255,.9);
  font-weight:900;
  color:var(--pai-text);
  font-size:1rem;
}
.pai-person__name{
  font-weight:900;
  color:var(--pai-text);
  line-height:1.25;
  font-size:1.02rem;
}
.pai-muted{
  color:var(--pai-muted);
  font-size:.95rem;
  margin-top:4px;
  line-height:1.35;
}

/* pill */
.pai-pill{
  display:inline-flex;
  align-items:center;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid rgba(2,6,23,.10);
  background:#fff;
  font-weight:900;
  font-size:.95rem;
  color:var(--pai-text);
}

/* actions */
.pai-actions{
  display:inline-flex;
  gap:10px;
  align-items:center;
  justify-content:flex-end;
  flex-wrap:wrap;
}

/* pagination */
.pai-pagination{
  padding:14px 18px;
  border-top:1px solid rgba(2,6,23,.08);
  background:rgba(241,245,249,.7);
}

/* responsive */
@media(max-width: 992px){
  .pai-card__head{flex-direction:column; align-items:stretch;}
  .pai-card__head-right{justify-content:space-between;}
  .pai-table{min-width:820px;}
}
</style>

@extends('adminlte::page')

@section('title', 'PAI')

@section('content_header')
<div class="pai-topbar">
    <div class="pai-topbar__left">
        <div class="pai-brand">
            <div class="pai-brand__logo">PAI</div>
            <div class="pai-brand__text">
                <div class="pai-brand__title">Cargue Registro Diario</div>
                <div class="pai-brand__subtitle">Importación masiva de afiliados y vacunas (Excel .xlsx / .xls)</div>
            </div>
        </div>
    </div>

    <div class="pai-topbar__right">
        <a href="{{ route('download.excel') }}" class="btn btn-pai btn-pai-secondary">
            <i class="fas fa-file-download mr-2"></i> Descargar formato
        </a>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">

    {{-- ✅ Mensajes dentro del DOM (si usas include) --}}
    <div id="mensajes-container" class="mb-3">
        @include('livewire.mensajes')
    </div>

    <div class="row">
        {{-- ===== Panel Importación ===== --}}
        <div class="col-lg-5 col-xl-4">
            <div class="pai-card">
                <div class="pai-card__head">
                    <div>
                        <div class="pai-card__title">Importar archivo</div>
                        <div class="pai-card__hint">Arrastra y suelta tu Excel o haz clic para seleccionarlo.</div>
                    </div>
                    <div class="pai-card__icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                </div>

                <form action="#" method="POST" enctype="multipart/form-data" id="file-upload-form">
                    @csrf

                    <div id="drag-drop-area" class="pai-dropzone">
                        <input type="file" name="file" id="file_input" class="pai-file" accept=".xlsx,.xls">

                        <div class="pai-dropzone__inner">
                            <div class="pai-dropzone__logo">
                                <i class="far fa-file-excel"></i>
                            </div>

                            <div class="pai-dropzone__text">
                                <div class="pai-dropzone__title">Suelta tu archivo aquí</div>
                                <div class="pai-dropzone__sub">o haz clic para buscar en tu equipo</div>
                            </div>

                            <div class="pai-filemeta" id="filemeta" style="display:none;">
                                <div class="pai-filemeta__name" id="filename">archivo.xlsx</div>
                                <div class="pai-filemeta__sub" id="filesub">Listo para importar</div>
                            </div>

                            <div class="pai-dropzone__footer">
                                <span class="pai-pill"><i class="fas fa-shield-alt mr-1"></i> Carga segura</span>
                                <span class="pai-pill"><i class="fas fa-file-excel mr-1"></i> .xlsx / .xls</span>
                                <span class="pai-pill"><i class="fas fa-clock mr-1"></i> Proceso asistido</span>
                            </div>
                        </div>
                    </div>

                    <div id="date-warning" class="alert alert-warning mt-3 text-center" style="display:none;">
                        El formulario no está disponible fuera del rango de fechas permitido.
                    </div>

                    <button type="submit" class="btn btn-pai btn-pai-primary btn-block mt-3" id="submit-button">
                        <i class="fas fa-play mr-2"></i> Iniciar importación
                    </button>

                    <div class="pai-minihelp mt-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        El sistema validará el archivo antes de guardar información.
                    </div>
                </form>
            </div>

            {{-- ✅ MODAL LOADING (VENTANA CONSOLA BLANCA + PASTELES SUAVES / SIN AZUL) --}}
            <div class="modal fade pai-console-modal" id="loadingModal" tabindex="-1" role="dialog"
                aria-labelledby="loadingModalLabel" aria-hidden="true"
                data-backdrop="static" data-keyboard="false">

                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content pai-console__content">

                        {{-- fondo pastel MUY leve --}}
                        <div class="pai-console__bg" aria-hidden="true">
                            <div class="pai-console__orb pai-console__orb--a"></div>
                            <div class="pai-console__orb pai-console__orb--b"></div>
                            <div class="pai-console__grid"></div>
                            <div class="pai-console__noise"></div>
                        </div>

                        {{-- Header tipo “ventana” --}}
                        <div class="modal-header pai-console__header">
                            <div class="pai-console__window">
                                <span class="pai-console__dot pai-console__dot--red"></span>
                                <span class="pai-console__dot pai-console__dot--yellow"></span>
                                <span class="pai-console__dot pai-console__dot--green"></span>
                            </div>

                            <div class="pai-console__titlewrap">
                                <div class="pai-console__title" id="loadingModalLabel">
                                    PAI • Import Engine
                                </div>
                                <div class="pai-console__subtitle">
                                    <span class="pai-console__k">Paso:</span>
                                    <span id="import_step">preparando…</span>
                                </div>
                            </div>

                            <button type="button" class="pai-console__x" data-dismiss="modal" aria-label="Close"
                                id="btnCloseX" style="display:none;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body pai-console__body">

                            <div class="pai-console__layout">

                                {{-- Columna izquierda: loader tipo “scanner” --}}
                                <div class="pai-console__left">
                                    <div class="pai-console__loader">
                                        {{-- ✅ IMPORTANTE: tu JS usa este id para estados --}}
                                        <div class="pai-console__ring" id="import_ring"></div>

                                        <div class="pai-console__core">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>

                                        <div class="pai-console__scan" aria-hidden="true"></div>
                                    </div>

                                    <div class="pai-console__chips">
                                        <span class="pai-console__chip"><i class="fas fa-shield-alt"></i> Seguro</span>
                                        <span class="pai-console__chip"><i class="fas fa-bolt"></i> Worker</span>
                                        <span class="pai-console__chip"><i class="fas fa-database"></i> Validación</span>
                                    </div>
                                </div>

                                {{-- Columna derecha: “consola” de información --}}
                                <div class="pai-console__right">

                                    <div class="pai-console__panel">
                                        <div class="pai-console__panelhead">
                                            <div class="pai-console__paneltitle">
                                                <i class="fas fa-terminal"></i> Consola de proceso
                                            </div>
                                            <div class="pai-console__panelmeta">
                                                <span class="pai-console__pill">JOB</span>
                                                <span class="pai-console__pill">LIVE</span>
                                            </div>
                                        </div>

                                        <div class="pai-console__terminal" role="status" aria-live="polite">
                                            <div class="pai-console__line">
                                                <span class="pai-console__prompt">pai@import</span><span class="pai-console__sep">:</span><span class="pai-console__path">~</span><span class="pai-console__sep">$</span>
                                                <span class="pai-console__msg" id="import_msg">Subiendo archivo… (no cierres esta ventana)</span>
                                            </div>

                                            <div class="pai-console__line pai-console__muted">
                                                <span class="pai-console__tag">TIP</span>
                                                Si el archivo es grande, el porcentaje puede subir por “saltos” mientras valida.
                                            </div>
                                            <div class="pai-console__line pai-console__muted">
                                                <span class="pai-console__tag">TIP</span>
                                                Recuerda no llenar ningún espacio vacío con cero (0).
                                            </div>
                                        </div>

                                        {{-- Progreso --}}
                                        <div class="pai-console__progress">
                                            <div class="pai-console__progressrow">
                                                <div class="pai-console__progresslabel">Progreso</div>
                                                <div class="pai-console__progresspct"><span id="import_pct">0%</span></div>
                                            </div>

                                            <div class="pai-console__bar">
                                                <div class="pai-console__fill" id="import_bar" style="width:0%;"></div>
                                                <div class="pai-console__shine" aria-hidden="true"></div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Errores (mantengo IDs para tu JS) --}}
                                    <div id="import_errors_box" class="pai-console__errors" style="display:none;">
                                        <div class="pai-console__errorshead">
                                            <div class="pai-console__errorstitle">
                                                <i class="fas fa-triangle-exclamation"></i>
                                                Errores encontrados
                                            </div>
                                            <div class="pai-console__errorstag">VALIDATION</div>
                                        </div>

                                        <div class="pai-console__errorsbody">
                                            <ul id="import_errors_list" class="pai-console__errorslist"></ul>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="modal-footer pai-console__footer">
                            <div class="pai-console__footleft">
                                <span class="pai-console__statusdot"></span>
                                <span class="pai-console__footnote">No cierres mientras esté procesando.</span>
                            </div>

                            <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                id="btnCloseImport" style="display:none;">
                                Cerrar
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ===== Tabla + Modales ===== --}}
        <div class="col-lg-7 col-xl-8">
            @include('livewire.tabla')
            @include('livewire.modal_tabla')
        </div>
    </div>

    {{-- Modal correo --}}
    <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailModalLabel">Enviar Correo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="patientId">
                        <input type="hidden" id="patientName">

                        <div class="form-group">
                            <label for="emailSubject">Asunto</label>
                            <input type="text" class="form-control" id="emailSubject" placeholder="Asunto">
                        </div>
                        <div class="form-group">
                            <label for="emailMessage">Mensaje</label>
                            <textarea class="form-control" id="emailMessage" rows="3"></textarea>
                        </div>
                        <input type="hidden" id="emailPatientName">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="sendEmailButton">Enviar Correo</button>
                </div>
            </div>
        </div>
    </div>

</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.css" rel="stylesheet">

@include('livewire.css')

<style>
/* ====== Topbar ====== */
.pai-topbar{display:flex; justify-content:space-between; align-items:center; padding:18px 10px; margin-bottom:10px; border-bottom:1px solid rgba(0,0,0,.08);}
.pai-brand{display:flex; align-items:center; gap:14px;}
.pai-brand__logo{background:linear-gradient(135deg,#0ea5e9,#2563eb); color:#fff; font-weight:900; border-radius:14px; padding:10px 12px; box-shadow:0 10px 25px rgba(37,99,235,.25); letter-spacing:.5px;}
.pai-brand__title{font-weight:900; color:#0f172a; font-size:1.25rem; line-height:1.1;}
.pai-brand__subtitle{color:#64748b; font-size:.92rem; margin-top:2px;}

/* ====== Buttons ====== */
.btn-pai{border-radius:12px; padding:10px 14px; font-weight:800; transition:transform .15s ease, box-shadow .15s ease; box-shadow:0 12px 25px rgba(0,0,0,.08);}
.btn-pai:hover{transform:translateY(-1px); box-shadow:0 18px 35px rgba(0,0,0,.10);}
.btn-pai-primary{background:linear-gradient(135deg,#2563eb,#0ea5e9); border:none; color:#fff;}
.btn-pai-secondary{background:#fff; border:1px solid rgba(0,0,0,.08); color:#0f172a;}

/* ====== Card ====== */
.pai-card{background:#fff; border-radius:18px; padding:18px; box-shadow:0 16px 40px rgba(2,6,23,.08); border:1px solid rgba(15,23,42,.06);}
.pai-card__head{display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px;}
.pai-card__title{font-weight:900; color:#0f172a; font-size:1.1rem;}
.pai-card__hint{color:#64748b; font-size:.92rem;}
.pai-card__icon{width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:rgba(37,99,235,.10); color:#2563eb; font-size:20px;}

/* ====== Dropzone ====== */
.pai-dropzone{position:relative; border-radius:18px; border:2px dashed rgba(37,99,235,.35); background:linear-gradient(180deg, rgba(37,99,235,.06), rgba(14,165,233,.04)); padding:20px; cursor:pointer; overflow:hidden;}
.pai-dropzone.drag-over{border-color:#2563eb; box-shadow:0 0 0 4px rgba(37,99,235,.15); transform:translateY(-1px);}
.pai-file{position:absolute; inset:0; opacity:0; cursor:pointer;}
.pai-dropzone__inner{display:flex; flex-direction:column; align-items:center; gap:10px; text-align:center;}
.pai-dropzone__logo{width:56px; height:56px; border-radius:18px; display:flex; align-items:center; justify-content:center; background:rgba(16,185,129,.12); color:#10b981; font-size:26px;}
.pai-dropzone__title{font-weight:900; color:#0f172a;}
.pai-dropzone__sub{color:#64748b; font-size:.95rem;}
.pai-dropzone__footer{display:flex; gap:8px; margin-top:6px; flex-wrap:wrap; justify-content:center;}
.pai-pill{font-size:.82rem; padding:6px 10px; border-radius:999px; border:1px solid rgba(0,0,0,.07); color:#334155; background:#fff;}
.pai-filemeta{width:100%; border-radius:14px; padding:10px 12px; border:1px solid rgba(0,0,0,.08); background:#fff; text-align:left; max-width:520px;}
.pai-filemeta__name{font-weight:900; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
.pai-filemeta__sub{color:#64748b; font-size:.9rem;}
.pai-minihelp{color:#64748b; font-size:.9rem; display:flex; align-items:center;}

/* =========================================================
   ✅ MODAL “VENTANA CONSOLA” BLANCA + PASTELES (SIN AZUL)
   ========================================================= */
.pai-console-modal .modal-dialog{max-width:920px;}

.pai-console__content{
  border: 1px solid rgba(15,23,42,.10);
  border-radius: 22px;
  overflow: hidden;
  background: rgba(255,255,255,.86);
  backdrop-filter: blur(16px) saturate(1.1);
  -webkit-backdrop-filter: blur(16px) saturate(1.1);
  box-shadow: 0 30px 90px rgba(2,6,23,.18);
  position: relative;
}

/* Backdrop suave */
.modal-backdrop.show{opacity:.50;}

/* Fondo pastel muy leve */
.pai-console__bg{position:absolute; inset:0; pointer-events:none;}
.pai-console__orb{
  position:absolute; width:560px; height:560px; border-radius:50%;
  filter: blur(42px);
  opacity: .60;
}
.pai-console__orb--a{
  left:-300px; top:-340px;
  background: radial-gradient(circle at 35% 35%, rgba(196,181,253,.55), transparent 62%); /* lilac */
}
.pai-console__orb--b{
  right:-320px; bottom:-360px;
  background: radial-gradient(circle at 45% 45%, rgba(165,243,252,.50), transparent 62%); /* cyan */
}
.pai-console__grid{
  position:absolute; inset:-2px;
  background:
    linear-gradient(rgba(15,23,42,.045) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15,23,42,.045) 1px, transparent 1px);
  background-size: 38px 38px;
  opacity:.42;
  mask-image: radial-gradient(circle at 50% 18%, rgba(0,0,0,1), rgba(0,0,0,.25) 55%, transparent 78%);
}
.pai-console__noise{
  position:absolute; inset:0;
  background-image: radial-gradient(rgba(15,23,42,.035) 1px, transparent 1px);
  background-size: 6px 6px;
  opacity:.18;
  mix-blend-mode: multiply;
}

/* Header ventana */
.pai-console__header{
  border:0;
  padding:12px 14px;
  display:flex;
  align-items:center;
  gap:12px;
  background: rgba(255,255,255,.80);
  border-bottom: 1px solid rgba(15,23,42,.08);
  position: relative;
  z-index: 2;
}
.pai-console__window{display:flex; gap:7px; align-items:center;}
.pai-console__dot{width:11px; height:11px; border-radius:999px; border:1px solid rgba(15,23,42,.10);}
.pai-console__dot--red{background: rgba(253,164,175,.85);}
.pai-console__dot--yellow{background: rgba(253,230,138,.85);}
.pai-console__dot--green{background: rgba(134,239,172,.80);}

.pai-console__titlewrap{flex:1;}
.pai-console__title{
  font-weight: 950;
  color:#0f172a;
  letter-spacing: .2px;
  font-size: .98rem;
}
.pai-console__subtitle{
  margin-top:2px;
  font-size: .86rem;
  color: rgba(100,116,139,.95);
  font-weight: 800;
}
.pai-console__k{color: rgba(15,23,42,.78); font-weight: 900;}

.pai-console__x{
  border: 1px solid rgba(15,23,42,.10);
  background: rgba(255,255,255,.70);
  color:#0f172a;
  width:38px; height:38px;
  border-radius: 12px;
  font-size: 22px;
  line-height: 1;
  display:flex; align-items:center; justify-content:center;
}
.pai-console__x:hover{background: rgba(255,255,255,.90);}

/* Body */
.pai-console__body{padding:14px 14px 16px 14px; position:relative; z-index:2;}
.pai-console__layout{
  display:flex;
  gap:14px;
  align-items:stretch;
  flex-wrap: wrap;
}

/* Left */
.pai-console__left{
  flex: 0 0 220px;
  display:flex;
  flex-direction:column;
  gap:12px;
}
@media (max-width: 768px){
  .pai-console__left{flex: 1 1 100%;}
}

.pai-console__loader{
  position:relative;
  width: 98px;
  height: 98px;
  margin: 4px auto 0 auto;
}
.pai-console__ring{
  position:absolute; inset:0;
  border-radius:50%;
  border: 3px solid rgba(15,23,42,.10);
  border-top-color: rgba(196,181,253,.95);  /* lilac */
  border-right-color: rgba(165,243,252,.95); /* cyan */
  animation: paiSpin .95s linear infinite;
  transition: border-color .25s ease, filter .25s ease;
}
@keyframes paiSpin{to{transform:rotate(360deg);}}

/* estados ring (tu JS agrega is-success / is-error) */
.pai-console__ring.is-success{
  border-top-color: rgba(134,239,172,1); /* mint */
  border-right-color: rgba(196,181,253,.95);
  filter: drop-shadow(0 0 10px rgba(134,239,172,.30)) drop-shadow(0 0 18px rgba(196,181,253,.18));
}
.pai-console__ring.is-error{
  border-top-color: rgba(253,164,175,1); /* rose */
  border-right-color: rgba(251,113,133,.90);
  filter: drop-shadow(0 0 10px rgba(253,164,175,.30)) drop-shadow(0 0 18px rgba(251,113,133,.16));
}

.pai-console__core{
  position:absolute; inset:12px;
  border-radius: 22px;
  background: rgba(255,255,255,.78);
  border: 1px solid rgba(15,23,42,.10);
  display:flex; align-items:center; justify-content:center;
  box-shadow: 0 16px 38px rgba(2,6,23,.08);
}
.pai-console__core i{
  font-size: 24px;
  color: rgba(15,23,42,.70);
}

/* scan suave */
.pai-console__scan{
  position:absolute; inset:10px;
  border-radius: 22px;
  background: linear-gradient(180deg, transparent, rgba(196,181,253,.18), transparent);
  height: 44px;
  top: -44px;
  animation: paiScan 2.8s linear infinite;
  opacity:.60;
  pointer-events:none;
}
@keyframes paiScan{
  0%{transform:translateY(0);}
  100%{transform:translateY(120px);}
}

.pai-console__chips{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  justify-content:center;
}
.pai-console__chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:7px 10px;
  border-radius: 999px;
  background: rgba(255,255,255,.72);
  border: 1px solid rgba(15,23,42,.10);
  color: rgba(15,23,42,.78);
  font-weight: 850;
  font-size: .82rem;
  box-shadow: 0 10px 24px rgba(2,6,23,.06);
}
.pai-console__chip i{color: rgba(15,23,42,.55);}

/* Right */
.pai-console__right{flex: 1 1 520px; display:flex; flex-direction:column; gap:12px;}

.pai-console__panel{
  border-radius: 18px;
  border: 1px solid rgba(15,23,42,.10);
  background: rgba(255,255,255,.70);
  box-shadow: 0 18px 46px rgba(2,6,23,.08);
  overflow:hidden;
}

.pai-console__panelhead{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:10px 12px;
  border-bottom: 1px solid rgba(15,23,42,.08);
  background: rgba(255,255,255,.78);
}
.pai-console__paneltitle{
  font-weight: 950;
  color:#0f172a;
  display:flex;
  align-items:center;
  gap:10px;
  font-size: .95rem;
}
.pai-console__paneltitle i{color: rgba(15,23,42,.55);}

.pai-console__panelmeta{display:flex; gap:8px; align-items:center;}
.pai-console__pill{
  padding: 5px 9px;
  border-radius: 999px;
  font-weight: 950;
  font-size: .72rem;
  border: 1px solid rgba(15,23,42,.10);
  background: rgba(255,255,255,.68);
  color: rgba(15,23,42,.72);
}

/* Terminal blanca tipo “consola” */
.pai-console__terminal{
  padding: 12px;
  background: rgba(248,250,252,.75);
  border-top: 1px solid rgba(255,255,255,.35);
  border-bottom: 1px solid rgba(15,23,42,.06);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
  color: rgba(15,23,42,.86);
}
.pai-console__line{line-height: 1.55; font-size: .88rem; margin: 6px 0;}
.pai-console__prompt{font-weight: 900; color: rgba(15,23,42,.80);}
.pai-console__sep{margin:0 4px; color: rgba(15,23,42,.55);}
.pai-console__path{color: rgba(100,116,139,.90);}
.pai-console__msg{color: rgba(15,23,42,.86);}

.pai-console__muted{color: rgba(100,116,139,.95);}
.pai-console__tag{
  display:inline-block;
  margin-right:8px;
  padding: 2px 6px;
  border-radius: 8px;
  border: 1px solid rgba(15,23,42,.10);
  background: rgba(255,255,255,.70);
  font-weight: 900;
  font-size: .72rem;
  color: rgba(15,23,42,.70);
}

/* Progreso (SIN azul) */
.pai-console__progress{padding: 12px;}
.pai-console__progressrow{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom: 8px;
}
.pai-console__progresslabel{font-weight: 950; color:#0f172a;}
.pai-console__progresspct{font-weight: 950; color:#0f172a;}

.pai-console__bar{
  position:relative;
  height: 14px;
  border-radius: 999px;
  overflow:hidden;
  background: rgba(255,255,255,.62);
  border: 1px solid rgba(15,23,42,.10);
}
.pai-console__fill{
  height:100%;
  width:0%;
  border-radius:999px;
  background: linear-gradient(135deg,
    rgba(196,181,253,.95),
    rgba(165,243,252,.95),
    rgba(253,230,138,.70));
  box-shadow: 0 0 0 1px rgba(255,255,255,.35) inset, 0 16px 40px rgba(2,6,23,.08);
  transition: width .35s ease;
}
.pai-console__shine{
  position:absolute; inset:0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
  transform: translateX(-60%);
  animation: paiGlow 1.8s ease-in-out infinite;
  opacity: .40;
  pointer-events:none;
}
@keyframes paiGlow{
  0%{transform:translateX(-60%);}
  100%{transform:translateX(160%);}
}

/* Errores (limpios, sin caja negra) */
.pai-console__errors{
  border-radius: 18px;
  border: 1px solid rgba(251,113,133,.22);
  background: rgba(255,255,255,.70);
  box-shadow: 0 18px 46px rgba(2,6,23,.08);
  overflow:hidden;
}
.pai-console__errorshead{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:10px 12px;
  border-bottom: 1px solid rgba(251,113,133,.18);
  background: linear-gradient(135deg, rgba(253,164,175,.18), rgba(255,255,255,.75));
}
.pai-console__errorstitle{
  font-weight: 950;
  color: rgba(159,18,57,.92);
  display:flex;
  align-items:center;
  gap:10px;
}
.pai-console__errorstitle i{color: rgba(251,113,133,.95);}
.pai-console__errorstag{
  font-weight: 950;
  font-size: .72rem;
  letter-spacing: .6px;
  color: rgba(159,18,57,.92);
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid rgba(251,113,133,.28);
  background: rgba(255,255,255,.70);
}
.pai-console__errorsbody{
  padding: 10px 12px;
  background: rgba(248,250,252,.75);
}
.pai-console__errorslist{
  margin:0;
  padding-left:18px;
  color: rgba(15,23,42,.86);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
  font-size: .86rem;
}
.pai-console__errorslist li{margin:6px 0; line-height:1.35;}
.pai-console__errorslist li::marker{color: rgba(251,113,133,.95);}

/* Footer */
.pai-console__footer{
  border:0;
  padding:12px 14px 16px 14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  background: rgba(255,255,255,.78);
  border-top: 1px solid rgba(15,23,42,.08);
  position: relative;
  z-index: 2;
}
.pai-console__footleft{display:flex; align-items:center; gap:10px;}
.pai-console__statusdot{
  width:10px; height:10px; border-radius:999px;
  background: linear-gradient(135deg, rgba(196,181,253,.95), rgba(165,243,252,.95));
  box-shadow: 0 0 0 4px rgba(196,181,253,.18);
}
.pai-console__footnote{color: rgba(100,116,139,.95); font-weight: 800; font-size: .90rem;}

/* ================================
   ✅ FIX TIPS: NEGROS + LLAMATIVOS
   PÉGALO AL FINAL DEL <style>
   ================================ */

.pai-console__terminal .pai-console__line.pai-console__muted{
  color:#0f172a !important;          /* negro */
  font-weight:900 !important;        /* más fuerte */
  padding:10px 12px !important;      /* más “bloque” */
  border-radius:14px !important;
  border:1px solid rgba(15,23,42,.12) !important;
  background: rgba(255,255,255,.92) !important;
  box-shadow: 0 16px 34px rgba(2,6,23,.08) !important;
}

.pai-console__terminal .pai-console__tag{
  color:#0f172a !important;          /* negro */
  background: rgba(255,255,255,.98) !important;
  border:1px solid rgba(15,23,42,.18) !important;
  font-weight:950 !important;
  letter-spacing:.35px !important;
  padding:3px 8px !important;
  border-radius:10px !important;
  box-shadow: 0 10px 22px rgba(2,6,23,.08) !important;
}




/* ==========================================
   ✅ ERRORES COMO CONSOLA (FONDO NEGRO)
   PÉGALO AL FINAL DEL <style>
   ========================================== */

.pai-console__errors{
  background: transparent !important;
  border: 1px solid rgba(15,23,42,.16) !important;
}

.pai-console__errorshead{
  background: rgba(255,255,255,.80) !important;
  border-bottom: 1px solid rgba(15,23,42,.10) !important;
}

.pai-console__errorsbody{
  background: rgba(2,6,23,.94) !important;     /* ✅ negro consola */
  border-top: 0 !important;
}

.pai-console__errorslist{
  color: rgba(226,232,240,.95) !important;     /* texto claro */
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace !important;
  font-size: .88rem !important;
}

.pai-console__errorslist li{
  margin: 7px 0 !important;
  line-height: 1.4 !important;
}

.pai-console__errorslist li::marker{
  color: rgba(165,243,252,.95) !important;     /* marcador pastel */
}

/* scroll bonito tipo consola */
.pai-console__errorsbody{
  max-height: 260px !important;
  overflow: auto !important;
}

.pai-console__errorsbody::-webkit-scrollbar{ width: 10px; }
.pai-console__errorsbody::-webkit-scrollbar-thumb{
  background: rgba(148,163,184,.35);
  border-radius: 999px;
}
.pai-console__errorsbody::-webkit-scrollbar-track{
  background: rgba(2,6,23,.50);
}


</style>
@stop

@section('js')

<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>

@include('livewire.javascript')

<script>
window.IMPORT_ENDPOINTS = {
  start: @json(url('/import/start')),
  statusBase: @json(url('/import/status')),
};
</script>

<script>
(function(){

  // =========================================================
  // ✅ Modal close seguro (quita backdrop y libera pantalla)
  // =========================================================
  function forceCloseBootstrapModal(modalSelector){
      try{
          const $m = $(modalSelector);
          $m.modal('hide');

          setTimeout(() => {
              $('body').removeClass('modal-open');
              $('body').css('padding-right','');
              $('.modal-backdrop').remove();

              $m.removeClass('show');
              $m.attr('aria-hidden','true');
              $m.css('display','none');
          }, 250);
      }catch(e){}
  }

  // =========================================================
  // ✅ refs
  // =========================================================
  const form = document.getElementById('file-upload-form');
  const dz = document.getElementById('drag-drop-area');
  const input = document.getElementById('file_input');

  const filemeta = document.getElementById('filemeta');
  const filename = document.getElementById('filename');
  const filesub = document.getElementById('filesub');

  const submitButton = document.getElementById("submit-button");
  const dateWarning = document.getElementById("date-warning");

  const importMsg  = document.getElementById('import_msg');
  const importBar  = document.getElementById('import_bar');
  const importPct  = document.getElementById('import_pct');
  const importStep = document.getElementById('import_step');

  const errBox  = document.getElementById('import_errors_box');
  const errList = document.getElementById('import_errors_list');
  const btnClose = document.getElementById('btnCloseImport');

  // ✅ Habilita la X del header cuando termina
  const btnCloseX = document.getElementById('btnCloseX');

  // ✅ Ring para cambiar colores según estado
  const importRing = document.getElementById('import_ring');

  const START_URL  = window.IMPORT_ENDPOINTS.start;
  const STATUS_URL = (token) => window.IMPORT_ENDPOINTS.statusBase + '/' + encodeURIComponent(token);

  let pollTimer = null;
  let safetyTimer = null;
  let currentToken = null;

  // ✅ banderas para no repetir mensajes finales
  let alreadyFinished = false;
  let alreadyAlerted = false;
  let isPolling = false;

  // ✅ helper: ring states (tu CSS ahora es pastel)
  function setRingState(state){
      if(!importRing) return;
      importRing.classList.remove('is-success','is-error');
      if(state === 'success') importRing.classList.add('is-success');
      if(state === 'error') importRing.classList.add('is-error');
  }

  function setProgress(pct, message, step){
      const p = Math.max(0, Math.min(100, parseInt(pct || 0)));
      if(importMsg) importMsg.textContent = message || '';
      if(importStep) importStep.textContent = step ? (step) : 'preparando…';
      if(importBar) importBar.style.width = p + '%';
      if(importPct) importPct.textContent = p + '%';
  }

  function stopPolling(){
      if(pollTimer){ clearInterval(pollTimer); pollTimer = null; }
      if(safetyTimer){ clearTimeout(safetyTimer); safetyTimer = null; }
      isPolling = false;
  }

  function resetImportUI(){
      if(submitButton) submitButton.disabled = false;

      if(input) input.value = '';
      if(filemeta) filemeta.style.display = 'none';
      if(filename) filename.textContent = 'archivo.xlsx';
      if(filesub) filesub.textContent = 'Listo para importar';

      if(errBox) errBox.style.display = 'none';
      if(errList) errList.innerHTML = '';

      if(btnClose) btnClose.style.display = 'none';
      if(btnCloseX) btnCloseX.style.display = 'none';

      // ✅ volver ring a normal (sin clases)
      setRingState('normal');

      stopPolling();
      currentToken = null;

      alreadyFinished = false;
      alreadyAlerted = false;
  }

  function showErrors(errors){
      if(!errBox || !errList) return;

      errList.innerHTML = '';
      (errors || []).forEach((x)=> {
          const li = document.createElement('li');
          li.textContent = x;
          errList.appendChild(li);
      });
      errBox.style.display = 'block';
  }

  function finishSuccess(message){
      if(alreadyFinished) return;
      alreadyFinished = true;

      stopPolling();

      setRingState('success');
      setProgress(100, message || 'Importación finalizada.', 'final');

      if(btnClose) btnClose.style.display = 'inline-block';
      if(btnCloseX) btnCloseX.style.display = 'inline-flex';

      Swal.fire({
          icon: 'success',
          title: 'Importación finalizada',
          text: message || 'Proceso completado.',
          toast: true,
          position: 'top-end',
          timer: 5000,
          showConfirmButton: false
      });

      setTimeout(()=> {
          forceCloseBootstrapModal('#loadingModal');
          resetImportUI();
          // location.reload();
      }, 900);
  }

  function finishFailed(message, errors){
      if(alreadyFinished) return;
      alreadyFinished = true;

      stopPolling();

      setRingState('error');
      setProgress(100, message || 'Se encontraron errores.', 'failed');

      if(btnClose) btnClose.style.display = 'inline-block';
      if(btnCloseX) btnCloseX.style.display = 'inline-flex';

      showErrors(errors || []);

      Swal.fire({
          icon: 'error',
          title: 'Importación con errores',
          text: message || 'Revisa el listado de errores.',
          confirmButtonText: 'Entendido'
      });

      if(submitButton) submitButton.disabled = false;
  }

  // =========================================================
  // 1) DROPZONE
  // =========================================================
  function showFile(file){
      const ok = file && (
          file.name.toLowerCase().endsWith('.xlsx') ||
          file.name.toLowerCase().endsWith('.xls')
      );

      if(!ok){
          Swal.fire({
              icon: 'warning',
              title: 'Archivo inválido',
              text: 'Sube un Excel (.xlsx o .xls)',
              toast: true,
              position: 'top-end',
              timer: 4500,
              showConfirmButton: false
          });
          if(input) input.value = '';
          if(filemeta) filemeta.style.display='none';
          return false;
      }

      if(filemeta) filemeta.style.display='block';
      if(filename) filename.textContent = file.name;
      if(filesub) filesub.textContent = 'Listo para importar';
      return true;
  }

  if(dz && input){
      dz.addEventListener('dragover', (e)=>{ e.preventDefault(); dz.classList.add('drag-over'); });
      dz.addEventListener('dragleave', ()=> dz.classList.remove('drag-over'));
      dz.addEventListener('drop', (e)=>{
          e.preventDefault(); dz.classList.remove('drag-over');
          if(e.dataTransfer.files && e.dataTransfer.files[0]){
              input.files = e.dataTransfer.files;
              showFile(input.files[0]);
          }
      });
      dz.addEventListener('click', ()=> input.click());
      input.addEventListener('change', ()=> {
          if(input.files && input.files[0]) showFile(input.files[0]);
      });
  }

  // =========================================================
  // 2) RANGO DE FECHAS
  // =========================================================
  const currentDate = new Date();
  const currentYear = currentDate.getFullYear();
  const currentMonth = currentDate.getMonth();

  const startDate = new Date(currentYear, currentMonth, 1);
  let endDate = new Date(currentYear, currentMonth + 1, 5);
  if (currentMonth === 11) endDate = new Date(currentYear + 1, 0, 5);

  if (!(currentDate >= startDate && currentDate <= endDate)) {
      if(submitButton) submitButton.disabled = true;
      if(dateWarning) dateWarning.style.display = "block";
      if(dz){
          dz.style.pointerEvents = 'none';
          dz.style.opacity = '0.55';
      }
  }

  // =========================================================
  // 3) POLLING (NO repetir mensajes / no concurrente)
  // =========================================================
  async function startPolling(token){
      stopPolling();
      currentToken = token;

      alreadyFinished = false;
      alreadyAlerted = false;
      isPolling = false;

      setRingState('normal');

      safetyTimer = setTimeout(() => {
          if(currentToken && !alreadyFinished && !alreadyAlerted){
              alreadyAlerted = true;

              if(btnClose) btnClose.style.display = 'inline-block';
              if(btnCloseX) btnCloseX.style.display = 'inline-flex';

              Swal.fire({
                icon: 'info',
                title: 'Aún estamos procesando…',
                html: `
                    <div style="text-align:left">
                    El archivo es grande y puede tardar más de lo normal.<br><br>
                    <b>Recomendación:</b> deja esta ventana abierta.<br>
                    Si la barra sigue avanzando, el proceso va bien.
                    </div>
                `,
                confirmButtonText: 'Entendido'
              });
          }
      }, 30 * 60 * 1000);

      pollTimer = setInterval(async () => {
          if(alreadyFinished) return;
          if(isPolling) return;
          isPolling = true;

          try{
              const base = STATUS_URL(token);
              const url = base + (base.includes('?') ? '&' : '?') + 't=' + Date.now();

              const r = await fetch(url, {
                  cache: 'no-store',
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest',
                      'Accept': 'application/json',
                      'Cache-Control': 'no-cache'
                  }
              });

              if(!r.ok){
                  if(importMsg) importMsg.textContent = 'Consultando estado…';
                  isPolling = false;
                  return;
              }

              let s = null;
              try{ s = await r.json(); }
              catch(e){
                  if(importMsg) importMsg.textContent = 'Consultando estado…';
                  isPolling = false;
                  return;
              }

              setProgress(s.percent || 0, s.message || '', s.step || '');

              const st = String(s.status || '').toLowerCase();
              const step = String(s.step || '').toLowerCase();
              const pct = parseInt(s.percent || 0);

              if (st === 'failed' || st === 'error') {
                  finishFailed(s.message || 'Se encontraron errores.', s.errors || []);
                  isPolling = false;
                  return;
              }

              const isDoneByStatus = ['done','completed','complete','success','finished','finalizado'].includes(st);
              const isDoneBy99 = (pct >= 99 && (step.includes('final') || step.includes('finalizando')));

              if (isDoneByStatus || isDoneBy99) {
                  const hasErrors = Array.isArray(s.errors) && s.errors.length > 0;
                  if(hasErrors){
                      finishFailed(s.message || 'Se encontraron errores.', s.errors || []);
                  }else{
                      finishSuccess(s.message || 'Importación finalizada.');
                  }
                  isPolling = false;
                  return;
              }

          }catch(e){
              if(importMsg) importMsg.textContent = 'Consultando estado…';
          }finally{
              isPolling = false;
          }
      }, 1200);
  }

  // =========================================================
  // 4) SUBMIT IMPORT
  // =========================================================
  if(form){
      form.addEventListener('submit', async function(e){
          e.preventDefault();

          if(!input || !input.files || !input.files[0]){
              Swal.fire({
                  icon: 'info',
                  title: 'Falta archivo',
                  text: 'Selecciona un Excel antes de importar.',
                  toast: true,
                  position: 'top-end',
                  timer: 4500,
                  showConfirmButton: false
              });
              return;
          }
          if(!showFile(input.files[0])) return;

          if(submitButton) submitButton.disabled = true;

          if(btnClose) btnClose.style.display = 'none';
          if(btnCloseX) btnCloseX.style.display = 'none';
          if(errBox) errBox.style.display = 'none';
          if(errList) errList.innerHTML = '';

          alreadyFinished = false;
          alreadyAlerted = false;

          setRingState('normal');

          setProgress(2, 'Subiendo archivo… (no cierres esta ventana)', 'subida');
          $('#loadingModal').modal('show');

          const formData = new FormData();
          formData.append('file', input.files[0]);

          const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

          try{
              const resp = await fetch(START_URL, {
                  method: 'POST',
                  headers: {
                      'X-Requested-With': 'XMLHttpRequest',
                      'X-CSRF-TOKEN': csrf,
                      'Accept': 'application/json'
                  },
                  body: formData
              });

              let data = null;
              try{ data = await resp.json(); }catch(e){ data = null; }

              if(!resp.ok || !data || !data.ok || !data.token){
                  if(btnClose) btnClose.style.display = 'inline-block';
                  if(btnCloseX) btnCloseX.style.display = 'inline-flex';
                  if(submitButton) submitButton.disabled = false;

                  setRingState('error');
                  setProgress(0, 'No se pudo iniciar la importación.', 'error');

                  Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: (data && data.message) ? data.message : 'No se pudo iniciar el proceso.',
                      confirmButtonText: 'Entendido'
                  });
                  return;
              }

              setProgress(5, 'Importación en cola…', 'cola');
              startPolling(data.token);

          }catch(err){
              if(btnClose) btnClose.style.display = 'inline-block';
              if(btnCloseX) btnCloseX.style.display = 'inline-flex';
              if(submitButton) submitButton.disabled = false;

              setRingState('error');
              setProgress(0, 'Error enviando el archivo al servidor.', 'error');

              Swal.fire({
                  icon: 'error',
                  title: 'Error de red',
                  text: 'No se pudo enviar el archivo. Revisa tu conexión y vuelve a intentar.',
                  confirmButtonText: 'Entendido'
              });
          }
      });
  }

  // cerrar manual
  if(btnClose){
      btnClose.addEventListener('click', function(){
          stopPolling();
          forceCloseBootstrapModal('#loadingModal');
          resetImportUI();
      });
  }

  // si se cierra por cualquier cosa, libera pantalla
  $('#loadingModal').on('hidden.bs.modal', function () {
      stopPolling();
      resetImportUI();
      $('body').removeClass('modal-open');
      $('body').css('padding-right','');
      $('.modal-backdrop').remove();
  });

  // =========================================================
  // 5) MENSAJES SESSION (si aún los usas)
  // =========================================================
  document.addEventListener('DOMContentLoaded', function(){

      @if (Session::has('success'))
          Swal.fire({
              icon: 'success',
              title: 'Proceso completado',
              text: @json(Session::get('success')),
              toast: true,
              position: 'top-end',
              timer: 5000,
              showConfirmButton: false
          });
      @endif

      @if (Session::has('error1'))
          @if (is_array(Session::get('error1')))
              let errorsHtml = '<ul style="margin:0; padding-left:18px;">';
              @foreach (Session::get('error1') as $error)
                  errorsHtml += '<li>{{ $error }}</li>';
              @endforeach
              errorsHtml += '</ul>';

              Swal.fire({
                  icon: 'error',
                  title: 'Se encontraron problemas',
                  html: errorsHtml,
                  confirmButtonText: 'Entendido'
              });
          @else
              Swal.fire({
                  icon: 'error',
                  title: 'Ocurrió un error',
                  text: @json(Session::get('error1')),
                  confirmButtonText: 'Entendido'
              });
          @endif
      @endif

      @if (count($errors) > 0)
          let validationErrors = '<ul style="margin:0; padding-left:18px;">';
          @foreach ($errors->all() as $error)
              validationErrors += '<li>{{ $error }}</li>';
          @endforeach
          validationErrors += '</ul>';

          Swal.fire({
              icon: 'warning',
              title: 'Errores de validación',
              html: validationErrors,
              confirmButtonText: 'Revisar'
          });
      @endif
  });

  // =========================================================
  // 6) TU CÓDIGO EXISTENTE (NO lo toco)
  // =========================================================

  $(document).on('hidden.bs.modal', '#exportModal', function () {
      $('body').removeClass('modal-open');
      $('body').css('padding-right','');
      $('.modal-backdrop').remove();
  });

  let searchTimer = null;

  $('#search').on('keyup', function(){
      const query = $(this).val().trim();
      clearTimeout(searchTimer);

      if(query.length < 2){
          $('#search-results').hide().empty();
          return;
      }

      searchTimer = setTimeout(function(){

          $('#search-results').show().empty()
              .append('<div style="text-align:center; padding:10px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');

          $.ajax({
              url: "{{ route('buscar.afiliados') }}",
              method: "GET",
              dataType: "json",
              data: { search: query },
              success: function(data){
                  $('#search-results').empty();

                  if(data.length > 0){
                      $.each(data, function(index, afiliado){

                          const nombre = [
                              afiliado.primer_nombre,
                              afiliado.segundo_nombre,
                              afiliado.primer_apellido,
                              afiliado.segundo_apellido
                          ].filter(Boolean).join(' ');

                          $('#search-results').append(
                              '<a href="#" class="list-group-item list-group-item-action search-result-item" ' +
                              'data-id="'+afiliado.numero_identificacion+'">' +
                              (afiliado.tipo_identificacion ? afiliado.tipo_identificacion + ' ' : '') +
                              afiliado.numero_identificacion + ' – ' + nombre +
                              '</a>'
                          );
                      });
                  }else{
                      $('#search-results').append('<a href="#" class="list-group-item list-group-item-action disabled">No se encontraron resultados</a>');
                  }
              },
              error: function(xhr){
                  $('#search-results').empty().append('<a href="#" class="list-group-item list-group-item-action disabled">Error en la búsqueda</a>');
                  console.log("Error AJAX buscador:", xhr.responseText);
              }
          });

      }, 250);
  });

  $(document).on('click', '.search-result-item', function(e){
      e.preventDefault();

      const numeroIdentificacion = $(this).data('id');
      $('#search').val(numeroIdentificacion);
      $('#search-results').hide().empty();

      $.ajax({
          url: "{{ route('afiliado') }}",
          method: "GET",
          dataType: "json",
          data: { search: numeroIdentificacion },
          success: function(response){

              const rows = response.sivigilas_usernormal || response.sivigilas || [];
              const $tbody = $('#sivigila tbody');
              $tbody.empty();

              $.each(rows, function(index, r){

                  const fullName = [r.primer_nombre, r.segundo_nombre, r.primer_apellido, r.segundo_apellido]
                      .filter(Boolean).join(' ');

                  const carnet = (r.numero_carnet ?? 0);

                  const acciones = '<a href="#" class="btn btn-sm btn-warning blinking-button send-email" data-toggle="modal" data-target="#emailModal" data-id="'+r.id+'" data-name="'+fullName+'"><i class="fas fa-envelope"></i> Solicitud</a>';

                  $tbody.append(
                      '<tr>' +
                          '<td><small>'+ (r.id ?? '') +'</small></td>' +
                          '<td><a href="#" class="numero-identificacion" data-id="'+(r.id ?? '')+'" data-carnet="'+carnet+'" style="text-decoration:underline;">'+ (r.numero_identificacion ?? '') +'</a></td>' +
                          '<td><small>'+fullName+'</small></td>' +
                          '<td><small>'+(r.numero_carnet ?? (r.batch_verifications_id ?? '') )+'</small></td>' +
                          '<td>'+acciones+'</td>' +
                      '</tr>'
                  );
              });

          },
          error: function(xhr){
              console.log("Error filtrando tabla:", xhr.responseText);
          }
      });
  });

  $(document).on('click', function(e){
      if(!$(e.target).closest('.search-container').length){
          $('#search-results').hide().empty();
      }
  });

  $(document).on('click', '.numero-identificacion', function(e){
      e.preventDefault();

      const id = $(this).data('id');
      let carnet = $(this).data('carnet');

      if(carnet === undefined || carnet === null || carnet === ''){
          carnet = 0;
      }

      let url = "{{ route('getVacunas', ['id' => ':id', 'numeroCarnet' => ':carnet']) }}";
      url = url.replace(':id', encodeURIComponent(id));
      url = url.replace(':carnet', encodeURIComponent(carnet));

      $.ajax({
          url: url,
          method: 'GET',
          success: function(data){

              $('#vacunaList').empty();

              if(!data || data.length === 0){
                  $('#vacunaList').append('<tr><td colspan="7" class="text-center">No se encontraron vacunas</td></tr>');
                  $('#vacunaModal').modal('show');
                  return;
              }

              const nombreCompleto = [data[0].prim_nom, data[0].seg_nom, data[0].pri_ape, data[0].seg_ape]
                  .filter(Boolean).join(' ');

              $('#nombrePaciente').text(nombreCompleto);
              $('#tipoIdentificacion').text(data[0].tipo_id ?? '');
              $('#identificacion').text(data[0].numero_id ?? '');
              $('#sexo').text(data[0].genero ?? '');
              $('#fechaNacimiento').text(data[0].fecha_nacimiento ?? '');
              $('#ips').text(data[0].ips ?? '');
              $('#edad').text(data[0].age ?? '');

              data.forEach(function(v){
                  $('#vacunaList').append(
                      '<tr>' +
                          '<td>' + (v.nombre_vacuna ?? '') + '</td>' +
                          '<td>' + (v.docis_vacuna ?? '') + '</td>' +
                          '<td>' + (v.fecha_vacunacion ?? '') + '</td>' +
                          '<td>' + (v.edad_anos ?? '') + '</td>' +
                          '<td>' + (v.total_meses ?? '') + '</td>' +
                          '<td>' + (v.nombre_usuario ?? '') + '</td>' +
                          '<td>' + (v.responsable ? v.responsable : '---') + '</td>' +
                      '</tr>'
                  );
              });

              $('#vacunaModal').modal('show');
          },
          error: function(xhr){
              console.log("Error getVacunas:", xhr.responseText);
              Swal.fire({
                  title: 'Error',
                  text: 'No se pudo cargar el detalle de vacunas.',
                  icon: 'error'
              });
          }
      });
  });

  $(document).on('click', '.send-email', function(){
      const patientId = $(this).data('id');
      const patientName = $(this).data('name');

      $('#patientName').val(patientName);
      $('#patientId').val(patientId);

      $('#emailModal').modal('show');
  });

  $('#exportButton').on('click', function (e) {
      e.preventDefault();

      const start = $('#start_date').val();
      const end   = $('#end_date').val();

      if (!start || !end) {
          Swal.fire({
              icon: 'warning',
              title: 'Fechas requeridas',
              text: 'Selecciona fecha de inicio y fecha final.',
          });
          return;
      }

      $('#button-text').hide();
      $('#loading-icon').show();
      $('#sending-text').show();
      $('#exportButton').prop('disabled', true);

      const url = "{{ route('exportVacunas') }}";

      function resetExportUI(){
          $('#button-text').show();
          $('#loading-icon').hide();
          $('#sending-text').hide();
          $('#exportButton').prop('disabled', false);
      }

      $.ajax({
          url: url,
          type: 'GET',
          data: { start_date: start, end_date: end },
          xhrFields: { responseType: 'blob' },

          success: function (blob, status, xhr) {

              const disposition = xhr.getResponseHeader('Content-Disposition') || '';
              let filename = 'reporte.xlsx';
              const match = disposition.match(/filename="?([^"]+)"?/);
              if (match && match[1]) filename = match[1];

              const downloadUrl = window.URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = downloadUrl;
              a.download = filename;
              document.body.appendChild(a);
              a.click();
              a.remove();
              window.URL.revokeObjectURL(downloadUrl);

              forceCloseBootstrapModal('#exportModal');
              resetExportUI();
          },

          error: function (xhr) {
              console.log("Export error:", xhr);

              forceCloseBootstrapModal('#exportModal');
              resetExportUI();

              const fallback = url + '?start_date=' + encodeURIComponent(start) + '&end_date=' + encodeURIComponent(end);
              window.location.href = fallback;
          }
      });
  });

})();
</script>
@stop

<style>
/* =========================
   ALERTS TECNOLÓGICAS PAI
   (legibles + modernas)
   ========================= */
.pai-alert{
  position:relative;
  display:flex;
  gap:14px;
  align-items:flex-start;
  padding:16px 16px 16px 14px;
  border-radius:18px;
  background:#fff;
  border:1px solid rgba(15,23,42,.10);
  box-shadow: 0 16px 40px rgba(2,6,23,.10);
  overflow:hidden;
  margin-bottom:14px;
  animation: paiAlertIn .35s ease-out;
}

/* Borde “neón” lateral + glow sutil */
.pai-alert::before{
  content:"";
  position:absolute;
  inset:0;
  pointer-events:none;
  background: radial-gradient(1200px 220px at 20% 0%, rgba(37,99,235,.12), transparent 55%),
              radial-gradient(900px 220px at 90% 100%, rgba(34,197,94,.10), transparent 55%);
  opacity:.9;
}
.pai-alert::after{
  content:"";
  position:absolute;
  left:10px; top:10px; bottom:10px;
  width:6px;
  border-radius:999px;
  background: linear-gradient(180deg, rgba(37,99,235,1), rgba(14,165,233,1));
  box-shadow: 0 0 0 6px rgba(37,99,235,.10);
}

/* Variantes */
.pai-alert--success::after{
  background: linear-gradient(180deg, #22c55e, #16a34a);
  box-shadow: 0 0 0 6px rgba(34,197,94,.12);
}
.pai-alert--danger::after{
  background: linear-gradient(180deg, #ef4444, #f97316);
  box-shadow: 0 0 0 6px rgba(239,68,68,.12);
}
.pai-alert--warning::after{
  background: linear-gradient(180deg, #f59e0b, #fbbf24);
  box-shadow: 0 0 0 6px rgba(245,158,11,.14);
}

/* Icono tipo “chip” */
.pai-alert__icon{
  flex:0 0 auto;
  width:44px;
  height:44px;
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(2,6,23,.04);
  border: 1px solid rgba(2,6,23,.10);
  box-shadow: 0 12px 25px rgba(2,6,23,.08);
  margin-left:12px; /* para no chocar con la barra izquierda */
  z-index:1;
}
.pai-alert--success .pai-alert__icon{
  background: rgba(34,197,94,.10);
  border-color: rgba(34,197,94,.20);
}
.pai-alert--danger .pai-alert__icon{
  background: rgba(239,68,68,.10);
  border-color: rgba(239,68,68,.20);
}
.pai-alert--warning .pai-alert__icon{
  background: rgba(245,158,11,.12);
  border-color: rgba(245,158,11,.22);
}

.pai-alert__icon i{
  font-size:20px;
  color:#0f172a;
}
.pai-alert--success .pai-alert__icon i{ color:#16a34a; }
.pai-alert--danger  .pai-alert__icon i{ color:#ef4444; }
.pai-alert--warning .pai-alert__icon i{ color:#f59e0b; }

/* Contenido */
.pai-alert__body{ flex:1 1 auto; z-index:1; }
.pai-alert__title{
  font-weight:900;
  color:#0f172a;
  font-size:1.05rem;
  line-height:1.2;
}
.pai-alert__text{
  margin-top:6px;
  color:#475569;
  font-size:.98rem;
  line-height:1.45;
}

/* Lista de errores */
.pai-alert__list{
  margin:8px 0 0 0;
  padding-left:18px;
  color:#475569;
  font-size:.98rem;
}
.pai-alert__list li{ margin:4px 0; }

/* “Chip” de estado opcional */
.pai-alert__chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  margin-top:10px;
  padding:6px 10px;
  border-radius:999px;
  font-weight:900;
  font-size:.85rem;
  border:1px solid rgba(2,6,23,.10);
  background:#fff;
  color:#0f172a;
}
.pai-alert__chip .dot{
  width:9px; height:9px; border-radius:999px;
  background:#22c55e;
  box-shadow:0 0 0 5px rgba(34,197,94,.18);
}
.pai-alert--danger .pai-alert__chip .dot{
  background:#ef4444;
  box-shadow:0 0 0 5px rgba(239,68,68,.18);
}
.pai-alert--warning .pai-alert__chip .dot{
  background:#f59e0b;
  box-shadow:0 0 0 5px rgba(245,158,11,.20);
}

/* Botón cerrar (más “pro”) */
.pai-alert__close{
  position:absolute;
  top:10px;
  right:10px;
  z-index:2;
  width:36px;
  height:36px;
  border-radius:12px;
  border:1px solid rgba(2,6,23,.10);
  background:rgba(255,255,255,.9);
  color:#0f172a;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
}
.pai-alert__close:hover{
  transform: translateY(-1px);
  box-shadow: 0 12px 24px rgba(2,6,23,.12);
  background:#fff;
}

/* Animación entrada */
@keyframes paiAlertIn{
  from{ opacity:0; transform: translateY(-8px); }
  to{ opacity:1; transform: translateY(0); }
}
</style>

{{-- =========================
   MENSAJE ÉXITO / INFO
   ========================= --}}
@if(Session::has('mensaje'))
<div class="pai-alert pai-alert--success" role="alert">
    <button type="button" class="pai-alert__close" data-dismiss="alert" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>

    <div class="pai-alert__icon">
        <i class="fas fa-check-circle"></i>
    </div>

    <div class="pai-alert__body">
        <div class="pai-alert__title">Proceso completado</div>
        <div class="pai-alert__text"><strong>{{ Session::get('mensaje') }}</strong></div>

        <div class="pai-alert__chip">
            <span class="dot"></span> Estado: OK
        </div>
    </div>
</div>
@endif

{{-- =========================
   ERROR PERSONALIZADO (error1)
   ========================= --}}
@if(Session::has('error1'))
<div class="pai-alert pai-alert--danger" role="alert">
    <button type="button" class="pai-alert__close" data-dismiss="alert" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>

    <div class="pai-alert__icon">
        <i class="fas fa-exclamation-circle"></i>
    </div>

    <div class="pai-alert__body">
        <div class="pai-alert__title">Se encontraron problemas</div>

        @if(is_array(Session::get('error1')))
            <div class="pai-alert__text">Revisa los detalles:</div>
            <ul class="pai-alert__list">
                @foreach(Session::get('error1') as $error)
                    <li><strong>{{ $error }}</strong></li>
                @endforeach
            </ul>
        @else
            <div class="pai-alert__text"><strong>{{ Session::get('error1') }}</strong></div>
        @endif

        <div class="pai-alert__chip">
            <span class="dot"></span> Estado: ERROR
        </div>
    </div>
</div>
@endif

{{-- =========================
   ERRORES DE VALIDACIÓN ($errors)
   ========================= --}}
@if(count($errors) > 0)
<div class="pai-alert pai-alert--warning" role="alert">
    <button type="button" class="pai-alert__close" data-dismiss="alert" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>

    <div class="pai-alert__icon">
        <i class="fas fa-exclamation-triangle"></i>
    </div>

    <div class="pai-alert__body">
        <div class="pai-alert__title">Errores de validación</div>
        <div class="pai-alert__text">Corrige lo siguiente:</div>

        <ul class="pai-alert__list">
            @foreach($errors->all() as $error)
                <li><strong>{{ $error }}</strong></li>
            @endforeach
        </ul>

        <div class="pai-alert__chip">
            <span class="dot"></span> Estado: REVISAR
        </div>
    </div>
</div>
@endif

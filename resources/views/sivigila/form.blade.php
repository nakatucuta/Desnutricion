@include('seguimiento.mensajes')

@php
    $registro = $incomeedit1 ?? null;
    $eventCode = (int) ($eventCode ?? (optional($registro)->cod_eve ?? 113));
    $moduleBackUrl = $moduleBackUrl ?? url('sivigila');
    $preferidos = collect($income12 ?? []);
    $allPrestadores = collect($incomeedit15 ?? []);
    $prestadores = $preferidos->concat($allPrestadores)->unique('id')->values();
    $preferidoId = optional($preferidos->first())->id;
    $pacienteNombre = trim(implode(' ', array_filter([
        optional($registro)->pri_nom_,
        optional($registro)->seg_nom_,
        optional($registro)->pri_ape_,
        optional($registro)->seg_ape_,
    ])));
@endphp

<style>
    .siv-hero {
        border-radius: 20px;
        padding: 1.2rem 1.35rem;
        margin-bottom: 1rem;
        color: #fff;
        background: radial-gradient(circle at 85% 22%, rgba(255,255,255,.22), transparent 36%), linear-gradient(130deg,#0f5a7a,#0f7d9b 54%, #16a36d);
        box-shadow: 0 16px 34px rgba(8,55,77,.24);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .siv-hero-logo {
        width: 74px;
        height: 74px;
        border-radius: 18px;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.24);
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .siv-hero-logo img { width: 46px; height: auto; object-fit: contain; }
    .siv-hero h2 { margin: 0; font-size: 1.45rem; font-weight: 800; }
    .siv-hero p { margin: .35rem 0 0; opacity: .92; }
    .siv-card {
        border-radius: 18px;
        border: 1px solid #d7e8ee;
        box-shadow: 0 12px 26px rgba(15,66,82,.08);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .siv-card .card-header {
        background: #f4fafc;
        border-bottom: 1px solid #e2edf2;
        color: #315b66;
        font-weight: 800;
    }
    .siv-readonly { background: #f8fbfd !important; }
    .siv-actions { display: flex; gap: .6rem; flex-wrap: wrap; }
    .siv-hint { font-size: .84rem; color: #4d6971; margin-bottom: .6rem; }
</style>

<div class="siv-hero">
    <div style="display:flex; gap:.85rem; align-items:center;">
        <div class="siv-hero-logo">
            <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional">
        </div>
        <div>
            <div style="font-size:.76rem; text-transform:uppercase; letter-spacing:.08em; font-weight:800; opacity:.92;">Evento {{ $eventCode }}</div>
            <h2>Asignacion y verificacion de paciente</h2>
            <p>Valida la informacion, define prestador y confirma el envio al seguimiento.</p>
        </div>
    </div>
    <div class="badge badge-light p-2" style="font-size:.86rem;">{{ $pacienteNombre ?: 'Paciente sin nombre' }}</div>
</div>

<div class="card siv-card">
    <div class="card-header"><i class="fas fa-id-card mr-1"></i> Datos basicos del paciente</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 form-group">
                <label>Nombre completo</label>
                <input class="form-control siv-readonly" type="text" value="{{ $pacienteNombre }}" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 form-group">
                <label>Codigo evento</label>
                <input class="form-control siv-readonly" type="text" name="cod_eve" id="cod_eve" value="{{ old('cod_eve', optional($registro)->cod_eve) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Semana notificacion</label>
                <input class="form-control siv-readonly" type="text" name="semana" id="semana" value="{{ old('semana', $incomeedit5) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Fecha notificacion</label>
                <input class="form-control siv-readonly" type="date" name="fec_not" id="fec_not" value="{{ old('fec_not', $incomeedit2) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Ano</label>
                <input class="form-control siv-readonly" type="text" name="year" id="year" value="{{ old('year', $incomeedit4) }}" readonly>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 form-group">
                <label>Departamento</label>
                <input class="form-control siv-readonly" type="text" name="dpto" id="dpto" value="{{ old('dpto', $incomeedit7) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Municipio</label>
                <input class="form-control siv-readonly" type="text" name="mun" id="mun" value="{{ old('mun', $incomeedit6) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Tipo identificacion</label>
                <input class="form-control siv-readonly" type="text" name="tip_ide_" id="tip_ide_" value="{{ old('tip_ide_', optional($registro)->tip_ide_) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Identificacion</label>
                <input class="form-control siv-readonly" type="text" name="num_ide_" id="num_ide_" value="{{ old('num_ide_', $incomeedit) }}" readonly>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 form-group">
                <label>Primer nombre</label>
                <input class="form-control siv-readonly" type="text" name="pri_nom_" id="pri_nom_" value="{{ old('pri_nom_', optional($registro)->pri_nom_) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Segundo nombre</label>
                <input class="form-control siv-readonly" type="text" name="seg_nom_" id="seg_nom_" value="{{ old('seg_nom_', optional($registro)->seg_nom_) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Primer apellido</label>
                <input class="form-control siv-readonly" type="text" name="pri_ape_" id="pri_ape_" value="{{ old('pri_ape_', optional($registro)->pri_ape_) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Segundo apellido</label>
                <input class="form-control siv-readonly" type="text" name="seg_ape_" id="seg_ape_" value="{{ old('seg_ape_', optional($registro)->seg_ape_) }}" readonly>
            </div>
        </div>
    </div>
</div>

<div class="card siv-card">
    <div class="card-header"><i class="fas fa-stethoscope mr-1"></i> Datos clinicos y asignacion</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 form-group">
                <label>Edad</label>
                <input class="form-control" type="text" name="edad_" id="edad_" value="{{ old('edad_', $incomeedit3) }}">
            </div>
            <div class="col-md-3 form-group">
                <label>Sexo</label>
                <input class="form-control siv-readonly" type="text" name="sexo_" id="sexo_" value="{{ old('sexo_', optional($registro)->sexo_) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Fecha nacimiento</label>
                <input class="form-control" type="date" name="fecha_nto_" id="fecha_nto_" value="{{ old('fecha_nto_', $incomeedit8) }}">
            </div>
            <div class="col-md-3 form-group">
                <label>Edad meses</label>
                <input class="form-control" type="text" name="edad_ges" id="edad_ges" value="{{ old('edad_ges', $incomeedit9) }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 form-group">
                <label>Telefono</label>
                <input class="form-control" type="text" name="telefono_" id="telefono_" value="{{ old('telefono_', optional($registro)->telefono_) }}">
            </div>
            <div class="col-md-3 form-group">
                <label>Etnia</label>
                <input class="form-control siv-readonly" type="text" name="nom_grupo_" id="nom_grupo_" value="{{ old('nom_grupo_', optional($registro)->nom_grupo_) }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>IPS atencion inicial</label>
                <input class="form-control" type="text" name="Ips_at_inicial" id="Ips_at_inicial" value="{{ old('Ips_at_inicial', $income11) }}">
            </div>
            <div class="col-md-3 form-group">
                <label>Fecha atencion inicial</label>
                <input class="form-control siv-readonly" type="date" name="fecha_aten_inicial" id="fecha_aten_inicial" value="{{ old('fecha_aten_inicial', $incomeedit2) }}" readonly>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 form-group">
                <label>Regimen</label>
                <input class="form-control siv-readonly" type="text" name="regimen" id="regimen" value="{{ old('regimen', $incomeedit13) }}" readonly>
            </div>
            <div class="col-md-5 form-group">
                <label>IPS seguimiento ambulatorio</label>
                @if($preferidos->isEmpty())
                    <div class="alert alert-warning py-2 mb-2">No se encontro IPS primaria asociada. Selecciona prestador manualmente.</div>
                @endif
                <select class="form-control person2" name="user_id" id="user_id" required>
                    <option value="">Seleccione prestador...</option>
                    @foreach($prestadores as $prestador)
                        <option value="{{ $prestador->id }}"
                            {{ (string) old('user_id', $preferidoId) === (string) $prestador->id ? 'selected' : '' }}>
                            {{ trim(($prestador->codigohabilitacion ?? '') . ' - ' . ($prestador->name ?? '')) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label>Caso confirmado por etiologia primaria</label>
                <select class="form-control person2" name="Caso_confirmada_desnutricion_etiologia_primaria" id="Caso_confirmada_desnutricion_etiologia_primaria" required>
                    <option value="">Seleccione...</option>
                    <option value="SI APLICA" {{ old('Caso_confirmada_desnutricion_etiologia_primaria') === 'SI APLICA' ? 'selected' : '' }}>SI APLICA</option>
                    <option value="NO APLICA" {{ old('Caso_confirmada_desnutricion_etiologia_primaria') === 'NO APLICA' ? 'selected' : '' }}>NO APLICA</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card siv-card">
    <div class="card-header"><i class="fas fa-file-medical-alt mr-1"></i> Informe nominal</div>
    <div class="card-body">
        <p class="siv-hint">Define si hubo manejo hospitalario. Si eliges "SI", se habilita la IPS de manejo.</p>
        <div class="row">
            <div class="col-md-5 form-group">
                <label>Manejo hospitalario</label>
                <select class="form-control person2" name="nombreips_manejo_hospita" id="nombreips_manejo_hospita" required>
                    <option value="">Seleccione...</option>
                    <option value="SI" {{ old('nombreips_manejo_hospita') === 'SI' ? 'selected' : '' }}>SI</option>
                    <option value="NO" {{ old('nombreips_manejo_hospita') === 'NO' ? 'selected' : '' }}>NO</option>
                </select>
            </div>
            <div class="col-md-7 form-group" id="ips_manejo_group">
                <label>IPS manejo hospitalario</label>
                <select class="form-control person2" name="Ips_manejo_hospitalario" id="Ips_manejo_hospitalario">
                    <option value="">Seleccione...</option>
                    @foreach(($incomeedit16 ?? []) as $ips)
                        <option value="{{ $ips->nombrepres }}" {{ old('Ips_manejo_hospitalario') === $ips->nombrepres ? 'selected' : '' }}>
                            {{ $ips->nombrepres }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="siv-actions mb-3">
    <button id="update-btn" class="btn btn-success" type="button" onclick="submitForm()">
        <span id="button-text"><i class="fas fa-paper-plane mr-1"></i> Guardar y asignar</span>
        <span id="loading-icon" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;"></span>
        <span id="sending-text" style="display:none;">Enviando correo...</span>
    </button>
    <a class="btn btn-outline-primary" href="{{ $moduleBackUrl }}">
        <i class="fas fa-arrow-left mr-1"></i> Regresar
    </a>
</div>

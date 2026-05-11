@extends('adminlte::page')

@section('title', 'Perfil')

@section('content_header')
    <div class="profile-hero">
        <div class="profile-hero__brand">
            <div class="profile-hero__logo-wrap">
                <img src="{{ asset('img/logo.png') }}" alt="Escudo institucional" class="profile-hero__logo">
            </div>
            <div>
                <h1 class="profile-hero__title">Perfil de Usuario</h1>
                <p class="profile-hero__subtitle mb-0">Gestion segura de datos personales y credenciales institucionales</p>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if (!empty($user->force_password_change))
        <div class="alert alert-warning">
            Tu contrasena fue restablecida por administracion. Debes cambiarla para continuar usando el sistema.
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(!empty($pendingEmailChange))
        <div class="alert alert-warning">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="mr-3">
                    Tienes un cambio de correo pendiente para <strong>{{ $pendingEmailChange->new_email }}</strong>.
                    Revisa tu bandeja y confirma antes de <strong>{{ optional($pendingEmailChange->expires_at)->format('Y-m-d H:i') ?? 'sin limite' }}</strong>.
                </div>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <form method="POST" action="{{ route('profile.email.resend') }}" class="mr-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning">Reenviar enlace</button>
                    </form>
                    <form method="POST" action="{{ route('profile.email.cancel') }}" onsubmit="return confirm('Cancelar solicitud pendiente de cambio de correo?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar solicitud</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card card-outline card-primary profile-card">
                <div class="card-header profile-card__header"><h3 class="card-title mb-0"><i class="fas fa-id-badge mr-2"></i>Datos del Perfil</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group text-center">
                            <img
                                src="{{ $user->adminlte_image() }}"
                                alt="Foto de perfil"
                                style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:2px solid #2d7ff9;">
                        </div>

                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Correo</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            <small id="email-live-feedback" class="form-text d-none"></small>
                            @if(!empty($pendingEmailChange))
                                <small class="form-text text-warning">Hay una solicitud pendiente para cambiar este correo.</small>
                            @endif
                            @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="codigohabilitacion">Codigo de Habilitacion / Usuario</label>
                            <input id="codigohabilitacion" name="codigohabilitacion" type="text" class="form-control @error('codigohabilitacion') is-invalid @enderror" value="{{ old('codigohabilitacion', $user->codigohabilitacion) }}">
                            @error('codigohabilitacion') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="profile_photo">Foto de Perfil</label>
                            <input id="profile_photo" name="profile_photo" type="file" class="form-control-file @error('profile_photo') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <small class="text-muted">Formatos: JPG, PNG, WEBP. Maximo 2MB.</small>
                            @error('profile_photo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <button id="profile-save-btn" class="btn btn-primary profile-btn-primary" type="submit">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-outline card-primary profile-card">
                <div class="card-header profile-card__header"><h3 class="card-title mb-0"><i class="fas fa-shield-alt mr-2"></i>Cambiar Contrasena</h3></div>
                <div class="card-body">
                    <form id="password-form" method="POST" action="{{ route('profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="current_password">Contrasena Actual</label>
                            <div class="input-group">
                                <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#current_password" aria-label="Mostrar u ocultar contrasena actual">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('current_password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Nueva Contrasena</label>
                            <div class="input-group">
                                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#password" aria-label="Mostrar u ocultar nueva contrasena">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Minimo 10 caracteres, con mayuscula, minuscula, numero y simbolo.</small>
                            <div class="password-strength mt-2" aria-hidden="true">
                                <div id="password-strength-bar" class="password-strength__bar"></div>
                            </div>
                            <small id="password-strength-label" class="form-text text-muted">Fortaleza: sin evaluar</small>
                            <small id="password-live-feedback" class="form-text d-none"></small>
                            @error('password') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Nueva Contrasena</label>
                            <div class="input-group">
                                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#password_confirmation" aria-label="Mostrar u ocultar confirmacion de contrasena">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <ul class="password-checklist mb-3" id="password-checklist">
                            <li id="rule-length"><i class="far fa-circle"></i> Minimo 10 caracteres</li>
                            <li id="rule-upper"><i class="far fa-circle"></i> Al menos una letra mayuscula</li>
                            <li id="rule-lower"><i class="far fa-circle"></i> Al menos una letra minuscula</li>
                            <li id="rule-number"><i class="far fa-circle"></i> Al menos un numero</li>
                            <li id="rule-symbol"><i class="far fa-circle"></i> Al menos un simbolo</li>
                            <li id="rule-space"><i class="far fa-circle"></i> Sin espacios</li>
                            <li id="rule-match"><i class="far fa-circle"></i> Confirmacion coincide</li>
                            <li id="rule-current"><i class="far fa-circle"></i> Contrasena actual diligenciada</li>
                            <li id="rule-different"><i class="far fa-circle"></i> Debe ser diferente de la actual</li>
                        </ul>

                        <button id="password-save-btn" class="btn btn-primary profile-btn-primary" type="submit">Actualizar Contrasena</button>
                    </form>
                </div>
            </div>

            @if($canViewAudit)
                <div class="mt-2">
                    <a href="{{ route('profile.audit') }}" class="btn btn-outline-primary">Ver Auditoria de Cambios de Perfil</a>
                </div>
            @endif

            <div class="card card-outline card-primary profile-card mt-3">
                <div class="card-header profile-card__header"><h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Tu Actividad Reciente</h3></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush profile-history">
                        @forelse($recentAudits as $audit)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ implode(', ', (array) $audit->changed_fields) }}</strong>
                                        <div class="text-muted small">{{ optional($audit->changed_at)->format('Y-m-d H:i:s') ?? '-' }}</div>
                                        <div class="text-muted small">IP: {{ $audit->ip ?? 'N/D' }}</div>
                                    </div>
                                    <span class="badge badge-light">{{ $loop->iteration }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aun no tienes actividad registrada.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .profile-hero{
        border-radius:18px;
        padding:1rem 1.2rem;
        margin-bottom:.9rem;
        background:linear-gradient(135deg,#0f6f7e 0%,#118a96 55%,#18a874 100%);
        box-shadow:0 14px 30px rgba(12,77,86,.22);
        color:#fff;
    }
    .profile-hero__brand{display:flex;align-items:center;gap:.95rem}
    .profile-hero__logo-wrap{
        width:74px;height:74px;border-radius:18px;display:flex;align-items:center;justify-content:center;
        background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.25)
    }
    .profile-hero__logo{width:52px;height:auto;object-fit:contain}
    .profile-hero__title{margin:0;font-size:1.35rem;font-weight:800;letter-spacing:.2px}
    .profile-hero__subtitle{opacity:.92;font-size:.92rem}
    .profile-card{border-radius:14px;overflow:hidden;box-shadow:0 8px 20px rgba(17,72,79,.08)}
    .profile-card__header{
        background:linear-gradient(135deg,#f7fbfc 0%,#eef6f7 100%);
        border-bottom:1px solid #d6e7ea;
        color:#0f6070;
        font-weight:700;
    }
    .profile-btn-primary{
        background:linear-gradient(135deg,#0f7f8e,#17a16f);
        border-color:#0f7f8e;
        font-weight:700;
    }
    .profile-btn-primary:hover{filter:brightness(1.03)}
    .password-strength{
        width:100%;
        height:8px;
        border-radius:999px;
        background:#e8eff1;
        overflow:hidden;
    }
    .password-strength__bar{
        width:0%;
        height:100%;
        border-radius:999px;
        transition:width .2s ease, background-color .2s ease;
        background:#dc3545;
    }
    .password-checklist{
        list-style:none;
        margin:0;
        padding:0;
    }
    .password-checklist li{
        font-size:.87rem;
        color:#5d6772;
        margin-bottom:.25rem;
    }
    .password-checklist li i{margin-right:.35rem}
    .password-checklist li.is-valid{color:#1f7a4a}
    .password-checklist li.is-valid i{color:#1f7a4a}
    .password-checklist li.is-invalid{color:#a43b45}
    .password-checklist li.is-invalid i{color:#a43b45}
    .toggle-password-btn{border-left:0}
    .profile-history .list-group-item{border-left:0;border-right:0}
    @media (max-width: 767.98px){
        .profile-hero__brand{align-items:flex-start}
        .profile-hero__logo-wrap{width:64px;height:64px}
        .profile-hero__logo{width:44px}
        .profile-hero__title{font-size:1.1rem}
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emailInput = document.getElementById('email');
    const feedback = document.getElementById('email-live-feedback');
    const saveButton = document.getElementById('profile-save-btn');
    const profileForm = emailInput ? emailInput.closest('form') : null;

    if (!emailInput || !feedback || !saveButton || !profileForm) {
        return;
    }

    const knownDomains = ['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 'icloud.com', 'live.com', 'epsianaswayuu.com'];

    const levenshtein = function (a, b) {
        const matrix = Array.from({ length: b.length + 1 }, () => []);
        for (let i = 0; i <= b.length; i++) matrix[i][0] = i;
        for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                const cost = b.charAt(i - 1) === a.charAt(j - 1) ? 0 : 1;
                matrix[i][j] = Math.min(
                    matrix[i - 1][j] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j - 1] + cost
                );
            }
        }
        return matrix[b.length][a.length];
    };

    const suggestDomain = function (domain) {
        if (!domain || knownDomains.includes(domain)) {
            return null;
        }

        let suggestion = null;
        let minDistance = 99;

        knownDomains.forEach(function (known) {
            const distance = levenshtein(domain, known);
            if (distance < minDistance) {
                minDistance = distance;
                suggestion = known;
            }
        });

        return minDistance <= 2 ? suggestion : null;
    };

    const setFeedback = function (message, type) {
        if (!message) {
            feedback.className = 'form-text d-none';
            feedback.textContent = '';
            return;
        }

        feedback.className = 'form-text d-block ' + (type === 'ok' ? 'text-success' : 'text-danger');
        feedback.textContent = message;
    };

    const setSaveState = function (isValid) {
        saveButton.disabled = !isValid;
        saveButton.classList.toggle('disabled', !isValid);
        saveButton.setAttribute('aria-disabled', !isValid ? 'true' : 'false');
    };

    const validateLive = function () {
        const raw = emailInput.value || '';
        const email = raw.trim().toLowerCase();

        if (raw === '') {
            setFeedback('', 'ok');
            setSaveState(false);
            return false;
        }

        if (/\s/.test(raw)) {
            setFeedback('El correo no puede contener espacios.', 'error');
            setSaveState(false);
            return false;
        }

        const parts = email.split('@');
        if (parts.length !== 2) {
            setFeedback('El correo debe contener un solo simbolo @.', 'error');
            setSaveState(false);
            return false;
        }

        const local = parts[0];
        const domain = parts[1];

        if (!local || !domain) {
            setFeedback('El correo debe tener el formato usuario@dominio.com.', 'error');
            setSaveState(false);
            return false;
        }

        if (local.startsWith('.') || local.endsWith('.') || local.includes('..') || domain.includes('..')) {
            setFeedback('El correo no puede tener puntos invalidos.', 'error');
            setSaveState(false);
            return false;
        }

        if (!domain.includes('.')) {
            setFeedback('El dominio del correo debe incluir una extension valida, por ejemplo .com.', 'error');
            setSaveState(false);
            return false;
        }

        if (/[^a-z0-9.-]/i.test(domain)) {
            setFeedback('El dominio del correo tiene caracteres no permitidos.', 'error');
            setSaveState(false);
            return false;
        }

        const suggestion = suggestDomain(domain);
        if (suggestion) {
            setFeedback('Parece que el dominio esta mal escrito. Quiza quisiste escribir ' + suggestion + '.', 'error');
            setSaveState(false);
            return false;
        }

        setFeedback('Formato de correo valido.', 'ok');
        setSaveState(true);
        return true;
    };

    profileForm.addEventListener('submit', function (event) {
        if (!validateLive()) {
            event.preventDefault();
            emailInput.focus();
        }
    });

    emailInput.addEventListener('input', validateLive);
    emailInput.addEventListener('blur', validateLive);
    validateLive();

    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const passwordSaveButton = document.getElementById('password-save-btn');
    const passwordForm = document.getElementById('password-form');
    const passwordFeedback = document.getElementById('password-live-feedback');
    const passwordStrengthBar = document.getElementById('password-strength-bar');
    const passwordStrengthLabel = document.getElementById('password-strength-label');
    const togglePasswordButtons = document.querySelectorAll('.toggle-password-btn');

    const ruleElements = {
        length: document.getElementById('rule-length'),
        upper: document.getElementById('rule-upper'),
        lower: document.getElementById('rule-lower'),
        number: document.getElementById('rule-number'),
        symbol: document.getElementById('rule-symbol'),
        space: document.getElementById('rule-space'),
        match: document.getElementById('rule-match'),
        current: document.getElementById('rule-current'),
        different: document.getElementById('rule-different')
    };

    if (
        !currentPasswordInput || !newPasswordInput || !passwordConfirmationInput ||
        !passwordSaveButton || !passwordForm || !passwordFeedback || !passwordStrengthBar || !passwordStrengthLabel
    ) {
        return;
    }

    togglePasswordButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.querySelector(btn.getAttribute('data-target') || '');
            if (!target) {
                return;
            }
            const show = target.type === 'password';
            target.type = show ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            }
        });
    });

    const setRuleState = function (element, isValid, touched) {
        if (!element) {
            return;
        }

        element.classList.remove('is-valid', 'is-invalid');

        const icon = element.querySelector('i');
        if (!touched) {
            if (icon) {
                icon.className = 'far fa-circle';
            }
            return;
        }

        if (isValid) {
            element.classList.add('is-valid');
            if (icon) {
                icon.className = 'fas fa-check-circle';
            }
        } else {
            element.classList.add('is-invalid');
            if (icon) {
                icon.className = 'fas fa-times-circle';
            }
        }
    };

    const setPasswordButtonState = function (isValid) {
        passwordSaveButton.disabled = !isValid;
        passwordSaveButton.classList.toggle('disabled', !isValid);
        passwordSaveButton.setAttribute('aria-disabled', !isValid ? 'true' : 'false');
    };

    const setPasswordFeedback = function (message, type) {
        if (!message) {
            passwordFeedback.className = 'form-text d-none';
            passwordFeedback.textContent = '';
            return;
        }

        passwordFeedback.className = 'form-text d-block ' + (type === 'ok' ? 'text-success' : 'text-danger');
        passwordFeedback.textContent = message;
    };

    const evaluatePasswordRules = function () {
        const current = currentPasswordInput.value || '';
        const password = newPasswordInput.value || '';
        const confirmation = passwordConfirmationInput.value || '';
        const touched = current.length > 0 || password.length > 0 || confirmation.length > 0;

        const rules = {
            length: password.length >= 10,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /\d/.test(password),
            symbol: /[^A-Za-z0-9\s]/.test(password),
            space: !/\s/.test(password) && password.length > 0,
            match: confirmation.length > 0 && password === confirmation,
            current: current.length > 0,
            different: current.length > 0 && password.length > 0 && current !== password
        };

        Object.keys(rules).forEach(function (key) {
            setRuleState(ruleElements[key], rules[key], touched);
        });

        const passedCount = Object.values(rules).filter(Boolean).length;
        const score = Math.round((passedCount / 9) * 100);
        passwordStrengthBar.style.width = score + '%';

        if (score < 40) {
            passwordStrengthBar.style.backgroundColor = '#dc3545';
            passwordStrengthLabel.textContent = 'Fortaleza: debil';
        } else if (score < 75) {
            passwordStrengthBar.style.backgroundColor = '#fd7e14';
            passwordStrengthLabel.textContent = 'Fortaleza: media';
        } else {
            passwordStrengthBar.style.backgroundColor = '#28a745';
            passwordStrengthLabel.textContent = 'Fortaleza: fuerte';
        }

        const isStrongAndValid = Object.values(rules).every(Boolean);
        setPasswordButtonState(isStrongAndValid);

        if (!touched) {
            setPasswordFeedback('', 'ok');
            passwordStrengthLabel.textContent = 'Fortaleza: sin evaluar';
            return false;
        }

        if (isStrongAndValid) {
            setPasswordFeedback('Contrasena segura. Ya puedes actualizar.', 'ok');
            return true;
        }

        setPasswordFeedback('La contrasena aun no cumple todos los criterios de seguridad.', 'error');
        return false;
    };

    passwordForm.addEventListener('submit', function (event) {
        if (!evaluatePasswordRules()) {
            event.preventDefault();
            newPasswordInput.focus();
        }
    });

    currentPasswordInput.addEventListener('input', evaluatePasswordRules);
    newPasswordInput.addEventListener('input', evaluatePasswordRules);
    passwordConfirmationInput.addEventListener('input', evaluatePasswordRules);
    evaluatePasswordRules();
});
</script>
@stop

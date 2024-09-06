<style>
    /* Estilos personalizados para los mensajes */
    .custom-alert {
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        position: relative;
        font-size: 16px;
    }

    .custom-alert .close {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 20px;
        cursor: pointer;
    }

    .alert-primary {
        background-color: #cce5ff;
        border-color: #b8daff;
        color: #004085;
    }

    .alert-primary .fas {
        color: #004085;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-danger .fas {
        color: #721c24;
    }

    .alert ul {
        padding-left: 20px;
    }

    .alert ul li {
        margin-bottom: 5px;
    }

    /* Agregamos animaciones */
    .custom-alert {
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Opcional: íconos de FontAwesome */
    .fas {
        margin-right: 10px;
    }
</style>


@if(Session::has('mensaje'))
<div class="alert alert-primary custom-alert" role="alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-check-circle"></i> <!-- Icono llamativo -->
    <strong>{{ Session::get('mensaje') }}</strong>
</div>
@endif

@if(Session::has('error1'))
<div class="alert alert-danger custom-alert" role="alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-exclamation-circle"></i> <!-- Icono llamativo -->
    <strong>{{ Session::get('error1') }}</strong>
</div>
@endif

@if(count($errors) > 0)
<div class="alert alert-danger custom-alert" role="alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-exclamation-triangle"></i> <!-- Icono llamativo -->
    <ul>
        @foreach($errors->all() as $error)
        <li><strong>{{ $error }}</strong></li>
        @endforeach
    </ul>
</div>
@endif



{{-- @if(Session::has('mensaje'))
<div class="alert alert-primary custom-alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{Session::get('mensaje')}}
</div>
@endif

@if(Session::has('error1'))
<div class="alert alert-danger custom-alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{Session::get('error1')}}
</div>
@endif

@if(count($errors)>0)
<div class="alert alert-danger custom-alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif --}}

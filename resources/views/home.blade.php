 @extends('adminlte::page'){{-- , ['iFrameEnabled' => true] --}}

@section('content')

@if(Session::has('error1'))

<div class="alert alert-danger">

<button type="button" class="close" data-dismiss="alert">
&times;
	
</button>
	{{Session::get('error1')}}
</div>

@endif

<section class="content">
    <div class="container-fluid">
        <p></p>
        <div class="row" >
            <div class="col-lg-3 col-6" onclick="Redirectp()">
                <!-- Funcion para Redirijir-->
            <script>function Redirectp() { location.href = "/Desnutricion/public/sivigila"; }</script>

                <div class="small-box border bg-gradient-primary">
                    <div class="inner">
                        <h3 class="text-dark">sivigila</h3>
                        <p></p>
                    </div>
                    
                    <div class="icon">
                        <i class="fa fa-user-circle position-static"></i>
                    </div>
                </div>
               
            </div>


            {{-- @if(auth()->user()->usertype == 5 ) --}}
            <div class="col-lg-3 col-6" onclick="RedirectI()" >
            <script>function RedirectI() { location.href = "/Desnutricion/public/Ingreso/create"; }</script>

                <div class="small-box border bg-success">
                    <div class="inner">
                        <h3 class="text-dark">Ingreso</h3>
                        <p></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa fa-calendar position-static"></i>
                    </div>
                </div>
            </div>
            {{-- @else

            <h1><strong>NO TIENE ACCESO A ESTA SECCION </strong></h1>
            
            @endif --}}

            
            <div class="col-lg-3 col-6 " onclick="RedirectS()">
            <script>function RedirectS() { location.href = "Ingreso/create"; }</script>

                <div class="small-box border bg-gradient-danger">
                    <div class="inner">
                        <h3 class="text-dark">Alertas</h3>
                        <p></p>
                    </div>
                    <div class="icon">
                       <i class=" fa fa-window-close position-static "></i>
                    </div>
                
                </div>
</div>

        </div>
@endsection

@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

@stop
@section('content')



    <br> <br>
    <div class="card border-info mb-3" >
        <div class="card-header bg-success">DETALLE DEL SEGUIMIENTO DE: {{$seguimientoshow->pri_nom_.' '.$seguimientoshow->seg_nom_.' '.$seguimientoshow->pri_ape_.' '.$seguimientoshow->seg_ape_}}</div>
        <div class="card-body">
            <h6 class="card-title"> <strong>Numero de identificacion: {{$seguimientoshow->num_ide_}}</h6></strong> 
            <p class="card-text">
            <br>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <strong>  MOTIVO DE REAPERTURA DEL CASO: </strong>{{$seguimientodetail->motivo_reapuertura}}

                    </div>
                    </div>
                </div> 
             </div>
            </div>



@stop
            
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop

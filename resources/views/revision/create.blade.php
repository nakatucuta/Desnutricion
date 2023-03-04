
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')



@stop

@section('content')
<br> <br>
<div class="card border-info mb-3" >
    <div class="card-header bg-primary">Revisar seguimientos</div>
    <div class="card-body">
      <h6 class="card-title">Detalle de seguimiento:</h6>
      <p class="card-text">
        <br>
        <div class="row">
                <div class="col-sm-4">
                <div class="form-group">
                        <strong>ID</strong>: {{$segene->id }}
                        <br>
                        <strong>Identificacion</strong>: {{$segene2 }}
                </div>
                </div>
                    <div class="col-sm-4">
                    <div class="form-group">
                            <strong>Nombre</strong>: {{$segene1 }}
                            <br>
                            <strong>Fecha consulta</strong>: {{$segene->fecha_consulta }}
                    </div>
                    </div>

                        <div class="col-sm-4">
                        <div class="form-group">
                                <strong>Peso en kilos</strong>: {{$segene->peso_kilos }}
                                <br>
                                <strong>Talla cm</strong>: {{$segene->talla_cm }}
                        </div>
                        </div>

                
              
                    </div>
                    <div class="row">

                        <div class="col-sm-4">
                        <div class="form-group">
                                <strong>Puntaje Z</strong>: {{$segene->puntajez }}
                                <br>
                                <strong>Clasificacion</strong>: {{$segene->clasificacion }}
                        </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                    <strong>Requerimiento de energia ftlc</strong>: {{$segene->requerimiento_energia_ftlc }}
                                    <br>
                                    <strong>Fecha de entrega ftlc</strong>: {{$segene->fecha_entrega_ftlc }}
                            </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                        <strong>Medicamento</strong>: {{$segene->medicamento }}
                                        <br>
                                        <strong>Recomendaciones de manejo</strong>: {{$segene->recomendaciones_manejo }}
                                </div>
                                </div>
                    
                
                                </div>  


        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                        <strong>Resultado de seguimientos</strong>: {{$segene->resultados_seguimientos }}
                        <br>
                        <strong>Observaciones</strong>: {{$segene->observaciones }}
                </div>
                </div>


                <div class="col-sm-4">
                    <div class="form-group">
                            <strong>Estado actual del menor</strong>: {{$segene->est_act_menor }}
                            <br>
                            <strong>Tratamiento f75</strong>: {{$segene->tratamiento_f75 }}
                    </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                                <strong>Fecha en la que recibio f75</strong>: {{$segene->fecha_recibio_tratf75 }}
                                <br>
                                <strong>Fecha de proximo control</strong>: {{$segene->fecha_proximo_control }}
                        </div>
                        </div>

                        </div> 

                        <form action="{{url('/revision')}}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group" hidden>
                                <label for="Nombre">Regimen</label>
                                <input class="form-control" type="text" name="seguimientos_id" 
                                id="seguimientos_id" value="{{$segene->id}}" readonly>
                            </div>
                        
                            <input class="btn btn-success" type="submit" value="enviar" onclick="return confirm('¿Estás seguro de que deseas enviar el formulario?');">
                            <a  class="btn btn-primary" href="{{route('revision.index')}}" class="btn  btn-success"> REGRESAR</a>
                        
                            </form>
                        
            
            

        </div>
  </div>


@stop
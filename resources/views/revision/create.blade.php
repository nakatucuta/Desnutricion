
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')



@stop

@section('content')
@foreach($segene as $student2)
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
                        <strong>ID</strong>: {{ $student2->id }}
                        
                        <br>
                        <strong>Identificacion</strong>: {{ $student2->num_ide_ }}
                </div>
                </div>
                    <div class="col-sm-4">
                    <div class="form-group">
                            <strong>Nombre</strong>: {{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.$student2->seg_ape_ }}
                            <br>
                            <strong>Fecha consulta</strong>: {{ $student2->fecha_consulta }}
                    </div>
                    </div>

                        <div class="col-sm-4">
                        <div class="form-group">
                                <strong>Peso en kilos</strong>: {{ $student2->peso_kilos }}
                                <br>
                                <strong>Talla cm</strong>: {{ $student2->talla_cm }}
                        </div>
                        </div>

                
              
                    </div>
                    <div class="row">

                        <div class="col-sm-4">
                        <div class="form-group">
                                <strong>Puntaje Z</strong>: {{$student2->puntajez}}
                                <br>
                                <strong>Clasificacion</strong>: {{$student2->clasificacion}}
                        </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                    <strong>Requerimiento de energia ftlc</strong>: {{$student2->requerimiento_energia_ftlc}}
                                    <br>
                                    <strong>Fecha de entrega ftlc</strong>: {{$student2->fecha_entrega_ftlc}}
                            </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                        <strong>Medicamento</strong>: {{$student2->medicamento}}
                                        <br>
                                        {{-- <strong>Recomendaciones de manejo</strong>: {{$segene->recomendaciones_manejo }} --}}
                                </div>
                                </div>
                    
                
                                </div>  


        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                        {{-- <strong>Resultado de seguimientos</strong>: {{$segene->resultados_seguimientos }} --}}
                        <br>
                        <strong>Observaciones</strong>: {{$student2->observaciones}}
                </div>
                </div>


                <div class="col-sm-4">
                    <div class="form-group">
                            <strong>Estado actual del menor</strong>: {{$student2->est_act_menor}}
                            <br>
                            <strong>Tratamiento f75</strong>: {{$student2->tratamiento_f75}}
                    </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
                                <strong>Fecha en la que recibio f75</strong>: {{$student2->fecha_recibio_tratf75}}
                                <br>
                                <strong>Fecha de proximo control</strong>: {{$student2->fecha_proximo_control}}
                        </div>
                        </div>

                        </div> 
                       
                       
            
            

        </div>
  </div>
  @endforeach 
  <form action="{{url('/revision')}}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="form-group" hidden>
            <label for="Nombre">Regimen</label>
            <input class="form-control" type="text" name="seguimientos_id" 
            id="seguimientos_id" value="{{$student2->id}}" readonly>
        </div>
    
        <input class="btn btn-success" type="submit" value="enviar" onclick="return confirm('¿Estás seguro de que deseas enviar el formulario?');">
        <a  class="btn btn-primary" href="{{route('revision.index')}}" class="btn  btn-success"> REGRESAR</a>
    
        </form>
    
@stop

@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

@stop
@section('content')

<form action="{{url('/Seguimiento/'.$empleado->id)}}" method="post" enctype="multipart/form-data">
    @csrf
    {{method_field('PATCH')}}
    
   
    




    <br>
    @include('seguimiento.mensajes')
    
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-info card-outline card-tabs">
                <div class="card-header">
                    <h2 class="card-title text-center">
                          <i class="far fa-hospital" style="font-size: 45px; color: #3333ff; "></i>
                          Seguimiento
                          <i class="bi bi-plus"></i>
                          <i class="fas fa-user-md" style="font-size: 45px; color: #3333ff;"></i>
                    </h2>
    
                    
                </div>
                
                <div class="card-body">
    
                    <div class="row">
                        <div class="col-md-6 ">
                            <div class="form-group">
                                <label for="Nombre">Paciente</label>
                            <select class="person " name="sivigilas_id" id="sivigilas_id"  style="width: 100%">
                                
                                @foreach($incomeedit as $developer)
                                <option  value="{{$developer->idin }}">{{$developer->idin.' '.$developer->num_ide_.' '.$developer->pri_nom_.' '.$developer->seg_nom_.' '.$developer->pri_ape_.' '.$developer->seg_ape_ }}</option>
                                @endforeach
                                
                              </select>
                        </div>
                    </div>
    
    
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="Nombre">Fecha Consulta</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                            <input class="form-control" type="date" name="fecha_consulta" id = 'fecha_consulta'
                            value="{{$empleado->fecha_consulta}}">
                        </div>
                        </div>
                 </div>
    
    
               
                    <div class="row">
    
    
    
    
    
    
    
    </div>
    
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="Nombre"> Talla en centimetros</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <input class="form-control" type="number" step="0.01" name="talla_cm" id = 'talla_cm'
                value="{{$empleado->talla_cm}}">
            </div>
            </div>
    
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="Nombre">Peso En Kilos y un decimal</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                    <input class="form-control" type="number" step="0.0001" name="peso_kilos" id = 'peso_kilos'
                    value="{{$empleado->peso_kilos}}">
                </div>
                </div>
        </div>
    
     <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="Nombre">Calificacion </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                    <select class="person2 " name="clasificacion" id="clasificacion"  style="width: 100% ">
                        
                        <option  value="{{$empleado->clasificacion}}">{{$empleado->clasificacion}}</option>
                       
                      </select>
                </div>
                </div>
            
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="Nombre">Puntaje z (peso / talla)</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                        <input class="form-control" type="number" step="0.0001" name="puntajez" id = 'puntajez'
                        value="{{$empleado->puntajez}}">
                    </div>
                    </div>                       
            
         
            </div>
    
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="Nombre"> Fecha de entrega FTLC </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                        <input class="form-control" type="date" name="fecha_entrega_ftlc" id = 'fecha_entrega_ftlc'
                        value="{{$empleado->fecha_entrega_ftlc}}">
                    </div>
                    </div>
            
            
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="Nombre"> Requerimiento De Energia FTLC <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                           <input class="form-control" type="text" name="requerimiento_energia_ftlc" id = 'requerimiento_energia_ftlc'
                           value="{{$empleado->requerimiento_energia_ftlc}}">
                               </div>
                               </div>
    
                            </div>
                            <div class="row">
    
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="Nombre"> Observaciones </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                        

                                        <textarea name="observaciones" id="observaciones" 
                                         class="form-control" rows="5" maxlength="600">{{$empleado->observaciones}}</textarea>
                 
                                    </div>
                                    </div>
    
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="medicamento">Medicamento</label>
                                            <select class="js-example-basic-multiple" name="medicamento[]" multiple="multiple" style="width: 100%">
                                                <option value="23072-2" {{ in_array('23072-2', explode(',', $empleado->medicamento)) ? 'selected' : '' }}>albendazol 200MG</option>
                                                <option value="54114-1" {{ in_array('54114-1', explode(',', $empleado->medicamento)) ? 'selected' : '' }}>albendazol 400MG</option>
                                                <option value="35662-18" {{ in_array('35662-18', explode(',', $empleado->medicamento)) ? 'selected' : '' }}>Acido folico</option>
                                                <option value="31063-1" {{ in_array('31063-1', explode(',', $empleado->medicamento)) ? 'selected' : '' }}>Vitamina A</option>
                                                <option value="27440-3" {{ in_array('27440-3', explode(',', $empleado->medicamento)) ? 'selected' : '' }}>Hierro</option>
                                                <!-- Agrega más opciones aquí -->
                                            </select>
                                        </div>
                                    </div>
                                    
                                    
                                    
                                    
                                        </div>
    
                                        <div class="row">
                                            
                                    
                                            <div class="col-sm-6">
                                                
                                                </div>
                                                <div class="col-sm-6">
                                                    
                                                    </div>
                                               
                                                </div>
    
                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                        <label for="Nombre">Estado actual del menor</label>
                                                        <select class="person2 " name="est_act_menor" id="est_act_menor"  style="width: 100% ">
                                                        <option  value="{{$empleado->est_act_menor}}">{{$empleado->est_act_menor}}</option>
                                                       
                                                        
                                                      </select>
                                                        </div>
                                                        </div>
                                                
                                                
                                                        <div class="col-sm-4">
                                                            <div class="form-group">
                                                                <label for="Nombre">Tratamiento f75</label>
                                                                <select class="person2 " name="tratamiento_f75" id="tratamiento_f75"  style="width: 100% ">
                                                                    <option  value="{{$empleado->tratamiento_f75}}">{{$empleado->tratamiento_f75}}</option>
                                                       
                                                                
                                                                
                                                              </select>
                                                            </div>
                                                        </div>
                                                
                                                
                                                            <div class="col-sm-4">
                                                                <div class="form-group" id="input_oculto1">
                                                                    <label for="Nombre"> Fecha en la que recibe tratamiento f75 </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                    <input class="form-control" type="date" name="fecha_recibio_tratf75" id = 'fecha_recibio_tratf75'
                                                                    value="{{$empleado->fecha_recibio_tratf75}}">
                                                                </div>
                                
                                                                
                                                                </div>
                                                
                                
                                
                                                                
                                                               
                                                
                                                    </div>
                                                
                                                
                                                @if(Auth::user()->usertype == 2)

                                                @else
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group" >
                                                            <label for="Nombre">Desea modificar el estado del caso ?</label>
                                                            <select class="person2 " name="estado" id="estado"  style="width: 100% ">
                                                            <option  value="{{$empleado->estado}}"></option>
                                                            <option  value="1">ABIERTO</option>
                                                            <option  value="0">CERRADO</option>
                                                            
                                                          </select>
                                                    </div>
                                                   
                                                    </div>
                                                        <div class="col-md-6 " >
                                                        <div class="form-group" id="input_oculto">
                                                            <label for="Nombre"> Fecha Proximo Seguimiento </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                            <input class="form-control" type="date" name="fecha_proximo_control" id="fecha_proximo_control" 
                                                            value="{{$empleado->fecha_proximo_control}}">
                                                        </div>
                                                        </div>
                                                
                                        
                                             </div>


                                             <div class="row">

                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label for="Nombre"> Motivo de reapuertura </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                        
                
                                                        <textarea name="motivo_reapuertura" id="motivo_reapuertura" 
                                                         class="form-control" rows="5" maxlength="1000">{{$empleado->motivo_reapuertura}}</textarea>
                                 
                                                    </div>
                                                    </div>
                                             </div>
                                               
    
                                             @endif
                
    
                               
    
    
    <input class="btn btn-success" type="submit" value="enviar">
                <a  class="btn btn-primary" href="{{url('Seguimiento')}}" class="btn  btn-success"> REGRESAR</a>
            </div>
        </div>
            </div>
                </div>
                    </div>
                        </div>
    
                        
                            </div>
       
    
    
                        
               
    </form>

    
       
    
    @stop
            
    @section('css')
        <link rel="stylesheet" href="/css/admin_custom.css">
    @stop
    
    @section('js')
        <script>   $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
        }); </script>
    @stop
    
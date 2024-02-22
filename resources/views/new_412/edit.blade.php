@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')

@stop
@section('content')

<div class="container">
    {{-- <h2>Editar edit_cargue</h2> --}}
    <form action="{{url('/new412/'.$edit_cargue->id)}}" method="post" enctype="multipart/form-data">
        @csrf
        {{method_field('PATCH')}}

     

       
                



<div class="row">
    <div class="col-lg-12">
        <div class="card card-info card-outline card-tabs">
            <div class="card-header">
                <h2 class="card-title text-center">
                      <i class="far fa-hospital" style="font-size: 45px; color: #3333ff; "></i>
                      EDITAR
                      <i class="bi bi-plus"></i>
                      <i class="fas fa-user-md" style="font-size: 45px; color: #3333ff;"></i>
                </h2>

                
            </div>
            

            
            <div class="card-body">

                
        <div class="row">  
    
            {{-- el mensaje inicia aqui --}}
            <div class="col-sm-12">
                @if(count($income12) == 0)
                <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">
                    &times;
                </button>
            La ips primaria del afiliado no se encuentra registrada, por favor  registrarla o escoger otro prestador para realizar el seguimiento
            </div>
           {{-- el mensaje termina aqui  --}}
        
           {{-- este es el select que mostrara en caso que no encuentre el cod habilitacin de este usuario --}}
        <div class="form-group">
            <label for="Nombre">Ips seguimiento Ambulatorio</label>
            <select class="person2 " name="user_id" id="user_id"  style="width: 100% ">
                  <option  value="0">{{--REGISTRAR USUARIO--}}</option>  
                 @foreach($incomeedit15 as $developer)
                 <option  value="{{$developer->id}}">{{$developer->codigohabilitacion.' '.$developer->name}}</option>
                 @endforeach 
            </select>
        </div>
        {{-- aqui termina el select en caso de error --}}
                @else
              
                {{-- si encuentra el el prestador muestre este  --}}
                 <div class="form-group">
                    <label for="Nombre">Ips seguimiento Ambulatorio</label>
                    <select class="person2 " name="user_id" id="user_id"  style="width: 100% ">
                        {{-- <option  value="0">SELECCIONAR</option> --}}
                        
                      
                        @foreach($income12 as $developer)
                            <option value="{{$developer->id}}">{{$developer->codigohabilitacion.' '.$developer->name }}</option>
                        @endforeach
                        @foreach($incomeedit15 as $developer)
                        <option  value="{{$developer->id}}">{{$developer->codigohabilitacion.' '.$developer->name}}</option>
                        @endforeach
                        
                    </select>
                </div>
                @endif
        
               
                 </div> </div> 



                <div class="row">
                    <div class="col-md-3 ">
                        <div class="form-group">
                            <label for="Nombre"> Numero_orden <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                           <input class="form-control" type="text" name="numero_orden" id = 'numero_orden'
                           value="{{$edit_cargue->numero_orden}}">
                        </div>
                        </div>
               


                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="Nombre"> Nombre_coperante <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                        <input class="form-control" type="text" name="nombre_coperante" id = 'nombre_coperante'
                        value="{{$edit_cargue->nombre_coperante}}">
    
                    </div>
             </div>


             <div class="col-sm-3">
                <div class="form-group">
                    <label for="Nombre"> Fecha de captacion </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                    <input class="form-control" type="date" name="fecha_captacion" id = 'fecha_captacion'
                    value="{{$edit_cargue->fecha_captacion}}">

                </div>
         </div>


         <div class="col-sm-3">
            <div class="form-group">
                <label for="Nombre">Municipio</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <input class="form-control" type="text"  name="municipio" id = 'municipio'
                value="{{$edit_cargue->municipio}}">

            </div>
     </div>
            </div>

           
               

<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <label for="Nombre">Nombre rancheria</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
            <input class="form-control" type="text"  name="nombre_rancheria" id = 'nombre_rancheria'
            value="{{$edit_cargue->nombre_rancheria}}">
        </div>
        </div>

        <div class="col-sm-3">
            <div class="form-group">
                <label for="Nombre">Ubicacion casa</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <input class="form-control" type="text"  name="ubicacion_casa" id = 'ubicacion_casa'
                value="{{$edit_cargue->ubicacion_casa}}">
            </div>
            </div>


            <div class="col-sm-3">
                <div class="form-group">
                    <label for="Nombre"> Nombre cuidador <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                    <input class="form-control" type="text" name="nombre_cuidador" id = 'nombre_cuidador'
                    value="{{$edit_cargue->nombre_cuidador}}">
                </div>
                </div>


                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="Nombre"> Identificacion cuidador <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                   <input class="form-control" type="text" name="identioficacion_cuidador" id = 'identioficacion_cuidador'
                                   value="{{$edit_cargue->identioficacion_cuidador}}">
                    </div>
                    </div>
    </div>

    <div class="row">
                            
        <div class="col-sm-3">
            <div class="form-group">
                <label for="Nombre"> Telefono cuidador <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                       <input class="form-control" type="text" name="telefono_cuidador" id = 'telefono_cuidador'
                                       value="{{$edit_cargue->telefono_cuidador}}">
            </div>
            </div>       
            
            

            <div class="col-sm-3">
                <div class="form-group">
                    <label for="Nombre"> nombre_autoridad_trad_ansestral <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                   <input class="form-control" type="text" name="nombre_autoridad_trad_ansestral" id = 'nombre_autoridad_trad_ansestral'
                   value="{{$edit_cargue->nombre_autoridad_trad_ansestral}}">
                       </div>
                </div>


                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="Nombre"> primer_nombre <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                       <input class="form-control" type="text" name="primer_nombre" id = 'primer_nombre'
                       value="{{$edit_cargue->primer_nombre}}">
                           </div>
                </div>


                <div class="col-sm-3">
                    <div class="form-group">
                       <label for="Nombre"> segundo_nombre <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                      <input class="form-control" type="text" name="segundo_nombre" id = 'segundo_nombre'
                      value="{{$edit_cargue->segundo_nombre}}">
                        </div>


    </div>   
</div>                   
                               
                       

                     <div class="row">
                        
                        
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="Nombre"> primer_apellido <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                               <input class="form-control" type="text" name="primer_apellido" id = 'primer_apellido'
                               value="{{$edit_cargue->primer_apellido}}">
                                   </div>
                                    </div>


                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="Nombre"> segundo_apellido <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                               <input class="form-control" type="text" name="segundo_apellido" id = 'segundo_apellido'
                               value="{{$edit_cargue->segundo_apellido}}">
                                   </div>
                                    </div>
                                                    
                                            
                                            
                                                    
                                            
                                            
                                                        <div class="col-sm-3">
                                                            <div class="form-group">
                                                                <label for="Nombre"> tipo_identificacion <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                               <input class="form-control" type="text" name="tipo_identificacion" id = 'tipo_identificacion'
                                                               value="{{$edit_cargue->tipo_identificacion}}">
                                                                   </div>
                            
                                                            
                                                            </div>
                                            
                            
                            
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <label for="Nombre"> numero_identificacion <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                   <input class="form-control" type="text" name="numero_identificacion" id = 'numero_identificacion'
                                                                   value="{{$edit_cargue->numero_identificacion}}">
                                                                       </div>
                                                           
                                                            </div>
                                                           
                                            
                                                </div>
                                            
                                            
                                         

                                         
                                            <div class="row">
                                               
                                                    <div class="col-md-3" >
                                                    <div class="form-group">
                                                        <label for="Nombre"> sexo <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                       <input class="form-control" type="text" name="sexo" id = 'sexo'
                                                       value="{{$edit_cargue->sexo}}">
                                                           </div>
                                                    </div>

                                                    <div class="col-md-3" >
                                                        <div class="form-group">
                                                            <label for="Nombre"> fecha_nacimieto_nino <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                           <input class="form-control" type="date" name="fecha_nacimieto_nino" id = 'fecha_nacimieto_nino'
                                                           value="{{$edit_cargue->fecha_nacimieto_nino}}">
                                                               </div>
                                                        </div>
                                            
                                                        <div class="col-md-3" >
                                                            <div class="form-group">
                                                                <label for="Nombre"> edad_meses <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                               <input class="form-control" type="text" name="edad_meses" id = 'edad_meses'
                                                               value="{{$edit_cargue->edad_meses}}">
                                                                   </div>
                                                            </div>


                                                            <div class="col-md-3" >
                                                                <div class="form-group">
                                                                    <label for="Nombre"> calsificacion_antropometrica <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                   <input class="form-control" type="text" name="calsificacion_antropometrica" id = 'calsificacion_antropometrica'
                                                                   value="{{$edit_cargue->calsificacion_antropometrica}}">
                                                                       </div>
                                                                </div>
                        
                                         </div>


                                         <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Nombre"> regimen_afiliacion <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                   <input class="form-control" type="text" name="regimen_afiliacion" id = 'regimen_afiliacion'
                                                   value="{{$edit_cargue->regimen_afiliacion}}">
                                                       </div>
                                           
                                            </div>
                                                <div class="col-md-3" >
                                                <div class="form-group">
                                                    <label for="Nombre"> nombre_eapb_menor <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                   <input class="form-control" type="text" name="nombre_eapb_menor" id = 'nombre_eapb_menor'
                                                   value="{{$edit_cargue->nombre_eapb_menor}}">
                                                       </div>
                                                </div>

                                                <div class="col-md-3" >
                                                    <div class="form-group">
                                                        <label for="Nombre"> peso_kg <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                       <input class="form-control" type="date" name="logitud_talla_cm" id = 'logitud_talla_cm'
                                                       value="{{$edit_cargue->logitud_talla_cm}}">
                                                           </div>
                                                    </div>
                                        
                                                    <div class="col-md-3" >
                                                        <div class="form-group">
                                                            <label for="Nombre"> logitud_talla_cm <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                           <input class="form-control" type="text" name="logitud_talla_cm" id = 'edad_meses'
                                                           value="{{$edit_cargue->edad_meses}}">
                                                               </div>
                                                        </div>
                                     </div>

                                           
                                     
                                     <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="Nombre"> perimetro_braqueal <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                               <input class="form-control" type="text" name="perimetro_braqueal" id = 'perimetro_braqueal'
                                               value="{{$edit_cargue->perimetro_braqueal}}">
                                                   </div>
                                       
                                        </div>
                                            <div class="col-md-3" >
                                            <div class="form-group">
                                                <label for="Nombre"> signos_peligro_infeccion_respiratoria <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                               <input class="form-control" type="text" name="signos_peligro_infeccion_respiratoria" id = 'signos_peligro_infeccion_respiratoria'
                                               value="{{$edit_cargue->signos_peligro_infeccion_respiratoria}}">
                                                   </div>
                                            </div>

                                            <div class="col-md-3" >
                                                <div class="form-group">
                                                    <label for="Nombre"> sexosignos_desnutricion <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                   <input class="form-control" type="text" name="sexosignos_desnutricion" id = 'sexosignos_desnutricion'
                                                   value="{{$edit_cargue->sexosignos_desnutricion}}">
                                                       </div>
                                                </div>
                                    
                                                <div class="col-md-3" >
                                                    <div class="form-group">
                                                        <label for="Nombre"> puntaje_z <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                       <input class="form-control" type="text" name="puntaje_z" id = 'puntaje_z'
                                                       value="{{$edit_cargue->puntaje_z}}">
                                                           </div>
                                                    </div>
                                 </div>



                                 <div class="row">
                                   
                                </div>

                                         
            

                           


<input class="btn btn-success" type="submit" value="ACTUALIZAR">
            <a  class="btn btn-primary" href="{{url('import-excel')}}" class="btn  btn-success"> REGRESAR</a>
        </div>
    </div>
        </div>
            </div>
                </div>
                    </div>

                    
                        </div>
   


                    
           
</form>
@endsection
            
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>   $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    }); </script>
@stop


@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
formulario de edicion 
@stop
@section('content')

<form action="{{url('/Ingreso/'.$empleado->id)}}" method="post" enctype="multipart/form-data">
    @csrf
    {{method_field('PATCH')}}
    
   
    




<div class="row">
    <div class="col-lg-12">
        <div class="card card-info card-outline card-tabs">
            <div class="card-header">
                <h2 class="card-title text-center">
                      <i class="far fa-hospital" style="font-size: 45px; color: #3333ff; "></i>
                      DATOS PRIMER INGRESO
                      
                      {{-- {{$master}} --}}
                      
                      <i class="bi bi-plus"></i>
                      <i class="fas fa-user-md" style="font-size: 45px; color: #3333ff;"></i>
                </h2>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-2 col-md-offset-0">
                        <div class="form-group">
                        <select class="person " name="sivigilas_id" id="sivigilas_id"  style="width: 200%">
                            
                            @foreach($students2 as $categoria)
                            <option value="{{$categoria->id}}" {{($categoria->id == $empleado->sivigilas_id)?'selected':''}}>{{$categoria->pri_nom_.' '.$categoria->seg_nom_.' '.$categoria->pri_ape_}}</option>
                            @endforeach
                          </select>
                    </div>
                </div>
             </div>

                <div class="row">



<div class="col-sm-6">
<div class="form-group">
    <label for="Nombre">Fecha Atencion ingreso</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="date" name="Fecha_ingreso_ingres" id = 'Fecha_ingreso_ingres'
    value="{{ isset($empleado->Fecha_ingreso_ingres)?$empleado->Fecha_ingreso_ingres:old('Fecha_ingreso_ingres')}}">
</div>
</div>

<div class="col-sm-6">
<div class="form-group">
    <label for="Nombre">Peso En Kilos y un decimal</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="peso_ingres" id = 'peso_ingres'
    value="{{ isset($empleado->peso_ingres)?$empleado->peso_ingres:old('peso_ingres')}}">
</div>
</div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="Nombre"> Talla en cms y un decimal </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
            <input class="form-control" type="text" name="talla_ingres" id = 'talla_ingres'
            value="{{ isset($empleado->talla_ingres)?$empleado->talla_ingres:old('talla_ingres')}}">
        </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label for="Nombre">Puntaje z (peso / talla)</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <input class="form-control" type="text" name="puntaje_z" id = 'puntaje_z'
                value="{{ isset($empleado->puntaje_z)?$empleado->puntaje_z:old('puntaje_z')}}">
            </div>
            </div>
            </div>


            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="Nombre">Calificacion </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                        <input class="form-control" type="text" name="calificacion" id = 'calificacion'
                        value="{{ isset($empleado->calificacion)?$empleado->calificacion:old('calificacion')}}">
                    </div>
                    </div>
                
                                               
                
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="Nombre"> Edema <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                            <input class="form-control" type="text" name="Edema" id = 'Edema'
                            value="{{ isset($empleado->Edema)?$empleado->Edema:old('Edema')}}">
                        </div>
                        </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="Nombre">Calificacion </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                    <input class="form-control" type="text" name="calificacion" id = 'calificacion'
                                    value="{{ isset($empleado->calificacion)?$empleado->calificacion:old('calificacion')}}">
                                </div>
                                </div>
                            
                                                           
                            
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="Nombre"> Edema <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                        <input class="form-control" type="text" name="Edema" id = 'Edema'
                                        value="{{ isset($empleado->Edema)?$empleado->Edema:old('Edema')}}">
                                    </div>
                                    </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="Nombre"> Emaciacion </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                <input class="form-control" type="text" name="Emaciacion" id = 'Emaciacion'
                                                value="{{ isset($empleado->Emaciacion)?$empleado->Emaciacion:old('Emaciacion')}}">
                                            </div>
                                            </div>
                                    
                                    
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="Nombre"> Perimetro del brazo </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                    <input class="form-control" type="text" name="perimetro_brazo" id = 'perimetro_brazo'
                                                    value="{{ isset($empleado->perimetro_brazo)?$empleado->perimetro_brazo:old('perimetro_brazo')}}">
                                                </div>
                                                </div>
                                    
                                    
                                                
                                                    </div>

                                                    <div class="row">

                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="Nombre"> Interpretacion perimetro braqueal </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                <input class="form-control" type="text" name="interpretacion_p_braqueal" id = 'interpretacion_p_braqueal'
                                                                value="{{ isset($empleado->interpretacion_p_braqueal)?$empleado->interpretacion_p_braqueal:old('interpretacion_p_braqueal')}}">
                                                            </div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label for="Nombre"> requerimiento de energia para cubrir FTLC -kcal/dia </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                    <input class="form-control" type="text" name="requ_energia_dia" id = 'requ_energia_dia'
                                                                    value="{{ isset($empleado->requ_energia_dia)?$empleado->requ_energia_dia:old('requ_energia_dia')}}">
                                                                </div>
                                                                </div>
                                                    </div>


                                                    <div class="row">

                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="Nombre"> mes </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                <input class="form-control" type="month" name="mes_entrega_FTLC" id = 'mes_entrega_FTLC'
                                                                value="{{ isset($empleado->mes_entrega_FTLC)?$empleado->mes_entrega_FTLC:old('mes_entrega_FTLC')}}">
                                                            </div>
                                                        </div>
                                                
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="Nombre"> fecha en la que se entrega ftlc </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                <input class="form-control" type="date" name="fecha_entrega_FTLC" id = 'fecha_entrega_FTLC'
                                                                value="{{ isset($empleado->fecha_entrega_FTLC)?$empleado->fecha_entrega_FTLC:old('fecha_entrega_FTLC')}}">
                                                            </div>
                                                            </div>

                                                    </div>


                                                    <div class="row">

                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="Nombre"> menor de  5 años con desnutricion aguda cuenta con prescripcion </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                <input class="form-control" type="text" name="Menor_anos_des_aguda" id = 'Menor_anos_des_aguda'
                                                                value="{{ isset($empleado->Menor_anos_des_aguda)?$empleado->Menor_anos_des_aguda:old('Menor_anos_des_aguda')}}">
                                                            </div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label for="Nombre"> medicamentos </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                    <input class="form-control" type="text" name="medicamentos" id = 'medicamentos'
                                                                    value="{{ isset($empleado->medicamentos)?$empleado->medicamentos:old('medicamentos')}}">
                                                                </div>
                                                                </div>
                                                    </div>


                                                    <div class="row">

                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="Nombre"> se remite a alguna institucion para apoyo </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                <input class="form-control" type="text" name="remite_alguna_inst_apoyo" id = 'remite_alguna_inst_apoyo'
                                                                value="{{ $empleado->remite_alguna_inst_apoyo}}">
                                                            </div>
                                                            </div>
                                
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label for="Nombre"> Ips-ese atencion primaria </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                    <input class="form-control" type="text" name="Nom_ips_at_prim" id = 'Nom_ips_at_prim'
                                                                    value="{{ isset($empleado->Nom_ips_at_prim)?$empleado->Nom_ips_at_prim:old('Nom_ips_at_prim')}}">
                                                                </div>
                                                                </div>
                                                    </div>
    <div class="row">
        <div class="col-lg-12 ">
            <div class="card card-info card-outline ">
        <center><h6 class=""> <strong>DEMANDA INDUCIDA</strong></h6></center>

    </div>
        </div>
            </div>



<div class="row">
    <div class="col-lg-12 ">
        <div class="card card-info card-outline ">
    <center><h6 class=""> <strong>ATENCION NOMINAL</strong></h6></center>

</div>
    </div>
        </div>




    <div class="row">
    
       

           
            </div>

            <div class="row">
                


                  
                        
                             </div>

                           


                         {{-- <div class="row">
                            <div class="col-md-2 col-md-offset-0">
                                <div class="form-group">
                                     <label for="Nombre">id paciente</label> el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final 
                                    <input  class="form-control" type="text" name="remite_alguna_inst_apoyo" id = 'remite_alguna_inst_apoyo'
                                    value="{{ $empleado->sivigilas_id}}">
                                </div>
                            </div>
                        </div> --}}

<input class="btn btn-success" type="submit" value="enviar">
            <a  class="btn btn-primary" href="{{url('Ingreso')}}" class="btn  btn-success"> REGRESAR</a>
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
        <script> console.log('Hi!'); </script>
    @stop
    

@include('seguimiento.mensajes')
<title>Seguimiento Extraordinario</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
</head>
<body>
    <div class="h1 seguimiento-extraordinario">
        SOLO PARA SEGUIMIENTOS EXTRAORDINARIOS<br>
        PARA REALIZAR SEGUIMIENTOS PROGRAMADOS DAR CLICK <a href="{{ route('Seguimiento.create') }}">AQUÍ</a>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-info card-outline card-tabs">
                <div class="card-header text-center">
                    <h2 class="card-title">
                        <i class="far fa-hospital" style="font-size: 45px; color: #3333ff;"></i>
                        Seguimiento
                        <i class="bi bi-plus"></i>
                        <i class="fas fa-user-md" style="font-size: 45px; color: #3333ff;"></i>
                    </h2>
                </div>
            
            <div class="card-body">

                <div class="form-group">
                    <label for="Nombre"> MOTIVO DE SEGUIMIENTO </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                    

                    <textarea name="motivo_seguimiento" id="motivo_seguimiento" value="{{ isset($empleado->motivo_seguimiento)?$empleado->motivo_seguimiento:old('motivo_seguimiento')}}" class="form-control" rows="5" maxlength="600"></textarea>

                </div>

                <div class="row">
                    <div class="col-md-6 ">
                        <div class="form-group">
                            <label for="Nombre">Paciente</label>
                        <select class="person " name="seguimiento_id" id="seguimiento_id"  style="width: 100%">
                            
                            @foreach($consultapaciente as $developer)
                            <option  value="{{$developer->idin }}">{{$developer->idin.' '.$developer->num_ide_.' '.$developer->pri_nom_.' '.$developer->seg_nom_.' '.$developer->pri_ape_.' '.$developer->seg_ape_ }}</option>
                            @endforeach
                            
                          </select>
                    </div>
                </div>


                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="Nombre">Fecha Consulta</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                        <input class="form-control" type="date" name="fecha_consulta" id = 'fecha_consulta'
                        value="">
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
            value="">
        </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label for="Nombre">Peso En Kilos y un decimal</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <input class="form-control" type="number" step="0.0001" name="peso_kilos" id = 'peso_kilos'
                value="">
            </div>
            </div>
    </div>

 <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="Nombre">Calificacion </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <select class="person2 " name="clasificacion" id="clasificacion"  style="width: 100% ">
                    <option  value="">SELECCIONAR</option>
                    <option  value="DESNUTRICION AGUDA MODERADA">DESNUTRICION AGUDA MODERADA</option>
                    <option  value="DESNUTRICION AGUDA SEVERA">DESNUTRICION AGUDA SEVERA</option>
                    <option  value="DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR">DESNUTRICION AGUDA SEVERA TIPO KWASHIORKOR</option>
                    <option  value="DESNUTRICION AGUDA SEVERA TIPO MARASMO">DESNUTRICION AGUDA SEVERA TIPO MARASMO</option>
                    <option  value="DESNUTRICION AGUDA SEVERA MIXTA">DESNUTRICION AGUDA SEVERA MIXTA</option>
                    <option  value="RIESGO DE DESNUTRICION">RIESGO DE DESNUTRICION</option>
                    
                    <option  value="PESO ADECUADO PARA LA TALLA">PESO ADECUADO PARA LA TALLA</option>
                    
                  </select>
            </div>
            </div>
        
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="Nombre">Puntaje z (peso / talla)</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                    <input class="form-control" type="number" step="0.0001" name="puntajez" id = 'puntajez'
                    value="">
                </div>
                </div>                       
        
     
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group" id="inputsuperoculto">
                    <label for="Nombre"> Fecha de entrega FTLC </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                    <input class="form-control" type="date" name="fecha_entrega_ftlc" id = 'fecha_entrega_ftlc'
                    value="">
                </div>
                </div>
        
        
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="Nombre"> Requerimiento De Energia FTLC <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                       <input class="form-control" type="text" name="requerimiento_energia_ftlc" id = 'requerimiento_energia_ftlc'
                           value="">
                           </div>
                           </div>

                        </div>
                        <div class="row">

                            {{-- <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="Nombre"> Recomendacion De Manejo </label> 
                                    

                                    <textarea name="recomendaciones_manejo" id="recomendaciones_manejo" value="{{ isset($empleado->recomendaciones_manejo)?$empleado->recomendaciones_manejo:old('recomendaciones_manejo')}}" class="form-control" rows="5" maxlength="600"></textarea>
                         
                                </div>
                                </div> --}}

                              
                                
                                    </div>
                                    

                                    <div class="row">
                                        
                                
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="Nombre"> Observaciones </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                

                                                <textarea name="observaciones" id="observaciones" value="{{ isset($empleado->observaciones)?$empleado->observaciones:old('observaciones')}}" class="form-control" rows="5" maxlength="600"></textarea>
                         
                                            </div>
                                            </div>
                                            {{-- <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="Nombre"> Resultados de Seguimientos </label> 
                
                                                    <textarea name="resultados_seguimientos" id="resultados_seguimientos" value="{{ isset($empleado->resultados_seguimientos)?$empleado->resultados_seguimientos:old('resultados_seguimientos')}}" class="form-control" rows="5" maxlength="600"></textarea>
                                         
                                                </div>
                                                </div> --}}

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                      <label for="medicamento">Medicamento</label>
                                                      <select class="js-example-basic-multiple" name="medicamento[]" multiple="multiple" style="width: 100%">
                                                        <option value="23072-2">albendazol 200MG</option>
                                                        <option value="54114-1">albendazol 400MG</option>
                                                        <option value="35662-18">Acido folico</option>
                                                        <option value="31063-1">Vitamina A</option>
                                                        <option value="27440-3">Hierro</option>
                                                        <option value="NO APLICA">NO APLICA</option>
                                                        <!-- Agrega más opciones aquí -->
                                                      </select>
                                                    </div>
                                                  </div>
                                           
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="form-group">
                                                    <label for="Nombre">Estado actual del menor</label>
                                                    <select class="person2 " name="est_act_menor" id="est_act_menor"  style="width: 100% ">
                                                    <option  value="">SELECCIONAR</option>
                                                    <option  value="BUSQUEDA FALLIDA - MENORES QUE NO SE ENCUENTRAN EN EL TERRITORIO">BUSQUEDA FALLIDA - MENORES QUE NO SE ENCUENTRAN EN EL TERRITORIO</option>
                                                    <option  value="BUSQUEDA FALLIDA-MANIFIESTA QUIEN FUE SU MADRE SUSTITUTA , QUE LA MENOR FUE ENTREGADA A SU MADRE BIOLÓGICA,
                                                    HACE 3 MESES Y SE ENCUENTRAN RESIDIENDO EN LA CIUDAD DE PEREIRA">BUSQUEDA FALLIDA-MANIFIESTA QUIEN FUE SU MADRE SUSTITUTA , QUE LA MENOR FUE ENTREGADA A SU MADRE BIOLÓGICA,
                                                        HACE 3 MESES Y SE ENCUENTRAN RESIDIENDO EN LA CIUDAD DE PEREIRA</option>
                                                    <option  value="EN PROCESO DE BUSQUEDA">EN PROCESO DE BUSQUEDA</option>
                                                    <option  value="EN PROCESO DE RECUPERACION ">EN PROCESO DE RECUPERACION </option>
                                                    <option  value="PROCESO DE RECUPERACION">PROCESO DE RECUPERACION</option>
                                                    <option  value="RECUPERADO">RECUPERADO</option>
                                                    <option  value="REINGRESO -PROCESO DE RECUPERACION">REINGRESO -PROCESO DE RECUPERACION</option>
                                                    
                                                    
                                                    
                                                  </select>
                                                    </div>
                                                    </div>
                                            
                                            
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label for="Nombre">Tratamiento f75</label>
                                                            <select class="person2 " name="tratamiento_f75" id="tratamiento_f75"  style="width: 100% ">
                                                            <option  value="">SELECCIONAR</option>
                                                            <option  value="SI">SI</option>
                                                            <option  value="NO">NO</option>
                                                            
                                                            
                                                          </select>
                                                        </div>
                                                    </div>
                                            
                                            
                                                        <div class="col-sm-4">
                                                            <div class="form-group" id="input_oculto1">
                                                                <label for="Nombre"> Fecha en la que recibe tratamiento f75 </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                                <input class="form-control" type="date" name="fecha_recibio_tratf75" id = 'fecha_recibio_tratf75'
                                                                value="">
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
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label for="Nombre">Esquema pai completo para la edad</label>
                                                            <select class="person2 " name="Esquemq_complrto_pai_edad" id="Esquemq_complrto_pai_edad"  style="width: 100% ">
                                                            <option  value="">SELECCIONAR</option>
                                                            <option  value="INCOMPLETO">INCOMPLETO</option>
                                                            <option  value="INCOMPLETO">COMPLETO</option>
                                                            
                                                            
                                                          </select>
                                                    </div>
                                                        </div>
                                                    
                                                                                   
                                                    
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label for="Nombre">Atencion en la ruta de promocion y mantenimieto </label>
                                                                <select class="person2 " name="Atecion_primocion_y_mantenimiento_res3280_2018" id="Atecion_primocion_y_mantenimiento_res3280_2018"  style="width: 100% ">
                                                                    <option  value="0">SELECCIONAR</option>
                                                                    <option  value="SI">SI</option>
                                                                    <option  value="NO">NO</option>
                                                                </select>
                                                            </div>
                                                            </div>
                                                    
                                                    
                                                    </div>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <div class="form-group" >
                                                        <label for="Nombre">Desea cerrar el caso ?</label>
                                                        <select class="person2 " name="estado" id="estado"  style="width: 100% ">
                                                       
                                                        <option  value="0">CERRADO</option>
                                                        
                                                        
                                                      </select>
                                                </div> 
                                               
                                                </div>
                                                    <div class="col-md-5 " >
                                                    {{-- <div class="form-group" id="input_oculto">
                                                        <label for="Nombre"> Fecha Proximo Seguimiento </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final 
                                                         <input class="form-control" type="date" name="fecha_proximo_control" id="fecha_proximo_control" value="{{ isset($empleado->fecha_proximo_control) ? $empleado->fecha_proximo_control : old('fecha_proximo_control') }}" {{-- min="{{ date('Y-m-d') }}"  > 
                                                    </div>--}}
                                                    </div>
                                            
                                    
                                         </div>
                                           
                                         <div class="form-group">
                                            <label for="pdf">PDF:</label>
                                            <input type="file" name="pdf" class="form-control-file" required>
                                            
                                        </div> 

            

                           


<input class="btn btn-success" type="submit" value="enviar">
            <a  class="btn btn-primary" href="{{url('Seguimiento')}}" class="btn  btn-success"> REGRESAR</a>
        </div>
    </div>
        </div>
            </div>
                </div>
                    </div>

                    
                        </div>
   


                        @section('css')
                       <style>
                        body {
    font-family: Arial, sans-serif;
}

.seguimiento-extraordinario {
    text-align: center;
    font-size: 20px;
    margin: 20px;
    padding: 10px;
    border: 2px solid #000;
    display: inline-block;
    background-color: #f0f0f0;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.seguimiento-extraordinario a {
    color: #0066cc;
    text-decoration: none;
}

.seguimiento-extraordinario a:hover {
    text-decoration: underline;
}

.card-title {
    font-size: 24px;
}

                       </style>
                        @stop
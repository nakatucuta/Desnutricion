
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
                            <select class="person" name="cargue412_id" id="cargue412_id" style="width: 100%">
                                <option value="">SELECCIONE</option>
                                @foreach($incomeedit as $developer)
                                    <option value="{{ $developer->idin }}">
                                        ({{ $developer->created_at ? \Carbon\Carbon::parse($developer->created_at)->format('Y-m-d') : 'SIN FECHA' }})
                                        {{ $developer->idin }}
                                        {{ $developer->numero_identificacion }}
                                        {{ $developer->primer_nombre }}
                                        {{ $developer->segundo_nombre }}
                                        {{ $developer->primer_apellido }}
                                        {{ $developer->segundo_apellido }}
                                    </option>
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
                    <option  value="BUSQUEDA FALLIDA">BUSQUEDA FALLIDA</option>
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
                <div class="form-group">
                    <label for="Nombre"> Perimetro Braqueal <br> </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                   <input class="form-control" type="text" name="perimetro_braqueal" id = 'perimetro_braqueal'
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
                                                    <option  value="DESNUTRICION AGUDA MODERADA">DESNUTRICION AGUDA MODERADA</option>
                                                    <option  value="PESO ADECUADO PARA LA TALLA">PESO ADECUADO PARA LA TALLA</option>
                                                    <option  value="RIESGO DE DESNUTRICIÓN AGUDA ">RIESGO DE DESNUTRICIÓN AGUDA</option>
                                                    <option  value="RIESGO DE DESNUTRICIÓN AGUDA SEVERA">DESNUTRICIÓN AGUDA SEVERA</option>
                                                    <option  value="DESNUTRICIÓN AGUDA SEVERA TIPO MARASMO">DESNUTRICIÓN AGUDA SEVERA TIPO MARASMO</option>
                                                    <option  value="DESNUTRICIÓN AGUDA SEVERA TIPO KWASHIORKOR">DESNUTRICIÓN AGUDA SEVERA TIPO KWASHIORKOR</option>
                                                    <option  value="DESNUTRICIÓN AGUDA SEVERA TIPO MIXTA">DESNUTRICIÓN AGUDA SEVERA TIPO MIXTA</option>
                                                    <option  value="EN PROCESO DE RECUPERACION">EN PROCESO DE RECUPERACION</option>
                                                    <option  value="BUSQUEDA FALLIDA ">BUSQUEDA FALLIDA</option>
                                                    <option  value="PROCESO DE RECUPERACION">PROCESO DE RECUPERACION</option>
                                                    <option  value="RECUPERADO">RECUPERADO</option>
                                                    <option  value="FALLECIDO">FALLECIDO</option>
                                                    
                                                    
                                                    
                                                  </select>
                                                    </div>
                                                    </div>
                                            
                                            
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            
                                                          
                                                        </div>
                                                    </div>
                                            
                                            
                                                        <div class="col-sm-4">
                                                            
                            
                                                            
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
                                                <div class="col-md-4">
                                                    <div class="form-group" >
                                                        <label for="Nombre">Desea cerrar el caso ?</label>
                                                        <select class="person2 " name="estado" id="estado"  style="width: 100% ">
                                                        <option  value="">SELECCIONAR</option>
                                                        <option  value="1">ABIERTO</option>
                                                        <option  value="0">CERRADO</option>
                                                        
                                                      </select>
                                                </div>
                                               
                                                </div>
                                                    <div class="col-md-4 " >
                                                    <div class="form-group" id="input_oculto">
                                                        <label for="Nombre"> Fecha Proximo Seguimiento </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                         <input class="form-control" type="date" name="fecha_proximo_control" id="fecha_proximo_control" value="{{ isset($empleado->fecha_proximo_control) ? $empleado->fecha_proximo_control : old('fecha_proximo_control') }}" {{-- min="{{ date('Y-m-d') }}" --}} > 
                                                    </div>
                                                    


                                                   
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group" id="inputsuperoculto">
                                                          
                                                        </div>
                                                        </div>
                                    
                                         </div>
                                           
                                         <div class="form-group">
                                            <label for="pdf">PDF:</label>
                                            <input type="file" name="pdf" class="form-control-file" required>
                                            
                                        </div>

            

                           


                                        <button id="update-btn" class="btn btn-success" type="button" onclick="submitForm()">
                                            <span id="button-text">ENVIAR</span>
                                            <span id="loading-icon" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                            <span id="sending-text" style="display: none;">Enviando correo...</span>
                                        </button>
                                        <a  class="btn btn-primary" href="{{url('Seguimiento')}}" class="btn  btn-success"> REGRESAR</a>
        </div>
    </div>
        </div>
            </div>
                </div>
                    </div>

                    
                        </div>
   


                    
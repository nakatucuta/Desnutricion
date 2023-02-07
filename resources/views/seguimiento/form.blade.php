
@include('seguimiento.mensajes')


<div class="row">
    <div class="col-lg-12">
        <div class="card card-info card-outline card-tabs">
            <div class="card-header">
                <h2 class="card-title text-center">
                      <i class="far fa-hospital" style="font-size: 45px; color: #3333ff; "></i>
                      Segumiento
                      <i class="bi bi-plus"></i>
                      <i class="fas fa-user-md" style="font-size: 45px; color: #3333ff;"></i>
                </h2>

                
            </div>
            
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6 ">
                        <div class="form-group">
                            <label for="Nombre">Paciente</label>
                        <select class="person " name="ingresos_id" id="ingresos_id"  style="width: 100%">
                            <option value="">SELECCIONE</option>
                            @foreach($incomeedit as $developer)
                            <option  value="{{$developer->idin }}">{{$developer->idin.' '.$developer->num_ide_.' '.$developer->pri_nom_.' '.$developer->seg_nom_.' '.$developer->pri_ape_.' '.$developer->seg_ape_ }}</option>
                            @endforeach
                            
                          </select>
                    </div>
                </div>
             </div>


           
                <div class="row">



<div class="col-sm-6">
<div class="form-group">
    <label for="Nombre">Fecha Consulta</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="date" name="fecha_consulta" id = 'fecha_consulta'
    value="">
</div>
</div>

<div class="col-sm-6">
<div class="form-group">
    <label for="Nombre">Peso En Kilos y un decimal</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="number" step="0.01" name="peso_kilos" id = 'peso_kilos'
    value="">
</div>
</div>

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
                <label for="Nombre">Puntaje z (peso / talla)</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <input class="form-control" type="number" step="0.0001" name="puntajez" id = 'puntajez'
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
                    <option  value="DESNUTRICION AGUDA SEVERA TIPO KWWASHIORKOR">DESNUTRICION AGUDA SEVERA TIPO KWWASHIORKOR</option>
                    
                  </select>
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
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="Nombre"> Fecha de entrega FTLC </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                    <input class="form-control" type="date" name="fecha_entrega_ftlc" id = 'fecha_entrega_ftlc'
                    value="">
                </div>
                </div>
        
        
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="Nombre"> Medicamento </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                        <textarea name="medicamento" id="medicamento" value=""
                         class="form-control" rows="5" maxlength="600"></textarea>
                                           
                    </div>
                    </div>

                        </div>
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="Nombre"> Recomendacion De Manejo </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                    

                                    <textarea name="recomendaciones_manejo" id="recomendaciones_manejo" value="{{ isset($empleado->recomendaciones_manejo)?$empleado->recomendaciones_manejo:old('recomendaciones_manejo')}}" class="form-control" rows="5" maxlength="600"></textarea>
                         
                                </div>
                                </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="Nombre"> Resultados de Seguimientos </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                    

                                    <textarea name="resultados_seguimientos" id="resultados_seguimientos" value="{{ isset($empleado->resultados_seguimientos)?$empleado->resultados_seguimientos:old('resultados_seguimientos')}}" class="form-control" rows="5" maxlength="600"></textarea>
                         
                                </div>
                                </div>
                                
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="Nombre">Ips-ese atencion primaria</label>
                                                <select class="person" name="ips_realiza_seguuimiento" id="ips_realiza_seguuimiento"  style="width: 100% ">
                                                    <option  value="0">SELECCIONAR</option>
                                                    @foreach($income12 as $developer)
                                                    <option  value="{{$developer->descrip}}">{{$developer->descrip}}</option>
                                                    @endforeach
                                    
                                                </select>
                                            </div>
                                        </div>
                                
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="Nombre"> Observaciones </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                

                                                <textarea name="observaciones" id="observaciones" value="{{ isset($empleado->observaciones)?$empleado->observaciones:old('observaciones')}}" class="form-control" rows="5" maxlength="600"></textarea>
                         
                                            </div>
                                            </div>
                                
                                           
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="Nombre">Desea cerrar el caso ?</label>
                                                        <select class="person2 " name="estado" id="estado"  style="width: 100% ">
                                                        <option  value="">SELECCIONAR</option>
                                                        <option  value="1">ABIERTO</option>
                                                        <option  value="0">CERRADO</option>
                                                        
                                                        
                                                      </select>
                                                </div>
                                               
                                                </div>
                                                <div class="col-sm-6 ">
                                                    <div class="form-group" id="input_oculto" style="display: none;">
                                                        <label for="Nombre"> Fecha Proximo Seguimiento </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                                        <input class="form-control" type="date" name="fecha_proximo_control" id = 'fecha_proximo_control'
                                                        value="">
                                                    </div>
                                                    </div>
                                            
                                    
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
   


                    
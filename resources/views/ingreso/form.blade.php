


@include('ingreso.mensajes')

<div class="row">
    <div class="col-lg-12">
        <div class="card card-info card-outline card-tabs">
            <div class="card-header">
                <h2 class="card-title text-center">
                      <i class="far fa-hospital" style="font-size: 45px; color: #3333ff; "></i>
                      DATOS PRIMER INGRESO
                      <i class="bi bi-plus"></i>
                      <i class="fas fa-user-md" style="font-size: 45px; color: #3333ff;"></i>
                </h2>

                
            </div>
            
            <div class="card-body">

                <div class="row">
                    <div class="col-md-3 col-md-offset-0">
                        <div class="form-group">
                            <label for="Nombre">Paciente</label>
                        <select class="person " name="sivigilas_id" id="sivigilas_id"  style="width: 200%">
                            <option value="">SELECCIONE</option>
                            @foreach($incomeedit as $developer)
                            <option  value="{{$developer->id}}">{{$developer->num_ide_.' '. $developer->pri_nom_ .' '. $developer->seg_nom_
                             .' '. $developer->pri_ape_ .' '. $developer->seg_ape_}}</option>
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
                <label for="Nombre">Calificacion</label>
                <select class="person2 " name="calificacion" id="calificacion"  style="width: 100% ">
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
                    <label for="Nombre">Edema</label>
                    <select class="person2 " name="Edema" id="Edema"  style="width: 100% ">
                    <option  value="">SELECCIONAR</option>
                    <option  value="EDEMA MODERADO">EDEMA MODERADO</option>
                    <option  value="LEVE">EDEMA LEVE</option>
                    <option  value="MODERADO">EDEMA SEVERO</option>
                    <option  value="NO">NO</option>
              
                    
                    
                  </select>
            </div>
                </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="Nombre">Emaciacion</label>
                    <select class="person2 " name="Emaciacion" id="Emaciacion"  style="width: 100% ">
                    <option  value="">SELECCIONAR</option>
                    <option  value="SI">SI</option>
                    <option  value="NO">NO</option>
                    
                    
                  </select>
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
                                    <label for="Nombre">Interpretacion perimetro o braqueal</label>
                                    <select class="person2 " name="interpretacion_p_braqueal" id="interpretacion_p_braqueal"  style="width: 100% ">
                                    <option  value="">SELECCIONAR</option>
                                    <option  value="MENOR DE 6 MESES">MENOR DE 6 MESES</option>
                                    <option  value="NEGATIVO PARA EL TAMIZAJE ">NEGATIVO PARA EL TAMIZAJE </option>
                                    <option  value="NO APLICA">NO APLICA</option>
                                    <option  value="RIEGO DE MUERTE">RIEGO DE MUERTE</option>
                                   
                                    
                                    
                                  </select>
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
                                                <input class="form-control" type="number" name="mes_entrega_FTLC" id = 'mes_entrega_FTLC'
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
                                                        <label for="Nombre">Menor de 5 años desnutricion aguda</label>
                                                        <select class="person2 " name="Menor_anos_des_aguda" id="Menor_anos_des_aguda"  style="width: 100% ">
                                                        <option  value="">SELECCIONAR</option>
                                                        <option  value="SI">SI</option>
                                                        <option  value="NO">NO</option>
                                                    </select>
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
                                        value="{{ isset($empleado->remite_alguna_inst_apoyo)?$empleado->remite_alguna_inst_apoyo:old('remite_alguna_inst_apoyo')}}">
                                    </div>
                                    </div>
        
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="Nombre">Ips-ese atencion primaria</label>
                                            <select class="person" name="Nom_ips_at_prim" id="Nom_ips_at_prim"  style="width: 100% ">
                                                <option  value="0">SELECCIONAR</option>
                                                @foreach($income12 as $developer)
                                                <option  value="{{$developer->descrip}}">{{$developer->descrip}}</option>
                                                @endforeach
                                
                                            </select>
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

                           


<input class="btn btn-success" type="submit" value="enviar">
            <a  class="btn btn-primary" href="{{url('Ingreso')}}" class="btn  btn-success"> REGRESAR</a>
        </div>
    </div>
        </div>
            </div>
                </div>
                    </div>

                    
                        </div>
   


                    
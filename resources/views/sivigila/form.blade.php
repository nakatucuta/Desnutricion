@include('seguimiento.mensajes')
<div class="row">
    <div class="col-lg-12">
        <div class="card card-info card-outline card-tabs">
            <div class="card-header">
                <h2 class="card-title text-center">
                      <i class="far fa-hospital" style="font-size: 45px; color: #3333ff;"></i>
                      VERIFICAR DATOS DE SIVIGILA
                      <i class="fas fa-user-md" style="font-size: 45px; color: #3333ff;"></i>
                </h2>
            </div>

<div class="card-body">
    <div class="row">
            <div class="col-sm-3">
                    <div class="form-group">
                        <label for="Nombre">Cod Eve</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                        <input class="form-control" type="text" name="cod_eve" id = 'cod_eve'
                        value="{{$incomeedit1->cod_eve}}" readonly>
             </div>
    </div>
<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Semana de notificacion</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="semana" id = 'semana'
    value="{{$incomeedit5}}" readonly>
</div>
</div>
<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre"> Ultima Fecha de Notificacion</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="date" name="fec_not" id = 'fec_not'
    value="{{$incomeedit2}}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre"> Año</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="year" id = 'year'
    value="{{$incomeedit4}}" readonly>
</div>
</div>
</div>

<div class="row">
<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre"> Departamento</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="dpto" id = 'dpto'
    value="{{$incomeedit7}}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre"> Municipio</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="mun" id = 'mun'
    value="{{$incomeedit6}}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Tipo Identificacion</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="tip_ide_" id = 'tip_ide_'
    value="{{$incomeedit1->tip_ide_}}" readonly>
</div>
</div>


<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Identificacion</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="num_ide_" id = 'num_ide_'
    value="{{$incomeedit}}" readonly>
</div>
</div>
</div>

<div class="row">
<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Primer nombre</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="pri_nom_" id = 'pri_nom_'
    value="{{ $incomeedit1->pri_nom_ }}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Segundo nombre</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="seg_nom_" id = 'seg_nom_'
    value="{{$incomeedit1->seg_nom_}}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Primer Apellido</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="pri_ape_" id = 'pri_ape_'
    value="{{$incomeedit1->pri_ape_}}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Segundo Apellido</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="seg_ape_" id = 'seg_ape_'
    value="{{$incomeedit1->seg_ape_}}" readonly>
</div>
</div>
</div>

<div class="row">
<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">edad</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="edad_" id = 'edad_'
    value="{{$incomeedit3}}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Sexo</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="sexo_" id = 'sexo_'
    value="{{$incomeedit1->sexo_}}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Fecha Nacimiento</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="date" name="fecha_nto_" id = 'fecha_nto_'
    value="{{$incomeedit8}}" readonly>
</div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Edad Meses</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="edad_ges" id = 'edad_ges'
    value="{{$incomeedit9}}" readonly>
</div>
</div>
</div>

<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <label for="Nombre">Telefono</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
            <input class="form-control" type="text" name="telefono_" id = 'telefono_'
            value="{{$incomeedit1->telefono_}}" readonly>
        </div>
    </div>

    <div class="col-sm-3">
    <div class="form-group">
        <label for="Nombre">Etnia</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="nom_grupo_" id = 'nom_grupo_'
        value="{{$incomeedit1->nom_grupo_}}" readonly>
    </div>
    </div>

    <div class="col-sm-3">
    <div class="form-group">
    <label for="Nombre">IPS Atencion inicial</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="text" name="Ips_at_inicial" id = 'Ips_at_inicial'
    value="{{$income11}}" readonly>
    </div>
</div>

<div class="col-sm-3">
<div class="form-group">
    <label for="Nombre">Fecha de atencion inicial</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
    <input class="form-control" type="date" name="fecha_aten_inicial" id = 'fecha_aten_inicial'
    value="{{$incomeedit2}}" readonly>
</div>
</div>
</div>

<div class="row">
    <div class="col-sm-3">

        <div class="form-group">
            <label for="Nombre">Regimen</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
            <input class="form-control" type="text" name="regimen" id = 'regimen'
            value="{{$incomeedit13}}" readonly>
        </div>

        </div>
        </div>
<div class="row">   
    <div class="col-sm-6">
            <div class="form-group">
                <label for="Nombre">Ips seguimiento Ambulatorio</label>
                <select class="person2 " name="Ips_seguimiento_Ambulatorio" id="Ips_seguimiento_Ambulatorio"  style="width: 100% ">
                    <option  value="0">SELECCIONAR</option>
                    @foreach($income12 as $developer)
                    <option  value="{{$developer->descrip}}">{{$developer->descrip}}</option>
                    @endforeach
    
                </select>
            </div>
         </div>
        
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="Nombre">Caso confirmada de desnutricion etiologia primaria</label>
                    <select class="person2 " name="Caso_confirmada_desnutricion_etiologia_primaria" id="Caso_confirmada_desnutricion_etiologia_primaria"  style="width: 100% ">
                    <option  value="">SELECCIONAR</option>
                    <option  value="SI APLICA">SI APLICA</option>
                    <option  value="NO APLICA">NO APLICA</option>
                    
                    
                  </select>
            </div>
            </div>
        
             </div>


             <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                            <label for="Nombre"> Promedio en  dias para hacerse efectiva la remisión </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                            <input class="form-control" type="number" name="Promedio_dias_oportuna_remision" id = 'Promedio_dias_oportuna_remision'
                                        value="">
                    </div>
                </div>
                            
                <div class="col-sm-6">
                    <div class="form-group">
                            <label for="Nombre">Tipo de ajuste</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                            <input class="form-control" type="number" name="Tipo_ajuste" id = 'Tipo_ajuste'value="">
                    </div>
                </div> 
            </div>
        </div>
    </div>
        </div>
            </div>
                </div>

                
                            
                            <div class="card-body">
       
                
                    
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
                    <div class="col-lg-12 ">
                        <div class="card card-info card-outline ">
                    <center><h6 class=""> <strong>INFORME NOMINAL</strong></h6></center>
                
                </div>
                    </div>
                        </div>
                
                
                <div class="row">
                    <div class="col-sm-4">
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
                                <div class="form-group">
                                    <label for="Nombre"> Fecha en la que recibe tratamiento f75 </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                    <input class="form-control" type="date" name="fecha_recibio_tratf75" id = 'input_oculto'
                                    value="">
                                </div>
                                </div>
                
                
                               
                
                    </div>
                
                    <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="Nombre">Ips manejo hospitalario</label>
                            <select class="person2 " name="nombreips_manejo_hospita" id="nombreips_manejo_hospita"  style="width: 100% ">
                                <option  value="0">SELECCIONAR</option>
                                @foreach($income12 as $developer)
                                <option  value="{{$developer->descrip}}">{{$developer->descrip}}</option>
                                @endforeach
                
                            </select>
                        </div>
                        </div>
                    </div>
                    <input class="btn btn-success" type="submit" value="enviar">
            <a  class="btn btn-primary" href="{{url('sivigila')}}" class="btn  btn-success"> REGRESAR</a>
                </div>
            </div>
                </div>
                    </div>
                        </div>
                            </div>
        
                            
                                </div>                


                
                    </div>
                        </div>
   

                        
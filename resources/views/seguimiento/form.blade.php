
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
                            <option  value="{{$developer->idin }}">{{$developer->iden.' '.$developer->num_ide_.' '.$developer->pri_nom_.' '.$developer->seg_nom_.' '.$developer->pri_ape_.' '.$developer->seg_ape_ }}</option>
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
    <input class="form-control" type="number" name="peso_kilos" id = 'peso_kilos'
    value="">
</div>
</div>

</div>

<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="Nombre"> Talla en centimetros</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
            <input class="form-control" type="number" name="talla_cm" id = 'talla_cm'
            value="">
        </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label for="Nombre">Puntaje z (peso / talla)</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <input class="form-control" type="text" name="puntajez" id = 'puntajez'
                value="">
            </div>
            </div>
    </div>

 <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="Nombre">Calificacion </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                <input class="form-control" type="text" name="clasificacion" id = 'clasificacion'
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
                        <input class="form-control" type="text" name="medicamento" id = 'medicamento'
                        value="">
                    </div>
                    </div>

                        </div>
                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="Nombre"> Recomendacion De Manejo </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                    <input class="form-control" type="text" name="recomendaciones_manejo" id = 'recomendaciones_manejo'
                                    value="">
                                </div>
                                </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="Nombre"> Resultados de Seguimientos </label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
                                    <input class="form-control" type="text" name="resultados_seguimientos" id = 'resultados_seguimientos'
                                    value="">
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
                                                <input class="form-control" type="text" name="observaciones" id = 'observaciones'
                                                value="">
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
            <a  class="btn btn-primary" href="{{url('Seguimiento')}}" class="btn  btn-success"> REGRESAR</a>
        </div>
    </div>
        </div>
            </div>
                </div>
                    </div>

                    
                        </div>
   


                    
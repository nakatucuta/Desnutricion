
<style>

    #tit{
        background-color: blue;
    }
    
    #todo{
    
        
    }
    
    .franja{    
        height:30px;
        background: #D34646;
    }
    
    
    
    
    .posicion{
    position: relative;
      top:  0;/*abajo*/
      bottom: 100px ;
      right: 0px ;
      left: 570px;
      
    
      width: 150px;
      background-color: green;
    }
    
    
    .posicion2{
    position: relative;
      top: 0px ;/*abajo*/
      bottom: 0px ;
      right: 0px ;
      left: 300px;
      
    
      width: 240px;
      
     
    
       font-size: 10;
     
    }
    
    
    #posicion3{
    position: fixed;
      top: 484px ;/*abajo*/
      bottom: 0px ;
      right: 0px ;
      left: 290px;
      
    
     
    }
    
    
    
    
    #customers {
        font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 75%;
    }
    
    #customers td, #customers th {
        border: 1px solid ;
        padding: 8px;
    }
    
    
    
    
    
    #customers th {
        padding-top: 2px;
        padding-bottom: 12px;
        text-align: left;
        background-color: none;
        color: black;
    }
     
     
    #repinta {
    
        border: 1px solid ;
       /* width: 40%;
        height: 13%;*/
        text-align:center;
    
    }
    
    #derecha {
    
        float: left;
    }
    
    #sinnada{
        border: none;
    }
       
    </style>
    
    
    
    
    
    
    
 
    <img src="img/logo.jpg" alt="" style="float: left; width: 50px; margin-left: 40px;">
    
      <div   style="width: 140px; height: 30px;  float: right; border: groove; font-size: 9; ">
            <strong >Fecha:{{Carbon\Carbon::now()->format('d-m-Y')}}</strong> 
             
              <br>
              
              <strong>Hora:{{Carbon\Carbon::now()->format('H:i:s')}} </strong>
             
    
        </div>
    
    
    
         <div  class="posicion2"  style="text-align: center;">
           
    
    
          <strong style="font-size: 10;" >ANAS WAYUU <br> Nit: 839.000.495-6 <br> Regimen:simplificado  </strong>  <br>
         
            
       
            
            
         
         
        </div>
    
           
    
    
         <hr><!-- LINEA OOJO -->
    
    
       
    
    
    <p style="text-align: center;">
        <strong > 
            EMPRESA PROMOTORA DE SALUD
            <br>
            Vigilado Supersalud Resolución No 15-10 de Julio de 2001
            Nit: 839.000.495-6
            </strong> 
    </p>
         
                       
     {{-- &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<strong>CC:</strong> 
    <br>   &nbsp;  &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;<strong>Nombre:</strong> 
                           --}}
    
    
       
        
            
            <table   id="customers" align="center">
            <thead>
    
                            <tr>
                                
                              
                                
                                <br>
                                
                               
                                <p style="display: flex; justify-content: center;">


    
                                    Hola <strong> {{ auth()->user()->name }}</strong> la <strong>  E.P.S.I ANAS WAYUU</strong> <br> <br>
                                     REPORTE PARA REVISION DE SEGUIMIENTOS: {{--el dia: <strong>{{Carbon\Carbon::now()->format('d-m-Y')}}</strong><br> --}}




    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Identificacion</th>
                <th>Fecha consulta</th>
                <th>Est actual</th>
                {{-- <th>Medicamento</th> --}}
                <th>Fecha de proximo control</th>
            </tr>
        </thead>
        <tbody>
             @foreach($segene as $ingreso)
                <tr>
                    <td>{{ $ingreso->id }}</td>
                    <td>{{ $ingreso->pri_nom_.' '.$ingreso->seg_nom_.' '.$ingreso->pri_ape_.' '.$ingreso->seg_ape_ }}</td>
                    <td>{{ $ingreso->num_ide_ }}</td>
                    <td>{{ $ingreso->fecha_consulta }}</td>
                    <td>{{$ingreso->est_act_menor}}</td>
                    {{-- <td>{{ $ingreso->medicamento }}</td> --}}
                    <td>{{$ingreso->fecha_proximo_control}}</td>
                 
                </tr>
            @endforeach 
        </tbody>
    </table>

                                    </p> 
                            </tr>
                                
                               
    
    
       
    
    
    
                   
                
             
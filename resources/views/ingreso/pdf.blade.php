
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
      left: 170px;
      
    
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
    
    
    
    
    
    
    
 
      <img src="img/logo.jpg" alt="" style=" float: left; width: 100px" >
    
      <div   style="width: 140px; height: 30px;  float: right; border: groove; font-size: 9; ">
            <strong >Fecha:{{Carbon\Carbon::now()->format('d-m-Y')}}</strong> 
             
              <br>
              
              <strong>Hora:</strong>
             
    
        </div>
    
    
    
         <div  class="posicion2" align="left">
           
    
    
          <strong style="font-size: 10;" >ANAS WAYUU <br> Nit: 839.000.495-6 <br> Regimen:simplificado</strong> <br> <p align="center"></p>
          <p > 
            
            EMPRESA PROMOTORA DE SALUD SUBSIDIADA 
            
            
         </p>
         
         
        </div>
    
           
    
    
         <hr><!-- LINEA OOJO -->
    
    
       
    
    
    
      <strong>Cant:</strong>  Productos.  
                       
     {{-- &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<strong>CC:</strong> 
    <br>   &nbsp;  &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;<strong>Nombre:</strong> 
                           --}}
    
    
       
        
            
            <table   id="customers" align="center">
            <thead>
    
                            <tr>
                                
                              
                                <br>
                                <br>
                                
                               
                                <p>


    
                                    Hola  {{ auth()->user()->name }} <strong> la E.P.S.I ANAS WAYUU</strong> <br> <br>
                                    Ha Recepcionado la informacion de manera exitosa el dia <strong>{{Carbon\Carbon::now()->format('d-m-Y')}}</strong>
                                    </p> 
                            </tr>
                                
                               
    
    
       
    
    
    
                   
                
             
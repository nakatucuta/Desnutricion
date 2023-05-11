
@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')


@include('seguimiento.modal_notificaciones')

    <h1 style="font-family: 'Helvetica Neue', sans-serif; 
    font-weight: 700;
    font-size: 2rem;">Datos Revision</h1>
   
 

@stop

            @section('content')
            
            <table class="table table-hover table-striped table-bordered  {{-- table-responsive--}}" style="border: 1px solid #000000;" id="sivigila"> 
              <thead class="table table-hover table-info table-bordered " style="background-color: #d9f2e6 ;border: 1px solid #000000;">
           <tr>
                    <th>ID</th>
                    <th >Identificacion</th>
                    <th >Nombre</th>
                   
                    <th >Ips</th>
                    
                    {{-- <th >Fecha proximo control</th> --}}
                    <th >Revisar</th>
                  </tr>
                </thead>
                <tbody id="table">
                  <tr>

                    @php
                   
                    $count = DB::table('seguimientos')
                    ->where('estado', '=', '0')->count();
                    
                    @endphp
                    
                    @if($count < 0 ) 
                    
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>

                        @elseif($count > 0 )




                    @foreach($incomeedit as $student2)
                <th> {{ $student2->id }}</th>   
                <th >{{ $student2->num_ide_ }}</th>
                 <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.$student2->seg_ape_ }}</td>
                 <td>{{$student2->hospi}}</td>
                 
                 

                 
               
                 {{-- @if(!empty($student2->fecha_proximo_control))
                 <td>{{ $student2->fecha_proximo_control }}</td>
             @elseif(!empty($student2->created_at))
                 <td>{{ $student2->created_at }}</td>
             @else
                 <td>finalizado</td>
             @endif
           
                 
                    
                       --}}
                       <td>
                        @if (DB::table('seguimientos')
    ->join('sivigilas', 'seguimientos.sivigilas_id', '=', 'sivigilas.id')
    ->where('sivigilas.id', '=', $student2->id)
    ->whereExists(function ($query) {
        $query->select(DB::raw(1))
              ->from('revisions')
              ->whereRaw('revisions.seguimientos_id = seguimientos.id');
    })
    ->exists())
    
        <a href="" onclick="return false;" title="DETALLE" class="btn  btn-success btn-sm">
            <span class="icon-zoom-in" ></span> <i class="fas fa-check"></i>
        </a>
    
@else
    <a href="{{route('detalle_revisiones', [$student2->id])}}" title="DETALLE" class="btn btn-danger btn-sm">
        <span class="icon-zoom-in"></span><i class="fas fa-exclamation-triangle">  </i>
    </a>
@endif

 <a href="{{route('pdfcertificado', [$student2->id])}}" title="REPORTE" class="btn  btn-primary btn-sm">
  <span class="icon-zoom-in" ></span> <i class="fas fa-file-alt"></i>
</a> 
                </td>
                  </tr>
                    
             
              
                  @endforeach 
                 @endif
                </tbody>
                 
              </table>
               {{-- {{ $sivigilas->links() }}  --}}
              
            
          
             
            @stop
            

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/dataTables.bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/DataTables/css/jquery.dataTables.css') }}">

<style>

@keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(255, 99, 132, 0.7);
    }
    70% {
      box-shadow: 0 0 0 20px rgba(255, 99, 132, 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(255, 99, 132, 0);
    }
  }

  .btn-pulse {
  animation: pulse 1s ease-in-out infinite;
}


</style>
@stop

@section('js')
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/DataTables/js/dataTables.bootstrap5.min.js') }}"></script>
<style> 

.dataTables_filter input {
  width: 500px !important;
  height: 100%;
  background-color: #555 ;
  border: solid 3px !important;
  border-radius: 20px !important;
  color: rgb(64, 125, 232);
  padding: 10px !important;
  font-weight: bold !important;
}

.dataTables_filter label {
  font-weight: bold !important ;
}

 .dataTables_length label {
  
  font-weight: bold !important;
} 

.dataTables_length select {
  display: flex ;
  border: solid 3px !important;
  border-radius: 20px !important;
  align-items: center !important;
  margin-bottom: 10px !important;
  color: rgb(64, 125, 232) !important;
}

</style>


{{-- <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script> --}}
{{-- <script type="text/javascript"> 
  $(document).ready(function(){
      $("#q").on("keyup", function() {
          var value = $(this).val().toLowerCase();
          $("#table tr").filter(function() {
              $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
          });
      });
  });
</script> --}}

<script>
  $(document).ready(function () {
    $('#sivigila').DataTable({

      "language":{

            "search": "BUSCAR",
            "lengthMenu": "Mostrar _MENU_ registros",
            "info": "Mostrando pagina _PAGE_ de _PAGES_",
            "paginate": {
            "first": "Primero",
            "last": "Último",
            "next": "Siguiente",
            "previous": "Anterior"
                           }


              }

    });
});
</script>
@stop




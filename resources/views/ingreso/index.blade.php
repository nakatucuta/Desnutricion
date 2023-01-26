
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
@include('ingreso.mensajes')
<h1>Listado De Ingresos</h1>
<a href="{{route('Ingreso.create')}}" title="DETALLE" class="btn  btn-primary">
  <span class="icon-zoom-in" ></span> NUEVO INGRESO</a>
  <a href="{{route('export1')}}" class="btn  btn-success " style="float: right;
  margin-right: 0;
  width: 14%;
  position: relative;
  right: 0;"><i class="fas fa-book"></i>   REPORTE</a>
    
    {{-- <section class="content-header">
      
        <h1 class="pull-right">
        
            
            <a class="btn btn-primary pull-right"
            style="margin-top: -10px;
            margin-bottom: 5px" href="{{url('sivigila/create')}}">Realizar seguimiento</a>
            
        </h1>
        </section> --}}
@stop

            @section('content')
            


            <table class="table">
                <thead class="table table-hover table-dark">
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Fecha Ingreso</th>
                    <th scope="col">Tipo ID</th>
                    <th scope="col">Identificacion</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    @foreach($master as $student2)
                    <th scope="row">{{ $student2->id }}</th>
                    <td>{{$student2->Fecha_ingreso_ingres}}</td>
                    <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_ }}</td>
                    <td>{{$student2->num_ide_}}</td>
                    <td></td>
                        
                      <td>  <a class="btn  btn-warning" href="{{url('/Ingreso/'.$student2->id. '/edit')}}" class="ref" >
                        <i class="fas fa-edit"></i>
                    </a>
                  
                    

                  <a href="{{route('Ingreso.destroy', $student2->id)}}"
                    onclick="event.preventDefault();
                    if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                    document.getElementById('delete-form-{{$student2->id}}').submit();
                    }" class="btn  btn-danger">
                   <i class="fas fa-trash"></i>
                 </a>
                 <form id="delete-form-{{$student2->id}}" action="{{route('Ingreso.destroy', $student2->id)}}"
                  method="POST" style="display: none;">
                @method('DELETE')
                @csrf
            </form>
                  </td>
                   
                            
                </td>
                  </tr>
                
                 
             
                  
                  @endforeach 
            
                </tbody>
                
              </table>
              {{ $master->links() }} 
            
          
             
            @stop
            

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
   $('#q').on('keyup', function(){
    $value=$(this).val();
    $.ajax({
        type : 'get',
        url : '{{URL::to('search')}}',
        data:{
            'q':$value
        },
        success:function(data){
            $('#posts').html(data);
        }
    });
})
  </script>
@stop




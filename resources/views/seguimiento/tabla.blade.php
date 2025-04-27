<div class="content">

  
   <!-- Campo de búsqueda -->
   <div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Botón de Exportar a la izquierda -->

    <!-- Campo de búsqueda a la derecha con mayor ancho -->
    <div class="search-container ml-auto">
        <div class="search-input-wrapper">
            <input type="text" id="search" class="form-control search-input" placeholder="Buscar por Número de Identificación" autocomplete="off">
            <i class="fas fa-search search-icon"></i>
        </div>
        <!-- Área donde se mostrarán los resultados -->
        <div id="search-results" class="list-group search-results">
            <div id="loading-spinner" style="display:none; text-align:center; padding:10px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
        </div>
    </div>
</div>

</div>




    <div class="clearfix">
      <div class="box box-primary">
        <div class="box-body">
          <table class="table table-hover table-striped table-bordered  {{-- table-responsive--}}" style="border: 1px solid #000000;" id="seguimiento"> 
            <thead class="table table-hover table-info table-bordered " 
            style="background-color: #d9f2e6 ;
            border: 1px solid #000000;">
            <tr>
            <th>ID</th>
            <th >Fecha asignacion</th>
            <th >Identificacion</th>
            <th >Semana Epid</th>
            <th >Nombre</th>
            <th >Estado</th>
            <th >Ips</th>
            <th >Fecha proximo control</th>
            <th >Acciones</th>
            </tr>
            </thead>
             <tbody id="table">
              <tr>

                    @php
                    $user = Auth::user();
                    $userId = $user->id;
                    $user_id = Auth::id(); // Obtener el ID del usuario activo
                    $count = DB::table('seguimientos')->where('user_id', $user_id)->count();
                    $count2 = DB::table('seguimientos')
                    ->where('user_id', $userId)  // Asume que hay una columna 'user_id' en la tabla 'seguimientos'
                    ->whereYear('created_at', '>', 2023)
                    ->count();
                    $count1 = DB::table('seguimientos')->count(); // Contar los registros de ingresos del usuario activo
                    @endphp
                    
                    @if(($count < 1 && auth()->user()->usertype == 2) || ($count2 < 1 && auth()->user()->usertype == 2) )
                      
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                      <td  class="text-center">No hay registros disponibles</td>
                        @elseif($count >= 1 && (auth()->user()->usertype == 2))
                       
                     
                       @foreach($incomeedit as $student2)
                    
                    <th >{{ $student2->id }}</th>
                    <th >{{ $student2->creado }}</th>
                    
                    <th >{{ $student2->num_ide_ }}</th>
                    <th >{{ $student2->semana}}</th>
                    <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.$student2->seg_ape_ }}</td>
                    
                    <td> @if ($student2->estado == 1)
                     Abierto
                    @else
                     Cerrado
                    @endif</td>
                    <td>{{$student2->Ips_at_inicial}}</td>
                    @if(!empty($student2->fecha_proximo_control))
                    <td>{{ $student2->fecha_proximo_control }}</td>
                @elseif(!empty($student2->created_at))
                    <td>{{ $student2->created_at }}</td>
                @else
                    <td>finalizado</td>
                @endif
                      <td>  <a class="btn  btn-success btn-sm" href="{{url('/Seguimiento/'.$student2->id. '/edit')}}" class="ref" >
                        <i class="fas fa-edit"></i>
                        <a ></a>
                  @if($student2->motivo_reapuertura )
                  <a class="btn btn-primary btn-sm" href="{{route('detalleseguimiento', $student2->id)}}" class="ref">
                      <i class="far fa-eye"></i>
                  </a>
                  @endif
 
              
                <a href="{{ route('seguimiento.view-pdf', $student2->id) }}" target="_blank" class="btn btn-info btn-sm">
                  <i class="far fa-file-pdf"></i>   </a> 

                  
                  @if ($student2->estado == 1)
                  {{-- <a class="btn btn-primary btn-sm" href="{{ route('seguimiento_ocasional.create', ['id' => $student2->id]) }}" class="ref">
                      <i class="fas fa-plus"></i>
                  </a>  --}}
   
                  @else 
                  
              @endif
             

                    {{-- <a href="{{route('Seguimiento.destroy', $student2->id)}}"
                      onclick="event.preventDefault();
                      if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                      document.getElementById('delete-form-{{$student2->id}}').submit();
                      }" class="btn  btn-danger">
                     <i class="fas fa-trash"></i>
                
                   
                   <form id="delete-form-{{$student2->id}}" action="{{route('Seguimiento.destroy', $student2->id)}}"
                    method="POST" style="display: none;">
                    @method('DELETE')
                    @csrf
                    </form>
                  --}}
                    </td>
                
                            
                    </td>
                  </tr>
                
                 
             
                  
                  @endforeach 
                  @endif
                  
                  @if($count1 < 1 && (auth()->user()->usertype == 1 ||  auth()->user()->usertype == 3)) 
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                  <td  class="text-center">No hay registros disponibles</td>
                 @elseif($count1 >= 1 && (auth()->user()->usertype == 1 ||  auth()->user()->usertype == 3))
                 @foreach($incomeedit as $student2)
                        
                 <th >{{ $student2->id }}</th>
                 <th >{{ $student2->creado }}</th>
                 <th >{{ $student2->num_ide_ }}</th>
                 <th >{{ $student2->semana}}</th>
                 <td>{{ $student2->pri_nom_.' '.$student2->seg_nom_.' '.$student2->pri_ape_.' '.$student2->seg_ape_ }}</td>
                 
                 <td> @if ($student2->estado == 1)
                  Abierto
                @else
                  Cerrado
                @endif</td>
                 <td>{{$student2->name}}</td>
                 @if(!empty($student2->fecha_proximo_control))
                 <td>{{ $student2->fecha_proximo_control }}</td>
             @elseif(!empty($student2->created_at))
                 <td>{{ $student2->created_at }}</td>
             @else
                 <td>finalizado</td>
             @endif
                 
                   <td>  
                    
                    <a class="btn  btn-success btn-sm" href="{{url('/Seguimiento/'.$student2->id. '/edit')}}" class="ref" >
                     <i class="fas fa-edit"></i>
                    </a>
               
                 
                 @if( auth()->user()->usertype == 3) 
                 @else
                  <a href="{{route('Seguimiento.destroy', $student2->id)}}"
                    onclick="event.preventDefault();
                    if(confirm('¿Está seguro de que desea eliminar el producto?')) {
                    document.getElementById('delete-form-{{$student2->id}}').submit();
                    }" class="btn  btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                  </a>
                
              
                    <form id="delete-form-{{$student2->id}}" action="{{route('Seguimiento.destroy', $student2->id)}}"
                    method="POST" style="display: none;">
                    @method('DELETE')
                    @csrf
                    </form>
               @endif

               @if($student2->motivo_reapuertura )
                 <a class="btn btn-primary btn-sm" href="{{route('detalleseguimiento', $student2->id)}}" class="ref">
                     <i class="far fa-eye"></i>
                 </a>
             @endif

             
               <a href="{{ route('seguimiento.view-pdf', $student2->id) }}" target="_blank" class="btn btn-info btn-sm">
                 <i class="far fa-file-pdf"></i>
               </a>
            
               {{-- @if ($student2->estado == 1)
               <a class="btn btn-primary btn-sm" href="{{ route('seguimiento_ocasional.create', ['id' => $student2->id]) }}" class="ref">
                   <i class="fas fa-plus"></i>
               </a> 

               @else  --}}
               
           @endif
           
           
           
        
                
                   


                 </td>
                 
                         
                 </td>
               </tr>
             
              
          
               
               @endforeach 
               
                  @endif


                </tbody>
                
              </table>
           
              
               {{ $incomeedit->links() }} 
            
              </div>
              </div>
            </div>
          </div>
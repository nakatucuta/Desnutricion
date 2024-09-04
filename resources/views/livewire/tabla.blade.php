{{-- <h4>Exportar Vacunas</h4> --}}
<a href="{{ url('export-vacunas') }}" class="btn btn-success">Exportar a Excel</a>
<div class="table-responsive mt-5">

    @if( auth()->user()->usertype == 2)
    <table class="table table-hover table-striped table-bordered" id="sivigila">
        <thead class="table-info">
            <tr>
                <th style="font-size: smaller;" scope="col">Id</th>
                <th style="font-size: smaller;" scope="col">Numero Identificacion</th>
                <th style="font-size: smaller;" scope="col">Nombre Paciente</th>
                <th style="font-size: smaller;" scope="col">Lote</th>
                <th style="font-size: smaller;" scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
           
            @foreach($sivigilas as $student2)
            {{-- @if($user->name == auth()->user()->name)
            holi
             @else
             @endif --}}
            <tr>
                <td><small>{{ $student2->id }}</small></td>
                <td><a href="#" class="numero-identificacion" data-id="{{ $student2->id }}">{{ $student2->numero_identificacion }}</a></td>
                <td><small>{{ $student2->primer_nombre.' '.$student2->segundo_nombre.' '.$student2->primer_apellido.' '.$student2->segundo_apellido }}</small></td>
                <td><small>{{ $student2->batch_verifications_id }}</small></td>
                <td>
                    <a href="" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('batch_verifications.destroy', $student2->batch_verifications_id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            
            @endforeach
           
        </tbody>
    </table>
    @else
    {{-- VISTA PARTA LOS DEMAS USUARIOS --}}


    <table class="table table-hover table-striped table-bordered" id="sivigila">
        <thead class="table-info">
            <tr>
                <th style="font-size: smaller;" scope="col">Id</th>
                <th style="font-size: smaller;" scope="col">Numero Identificacion</th>
                <th style="font-size: smaller;" scope="col">Nombre Paciente</th>
                <th style="font-size: smaller;" scope="col">Lote</th>
                <th style="font-size: smaller;" scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
           
            @foreach($sivigilas_usernormal as $student2)
            {{-- @if($user->name == auth()->user()->name)
            holi
             @else
             @endif --}}
            <tr>
                <td><small>{{ $student2->id }}</small></td>
                <td><a href="#" class="numero-identificacion" data-id="{{ $student2->id }}">{{ $student2->numero_identificacion }}</a></td>
                <td><small>{{ $student2->primer_nombre.' '.$student2->segundo_nombre.' '.$student2->primer_apellido.' '.$student2->segundo_apellido }}</small></td>
                <td><small>{{ $student2->batch_verifications_id }}</small></td>
                <td>
                    <a href="#" class="btn btn-sm btn-warning blinking-button">
                        <i class="fas fa-envelope"></i> Solicitud
                    </a>
                                     
                </td>
            </tr>
            
            @endforeach
           
        </tbody>
    </table>


    @endif
</div>
<br>
<br>
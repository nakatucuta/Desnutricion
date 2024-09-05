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


  <!-- Tu tabla -->
<table class="table table-hover table-striped table-bordered" id="sivigila">
    <thead class="table-info">
        <tr>
            <th style="font-size: smaller;" scope="col">Id</th>
            <th style="font-size: smaller;" scope="col">Numero Identificacion</th>
            <th style="font-size: smaller;" scope="col">Nombre Paciente</th>
            {{-- <th style="font-size: smaller;" scope="col">Lote</th> --}}
            <th style="font-size: smaller;" scope="col">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sivigilas_usernormal as $student2)
        @php
        // Verificamos si ya se ha enviado un correo para este paciente y usuario actual
        $correoEnviado = App\Models\CorreoEnviado::where('user_id', auth()->id())
            ->where('patient_id', $student2->id)
            ->exists();
    @endphp
        <tr>
            <td><small>{{ $student2->id }}</small></td>
            <td><a href="#" class="numero-identificacion" data-id="{{ $student2->id }}">{{ $student2->numero_identificacion }}</a></td>
            <td><small>{{ $student2->primer_nombre.' '.$student2->segundo_nombre.' '.$student2->primer_apellido.' '.$student2->segundo_apellido }}</small></td>
            {{-- <td><small>{{ $student2->batch_verifications_id }}</small></td> --}}
            <td>
                @if($correoEnviado)
                    <!-- Deshabilitar el botón si ya se envió el correo -->
                    <button class="btn btn-sm btn-secondary" disabled>
                        <i class="fas fa-envelope"></i> Correo Enviado
                    </button>
                @else
                    <!-- Botón para enviar el correo -->
                    <a href="#" class="btn btn-sm btn-warning blinking-button send-email" 
                        data-toggle="modal" 
                        data-target="#emailModal" 
                        data-id="{{ $student2->id }}" 
                        data-name="{{ $student2->primer_nombre.' '.$student2->segundo_nombre.' '.$student2->primer_apellido.' '.$student2->segundo_apellido }}">
                        <i class="fas fa-envelope"></i> Solicitud
                    </a>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Modal para escribir el correo -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">Enviar Solicitud</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="emailForm" action="{{ route('send.email') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Nombre del paciente -->
                    <div class="form-group">
                        <label for="patientName">Nombre del Paciente</label>
                        <input type="text" class="form-control" id="patientName" name="patientName" readonly>
                    </div>

                    <!-- Asunto -->
                    <div class="form-group">
                        <label for="subject">Asunto</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="Solicitud de información" required>
                    </div>

                    <!-- Mensaje -->
                    <div class="form-group">
                        <label for="message">Mensaje</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>

                    <!-- Campo oculto con el ID del paciente -->
                    <input type="hidden" id="patientId" name="patientId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>



    @endif
</div>
<br>
<br>
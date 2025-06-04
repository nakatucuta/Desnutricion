@extends('adminlte::page')

@section('title', 'PDFs de ‘'.$numero.'’ - PAI')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>PDFs asociados a: <strong>{{ $numero }}</strong></h1>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @if($pdfs->isEmpty())
        <div class="alert alert-info">
            No hay archivos PDF asociados a la identificación <strong>{{ $numero }}</strong>.
        </div>
    @else
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Lista de PDFs</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($pdfs as $pdf)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-pdf text-danger"></i>
                                <a href="{{ asset('storage/'.$pdf->file_path) }}" target="_blank">
                                    {{ $pdf->original_name }}
                                </a>
                                <br>
                                <small class="text-muted">
                                    Subido: {{ $pdf->created_at->format('Y-m-d H:i') }}
                                    @if($pdf->tamizaje_id)
                                        – (Tamizaje ID: {{ $pdf->tamizaje_id }})
                                    @endif
                                </small>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@stop

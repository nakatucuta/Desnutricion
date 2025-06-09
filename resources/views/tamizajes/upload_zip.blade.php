@extends('adminlte::page')

@section('title', 'Subir ZIP de PDFs - PAI')

@section('content_header')
    <h1 class="text-center">Subida Masiva de RESULTADOS para Tamizajes</h1>
    <p class="text-center text-muted">
        Sube un archivo ZIP que contenga todos los PDFS.<br>
        Cada PDF debe nombrarse con la convención: <code>TIPO_NUMERO_*&lt;algo&gt;*.pdf</code>.<br>
        Ejemplo: <code>CC_12345678_historia.pdf</code> o <code>TI_87654321_resultados.pdf</code>.
    </p>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <div class="card">
        <div class="card-header bg-primary text-white">
          <h3 class="card-title">Subir ZIP de PDFs</h3>
        </div>
        <div class="card-body">
          <form action="{{ route('tamizajes.upload-zip') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
              <label for="pdf_zip">Selecciona tu archivo ZIP:</label>
              <input type="file" name="pdf_zip" id="pdf_zip" class="form-control" accept=".zip" required>
              @error('pdf_zip')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>

            <button type="submit" class="btn btn-success btn-block">
              <i class="fas fa-file-archive"></i> Subir ZIP
            </button>
          </form>
        </div>
      </div>
    </div>
</div>
@stop


@extends('adminlte::page')

@section('title', 'Anas wayuu')

@section('content_header')
formulario de edicion 
@stop
@section('content')

<form action="{{url('/sivigila')}}" method="post" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label for="Nombre">Cod Eve</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="cod_eve" id = 'cod_eve'
        value="{{$incomeedit1->cod_eve}}">
    </div>

    <div class="form-group">
        <label for="Nombre">Semana de notificacion</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="semana" id = 'semana'
        value="{{$incomeedit5}}">
    </div>

    <div class="form-group">
        <label for="Nombre"> Ultima Fecha de Notificacion</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="date" name="fec_not" id = 'fec_not'
        value="{{$incomeedit2}}">
    </div>

    <div class="form-group">
        <label for="Nombre"> Año</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="year" id = 'year'
        value="{{$incomeedit4}}">
    </div>

    <div class="form-group">
        <label for="Nombre"> Departamento</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="dpto" id = 'dpto'
        value="{{$incomeedit7}}">
    </div>


    <div class="form-group">
        <label for="Nombre"> Municipio</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="mun" id = 'mun'
        value="{{$incomeedit6}}">
    </div>

    <div class="form-group">
        <label for="Nombre">Tipo Identificacion</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="tip_ide_" id = 'tip_ide_'
        value="{{$incomeedit1->tip_ide_}}">
    </div>


   
    
    <div class="form-group">
        <label for="Nombre">Identificacion</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="num_ide_" id = 'num_ide_'
        value="{{$incomeedit}}">
    </div>

   
    
    <div class="form-group">
        <label for="Nombre">Primer nombre</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="pri_nom_" id = 'pri_nom_'
        value="{{ $incomeedit1->pri_nom_ }}">
    </div>

    
    <div class="form-group">
        <label for="Nombre">Segundo nombre</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="seg_nom_" id = 'seg_nom_'
        value="{{$incomeedit1->seg_nom_}}">
    </div>

    <div class="form-group">
        <label for="Nombre">Primer Apellido</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="pri_ape_" id = 'pri_ape_'
        value="{{$incomeedit1->pri_ape_}}">
    </div>

    <div class="form-group">
        <label for="Nombre">Segundo Apellido</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="seg_ape_" id = 'seg_ape_'
        value="{{$incomeedit1->seg_ape_}}">
    </div>

    <div class="form-group">
        <label for="Nombre">edad</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="edad_" id = 'edad_'
        value="{{$incomeedit3}}">
    </div>

    <div class="form-group">
        <label for="Nombre">Sexo</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="sexo_" id = 'sexo_'
        value="{{$incomeedit1->sexo_}}">
    </div>

    <div class="form-group">
        <label for="Nombre">Fecha Nacimiento</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="date" name="fecha_nto_" id = 'fecha_nto_'
        value="{{$incomeedit8}}">
    </div>
   
    
    <div class="form-group">
        <label for="Nombre">Edad Meses</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="edad_ges" id = 'edad_ges'
        value="{{$incomeedit9}}">
    </div>
    
    <div class="form-group">
        <label for="Nombre">Telefono</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="telefono_" id = 'telefono_'
        value="{{$incomeedit1->telefono_}}">
    </div>

    <div class="form-group">
        <label for="Nombre">Etnia</label> {{-- el isset pregunta si el archivo esta seleccionado lo muestre sino no muestra nada por eso las comillas vacias al final --}}
        <input class="form-control" type="text" name="nom_grupo_" id = 'nom_grupo_'
        value="{{$incomeedit1->nom_grupo_}}">
    </div>


   

                <input class="btn btn-success" type="submit" value="enviar">
                <a  class="btn btn-primary" href="{{url('sivigila')}}" class="btn  btn-success"> REGRESAR</a>


    </form>

    
       
    
    @stop
            
    @section('css')
        <link rel="stylesheet" href="/css/admin_custom.css">
    @stop
    
    @section('js')
        <script> console.log('Hi!'); </script>
    @stop
    
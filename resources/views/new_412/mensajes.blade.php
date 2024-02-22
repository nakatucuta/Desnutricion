@if(Session::has('mensaje'))
<div class="alert alert-primary custom-alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{Session::get('mensaje')}}
</div>
@endif

@if(Session::has('error1'))
<div class="alert alert-danger custom-alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {{Session::get('error1')}}
</div>
@endif

@if(count($errors)>0)
<div class="alert alert-danger custom-alert">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

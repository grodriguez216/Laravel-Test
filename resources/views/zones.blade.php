@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')
  
@endsection

@section('content')
  
  <div class="container-fluid pt-3">
    
    <div class="row">
      
      <div class="col-12 text-center my-3">
        <h1 class="page-title">Zonas de cobro</h1>
        <hr>
      </div>
      
      <div class="col-md-6 mb-3">
        <h3 class="text-center pb-3">Zonas Existentes</h3>
        <ul class="list-group w-100 px-5">
          @if ($zones->isNotEmpty())
            @foreach ($zones as $zone)
              
              <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $zone->name }}
                <a class="badge badge-danger badge- px-2 py-1" href="zonas/borrar/{{ $zone->id }}">borrar</a>
              </li>
              
            @endforeach
          @else
            <h1 class="text-center pt-5 mt-5">No hay zonas.</h1>
          @endif
        </ul>
      </div>
      
      <div class="col-md-6 mb-3">
        <h3 class="text-center pb-3">Nueva Zona</h3>
        <form method="POST" action="/zonas/agregar">
          {{ csrf_field() }}
          <fieldset>
            <div class="form-row  justify-content-center mb-3">
              <div class="col-md-8 pb-3">
                <input type="text" class="form-control" name="name" placeholder="Nombre de la zona" required>
              </div>
            </div>
          </fieldset>
          <div class="form-row justify-content-center">
            <button type="submit" class="btn btn-outline-success px-5">Agregar Zona</button>
          </div>
        </form>
      </div><!-- /.col -->
      
    </div>
  </div>
  
@endsection

@section('scripts')
  <script type="text/javascript">
  window.onload = function()
  {
    window.nicecify_money();
    toggle('loader', false );
  };
  
  function nicecify_money()
  {
    var items = document.getElementsByClassName('money');
    for (var i = 0; i < items.length; i++)
    items[i].innerHTML = nicecify( items[i].innerHTML );
  }
  
  function redir(id)
  {
    toggle('loader', true );
    window.location = '/clientes/perfil/' + id;
  }
  
  </script>
  
@endsection

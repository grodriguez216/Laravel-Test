@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')
  
@endsection

@section('content')
  
  <div class="container-fluid">
    
    <div class="row">
      
      <div class="col-12 text-center mb-3">
        <h1 class="page-title">Zonas de cobro</h1>
        <hr>
      </div>
      
      <div class="col-md-6 mb-3">
        <ul class="list-group w-100">
          
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

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
        <h1 class="page-title">{{ $user->name}}</h1>
        <hr>
      </div>
      
      <div class="col-md-6 mb-3">
        
        <h3 class="text-center pb-3">Zonas asociadas</h3>
        
        <table class="table">
          <tbody>
            @foreach ($user_zones as $zone)
              <tr>
                <th scope="row">{{ $zone->id }}</th>
                <td>{{ $zone->name }}</td>
                <td class="text-center">
                  <a class="btn btn-danger mx-1" href="/usuarios/update/{{$user->id}}/D/{{$zone->id}}">Eliminar</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        
      </div> {{-- col-6 --}}
      
      <div class="col-md-6 mb-3">
        <h3 class="text-center pb-3">Todas las Zonas</h3>
        <table class="table">
          <tbody>
            @foreach ($all_zones as $zone)
              <tr>
                <th scope="row">{{ $zone->id }}</th>
                <td>{{ $zone->name }}</td>
                <td class="text-center">
                  <a class="btn btn-outline-danger mx-1" href="/usuarios/update/{{$user->id}}/A/{{$zone->id}}/">Agregar</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div> {{-- col-6 --}}
      
      
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

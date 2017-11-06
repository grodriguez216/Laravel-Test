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
        <h1 class="page-title">Usuarios Cobradores</h1>
        <hr>
      </div>
      
      
      <div class="col-md-6 mb-3">
        
        <h3 class="text-center pb-3">Lista de Usuarios</h3>
        
        @if ($users->isNotEmpty())
          
          <table class="table">
            <thead class="thead-dark">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Telefono</th>
                <th scope="col" class="text-center">Opciones</th>
              </tr>
            </thead>
            <tbody>
              
              @foreach ($users as $user)
                <tr>
                  <th scope="row">{{ $user->id }}</th>
                  <td>{{ $user->name }}</td>
                  <td>{{ $user->email }}</td>
                  <td class="text-center">
                    <a class="btn btn-outline-danger mx-1" href="/usuarios/perfil/{{ $user->id}}">Editar Zonas</a>
                    <a class="btn btn-danger mx-1" href="/usuarios/borrar/"{{ $user->id}}>Eliminar</a>
                  </td>
                </tr>
                
              @endforeach
            </tbody>
          </table>
        @else
          <h1 class="text-center pt-5 mt-5">No hay cobros para hoy.</h1>
        @endif
        
        
        
      </div> {{-- col-6 --}}
      
      <div class="col-md-6 mb-3">
        <h3 class="text-center pb-3">Nuevo Usuario</h3>
        <form method="POST" action="/usuarios/agregar">
          {{ csrf_field() }}
          <fieldset>
            <div class="form-row  justify-content-center mb-3">
              <div class="col-md-8 pb-3">
                <input type="text" class="form-control" name="name" placeholder="Nombre" required>
              </div>
              <div class="col-md-8 pb-3">
                <input type="text" class="form-control" name="phone" placeholder="Telefono (contraseña)" required>
              </div>
            </div>
          </fieldset>
          <div class="form-row justify-content-center">
            <button type="submit" class="btn btn-outline-danger px-5">Agregar Usuario</button>
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
